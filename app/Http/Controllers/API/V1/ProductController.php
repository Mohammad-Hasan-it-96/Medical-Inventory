<?php

namespace App\Http\Controllers\API\V1;

use App\Http\Controllers\API\BaseController;
use App\Http\Resources\ProductResource;
use App\Models\Product;
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
     *   ?with_stock=1         – append current_stock to each product (costs 1 query/item)
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

        // Append current_stock only when explicitly requested to avoid N+1 by default.
        if ($request->boolean('with_stock')) {
            $products->getCollection()->transform(function (Product $product) {
                $product->setAttribute(
                    'current_stock',
                    $this->stockService->getCurrentStock($product->id)
                );
                return $product;
            });
        }

        return $this->sendResponse(
            ProductResource::collection($products),
            'Products retrieved successfully.'
        );
    }
}

