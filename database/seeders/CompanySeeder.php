<?php

namespace Database\Seeders;

use App\Models\Company;
use Illuminate\Database\Seeder;

class CompanySeeder extends Seeder
{
    public function run(): void
    {
        $companies = [
            [
                'name'      => 'شركة تاميكو للأدوية',
                'phone'     => '011-2345678',
                'address'   => 'دمشق - المزة - طريق المطار',
                'notes'     => 'من أكبر شركات الأدوية السورية',
                'is_active' => true,
            ],
            [
                'name'      => 'شركة سيريا فارما',
                'phone'     => '011-3456789',
                'address'   => 'حلب - المنطقة الصناعية',
                'notes'     => 'متخصصة في الأدوية الجنيسة',
                'is_active' => true,
            ],
            [
                'name'      => 'مصنع الدواء الحديث',
                'phone'     => '033-1234567',
                'address'   => 'حمص - المنطقة الصناعية',
                'notes'     => 'تصنيع محلي بجودة عالية',
                'is_active' => true,
            ],
            [
                'name'      => 'شركة الشفاء للأدوية',
                'phone'     => '021-9876543',
                'address'   => 'دمشق - باب توما',
                'notes'     => 'توزيع أدوية ومستلزمات طبية',
                'is_active' => true,
            ],
            [
                'name'      => 'مختبرات بيلسان الدوائية',
                'phone'     => '011-5544332',
                'address'   => 'دمشق - دمر - المنطقة الصناعية',
                'notes'     => 'أدوية جلدية وفيتامينات',
                'is_active' => true,
            ],
        ];

        foreach ($companies as $data) {
            Company::firstOrCreate(['name' => $data['name']], $data);
        }

        $this->command->info('✓ Companies seeded (5 companies)');
    }
}

