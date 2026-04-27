<?php

namespace App\Http\Controllers\API\V1;

use App\Http\Controllers\API\BaseController;
use App\Models\Product;
use App\Services\StockService;
use Illuminate\Http\JsonResponse;

class StockController extends BaseController
{
    public function __construct(protected StockService $stockService) {}

    /**
     * GET /api/v1/products/{product}/stock
     *
     * Returns the current on-hand stock level for a product calculated
     * from all stock_movements records via StockService::getCurrentStock().
     */
    public function show(Product $product): JsonResponse
    {
        $quantity = $this->stockService->getCurrentStock($product->id);

        return $this->sendResponse([
            'product_id'   => $product->id,
            'product_name' => $product->name,
            'quantity'     => $quantity,
        ], 'Stock retrieved successfully.');
    }
}

