<?php

namespace App\Http\Controllers\API\V1;

use App\Http\Controllers\API\BaseController;
use App\Http\Resources\ProductResource;
use App\Models\Product;
use App\Models\StockMovement;
use App\Services\StockService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProductController extends BaseController
{
    public function __construct(protected StockService $stockService) {}

    /**
     * GET /api/v1/products
     *
     * Filters (all optional):
     *   ?search=term          – name or barcode
     *   ?company_id=1         – filter by company
     *   ?updated_after=Y-m-d  – incremental sync support
     *   ?with_stock=1         – append current_stock; resolved via a single batch aggregate query (GROUP BY product_id)
     *   ?page=1               – paginated (15 per page)
     */
    public function index(Request $request): JsonResponse
    {
        $query = Product::with(['company', 'productPrice'])
            ->active();

        if ($search = $request->input('search')) {
            $query->search($search);
        }

        if ($companyId = $request->input('company_id')) {
            $query->where('company_id', $companyId);
        }

        if ($updatedAfter = $request->input('updated_after')) {
            $query->where('updated_at', '>', $updatedAfter);
        }

        $products = $query->orderBy('name')->paginate(15);

        // Append current_stock only when explicitly requested.
        // Use a single batch GROUP-BY query to avoid N+1 (one SUM per product).
        if ($request->boolean('with_stock')) {
            $ids    = $products->getCollection()->pluck('id');
            $stocks = StockMovement::whereIn('product_id', $ids)
                ->groupBy('product_id')
                ->selectRaw('product_id, SUM(quantity) as total')
                ->pluck('total', 'product_id');

            $products->getCollection()->transform(function (Product $product) use ($stocks) {
                $product->setAttribute('current_stock', (int) ($stocks[$product->id] ?? 0));
                return $product;
            });
        }

        return $this->sendResponse(
            ProductResource::collection($products),
            'Products retrieved successfully.'
        );
    }
}

