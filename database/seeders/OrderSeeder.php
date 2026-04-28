<?php

namespace Database\Seeders;

use App\Models\Order;
use App\Models\Pharmacy;
use App\Models\Product;
use App\Models\User;
use App\Services\OrderService;
use Illuminate\Database\Seeder;

class OrderSeeder extends Seeder
{
    public function __construct(protected OrderService $orderService) {}

    public function run(): void
    {
        $admin = User::where('email', 'admin@example.com')->firstOrFail();
        $rep1  = User::where('email', 'rep1@example.com')->firstOrFail();
        $rep2  = User::where('email', 'rep2@example.com')->firstOrFail();

        // ── Load pharmacies by phone (unique, safe) ────────────────────────
        $ph = Pharmacy::whereIn('phone', [
            '0944100001', // صيدلية الأمل      (rep1)
            '0944100002', // صيدلية الشفاء     (rep1)
            '0944100004', // صيدلية الرشيد     (rep1)
            '0955200001', // صيدلية النهضة     (rep2)
            '0955200004', // صيدلية ابن سينا   (rep2)
        ])->get()->keyBy('phone');

        // ── Load some products (by barcode) ────────────────────────────────
        $products = Product::whereIn('barcode', [
            '6912345000001', '6912345000006', '6912345000016',  // group A
            '6912345000002', '6912345000007', '6912345000023',  // group B
            '6912345000011', '6912345000021',                   // group C
        ])->get()->keyBy('barcode');

        // Helpers
        $price = fn (Product $p) => (float) ($p->productPrice?->net_price_syp ?? $p->price ?? 100);

        // ── Order definitions ──────────────────────────────────────────────
        // Each entry: [pharmacy_phone, rep, status, items[[barcode, qty]], notes]
        $orders = [
            // 1 – confirmed (full cycle: stock + debit entry posted)
            [
                'pharmacy' => '0944100001',
                'rep'      => $rep1,
                'confirm'  => true,
                'notes'    => 'طلب دوري شهري',
                'items'    => [
                    ['barcode' => '6912345000001', 'qty' => 20],
                    ['barcode' => '6912345000006', 'qty' => 10],
                    ['barcode' => '6912345000016', 'qty' => 15],
                ],
            ],
            // 2 – confirmed
            [
                'pharmacy' => '0944100002',
                'rep'      => $rep1,
                'confirm'  => true,
                'notes'    => 'طلب إضافي',
                'items'    => [
                    ['barcode' => '6912345000002', 'qty' => 25],
                    ['barcode' => '6912345000007', 'qty' => 8],
                    ['barcode' => '6912345000023', 'qty' => 30],
                ],
            ],
            // 3 – pending (created but not yet confirmed)
            [
                'pharmacy' => '0955200001',
                'rep'      => $rep2,
                'confirm'  => false,
                'notes'    => 'طلب قيد المراجعة',
                'items'    => [
                    ['barcode' => '6912345000011', 'qty' => 12],
                    ['barcode' => '6912345000021', 'qty' => 20],
                ],
            ],
            // 4 – pending
            [
                'pharmacy' => '0955200004',
                'rep'      => $rep2,
                'confirm'  => false,
                'notes'    => 'طلب جديد من صيدلية ابن سينا',
                'items'    => [
                    ['barcode' => '6912345000001', 'qty' => 50],
                    ['barcode' => '6912345000006', 'qty' => 20],
                    ['barcode' => '6912345000016', 'qty' => 30],
                ],
            ],
            // 5 – draft
            [
                'pharmacy' => '0944100004',
                'rep'      => $rep1,
                'confirm'  => false,
                'status'   => Order::STATUS_DRAFT,
                'notes'    => 'مسودة طلب جديد',
                'items'    => [
                    ['barcode' => '6912345000002', 'qty' => 10],
                    ['barcode' => '6912345000023', 'qty' => 20],
                ],
            ],
        ];

        $created = 0;

        foreach ($orders as $def) {
            $pharmacy = $ph->get($def['pharmacy']);
            if (! $pharmacy) {
                $this->command->warn("  ⚠ Pharmacy {$def['pharmacy']} not found — skipping.");
                continue;
            }

            // Skip if this pharmacy already has orders (idempotent re-seed)
            if (Order::where('pharmacy_id', $pharmacy->id)->exists()) {
                continue;
            }

            // Build items array for OrderService
            $items = [];
            foreach ($def['items'] as $itemDef) {
                $product = $products->get($itemDef['barcode']);
                if (! $product) continue;
                $items[] = [
                    'product_id' => $product->id,
                    'quantity'   => $itemDef['qty'],
                    'unit_price' => $price($product),
                    'discount'   => 0,
                ];
            }

            if (empty($items)) continue;

            try {
                $order = $this->orderService->createOrder([
                    'pharmacy_id' => $pharmacy->id,
                    'notes'       => $def['notes'] ?? null,
                    'discount'    => 0,
                    'items'       => $items,
                ], $def['rep']->id);

                // Optionally override to draft
                if (($def['status'] ?? null) === Order::STATUS_DRAFT) {
                    $order->update(['status' => Order::STATUS_DRAFT]);
                }

                // Confirm if requested
                if ($def['confirm'] ?? false) {
                    $this->orderService->confirmOrder($order, $admin->id);
                }

                $created++;
            } catch (\Exception $e) {
                $this->command->warn("  ⚠ Order for {$pharmacy->name} failed: " . $e->getMessage());
            }
        }

        $this->command->info("✓ Orders seeded ({$created} orders created)");
    }
}

