<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductPrice extends Model
{
    use HasFactory;

    protected $fillable = [
        'product_id',
        'net_price_syp',
        'public_price_syp',
    ];

    protected function casts(): array
    {
        return [
            'net_price_syp'    => 'decimal:2',
            'public_price_syp' => 'decimal:2',
        ];
    }

    // ─── Relationships ────────────────────────────────────────────────────────

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}

