<?php

namespace App\Http\Controllers\API\V1;

use App\Http\Controllers\API\BaseController;
use App\Http\Resources\AccountEntryResource;
use App\Http\Resources\PharmacyResource;
use App\Models\AccountEntry;
use App\Models\Pharmacy;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PharmacyController extends BaseController
{
    /**
     * GET /api/v1/pharmacies
     *
     * Filters (all optional):
     *   ?search=term          – name, phone, or area
     *   ?updated_after=Y-m-d  – incremental sync support
     *   ?page=1
     *
     * Role rules:
     *   rep   → only their assigned pharmacies
     *   admin → all pharmacies
     */
    public function index(Request $request): JsonResponse
    {
        $user  = auth()->user();
        $query = Pharmacy::with('rep')->active();

        if ($user->role === 'rep') {
            $query->forRep($user->id);
        }

        if ($search = $request->input('search')) {
            $query->search($search);
        }

        if ($updatedAfter = $request->input('updated_after')) {
            $query->where('updated_at', '>', $updatedAfter);
        }

        $pharmacies = $query->orderBy('name')->paginate(20);

        return $this->sendResponse(
            PharmacyResource::collection($pharmacies),
            'Pharmacies retrieved successfully.'
        );
    }

    /**
     * GET /api/v1/pharmacies/{pharmacy}/statement
     *
     * Returns the ledger entries for a pharmacy plus the current running balance.
     *
     * Balance formula:
     *   opening_balance  (stored on pharmacy)
     *   + SUM(debit entries)   – amount the pharmacy owes us
     *   − SUM(credit entries)  – payments received / order cancellations
     */
    public function statement(Request $request, Pharmacy $pharmacy): JsonResponse
    {
        $user = auth()->user();

        // Reps can only see their own pharmacies.
        if ($user->role === 'rep' && $pharmacy->rep_id !== $user->id) {
            return $this->sendError('Forbidden.', [], 403);
        }

        $debit  = (float) AccountEntry::where('pharmacy_id', $pharmacy->id)
            ->where('type', 'debit')
            ->sum('amount');

        $credit = (float) AccountEntry::where('pharmacy_id', $pharmacy->id)
            ->where('type', 'credit')
            ->sum('amount');

        $balance = (float) $pharmacy->opening_balance + $debit - $credit;

        // Attach computed balance so PharmacyResource can include it.
        $pharmacy->setAttribute('balance', $balance);

        // Paginated ledger entries, newest first.
        $entries = AccountEntry::where('pharmacy_id', $pharmacy->id)
            ->orderByDesc('entry_date')
            ->orderByDesc('id')
            ->paginate(30);

        return $this->sendResponse([
            'pharmacy' => new PharmacyResource($pharmacy->load('rep')),
            'balance'  => round($balance, 2),
            'entries'  => AccountEntryResource::collection($entries),
        ], 'Statement retrieved successfully.');
    }
}

