<?php

namespace Database\Seeders;

use App\Models\Pharmacy;
use App\Models\User;
use Illuminate\Database\Seeder;

class PharmacySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $rep1 = User::where('email', 'rep1@example.com')->first();
        $rep2 = User::where('email', 'rep2@example.com')->first();

        // 20 pharmacies — first 10 assigned to rep1, next 10 to rep2
        $pharmacies = [
            // ── Rep 1 ────────────────────────────────────────────────────────
            ['name' => 'صيدلية الأمل',            'phone' => '0944100001', 'address' => 'دمشق - المزة',         'area' => 'المزة',       'credit_limit' => 500000,  'opening_balance' => 0, 'rep' => $rep1],
            ['name' => 'صيدلية الشفاء',            'phone' => '0944100002', 'address' => 'دمشق - المالكي',       'area' => 'المالكي',     'credit_limit' => 750000,  'opening_balance' => 50000, 'rep' => $rep1],
            ['name' => 'صيدلية النور',             'phone' => '0944100003', 'address' => 'دمشق - كفرسوسة',       'area' => 'كفرسوسة',     'credit_limit' => 300000,  'opening_balance' => 0, 'rep' => $rep1],
            ['name' => 'صيدلية الرشيد',            'phone' => '0944100004', 'address' => 'دمشق - ركن الدين',     'area' => 'ركن الدين',   'credit_limit' => 400000,  'opening_balance' => 25000, 'rep' => $rep1],
            ['name' => 'صيدلية الهلال',            'phone' => '0944100005', 'address' => 'دمشق - الميدان',       'area' => 'الميدان',     'credit_limit' => 600000,  'opening_balance' => 0, 'rep' => $rep1],
            ['name' => 'صيدلية الحياة',            'phone' => '0944100006', 'address' => 'دمشق - باب سريجة',     'area' => 'باب سريجة',   'credit_limit' => 350000,  'opening_balance' => 10000, 'rep' => $rep1],
            ['name' => 'صيدلية الصحة الذهبية',     'phone' => '0944100007', 'address' => 'دمشق - القصاع',        'area' => 'القصاع',      'credit_limit' => 500000,  'opening_balance' => 0, 'rep' => $rep1],
            ['name' => 'صيدلية السلام',             'phone' => '0944100008', 'address' => 'دمشق - برزة',          'area' => 'برزة',        'credit_limit' => 280000,  'opening_balance' => 5000, 'rep' => $rep1],
            ['name' => 'صيدلية الوفاء',             'phone' => '0944100009', 'address' => 'دمشق - عدرا',          'area' => 'عدرا',        'credit_limit' => 200000,  'opening_balance' => 0, 'rep' => $rep1],
            ['name' => 'صيدلية الجمهورية',          'phone' => '0944100010', 'address' => 'دمشق - الزاهرة',       'area' => 'الزاهرة',     'credit_limit' => 450000,  'opening_balance' => 30000, 'rep' => $rep1],
            // ── Rep 2 ────────────────────────────────────────────────────────
            ['name' => 'صيدلية النهضة',             'phone' => '0955200001', 'address' => 'حلب - الجميلية',       'area' => 'الجميلية',    'credit_limit' => 600000,  'opening_balance' => 0, 'rep' => $rep2],
            ['name' => 'صيدلية الفارابي',           'phone' => '0955200002', 'address' => 'حلب - العزيزية',       'area' => 'العزيزية',    'credit_limit' => 500000,  'opening_balance' => 15000, 'rep' => $rep2],
            ['name' => 'صيدلية الزهراء',            'phone' => '0955200003', 'address' => 'حلب - السريان',        'area' => 'السريان',     'credit_limit' => 350000,  'opening_balance' => 0, 'rep' => $rep2],
            ['name' => 'صيدلية ابن سينا',           'phone' => '0955200004', 'address' => 'حلب - حمدانية',        'area' => 'الحمدانية',   'credit_limit' => 800000,  'opening_balance' => 75000, 'rep' => $rep2],
            ['name' => 'صيدلية الرازي',             'phone' => '0955200005', 'address' => 'حلب - مساكن هنانو',   'area' => 'مساكن هنانو', 'credit_limit' => 400000,  'opening_balance' => 0, 'rep' => $rep2],
            ['name' => 'صيدلية الطبيعة',            'phone' => '0955200006', 'address' => 'حلب - النيرب',         'area' => 'النيرب',      'credit_limit' => 250000,  'opening_balance' => 8000, 'rep' => $rep2],
            ['name' => 'صيدلية الفردوس',            'phone' => '0955200007', 'address' => 'حلب - الشيخ سعيد',    'area' => 'الشيخ سعيد', 'credit_limit' => 320000,  'opening_balance' => 0, 'rep' => $rep2],
            ['name' => 'صيدلية البشير',             'phone' => '0955200008', 'address' => 'حمص - الوعر',          'area' => 'الوعر',       'credit_limit' => 300000,  'opening_balance' => 20000, 'rep' => $rep2],
            ['name' => 'صيدلية المستقبل',           'phone' => '0955200009', 'address' => 'حمص - عكرمة',          'area' => 'عكرمة',       'credit_limit' => 450000,  'opening_balance' => 0, 'rep' => $rep2],
            ['name' => 'صيدلية الياسمين',           'phone' => '0955200010', 'address' => 'حمص - الإنشاءات',      'area' => 'الإنشاءات',   'credit_limit' => 380000,  'opening_balance' => 12000, 'rep' => $rep2],
        ];

        foreach ($pharmacies as $data) {
            Pharmacy::firstOrCreate(
                ['phone' => $data['phone']],
                [
                    'name'            => $data['name'],
                    'phone'           => $data['phone'],
                    'address'         => $data['address'],
                    'area'            => $data['area'],
                    'rep_id'          => $data['rep']?->id,
                    'credit_limit'    => $data['credit_limit'],
                    'opening_balance' => $data['opening_balance'],
                    'is_active'       => true,
                ]
            );
        }

        $this->command->info('✓ Pharmacies seeded (20 pharmacies — 10 per rep)');
    }
}
