<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

class StockMovement extends Model
{
    use HasFactory;

    // ─── Type constants ───────────────────────────────────────────────────────
    const TYPE_OPENING     = 'opening';
    const TYPE_PURCHASE    = 'purchase';
    const TYPE_SALE        = 'sale';
    const TYPE_SALE_CANCEL = 'sale_cancel';
    const TYPE_ADJUSTMENT  = 'adjustment';
    const TYPE_RETURN_IN   = 'return_in';
    const TYPE_RETURN_OUT  = 'return_out';

    protected $fillable = [
        'product_id',
        'type',
        'quantity',
        'reference_type',
        'reference_id',
        'notes',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'quantity'     => 'integer',
            'reference_id' => 'integer',
        ];
    }

    // ─── Relationships ────────────────────────────────────────────────────────

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    // ─── Scopes ───────────────────────────────────────────────────────────────

    public function scopeForProduct(Builder $query, int $productId): Builder
    {
        return $query->where('product_id', $productId);
    }

    public function scopeType(Builder $query, string $type): Builder
    {
        return $query->where('type', $type);
    }
}
