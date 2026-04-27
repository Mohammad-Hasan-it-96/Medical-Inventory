<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Product extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'user_id',
        'company_id',
        'name',
        'barcode',
        'unit',
        'form',
        'details',
        'price',
        'quantity',
        'min_stock',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'price'     => 'decimal:2',
            'quantity'  => 'integer',
            'min_stock' => 'integer',
            'is_active' => 'boolean',
        ];
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
