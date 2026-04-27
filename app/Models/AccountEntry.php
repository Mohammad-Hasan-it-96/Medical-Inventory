<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AccountEntry extends Model
{
    use HasFactory;

    protected $fillable = [
        'pharmacy_id',
        'order_id',
        'payment_id',
        'type',
        'amount',
        'description',
        'entry_date',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'amount'     => 'decimal:2',
            'entry_date' => 'date',
        ];
    }

    // ─── Relationships ────────────────────────────────────────────────────────

    public function pharmacy()
    {
        return $this->belongsTo(Pharmacy::class);
    }

    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function payment()
    {
        return $this->belongsTo(Payment::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}

