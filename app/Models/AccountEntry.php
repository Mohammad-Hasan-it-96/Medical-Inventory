<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

class AccountEntry extends Model
{
    use HasFactory;

    // ─── Type constants ───────────────────────────────────────────────────────
    const TYPE_DEBIT  = 'debit';   // pharmacy owes us (sale posted)
    const TYPE_CREDIT = 'credit';  // we received payment / issued credit

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

    // ─── Scopes ───────────────────────────────────────────────────────────────

    public function scopeForPharmacy(Builder $query, int $pharmacyId): Builder
    {
        return $query->where('pharmacy_id', $pharmacyId);
    }

    public function scopeBetweenDates(Builder $query, string $from, string $to): Builder
    {
        return $query->whereBetween('entry_date', [$from, $to]);
    }
}
