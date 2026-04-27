<?php

namespace App\Http\Controllers\API\V1;

use App\Http\Controllers\API\BaseController;
use App\Http\Resources\CompanyResource;
use App\Models\Company;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CompanyController extends BaseController
{
    /**
     * GET /api/v1/companies
     * Return all active companies (used to populate dropdowns in Flutter).
     */
    public function index(Request $request): JsonResponse
    {
        $companies = Company::where('is_active', true)
            ->orderBy('name')
            ->get();

        return $this->sendResponse(
            CompanyResource::collection($companies),
            'Companies retrieved successfully.'
        );
    }
}

