<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

        User::factory()->create([
            'name' => 'Super Admin',
            'email' => 'admin@Care.com',
            'password' => bcrypt('secret'),
            'role' => 'admin',
        ]);

        // Call the SystemConfigSeeder
        $this->call([
            SystemConfigSeeder::class,
            LanguageSeeder::class,
            // Add other seeders here if needed
        ]);
    }
}
