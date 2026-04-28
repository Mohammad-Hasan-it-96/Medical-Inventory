<?php

namespace App\Services;

use App\Models\AccountEntry;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Pharmacy;
use App\Models\Product;
use App\Models\StockMovement;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class OrderService
{
    public function __construct(
        protected StockService $stockService
    ) {}

    // ─── Create ───────────────────────────────────────────────────────────────

    /**
     * Create a new order with its line-items and calculated totals.
     *
     * Transaction flow:
     *   1. Validate all input data (pharmacy, items, products, quantities).
     *   2. Insert Order row (status = pending, temporary order_number).
     *   3. Update order_number → ORD-{year}-{5-digit-id}.
     *   4. Insert OrderItem rows, computing each line total.
     *   5. calculateTotals() and persist subtotal / discount / total.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function createOrder(array $data, ?int $repId = null): Order
    {
        // ── Validation ────────────────────────────────────────────────────────
        $validator = Validator::make($data, [
            'pharmacy_id'          => ['required', 'integer'],
            'discount'             => ['nullable', 'numeric', 'min:0'],
            'notes'                => ['nullable', 'string', 'max:1000'],
            'items'                => ['required', 'array', 'min:1'],
            'items.*.product_id'   => ['required', 'integer'],
            'items.*.quantity'     => ['required', 'integer', 'min:1'],
            'items.*.unit_price'   => ['required', 'numeric', 'min:0'],
            'items.*.discount'     => ['nullable', 'numeric', 'min:0'],
        ]);

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }

        $validated = $validator->validated();

        // Pharmacy must exist.
        if (! Pharmacy::where('id', $validated['pharmacy_id'])->exists()) {
            $validator->errors()->add('pharmacy_id', 'The selected pharmacy does not exist.');
            throw new ValidationException($validator);
        }

        // All products must exist — collect in one query for efficiency.
        $productIds    = collect($validated['items'])->pluck('product_id')->unique()->values();
        $foundProducts = Product::whereIn('id', $productIds)->pluck('id');
        $missing       = $productIds->diff($foundProducts);

        if ($missing->isNotEmpty()) {
            $validator->errors()->add('items', 'Product(s) not found: ' . $missing->implode(', '));
            throw new ValidationException($validator);
        }

        // ── Transaction ───────────────────────────────────────────────────────
        return DB::transaction(function () use ($validated, $repId) {

            // Step 1 — insert order with temporary number.
            $order = Order::create([
                'order_number' => 'TEMP-' . uniqid(),
                'pharmacy_id'  => $validated['pharmacy_id'],
                'rep_id'       => $repId,
                'status'       => Order::STATUS_PENDING,
                'discount'     => $validated['discount'] ?? 0,
                'subtotal'     => 0,
                'total'        => 0,
                'notes'        => $validated['notes'] ?? null,
            ]);

            // Step 2 — assign definitive order number.
            $order->order_number = 'ORD-' . now()->year . '-' . str_pad($order->id, 5, '0', STR_PAD_LEFT);
            $order->save();

            // Step 3 — insert line items.
            foreach ($validated['items'] as $item) {
                $itemDiscount = $item['discount'] ?? 0;
                $lineTotal    = max(0, ($item['quantity'] * $item['unit_price']) - $itemDiscount);

                OrderItem::create([
                    'order_id'   => $order->id,
                    'product_id' => $item['product_id'],
                    'quantity'   => $item['quantity'],
                    'unit_price' => $item['unit_price'],
                    'discount'   => $itemDiscount,
                    'total'      => $lineTotal,
                ]);
            }

            // Step 4 — recalculate and persist order totals.
            $totals = $this->calculateTotals($order->load('orderItems'));
            $order->update($totals);

            return $order->fresh(['orderItems']);
        });
    }

    // ─── Confirm ──────────────────────────────────────────────────────────────

    /**
     * Confirm a draft or pending order.
     *
     * Transaction flow:
     *   1. Guard: only draft|pending orders can be confirmed (idempotency check).
     *   2. Pre-check every item has sufficient stock (fail-fast, no mutations yet).
     *   3. Record TYPE_SALE stock movement for each line item.
     *   4. Post TYPE_DEBIT account entry (pharmacy now owes the order total).
     *   5. Set status → confirmed, confirmed_at → now().
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function confirmOrder(Order $order, ?int $userId = null): Order
    {
        if (! in_array($order->status, [Order::STATUS_DRAFT, Order::STATUS_PENDING])) {
            throw ValidationException::withMessages([
                'order' => "Cannot confirm an order with status '{$order->status}'.",
            ]);
        }

        return DB::transaction(function () use ($order, $userId) {

            $order->load('orderItems.product');

            // Pre-check all stock before any mutations.
            // Batch: single GROUP-BY query instead of one SUM query per item (avoids N+1).
            $productIds     = $order->orderItems->pluck('product_id')->unique();
            $stockTotals    = StockMovement::whereIn('product_id', $productIds)
                ->groupBy('product_id')
                ->selectRaw('product_id, SUM(quantity) as total_qty')
                ->pluck('total_qty', 'product_id');

            foreach ($order->orderItems as $item) {
                $available = (int) ($stockTotals[$item->product_id] ?? 0);
                if ($available < $item->quantity) {
                    $name = $item->product->name ?? "Product #{$item->product_id}";
                    throw ValidationException::withMessages([
                        'stock' => "Insufficient stock for '{$name}' (available: {$available}, requested: {$item->quantity}).",
                    ]);
                }
            }

            // Record sale movements — StockService applies the negative sign.
            foreach ($order->orderItems as $item) {
                $this->stockService->recordMovement([
                    'product_id'     => $item->product_id,
                    'type'           => StockMovement::TYPE_SALE,
                    'quantity'       => $item->quantity,
                    'reference_type' => Order::class,
                    'reference_id'   => $order->id,
                    'notes'          => "Confirmed order {$order->order_number}",
                    'created_by'     => $userId,
                ]);
            }

            // Debit entry: pharmacy's balance increases (they owe us the total).
            // Skip if total is zero to avoid polluting the ledger with empty entries.
            if ($order->total > 0) {
                AccountEntry::create([
                    'pharmacy_id' => $order->pharmacy_id,
                    'order_id'    => $order->id,
                    'payment_id'  => null,
                    'type'        => AccountEntry::TYPE_DEBIT,
                    'amount'      => $order->total,
                    'description' => "Order confirmed: {$order->order_number}",
                    'entry_date'  => now()->toDateString(),
                    'created_by'  => $userId,
                ]);
            }

            $order->update([
                'status'       => Order::STATUS_CONFIRMED,
                'confirmed_at' => now(),
            ]);

            return $order->fresh();
        });
    }

    // ─── Cancel ───────────────────────────────────────────────────────────────

    /**
     * Cancel any non-cancelled order.
     *
     * Transaction flow:
     *   1. Guard: cannot cancel an already-cancelled order.
     *   2. If order was CONFIRMED — reverse side-effects:
     *        a. Record TYPE_SALE_CANCEL movements to restore stock.
     *        b. Post TYPE_CREDIT account entry to reverse the debit.
     *   3. Draft/pending orders: no stock or accounting reversal needed.
     *   4. Set status → cancelled, cancelled_at → now().
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function cancelOrder(Order $order, ?int $userId = null): Order
    {
        if ($order->status === Order::STATUS_CANCELLED) {
            throw ValidationException::withMessages([
                'order' => 'This order is already cancelled.',
            ]);
        }

        return DB::transaction(function () use ($order, $userId) {

            if ($order->status === Order::STATUS_CONFIRMED) {
                $order->load('orderItems');

                // Restore stock for every line item.
                foreach ($order->orderItems as $item) {
                    $this->stockService->recordMovement([
                        'product_id'     => $item->product_id,
                        'type'           => StockMovement::TYPE_SALE_CANCEL,
                        'quantity'       => $item->quantity,
                        'reference_type' => Order::class,
                        'reference_id'   => $order->id,
                        'notes'          => "Cancelled order {$order->order_number}",
                        'created_by'     => $userId,
                    ]);
                }

                // Credit entry: reverses the debit posted on confirmation.
                // Only post if there was an original non-zero debit to reverse.
                if ($order->total > 0) {
                    AccountEntry::create([
                        'pharmacy_id' => $order->pharmacy_id,
                        'order_id'    => $order->id,
                        'payment_id'  => null,
                        'type'        => AccountEntry::TYPE_CREDIT,
                        'amount'      => $order->total,
                        'description' => "Order cancelled: {$order->order_number}",
                        'entry_date'  => now()->toDateString(),
                        'created_by'  => $userId,
                    ]);
                }
            }

            $order->update([
                'status'       => Order::STATUS_CANCELLED,
                'cancelled_at' => now(),
            ]);

            return $order->fresh();
        });
    }

    // ─── Helpers ──────────────────────────────────────────────────────────────

    /**
     * Recompute subtotal and total from the order's current line items.
     *
     *   subtotal = sum of item totals (each already net of item-level discount)
     *   total    = subtotal − order-level discount (minimum 0)
     *
     * Returns an array suitable for Order::update().
     */
    public function calculateTotals(Order $order): array
    {
        $items = $order->relationLoaded('orderItems')
            ? $order->orderItems
            : $order->orderItems()->get();

        $subtotal      = (float) $items->sum('total');
        $orderDiscount = (float) $order->discount;
        $total         = max(0.0, $subtotal - $orderDiscount);

        return [
            'subtotal' => round($subtotal, 2),
            'discount' => round($orderDiscount, 2),
            'total'    => round($total, 2),
        ];
    }
}

