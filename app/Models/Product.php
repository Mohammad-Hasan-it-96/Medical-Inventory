<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'details',
        'price',
        'quantity',
        'user_id',
        // Add other fields as needed
    ];

    /**
     * Get the user that owns the product.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
