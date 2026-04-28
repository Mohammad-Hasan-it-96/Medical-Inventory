<?php

namespace App\Services;

use App\Models\AccountEntry;
use App\Models\Pharmacy;
use Illuminate\Database\Eloquent\Collection;

class StatementService
{
    /**
     * Build a pharmacy ledger statement with optional date range filtering.
     *
     * The all-time debit/credit totals and balance are always computed over
     * the full history (ignoring the date filter) so the summary always
     * reflects the true outstanding balance of the pharmacy.
     * The `entries` list is optionally filtered by `entry_date`.
     *
     * @param  int          $pharmacyId
     * @param  string|null  $from  Y-m-d  (inclusive)
     * @param  string|null  $to    Y-m-d  (inclusive)
     *
     * @return array{
     *     pharmacy: Pharmacy,
     *     opening_balance: float,
     *     total_debit: float,
     *     total_credit: float,
     *     balance: float,
     *     entries: Collection,
     * }
     */
    public function getPharmacyStatement(
        int     $pharmacyId,
        ?string $from = null,
        ?string $to   = null,
    ): array {
        $pharmacy = Pharmacy::with('rep')->findOrFail($pharmacyId);

        // ── All-time totals (not affected by date filter) ─────────────────────
        $totalDebit = (float) AccountEntry::where('pharmacy_id', $pharmacyId)
            ->where('type', AccountEntry::TYPE_DEBIT)
            ->sum('amount');

        $totalCredit = (float) AccountEntry::where('pharmacy_id', $pharmacyId)
            ->where('type', AccountEntry::TYPE_CREDIT)
            ->sum('amount');

        $openingBalance = (float) $pharmacy->opening_balance;

        // balance = opening + all debits − all credits
        $balance = round($openingBalance + $totalDebit - $totalCredit, 2);

        // ── Entry list (optionally date-filtered) ─────────────────────────────
        $query = AccountEntry::where('pharmacy_id', $pharmacyId)
            ->with(['order'])           // eager-load related order when present
            ->orderBy('entry_date')
            ->orderBy('id');

        if ($from !== null) {
            $query->whereDate('entry_date', '>=', $from);
        }

        if ($to !== null) {
            $query->whereDate('entry_date', '<=', $to);
        }

        $entries = $query->get();

        return [
            'pharmacy'        => $pharmacy,
            'opening_balance' => round($openingBalance, 2),
            'total_debit'     => round($totalDebit,  2),
            'total_credit'    => round($totalCredit, 2),
            'balance'         => $balance,
            'entries'         => $entries,
        ];
    }
}

