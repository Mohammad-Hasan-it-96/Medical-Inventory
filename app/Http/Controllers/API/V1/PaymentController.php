<?php

namespace App\Http\Controllers\API\V1;

use App\Http\Controllers\API\BaseController;
use App\Http\Resources\PaymentResource;
use App\Models\Payment;
use App\Models\Pharmacy;
use App\Services\PaymentService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\ValidationException;

class PaymentController extends BaseController
{
    public function __construct(protected PaymentService $paymentService) {}

    // =========================================================================
    //  GET /api/v1/payments
    // =========================================================================

    /**
     * List payments with optional filters.
     *
     * Query params:
     *   ?pharmacy_id=1         – filter by pharmacy
     *   ?from=Y-m-d            – paid_at on or after
     *   ?to=Y-m-d              – paid_at on or before
     *
     * Role rules:
     *   rep   → only payments for their assigned pharmacies
     *   admin → all payments
     */
    public function index(Request $request): JsonResponse
    {
        $user  = $request->user();
        $query = Payment::with(['pharmacy', 'order'])->orderByDesc('paid_at');

        // Rep can only see payments for their own pharmacies.
        if ($user->hasRole('rep')) {
            $assignedIds = Pharmacy::where('rep_id', $user->id)->pluck('id');
            $query->whereIn('pharmacy_id', $assignedIds);
        }

        // Optional filters.
        if ($pharmacyId = $request->integer('pharmacy_id')) {
            // Rep must own this pharmacy.
            if ($user->hasRole('rep')) {
                $pharmacy = Pharmacy::find($pharmacyId);
                if (! $pharmacy || Gate::denies('view', $pharmacy)) {
                    return $this->sendError('Forbidden.', [], 403);
                }
            }
            $query->where('pharmacy_id', $pharmacyId);
        }

        if ($from = $request->input('from')) {
            $query->whereDate('paid_at', '>=', $from);
        }

        if ($to = $request->input('to')) {
            $query->whereDate('paid_at', '<=', $to);
        }

        $payments = $query->paginate(20);

        return $this->sendResponse(
            PaymentResource::collection($payments)->response()->getData(true),
            'Payments retrieved successfully.'
        );
    }

    // =========================================================================
    //  POST /api/v1/payments
    // =========================================================================

    /**
     * Record a new payment.
     *
     * Body:
     * {
     *   "pharmacy_id": 1,
     *   "amount": 5000,
     *   "method": "cash",       // optional, default cash
     *   "order_id": 3,          // optional – must belong to same pharmacy
     *   "notes": "",            // optional
     *   "paid_at": "2026-04-27T10:00:00"  // optional, defaults to now
     * }
     */
    public function store(Request $request): JsonResponse
    {
        $user = $request->user();

        $data = $request->validate([
            'pharmacy_id' => 'required|integer|exists:pharmacies,id',
            'amount'      => 'required|numeric|min:0.01',
            'method'      => 'nullable|in:cash,bank,other',
            'order_id'    => 'nullable|integer|exists:orders,id',
            'notes'       => 'nullable|string|max:1000',
            'paid_at'     => 'nullable|date',
        ]);

        // Rep may only record payments for their own assigned pharmacies.
        if ($user->hasRole('rep')) {
            $pharmacy = Pharmacy::find($data['pharmacy_id']);
            if (! $pharmacy || Gate::denies('view', $pharmacy)) {
                return $this->sendError('Forbidden. This pharmacy is not assigned to you.', [], 403);
            }
        }

        try {
            $payment = $this->paymentService->recordPayment($data, $user->id);
        } catch (ValidationException $e) {
            return $this->sendError('Validation failed.', $e->errors(), 422);
        } catch (\Exception $e) {
            return $this->sendError('Failed to record payment: ' . $e->getMessage(), [], 500);
        }

        return $this->sendResponse(
            new PaymentResource($payment),
            'Payment recorded successfully.',
        );
    }
}

