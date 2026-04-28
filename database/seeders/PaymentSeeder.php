<?php

namespace Database\Seeders;

use App\Models\Order;
use App\Models\Payment;
use App\Models\Pharmacy;
use App\Models\User;
use App\Services\PaymentService;
use Illuminate\Database\Seeder;

class PaymentSeeder extends Seeder
{
    public function __construct(protected PaymentService $paymentService) {}

    public function run(): void
    {
        $admin = User::where('email', 'admin@example.com')->firstOrFail();
        $rep1  = User::where('email', 'rep1@example.com')->firstOrFail();

        // ── Resolve pharmacies ─────────────────────────────────────────────
        $ph1 = Pharmacy::where('phone', '0944100001')->first(); // صيدلية الأمل
        $ph2 = Pharmacy::where('phone', '0944100002')->first(); // صيدلية الشفاء
        $ph3 = Pharmacy::where('phone', '0955200001')->first(); // صيدلية النهضة

        // ── Payment definitions ────────────────────────────────────────────
        // [pharmacy, amount, method, notes, offset_days, link_pharmacy_for_order]
        $payments = [
            // 1 – partial payment against the confirmed order of صيدلية الأمل
            [
                'pharmacy'   => $ph1,
                'amount'     => 5000,
                'method'     => 'cash',
                'notes'      => 'دفعة جزئية أولى',
                'days_ago'   => 2,
                'order_phone'=> '0944100001',  // link to first confirmed order of this pharmacy
            ],
            // 2 – full payment for صيدلية الشفاء order
            [
                'pharmacy'   => $ph2,
                'amount'     => 8000,
                'method'     => 'bank',
                'notes'      => 'تحويل بنكي كامل',
                'days_ago'   => 1,
                'order_phone'=> '0944100002',
            ],
            // 3 – advance / standalone payment (no specific order) for صيدلية النهضة
            [
                'pharmacy'   => $ph3,
                'amount'     => 15000,
                'method'     => 'cash',
                'notes'      => 'دفعة مقدمة',
                'days_ago'   => 0,
                'order_phone'=> null,
            ],
        ];

        $created = 0;

        foreach ($payments as $def) {
            $pharmacy = $def['pharmacy'];
            if (! $pharmacy) {
                $this->command->warn('  ⚠ Pharmacy not found — skipping payment.');
                continue;
            }

            // Idempotency: skip if pharmacy already has payments
            if (Payment::where('pharmacy_id', $pharmacy->id)->exists()) {
                continue;
            }

            // Optionally resolve a related confirmed order
            $orderId = null;
            if ($def['order_phone']) {
                $order = Order::whereHas('pharmacy', fn ($q) => $q->where('phone', $def['order_phone']))
                    ->where('status', Order::STATUS_CONFIRMED)
                    ->latest()
                    ->first();
                $orderId = $order?->id;
            }

            try {
                $this->paymentService->recordPayment([
                    'pharmacy_id' => $pharmacy->id,
                    'order_id'    => $orderId,
                    'amount'      => $def['amount'],
                    'method'      => $def['method'],
                    'notes'       => $def['notes'],
                    'paid_at'     => now()->subDays($def['days_ago'])->toDateString(),
                ], $admin->id);

                $created++;
            } catch (\Exception $e) {
                $this->command->warn("  ⚠ Payment for {$pharmacy->name} failed: " . $e->getMessage());
            }
        }

        $this->command->info("✓ Payments seeded ({$created} payments created)");
    }
}

