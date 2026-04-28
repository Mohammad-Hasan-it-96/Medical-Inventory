<?php

namespace App\Http\Controllers\API\V1;

use App\Http\Controllers\API\BaseController;
use App\Http\Resources\AccountEntryResource;
use App\Http\Resources\PharmacyResource;
use App\Models\Pharmacy;
use App\Services\StatementService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class StatementController extends BaseController
{
    public function __construct(protected StatementService $statementService) {}

    /**
     * GET /api/v1/pharmacies/{pharmacy}/statement
     *
     * Returns the full ledger statement for a pharmacy.
     *
     * Optional filters:
     *   ?from=Y-m-d   – entries on or after this date (entry_date)
     *   ?to=Y-m-d     – entries on or before this date (entry_date)
     *
     * Summary totals are always all-time (not filtered) so the balance
     * always reflects the pharmacy's true outstanding position.
     *
     * Role rules:
     *   rep   → can only access pharmacies assigned to them
     *   admin → any pharmacy
     */
    public function pharmacy(Request $request, Pharmacy $pharmacy): JsonResponse
    {
        $user = $request->user();

        // Reps can only view their own assigned pharmacies.
        if (Gate::denies('view', $pharmacy)) {
            return $this->sendError('Forbidden.', [], 403);
        }

        $request->validate([
            'from' => ['nullable', 'date'],
            'to'   => ['nullable', 'date', 'after_or_equal:from'],
        ]);

        $statement = $this->statementService->getPharmacyStatement(
            $pharmacy->id,
            $request->input('from'),
            $request->input('to'),
        );

        return $this->sendResponse([
            'pharmacy'        => new PharmacyResource($statement['pharmacy']),
            'opening_balance' => $statement['opening_balance'],
            'total_debit'     => $statement['total_debit'],
            'total_credit'    => $statement['total_credit'],
            'balance'         => $statement['balance'],
            'entries'         => AccountEntryResource::collection($statement['entries']),
        ], 'Statement retrieved successfully.');
    }
}

