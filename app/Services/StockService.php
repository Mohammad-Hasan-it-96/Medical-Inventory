<?php

namespace App\Services;

use App\Models\Product;
use App\Models\StockMovement;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class StockService
{
    /**
     * Movement types that add stock (quantity stored as positive).
     */
    private const INBOUND_TYPES = [
        StockMovement::TYPE_OPENING,
        StockMovement::TYPE_PURCHASE,
        StockMovement::TYPE_SALE_CANCEL,
        StockMovement::TYPE_RETURN_IN,
    ];

    /**
     * Movement types that remove stock (quantity stored as negative).
     */
    private const OUTBOUND_TYPES = [
        StockMovement::TYPE_SALE,
        StockMovement::TYPE_RETURN_OUT,
    ];

    /**
     * All allowed type values.
     */
    private const ALL_TYPES = [
        StockMovement::TYPE_OPENING,
        StockMovement::TYPE_PURCHASE,
        StockMovement::TYPE_SALE,
        StockMovement::TYPE_SALE_CANCEL,
        StockMovement::TYPE_ADJUSTMENT,
        StockMovement::TYPE_RETURN_IN,
        StockMovement::TYPE_RETURN_OUT,
    ];

    // ─────────────────────────────────────────────────────────────────────────

    /**
     * Calculate current on-hand stock for a product.
     *
     * Sign is enforced by recordMovement(), so a plain SUM is correct.
     */
    public function getCurrentStock(int $productId): int
    {
        return (int) StockMovement::where('product_id', $productId)
            ->sum('quantity');
    }

    /**
     * Check whether a product has at least $required units available.
     */
    public function hasEnoughStock(int $productId, int $required): bool
    {
        return $this->getCurrentStock($productId) >= $required;
    }

    /**
     * Validate, sign, and persist a stock movement.
     *
     * Expected keys in $data:
     *   - product_id       int      required
     *   - type             string   required — one of TYPE_* constants
     *   - quantity         int      required — non-zero; sign applied automatically
     *   - reference_type   string   nullable
     *   - reference_id     int      nullable
     *   - notes            string   nullable
     *   - created_by       int      nullable — user id
     *
     * Sign rules:
     *   inbound types  → stored as +abs(quantity)
     *   outbound types → stored as -abs(quantity)
     *   adjustment     → stored as-is (caller decides sign)
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function recordMovement(array $data): StockMovement
    {
        // ── 1. Structural / rule validation ───────────────────────────────────
        $validator = Validator::make($data, [
            'product_id'     => ['required', 'integer'],
            'type'           => ['required', 'string', 'in:' . implode(',', self::ALL_TYPES)],
            'quantity'       => ['required', 'integer', 'not_in:0'],
            'reference_type' => ['nullable', 'string', 'max:100'],
            'reference_id'   => ['nullable', 'integer'],
            'notes'          => ['nullable', 'string', 'max:1000'],
            'created_by'     => ['nullable', 'integer'],
        ]);

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }

        $validated = $validator->validated();

        // ── 2. Product existence ──────────────────────────────────────────────
        if (! Product::where('id', $validated['product_id'])->exists()) {
            $validator->errors()->add('product_id', 'The selected product does not exist.');
            throw new ValidationException($validator);
        }

        // ── 3. Apply directional sign ─────────────────────────────────────────
        $type     = $validated['type'];
        $quantity = (int) $validated['quantity'];

        $signedQuantity = match (true) {
            in_array($type, self::INBOUND_TYPES)  => abs($quantity),
            in_array($type, self::OUTBOUND_TYPES) => -abs($quantity),
            $type === StockMovement::TYPE_ADJUSTMENT => $quantity,
        };

        // ── 4. Persist ────────────────────────────────────────────────────────
        return StockMovement::create([
            'product_id'     => $validated['product_id'],
            'type'           => $type,
            'quantity'       => $signedQuantity,
            'reference_type' => $validated['reference_type'] ?? null,
            'reference_id'   => $validated['reference_id']   ?? null,
            'notes'          => $validated['notes']           ?? null,
            'created_by'     => $validated['created_by']      ?? null,
        ]);
    }
}

