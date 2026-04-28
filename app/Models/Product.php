<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Product extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * LEGACY NOTE — products.quantity
     * ---------------------------------
     * The `quantity` column predates the stock-movement system and is no longer
     * the source of truth for on-hand stock.  All stock levels must be read from
     * the `stock_movements` table (via StockService::getCurrentStock() or the
     * stockMovements relationship).
     *
     * The column is kept to avoid a destructive migration and to preserve
     * compatibility with old Excel imports that still populate it.
     * DO NOT read `$product->quantity` for business logic — use `currentStock()`
     * or StockService instead.  DO NOT write to it from forms or controllers.
     */
    protected $fillable = [
        'user_id',
        'company_id',
        'name',
        'barcode',
        'unit',
        'form',
        'details',
        'price',
        // 'quantity' intentionally omitted — legacy column, read-only via import compat.
        'min_stock',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'price'     => 'decimal:2',
            // quantity is cast for legacy reads only; do not rely on it for stock logic.
            'quantity'  => 'integer',
            'min_stock' => 'integer',
            'is_active' => 'boolean',
        ];
    }

    // ─── Stock helper ─────────────────────────────────────────────────────────

    /**
     * Return live on-hand stock from stock_movements (single aggregate query).
     * Prefer passing a pre-loaded stock map from the controller (batch query)
     * over calling this per-instance to avoid N+1.
     */
    public function currentStock(): int
    {
        return (int) $this->stockMovements()->sum('quantity');
    }

    // ─── Price helpers (delegates to productPrice relationship) ───────────────

    public function getNetPriceSypAttribute(): float
    {
        return (float) ($this->productPrice?->net_price_syp ?? 0);
    }

    public function getPublicPriceSypAttribute(): float
    {
        return (float) ($this->productPrice?->public_price_syp ?? 0);
    }

    // ─── Relationships ────────────────────────────────────────────────────────

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function productPrice()
    {
        return $this->hasOne(ProductPrice::class);
    }

    public function orderItems()
    {
        return $this->hasMany(OrderItem::class);
    }

    public function stockMovements()
    {
        return $this->hasMany(StockMovement::class);
    }

    // ─── Scopes ───────────────────────────────────────────────────────────────

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function scopeSearch(Builder $query, string $term): Builder
    {
        return $query->where(function (Builder $q) use ($term) {
            $q->where('name', 'like', "%{$term}%")
              ->orWhere('barcode', 'like', "%{$term}%")
              ->orWhere('details', 'like', "%{$term}%");
        });
    }
}
