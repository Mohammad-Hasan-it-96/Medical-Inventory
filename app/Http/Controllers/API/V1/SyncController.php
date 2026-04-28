<?php

namespace App\Http\Controllers\API\V1;

use App\Http\Controllers\API\BaseController;
use App\Http\Resources\CompanyResource;
use App\Http\Resources\PharmacyResource;
use App\Http\Resources\ProductPriceResource;
use App\Http\Resources\ProductResource;
use App\Models\Company;
use App\Models\Pharmacy;
use App\Models\Product;
use App\Models\ProductPrice;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;

class SyncController extends BaseController
{
    // ─── Constants ────────────────────────────────────────────────────────────

    private const DEFAULT_PER_PAGE = 100;
    private const MAX_PER_PAGE     = 500;

    // =========================================================================
    //  GET /api/v1/sync/bootstrap
    // =========================================================================

    /**
     * Full initial sync payload for a freshly-installed Flutter client.
     *
     * Query params (all optional):
     *   ?per_page=100              – items per page (max 500, default 100)
     *   ?companies_page=1          – page number for companies section
     *   ?products_page=1           – page number for products section
     *   ?pharmacies_page=1         – page number for pharmacies section
     *
     * Response:
     *   companies   – all active companies
     *   products    – all active products with embedded price data
     *   pharmacies  – active pharmacies (rep users see only their own)
     *   server_time – ISO-8601 timestamp to use as the next `updated_after`
     */
    public function bootstrap(Request $request): JsonResponse
    {
        $perPage = $this->resolvePerPage($request);
        $user    = $request->user();

        // ── Companies ──────────────────────────────────────────────────────
        $companies = Company::where('is_active', true)
            ->orderBy('updated_at')
            ->paginate($perPage, ['*'], 'companies_page');

        // ── Products with prices ───────────────────────────────────────────
        $products = Product::with('productPrice')
            ->active()
            ->orderBy('updated_at')
            ->paginate($perPage, ['*'], 'products_page');

        // ── Pharmacies (rep-scoped) ────────────────────────────────────────
        $pharmaciesQuery = Pharmacy::active()->with('rep');

        if ($user->hasRole('rep')) {
            $pharmaciesQuery->forRep($user->id);
        }

        $pharmacies = $pharmaciesQuery
            ->orderBy('updated_at')
            ->paginate($perPage, ['*'], 'pharmacies_page');

        return response()->json([
            'success' => true,
            'data'    => [
                'server_time'  => now()->toIso8601String(),
                'current_user' => [
                    'id'              => $user->id,
                    'name'            => $user->name,
                    'email'           => $user->email,
                    'role'            => $user->role,
                    'profile_picture' => $user->getProfilePictureUrl(),
                ],
                'companies'   => $this->section($request, $companies,  CompanyResource::class),
                'products'    => $this->section($request, $products,   ProductResource::class),
                'pharmacies'  => $this->section($request, $pharmacies, PharmacyResource::class),
            ],
            'message' => 'Bootstrap data retrieved successfully.',
        ]);
    }

    // =========================================================================
    //  GET /api/v1/sync/changes
    // =========================================================================

    /**
     * Incremental sync – returns only records that changed after a given point.
     *
     * Soft-deleted records are included (check `deleted_at != null` client-side
     * to remove them from the local store).
     *
     * Query params:
     *   updated_after (required)   – ISO-8601 or MySQL datetime string
     *   ?per_page=100              – items per page (max 500, default 100)
     *   ?companies_page=1
     *   ?products_page=1
     *   ?product_prices_page=1
     *   ?pharmacies_page=1
     */
    public function changes(Request $request): JsonResponse
    {
        $request->validate([
            'updated_after' => ['required', 'date'],
        ]);

        $perPage = $this->resolvePerPage($request);
        $after   = $request->input('updated_after');
        $user    = $request->user();

        // ── Companies (include trashed) ────────────────────────────────────
        $companies = Company::withTrashed()
            ->where('updated_at', '>', $after)
            ->orderBy('updated_at')
            ->paginate($perPage, ['*'], 'companies_page');

        // ── Products (include trashed, with price) ─────────────────────────
        $products = Product::withTrashed()
            ->with('productPrice')
            ->where('updated_at', '>', $after)
            ->orderBy('updated_at')
            ->paginate($perPage, ['*'], 'products_page');

        // ── Product prices (no soft-deletes on this model) ─────────────────
        $productPrices = ProductPrice::where('updated_at', '>', $after)
            ->orderBy('updated_at')
            ->paginate($perPage, ['*'], 'product_prices_page');

        // ── Pharmacies (include trashed, rep-scoped) ───────────────────────
        $pharmaciesQuery = Pharmacy::withTrashed()
            ->with('rep')
            ->where('updated_at', '>', $after);

        if ($user->hasRole('rep')) {
            $pharmaciesQuery->where('rep_id', $user->id);
        }

        $pharmacies = $pharmaciesQuery
            ->orderBy('updated_at')
            ->paginate($perPage, ['*'], 'pharmacies_page');

        return response()->json([
            'success' => true,
            'data'    => [
                'companies'      => $this->section($request, $companies,     CompanyResource::class),
                'products'       => $this->section($request, $products,      ProductResource::class),
                'product_prices' => $this->section($request, $productPrices, ProductPriceResource::class),
                'pharmacies'     => $this->section($request, $pharmacies,    PharmacyResource::class),
                'server_time'    => now()->toIso8601String(),
            ],
            'message' => 'Changes retrieved successfully.',
        ]);
    }

    // =========================================================================
    //  Helpers
    // =========================================================================

    /**
     * Resolve the per_page value, clamped between 1 and MAX_PER_PAGE.
     */
    private function resolvePerPage(Request $request): int
    {
        return min(
            max(1, (int) $request->input('per_page', self::DEFAULT_PER_PAGE)),
            self::MAX_PER_PAGE
        );
    }

    /**
     * Serialize a paginator into a section array:
     *
     *   {
     *     "data": [...resource items...],
     *     "meta": { current_page, last_page, per_page, total }
     *   }
     *
     * @param  class-string<\Illuminate\Http\Resources\Json\JsonResource>  $resourceClass
     */
    private function section(
        Request              $request,
        LengthAwarePaginator $paginator,
        string               $resourceClass,
    ): array {
        return [
            'data' => $resourceClass::collection($paginator->items())->resolve($request),
            'meta' => [
                'current_page' => $paginator->currentPage(),
                'last_page'    => $paginator->lastPage(),
                'per_page'     => $paginator->perPage(),
                'total'        => $paginator->total(),
            ],
        ];
    }
}

