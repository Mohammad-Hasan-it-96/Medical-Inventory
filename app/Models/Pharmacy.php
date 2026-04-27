<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Builder;

class Pharmacy extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'phone',
        'address',
        'area',
        'rep_id',
        'credit_limit',
        'opening_balance',
        'notes',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'credit_limit'    => 'decimal:2',
            'opening_balance' => 'decimal:2',
            'is_active'       => 'boolean',
        ];
    }

    // ─── Relationships ────────────────────────────────────────────────────────

    public function rep()
    {
        return $this->belongsTo(User::class, 'rep_id');
    }

    public function orders()
    {
        return $this->hasMany(Order::class);
    }

    public function payments()
    {
        return $this->hasMany(Payment::class);
    }

    public function accountEntries()
    {
        return $this->hasMany(AccountEntry::class);
    }

    // ─── Scopes ───────────────────────────────────────────────────────────────

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function scopeForRep(Builder $query, int $repId): Builder
    {
        return $query->where('rep_id', $repId);
    }

    public function scopeSearch(Builder $query, string $term): Builder
    {
        return $query->where(function (Builder $q) use ($term) {
            $q->where('name', 'like', "%{$term}%")
              ->orWhere('phone', 'like', "%{$term}%")
              ->orWhere('area', 'like', "%{$term}%");
        });
    }
}

