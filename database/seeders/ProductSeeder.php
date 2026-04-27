<?php

namespace Database\Seeders;

use App\Models\Company;
use App\Models\Product;
use App\Models\ProductPrice;
use App\Models\StockMovement;
use App\Models\User;
use Illuminate\Database\Seeder;

class ProductSeeder extends Seeder
{
    public function run(): void
    {
        $admin     = User::where('email', 'admin@example.com')->first();
        $companies = Company::pluck('id', 'name');

        // 30 realistic Arabic medicine names with their details
        $products = [
            // Analgesics / Anti-inflammatory
            ['name' => 'باراسيتامول 500 مج', 'barcode' => '6912345000001', 'unit' => 'علبة', 'form' => 'أقراص', 'company' => 'شركة تاميكو للأدوية',        'net' => 250,   'public' => 350,   'stock' => 500],
            ['name' => 'إيبوبروفين 400 مج',  'barcode' => '6912345000002', 'unit' => 'علبة', 'form' => 'أقراص', 'company' => 'شركة تاميكو للأدوية',        'net' => 300,   'public' => 420,   'stock' => 300],
            ['name' => 'ديكلوفيناك 50 مج',   'barcode' => '6912345000003', 'unit' => 'علبة', 'form' => 'أقراص', 'company' => 'شركة سيريا فارما',           'net' => 350,   'public' => 500,   'stock' => 250],
            ['name' => 'كيتوبروفين 100 مج',  'barcode' => '6912345000004', 'unit' => 'علبة', 'form' => 'كبسولات','company' => 'شركة سيريا فارما',          'net' => 450,   'public' => 620,   'stock' => 150],
            ['name' => 'ترامادول 50 مج',      'barcode' => '6912345000005', 'unit' => 'علبة', 'form' => 'أقراص', 'company' => 'مصنع الدواء الحديث',        'net' => 520,   'public' => 750,   'stock' => 100],
            // Antibiotics
            ['name' => 'أموكسيسيلين 500 مج', 'barcode' => '6912345000006', 'unit' => 'علبة', 'form' => 'كبسولات','company' => 'شركة تاميكو للأدوية',       'net' => 600,   'public' => 850,   'stock' => 400],
            ['name' => 'أزيثرومايسين 500 مج','barcode' => '6912345000007', 'unit' => 'علبة', 'form' => 'أقراص',  'company' => 'شركة الشفاء للأدوية',       'net' => 900,   'public' => 1300,  'stock' => 200],
            ['name' => 'سيبروفلوكساسين 500 مج','barcode'=>'6912345000008', 'unit' => 'علبة', 'form' => 'أقراص',  'company' => 'مصنع الدواء الحديث',        'net' => 750,   'public' => 1050,  'stock' => 280],
            ['name' => 'كلاريثرومايسين 500 مج','barcode'=>'6912345000009', 'unit' => 'علبة', 'form' => 'أقراص',  'company' => 'شركة سيريا فارما',          'net' => 1100,  'public' => 1500,  'stock' => 160],
            ['name' => 'دوكسيسايكلين 100 مج', 'barcode' => '6912345000010', 'unit' => 'علبة', 'form' => 'كبسولات','company' => 'شركة الشفاء للأدوية',      'net' => 550,   'public' => 780,   'stock' => 220],
            // Cardiovascular
            ['name' => 'أملوديبين 5 مج',      'barcode' => '6912345000011', 'unit' => 'علبة', 'form' => 'أقراص',  'company' => 'شركة تاميكو للأدوية',      'net' => 400,   'public' => 560,   'stock' => 350],
            ['name' => 'أتينولول 50 مج',       'barcode' => '6912345000012', 'unit' => 'علبة', 'form' => 'أقراص',  'company' => 'مصنع الدواء الحديث',       'net' => 380,   'public' => 530,   'stock' => 320],
            ['name' => 'ليزينوبريل 10 مج',    'barcode' => '6912345000013', 'unit' => 'علبة', 'form' => 'أقراص',  'company' => 'شركة سيريا فارما',          'net' => 450,   'public' => 620,   'stock' => 290],
            ['name' => 'فالسارتان 80 مج',      'barcode' => '6912345000014', 'unit' => 'علبة', 'form' => 'أقراص',  'company' => 'شركة الشفاء للأدوية',      'net' => 700,   'public' => 980,   'stock' => 180],
            ['name' => 'أسبرين 81 مج',         'barcode' => '6912345000015', 'unit' => 'علبة', 'form' => 'أقراص',  'company' => 'شركة تاميكو للأدوية',      'net' => 200,   'public' => 290,   'stock' => 600],
            // Gastrointestinal
            ['name' => 'أوميبرازول 20 مج',     'barcode' => '6912345000016', 'unit' => 'علبة', 'form' => 'كبسولات','company' => 'شركة سيريا فارما',          'net' => 480,   'public' => 680,   'stock' => 400],
            ['name' => 'ميتوكلوبراميد 10 مج',  'barcode' => '6912345000017', 'unit' => 'علبة', 'form' => 'أقراص',  'company' => 'مصنع الدواء الحديث',       'net' => 250,   'public' => 350,   'stock' => 350],
            ['name' => 'بوسكوبان 10 مج',       'barcode' => '6912345000018', 'unit' => 'علبة', 'form' => 'أقراص',  'company' => 'شركة تاميكو للأدوية',      'net' => 320,   'public' => 450,   'stock' => 300],
            ['name' => 'رانيتيدين 150 مج',      'barcode' => '6912345000019', 'unit' => 'علبة', 'form' => 'أقراص',  'company' => 'شركة الشفاء للأدوية',      'net' => 290,   'public' => 400,   'stock' => 260],
            ['name' => 'لوبيراميد 2 مج',        'barcode' => '6912345000020', 'unit' => 'علبة', 'form' => 'كبسولات','company' => 'مصنع الدواء الحديث',       'net' => 350,   'public' => 490,   'stock' => 200],
            // Diabetes
            ['name' => 'ميتفورمين 500 مج',      'barcode' => '6912345000021', 'unit' => 'علبة', 'form' => 'أقراص',  'company' => 'شركة تاميكو للأدوية',      'net' => 350,   'public' => 490,   'stock' => 380],
            ['name' => 'جليبنكلاميد 5 مج',      'barcode' => '6912345000022', 'unit' => 'علبة', 'form' => 'أقراص',  'company' => 'شركة سيريا فارما',          'net' => 300,   'public' => 420,   'stock' => 270],
            // Vitamins & Supplements
            ['name' => 'فيتامين سي 500 مج',     'barcode' => '6912345000023', 'unit' => 'علبة', 'form' => 'أقراص فوارة','company' => 'مختبرات بيلسان الدوائية','net' => 400, 'public' => 580,   'stock' => 500],
            ['name' => 'فيتامين د3 1000 وحدة',  'barcode' => '6912345000024', 'unit' => 'علبة', 'form' => 'كبسولات','company' => 'مختبرات بيلسان الدوائية', 'net' => 650,   'public' => 920,   'stock' => 300],
            ['name' => 'فيتامين ب كومبلكس',      'barcode' => '6912345000025', 'unit' => 'علبة', 'form' => 'أقراص',  'company' => 'مختبرات بيلسان الدوائية', 'net' => 550,   'public' => 780,   'stock' => 350],
            ['name' => 'زنك 50 مج',              'barcode' => '6912345000026', 'unit' => 'علبة', 'form' => 'أقراص',  'company' => 'مختبرات بيلسان الدوائية', 'net' => 380,   'public' => 540,   'stock' => 400],
            // Dermatology
            ['name' => 'هيدروكورتيزون كريم 1%', 'barcode' => '6912345000027', 'unit' => 'أنبوب','form' => 'كريم',    'company' => 'مختبرات بيلسان الدوائية', 'net' => 280,   'public' => 400,   'stock' => 250],
            ['name' => 'كلوتريمازول كريم 1%',   'barcode' => '6912345000028', 'unit' => 'أنبوب','form' => 'كريم',    'company' => 'شركة الشفاء للأدوية',      'net' => 320,   'public' => 450,   'stock' => 200],
            // Respiratory
            ['name' => 'سالبوتامول 2 مج',        'barcode' => '6912345000029', 'unit' => 'علبة', 'form' => 'أقراص',  'company' => 'مصنع الدواء الحديث',       'net' => 290,   'public' => 410,   'stock' => 300],
            ['name' => 'أمبروكسول 30 مج',        'barcode' => '6912345000030', 'unit' => 'علبة', 'form' => 'أقراص',  'company' => 'شركة سيريا فارما',          'net' => 340,   'public' => 480,   'stock' => 280],
        ];

        foreach ($products as $row) {
            $companyId = $companies[$row['company']] ?? null;

            $product = Product::firstOrCreate(
                ['barcode' => $row['barcode']],
                [
                    'name'       => $row['name'],
                    'barcode'    => $row['barcode'],
                    'unit'       => $row['unit'],
                    'form'       => $row['form'],
                    'details'    => $row['form'] . ' - ' . $row['unit'],
                    'company_id' => $companyId,
                    'price'      => $row['public'],   // legacy field
                    'quantity'   => 0,                // managed via stock_movements
                    'min_stock'  => 20,
                    'is_active'  => true,
                    'user_id'    => $admin?->id ?? 1,
                ]
            );

            // Product price (upsert so re-seeding updates prices)
            ProductPrice::updateOrCreate(
                ['product_id' => $product->id],
                [
                    'net_price_syp'    => $row['net'],
                    'public_price_syp' => $row['public'],
                ]
            );

            // Opening stock movement — only if none exists yet
            $hasOpeningStock = StockMovement::where('product_id', $product->id)
                ->where('type', 'opening')
                ->exists();

            if (! $hasOpeningStock) {
                StockMovement::create([
                    'product_id'     => $product->id,
                    'type'           => 'opening',
                    'quantity'       => $row['stock'],
                    'reference_type' => null,
                    'reference_id'   => null,
                    'notes'          => 'رصيد افتتاحي',
                    'created_by'     => $admin?->id ?? 1,
                ]);
            }
        }

        $this->command->info('✓ Products seeded (30 products + prices + opening stock)');
    }
}
