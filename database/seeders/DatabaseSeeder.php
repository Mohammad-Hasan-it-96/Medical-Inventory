<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * Order matters: Users → Companies → Products → Pharmacies
     * (Products depend on Companies; Pharmacies depend on Users/reps)
     */
    public function run(): void
    {
        $this->call([
            // Infrastructure / config
            SystemConfigSeeder::class,
            LanguageSeeder::class,

            // Core data — must run in this order
            UserSeeder::class,      // admin + 2 reps
            CompanySeeder::class,   // 5 companies
            ProductSeeder::class,   // 30 products + prices + opening stock
            PharmacySeeder::class,  // 20 pharmacies (10 per rep)

            // Transactional data — depends on all above
            OrderSeeder::class,     // 5 orders (2 confirmed, 2 pending, 1 draft)
            PaymentSeeder::class,   // 3 payments against confirmed orders
        ]);
    }
}
