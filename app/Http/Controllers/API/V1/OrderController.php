<?php

namespace App\Http\Controllers\API\V1;

use App\Http\Controllers\API\BaseController;
use App\Http\Resources\OrderResource;
use App\Models\Order;
use App\Services\OrderService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\ValidationException;

class OrderController extends BaseController
{
    public function __construct(protected OrderService $orderService) {}

    // ─────────────────────────────────────────────────────────────────────────
    //  List
    // ─────────────────────────────────────────────────────────────────────────

    /**
     * GET /api/v1/orders
     *
     * Filters (all optional):
     *   ?status=pending|confirmed|cancelled|draft
     *   ?pharmacy_id=1
     *   ?from=Y-m-d   – created_at ≥
     *   ?to=Y-m-d     – created_at ≤
     *   ?page=1
     *
     * Role rules:
     *   rep   → only their orders
     *   admin → all orders
     */
    public function index(Request $request): JsonResponse
    {
        $user  = auth()->user();
        $query = Order::with(['pharmacy', 'rep'])
            ->latest();

        if ($user->role === 'rep') {
            $query->forRep($user->id);
        }

        if ($status = $request->input('status')) {
            $query->status($status);
        }

        if ($pharmacyId = $request->input('pharmacy_id')) {
            $query->where('pharmacy_id', $pharmacyId);
        }

        if ($from = $request->input('from')) {
            $query->whereDate('created_at', '>=', $from);
        }

        if ($to = $request->input('to')) {
            $query->whereDate('created_at', '<=', $to);
        }

        $orders = $query->paginate(15);

        return $this->sendResponse(
            OrderResource::collection($orders),
            'Orders retrieved successfully.'
        );
    }

    // ─────────────────────────────────────────────────────────────────────────
    //  Create
    // ─────────────────────────────────────────────────────────────────────────

    /**
     * POST /api/v1/orders
     *
     * Body:
     * {
     *   "pharmacy_id": 1,
     *   "items": [
     *     { "product_id": 10, "quantity": 3, "unit_price": 500, "discount": 0 }
     *   ],
     *   "discount": 0,
     *   "notes": ""
     * }
     */
    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'pharmacy_id'           => 'required|integer|exists:pharmacies,id',
            'discount'              => 'nullable|numeric|min:0',
            'notes'                 => 'nullable|string|max:1000',
            'items'                 => 'required|array|min:1',
            'items.*.product_id'    => 'required|integer|exists:products,id',
            'items.*.quantity'      => 'required|integer|min:1',
            'items.*.unit_price'    => 'required|numeric|min:0',
            'items.*.discount'      => 'nullable|numeric|min:0',
        ]);

        $user  = auth()->user();

        // Rep users can only create orders for pharmacies assigned to them.
        if ($user->isRep()) {
            $pharmacy = \App\Models\Pharmacy::find($data['pharmacy_id']);
            if (! $pharmacy || $pharmacy->rep_id !== $user->id) {
                return $this->sendError(
                    'Forbidden: you can only create orders for your assigned pharmacies.',
                    [],
                    403
                );
            }
        }

        // Rep users automatically become the rep for this order.
        $repId = $user->isRep() ? $user->id : ($data['rep_id'] ?? null);

        try {
            $order = $this->orderService->createOrder($data, $repId);
        } catch (\Exception $e) {
            return $this->sendError('Failed to create order: ' . $e->getMessage(), [], 500);
        }

        return $this->sendResponse(
            new OrderResource($order->load(['pharmacy', 'orderItems.product'])),
            'Order created successfully.'
        );
    }

    // ─────────────────────────────────────────────────────────────────────────
    //  Show
    // ─────────────────────────────────────────────────────────────────────────

    /**
     * GET /api/v1/orders/{order}
     */
    public function show(Order $order): JsonResponse
    {
        if (Gate::denies('view', $order)) {
            return $this->sendError('Forbidden.', [], 403);
        }

        return $this->sendResponse(
            new OrderResource($order->load(['pharmacy', 'rep', 'orderItems.product'])),
            'Order retrieved successfully.'
        );
    }

    // ─────────────────────────────────────────────────────────────────────────
    //  Confirm
    // ─────────────────────────────────────────────────────────────────────────

    /**
     * POST /api/v1/orders/{order}/confirm
     */
    public function confirm(Order $order): JsonResponse
    {
        if (Gate::denies('confirm', $order)) {
            return $this->sendError('Forbidden.', [], 403);
        }

        try {
            $order = $this->orderService->confirmOrder($order, auth()->id());
        } catch (ValidationException $e) {
            return $this->sendError($e->getMessage(), $e->errors(), 422);
        } catch (\InvalidArgumentException $e) {
            return $this->sendError($e->getMessage(), [], 400);
        }

        return $this->sendResponse(
            new OrderResource($order->load(['pharmacy', 'orderItems.product'])),
            'Order confirmed successfully.'
        );
    }

    // ─────────────────────────────────────────────────────────────────────────
    //  Cancel
    // ─────────────────────────────────────────────────────────────────────────

    /**
     * POST /api/v1/orders/{order}/cancel
     */
    public function cancel(Order $order): JsonResponse
    {
        if (Gate::denies('cancel', $order)) {
            return $this->sendError('Forbidden.', [], 403);
        }

        try {
            $order = $this->orderService->cancelOrder($order, auth()->id());
        } catch (ValidationException $e) {
            return $this->sendError($e->getMessage(), $e->errors(), 422);
        }

        return $this->sendResponse(
            new OrderResource($order->load(['pharmacy', 'orderItems.product'])),
            'Order cancelled successfully.'
        );
    }
}

