<?php

namespace App\Services;

use App\Models\AccountEntry;
use App\Models\Order;
use App\Models\OrderItem;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class OrderService
{
    public function __construct(
        protected StockService $stockService
    ) {}

    // ─────────────────────────────────────────────────────────────────────────
    //  Create
    // ─────────────────────────────────────────────────────────────────────────

    /**
     * Create a new order with its line-items and calculated totals.
     *
     * Expected $data keys:
     *   pharmacy_id  (int)
     *   items        (array of {product_id, quantity, unit_price, discount?})
     *   discount     (float, optional) – order-level discount
     *   rep_id       (int,   optional) – overridden by $repId parameter
     *   notes        (string,optional)
     *
     * Transaction flow:
     *   1. Insert Order row (placeholder number, status = pending).
     *   2. Update order_number to ORD-{year}-{5-digit-id} using the new ID.
     *   3. Insert OrderItem rows, computing each line total.
     *   4. calculateTotals() → update subtotal / total on the order.
     */
    public function createOrder(array $data, ?int $repId = null): Order
    {
        return DB::transaction(function () use ($data, $repId) {

            // Step 1 – create the order with a temporary placeholder number.
            $order = Order::create([
                'order_number' => 'TEMP-' . uniqid(),
                'pharmacy_id'  => $data['pharmacy_id'],
                'rep_id'       => $repId ?? $data['rep_id'] ?? null,
                'status'       => 'pending',
                'discount'     => $data['discount'] ?? 0,
                'subtotal'     => 0,
                'total'        => 0,
                'notes'        => $data['notes'] ?? null,
            ]);

            // Step 2 – set the definitive order number now that we have the ID.
            $order->order_number = 'ORD-' . date('Y') . '-' . str_pad($order->id, 5, '0', STR_PAD_LEFT);
            $order->save();

            // Step 3 – insert line items.
            foreach ($data['items'] as $item) {
                $itemDiscount = $item['discount'] ?? 0;
                $lineTotal    = ($item['quantity'] * $item['unit_price']) - $itemDiscount;

                OrderItem::create([
                    'order_id'   => $order->id,
                    'product_id' => $item['product_id'],
                    'quantity'   => $item['quantity'],
                    'unit_price' => $item['unit_price'],
                    'discount'   => $itemDiscount,
                    'total'      => max(0, $lineTotal),
                ]);
            }

            // Step 4 – recalculate and persist order totals.
            $totals = $this->calculateTotals($order->load('orderItems'));
            $order->update($totals);

            return $order->fresh(['orderItems']);
        });
    }

    // ─────────────────────────────────────────────────────────────────────────
    //  Confirm
    // ─────────────────────────────────────────────────────────────────────────

    /**
     * Confirm a pending/draft order.
     *
     * Transaction flow:
     *   1. Guard: only draft|pending orders may be confirmed.
     *   2. Pre-check all items have sufficient stock (fail-fast before mutations).
     *   3. Record a 'sale' stock movement for every line item.
     *   4. Post a 'debit' account_entry (pharmacy now owes this amount).
     *   5. Update order status → confirmed + confirmed_at timestamp.
     */
    public function confirmOrder(Order $order, ?int $userId = null): Order
    {
        if (! in_array($order->status, ['draft', 'pending'])) {
            throw ValidationException::withMessages([
                'order' => "Cannot confirm an order with status '{$order->status}'.",
            ]);
        }

        return DB::transaction(function () use ($order, $userId) {

            $order->load('orderItems.product');

            // Pre-check all stock before touching anything.
            foreach ($order->orderItems as $item) {
                if (! $this->stockService->hasEnoughStock($item->product_id, $item->quantity)) {
                    $name = $item->product->name ?? "Product #{$item->product_id}";
                    throw ValidationException::withMessages([
                        'stock' => "Insufficient stock for '{$name}'.",
                    ]);
                }
            }

            // Deduct stock for every line item.
            foreach ($order->orderItems as $item) {
                $this->stockService->recordMovement(
                    productId:     $item->product_id,
                    type:          'sale',
                    quantity:      $item->quantity,
                    referenceType: Order::class,
                    referenceId:   $order->id,
                    notes:         "Confirmed order {$order->order_number}",
                    createdBy:     $userId,
                );
            }

            // Debit entry: pharmacy balance increases (they owe us the total).
            AccountEntry::create([
                'pharmacy_id' => $order->pharmacy_id,
                'order_id'    => $order->id,
                'payment_id'  => null,
                'type'        => 'debit',
                'amount'      => $order->total,
                'description' => "Order confirmed: {$order->order_number}",
                'entry_date'  => now()->toDateString(),
                'created_by'  => $userId,
            ]);

            $order->update([
                'status'       => 'confirmed',
                'confirmed_at' => now(),
            ]);

            return $order->fresh();
        });
    }

    // ─────────────────────────────────────────────────────────────────────────
    //  Cancel
    // ─────────────────────────────────────────────────────────────────────────

    /**
     * Cancel an order.
     *
     * Transaction flow:
     *   1. Guard: already-cancelled orders cannot be cancelled again.
     *   2. If the order was CONFIRMED, reverse all side-effects:
     *        a. Record 'sale_cancel' movements to restore stock.
     *        b. Post a 'credit' account_entry to reverse the debit.
     *   3. Draft/pending orders require no stock or accounting reversal.
     *   4. Update order status → cancelled + cancelled_at timestamp.
     */
    public function cancelOrder(Order $order, ?int $userId = null): Order
    {
        if ($order->status === 'cancelled') {
            throw ValidationException::withMessages([
                'order' => 'This order is already cancelled.',
            ]);
        }

        return DB::transaction(function () use ($order, $userId) {

            if ($order->status === 'confirmed') {
                $order->load('orderItems');

                // Restore stock for every line item.
                foreach ($order->orderItems as $item) {
                    $this->stockService->recordMovement(
                        productId:     $item->product_id,
                        type:          'sale_cancel',
                        quantity:      $item->quantity,
                        referenceType: Order::class,
                        referenceId:   $order->id,
                        notes:         "Cancelled order {$order->order_number}",
                        createdBy:     $userId,
                    );
                }

                // Reverse the debit with a matching credit entry.
                AccountEntry::create([
                    'pharmacy_id' => $order->pharmacy_id,
                    'order_id'    => $order->id,
                    'payment_id'  => null,
                    'type'        => 'credit',
                    'amount'      => $order->total,
                    'description' => "Order cancelled: {$order->order_number}",
                    'entry_date'  => now()->toDateString(),
                    'created_by'  => $userId,
                ]);
            }

            $order->update([
                'status'       => 'cancelled',
                'cancelled_at' => now(),
            ]);

            return $order->fresh();
        });
    }

    // ─────────────────────────────────────────────────────────────────────────
    //  Helpers
    // ─────────────────────────────────────────────────────────────────────────

    /**
     * Recompute subtotal and total from the order's current line items.
     *
     * subtotal = sum of every item's already-computed total (after item-level discounts)
     * discount = order-level discount (stored on the order record, not recalculated here)
     * total    = subtotal − order discount  (minimum 0)
     *
     * Returns an array suitable for Order::update().
     */
    public function calculateTotals(Order $order): array
    {
        $items = $order->relationLoaded('orderItems')
            ? $order->orderItems
            : $order->orderItems()->get();

        $subtotal      = $items->sum('total');
        $orderDiscount = (float) $order->discount;
        $total         = max(0, $subtotal - $orderDiscount);

        return [
            'subtotal' => round($subtotal, 2),
            'discount' => round($orderDiscount, 2),
            'total'    => round($total, 2),
        ];
    }
}

