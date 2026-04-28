<?php

namespace App\Http\Controllers\API\V1;

use App\Http\Controllers\API\BaseController;
use App\Http\Resources\AccountEntryResource;
use App\Http\Resources\PharmacyResource;
use App\Models\AccountEntry;
use App\Models\Pharmacy;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class StatementController extends BaseController
{
    /**
     * GET /api/v1/pharmacies/{pharmacy}/statement
     *
     * Returns paginated ledger entries for a pharmacy with a running balance summary.
     *
     * Optional filters:
     *   ?from=Y-m-d   – entries on or after this date
     *   ?to=Y-m-d     – entries on or before this date
     *
     * Summary (always computed over ALL time, not filtered):
     *   total_debit   – sum of all debit entries (pharmacy owes us)
     *   total_credit  – sum of all credit entries (payments / cancellations)
     *   balance       – opening_balance + total_debit − total_credit
     *
     * Role rules:
     *   rep   → can only access pharmacies assigned to them
     *   admin → any pharmacy
     */
    public function pharmacy(Request $request, Pharmacy $pharmacy): JsonResponse
    {
        $user = auth()->user();

        // Reps can only view their own pharmacies.
        if ($user->role === 'rep' && $pharmacy->rep_id !== $user->id) {
            return $this->sendError('Forbidden.', [], 403);
        }

        // ── All-time balance summary ──────────────────────────────────────────
        $totalDebit  = (float) AccountEntry::where('pharmacy_id', $pharmacy->id)
            ->where('type', AccountEntry::TYPE_DEBIT)
            ->sum('amount');

        $totalCredit = (float) AccountEntry::where('pharmacy_id', $pharmacy->id)
            ->where('type', AccountEntry::TYPE_CREDIT)
            ->sum('amount');

        $balance = (float) $pharmacy->opening_balance + $totalDebit - $totalCredit;

        // Attach so PharmacyResource can expose it.
        $pharmacy->setAttribute('balance', round($balance, 2));

        // ── Filtered entry list ───────────────────────────────────────────────
        $query = AccountEntry::where('pharmacy_id', $pharmacy->id)
            ->orderByDesc('entry_date')
            ->orderByDesc('id');

        if ($from = $request->input('from')) {
            $query->whereDate('entry_date', '>=', $from);
        }

        if ($to = $request->input('to')) {
            $query->whereDate('entry_date', '<=', $to);
        }

        $entries = $query->paginate(30);

        return $this->sendResponse([
            'pharmacy' => new PharmacyResource($pharmacy->load('rep')),
            'summary'  => [
                'total_debit'  => round($totalDebit, 2),
                'total_credit' => round($totalCredit, 2),
                'balance'      => round($balance, 2),
            ],
            'entries' => AccountEntryResource::collection($entries),
        ], 'Statement retrieved successfully.');
    }
}

