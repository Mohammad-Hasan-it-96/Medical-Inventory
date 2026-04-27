<?php

namespace App\Services;

use App\Models\AccountEntry;
use App\Models\Payment;
use Illuminate\Support\Facades\DB;

class PaymentService
{
    /**
     * Record a payment from a pharmacy and post the matching accounting credit.
     *
     * Expected $data keys:
     *   pharmacy_id  (int)
     *   amount       (float)
     *   method       (string: cash|bank|other, default cash)
     *   order_id     (int,    optional) – link to a specific order
     *   notes        (string, optional)
     *   paid_at      (string|Carbon, optional) – defaults to now()
     *
     * Transaction flow:
     *   1. Insert the Payment row.
     *   2. Post a 'credit' AccountEntry:
     *        credit = pharmacy's outstanding balance decreases (they paid us).
     *   Both steps share the same DB transaction; if the entry cannot be created
     *   the payment is automatically rolled back.
     */
    public function recordPayment(array $data, ?int $userId = null): Payment
    {
        return DB::transaction(function () use ($data, $userId) {

            $payment = Payment::create([
                'pharmacy_id' => $data['pharmacy_id'],
                'order_id'    => $data['order_id'] ?? null,
                'amount'      => $data['amount'],
                'method'      => $data['method'] ?? 'cash',
                'notes'       => $data['notes'] ?? null,
                'paid_at'     => $data['paid_at'] ?? now(),
                'created_by'  => $userId,
            ]);

            // Credit entry: reduces what the pharmacy owes.
            AccountEntry::create([
                'pharmacy_id' => $payment->pharmacy_id,
                'order_id'    => $payment->order_id,
                'payment_id'  => $payment->id,
                'type'        => 'credit',
                'amount'      => $payment->amount,
                'description' => 'Payment received via ' . $payment->method,
                'entry_date'  => $payment->paid_at?->toDateString() ?? now()->toDateString(),
                'created_by'  => $userId,
            ]);

            return $payment;
        });
    }
}

