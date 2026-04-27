<?php

namespace App\Services;

use App\Models\StockMovement;

class StockService
{
    /**
     * Return the current on-hand stock level for a product.
     *
     * Stock is tracked as signed integers in stock_movements.quantity:
     *   positive → stock coming IN  (opening, purchase, sale_cancel, return_in, positive adjustment)
     *   negative → stock going OUT  (sale, return_out, negative adjustment)
     *
     * So the current stock is simply the SUM of all quantity values.
     */
    public function getCurrentStock(int $productId): int
    {
        return (int) StockMovement::where('product_id', $productId)
            ->sum('quantity');
    }

    /**
     * Check whether a product has at least $requiredQuantity units available.
     */
    public function hasEnoughStock(int $productId, int $requiredQuantity): bool
    {
        return $this->getCurrentStock($productId) >= $requiredQuantity;
    }

    /**
     * Persist a stock movement and apply the correct sign to the quantity.
     *
     * Sign rules enforced here so callers never have to think about signs:
     *   - sale, return_out           → always stored as NEGATIVE (stock leaves)
     *   - opening, purchase,
     *     sale_cancel, return_in     → always stored as POSITIVE (stock arrives)
     *   - adjustment                 → stored as-is; caller passes +/- value
     *
     * @param  string      $type          One of the enum values in stock_movements.type
     * @param  int         $quantity      Unsigned magnitude (or signed for adjustment)
     */
    public function recordMovement(
        int     $productId,
        string  $type,
        int     $quantity,
        ?string $referenceType = null,
        ?int    $referenceId   = null,
        ?string $notes         = null,
        ?int    $createdBy     = null
    ): StockMovement {
        $signedQuantity = match ($type) {
            'sale', 'return_out'                                    => -abs($quantity),
            'opening', 'purchase', 'sale_cancel', 'return_in'      => abs($quantity),
            'adjustment'                                            => $quantity,
            default => throw new \InvalidArgumentException("Unknown stock movement type: [{$type}]"),
        };

        return StockMovement::create([
            'product_id'     => $productId,
            'type'           => $type,
            'quantity'       => $signedQuantity,
            'reference_type' => $referenceType,
            'reference_id'   => $referenceId,
            'notes'          => $notes,
            'created_by'     => $createdBy,
        ]);
    }
}

