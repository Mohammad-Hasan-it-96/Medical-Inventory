<?php

namespace App\Services;

use App\Models\AccountEntry;
use App\Models\Order;
use App\Models\Payment;
use App\Models\Pharmacy;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class PaymentService
{
    /**
     * Record a payment from a pharmacy and post the matching accounting credit.
     *
     * Expected $data keys:
     *   pharmacy_id  (int)     required
     *   amount       (float)   required, > 0
     *   method       (string)  optional: cash|bank|other, default cash
     *   order_id     (int)     optional – must belong to same pharmacy when supplied
     *   notes        (string)  optional
     *   paid_at      (string)  optional – defaults to now()
     *
     * Transaction flow:
     *   1. Validate all fields.
     *   2. Insert Payment row.
     *   3. Post a TYPE_CREDIT AccountEntry (pharmacy's balance decreases — they paid us).
     *   4. Return payment with pharmacy and order eager-loaded.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function recordPayment(array $data, ?int $userId = null): Payment
    {
        // ── Validation ────────────────────────────────────────────────────────
        $validator = Validator::make($data, [
            'pharmacy_id' => ['required', 'integer'],
            'amount'      => ['required', 'numeric', 'min:0.01'],
            'method'      => ['nullable', 'in:cash,bank,other'],
            'order_id'    => ['nullable', 'integer'],
            'notes'       => ['nullable', 'string', 'max:1000'],
            'paid_at'     => ['nullable', 'date'],
        ]);

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }

        $validated = $validator->validated();

        // Pharmacy must exist.
        if (! Pharmacy::where('id', $validated['pharmacy_id'])->exists()) {
            $validator->errors()->add('pharmacy_id', 'The selected pharmacy does not exist.');
            throw new ValidationException($validator);
        }

        // If order_id provided it must belong to the same pharmacy AND be confirmed.
        if (! empty($validated['order_id'])) {
            $linkedOrder = Order::where('id', $validated['order_id'])
                ->where('pharmacy_id', $validated['pharmacy_id'])
                ->first();

            if (! $linkedOrder) {
                $validator->errors()->add('order_id', 'The selected order does not belong to this pharmacy.');
                throw new ValidationException($validator);
            }

            if ($linkedOrder->status !== Order::STATUS_CONFIRMED) {
                $validator->errors()->add('order_id', 'Payments can only be linked to confirmed orders.');
                throw new ValidationException($validator);
            }
        }

        // ── Transaction ───────────────────────────────────────────────────────
        return DB::transaction(function () use ($validated, $userId) {

            $paidAt = isset($validated['paid_at'])
                ? \Carbon\Carbon::parse($validated['paid_at'])
                : now();

            $payment = Payment::create([
                'pharmacy_id' => $validated['pharmacy_id'],
                'order_id'    => $validated['order_id'] ?? null,
                'amount'      => $validated['amount'],
                'method'      => $validated['method'] ?? 'cash',
                'notes'       => $validated['notes'] ?? null,
                'paid_at'     => $paidAt,
                'created_by'  => $userId,
            ]);

            // Credit entry: reduces what the pharmacy owes.
            AccountEntry::create([
                'pharmacy_id' => $payment->pharmacy_id,
                'order_id'    => $payment->order_id,
                'payment_id'  => $payment->id,
                'type'        => AccountEntry::TYPE_CREDIT,
                'amount'      => $payment->amount,
                'description' => 'Payment received via ' . ($payment->method ?? 'cash'),
                'entry_date'  => $paidAt->toDateString(),
                'created_by'  => $userId,
            ]);

            return $payment->load(['pharmacy', 'order']);
        });
    }
}

