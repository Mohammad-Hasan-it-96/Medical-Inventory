<?php

namespace App\Http\Controllers\API\V1;

use App\Http\Controllers\API\BaseController;
use App\Http\Resources\PharmacyResource;
use App\Models\Pharmacy;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PharmacyController extends BaseController
{
    /**
     * GET /api/v1/pharmacies
     *
     * Filters (all optional):
     *   ?search=term          – name, phone, or area
     *   ?updated_after=Y-m-d  – incremental sync support
     *   ?page=1
     *
     * Role rules:
     *   rep   → only their assigned pharmacies
     *   admin → all pharmacies
     */
    public function index(Request $request): JsonResponse
    {
        $user  = auth()->user();
        $query = Pharmacy::with('rep')->active();

        if ($user->role === 'rep') {
            $query->forRep($user->id);
        }

        if ($search = $request->input('search')) {
            $query->search($search);
        }

        if ($updatedAfter = $request->input('updated_after')) {
            $query->where('updated_at', '>', $updatedAfter);
        }

        $pharmacies = $query->orderBy('name')->paginate(20);

        return $this->sendResponse(
            PharmacyResource::collection($pharmacies),
            'Pharmacies retrieved successfully.'
        );
    }
}
