<?php

namespace App\Http\Controllers\API\V1;

use App\Http\Controllers\API\BaseController;
use App\Http\Resources\PaymentResource;
use App\Services\PaymentService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PaymentController extends BaseController
{
    public function __construct(protected PaymentService $paymentService) {}

    /**
     * POST /api/v1/payments
     *
     * Body:
     * {
     *   "pharmacy_id": 1,
     *   "amount": 5000,
     *   "method": "cash",
     *   "order_id": 3,     // optional — link to a specific order
     *   "notes": "",       // optional
     *   "paid_at": "2026-04-27T10:00:00"  // optional, defaults to now
     * }
     */
    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'pharmacy_id' => 'required|integer|exists:pharmacies,id',
            'amount'      => 'required|numeric|min:0.01',
            'method'      => 'nullable|in:cash,bank,other',
            'order_id'    => 'nullable|integer|exists:orders,id',
            'notes'       => 'nullable|string|max:1000',
            'paid_at'     => 'nullable|date',
        ]);

        try {
            $payment = $this->paymentService->recordPayment($data, auth()->id());
        } catch (\Exception $e) {
            return $this->sendError('Failed to record payment: ' . $e->getMessage(), [], 500);
        }

        return $this->sendResponse(
            new PaymentResource($payment->load('pharmacy')),
            'Payment recorded successfully.'
        );
    }
}

