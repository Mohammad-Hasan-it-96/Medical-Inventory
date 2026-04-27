<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\API\BaseController;
use App\Models\Product;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Validator;

class ProductController extends BaseController
{
    /**
     * Display a paginated/filtered listing of products (JSON).
     *
     * GET /api/products?user_id=&per_page=
     */
    public function index(Request $request): JsonResponse
    {
        $query = Product::with('user:id,name');

        if ($userId = $request->input('user_id')) {
            $query->where('user_id', $userId);
        }

        $perPage  = (int) $request->input('per_page', 15);
        $products = $perPage > 0
            ? $query->paginate($perPage)
            : $query->get();

        return $this->sendResponse($products, 'Products retrieved successfully.');
    }

    /**
     * Store a newly created product (JSON).
     *
     * POST /api/products
     */
    public function store(Request $request): JsonResponse
    {
        if (! Gate::allows('create', Product::class)) {
            return $this->sendError('Forbidden.', ['error' => 'You do not have permission to create products.'], 403);
        }

        $validator = Validator::make($request->all(), [
            'name'     => 'required|string|max:255',
            'price'    => 'required|numeric|min:0',
            'details'  => 'required|string',
            'quantity' => 'required|integer|min:0',
        ]);

        if ($validator->fails()) {
            return $this->sendError('Validation Error.', $validator->errors(), 422);
        }

        $product          = new Product($validator->validated());
        $product->user_id = Auth::id();
        $product->save();

        $product->load('user:id,name');

        return $this->sendResponse($product, 'Product created successfully.');
    }

    /**
     * Display the specified product (JSON).
     *
     * GET /api/products/{id}
     */
    public function show(int $id): JsonResponse
    {
        $product = Product::with('user:id,name')->find($id);

        if (! $product) {
            return $this->sendError('Product not found.', [], 404);
        }

        return $this->sendResponse($product, 'Product retrieved successfully.');
    }

    /**
     * Update the specified product (JSON).
     *
     * PUT /api/products/{id}
     */
    public function update(Request $request, int $id): JsonResponse
    {
        $product = Product::find($id);

        if (! $product) {
            return $this->sendError('Product not found.', [], 404);
        }

        if (! Gate::allows('update', $product)) {
            return $this->sendError('Forbidden.', ['error' => 'You do not have permission to update this product.'], 403);
        }

        $validator = Validator::make($request->all(), [
            'name'     => 'required|string|max:255',
            'price'    => 'required|numeric|min:0',
            'details'  => 'required|string',
            'quantity' => 'required|integer|min:0',
        ]);

        if ($validator->fails()) {
            return $this->sendError('Validation Error.', $validator->errors(), 422);
        }

        $product->update($validator->validated());
        $product->load('user:id,name');

        return $this->sendResponse($product, 'Product updated successfully.');
    }

    /**
     * Delete the specified product (JSON).
     *
     * DELETE /api/products/{id}
     */
    public function destroy(int $id): JsonResponse
    {
        $product = Product::find($id);

        if (! $product) {
            return $this->sendError('Product not found.', [], 404);
        }

        if (! Gate::allows('delete', $product)) {
            return $this->sendError('Forbidden.', ['error' => 'You do not have permission to delete this product.'], 403);
        }

        $product->delete();

        return $this->sendResponse([], 'Product deleted successfully.');
    }
}
