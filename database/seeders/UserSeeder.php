<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        // ── Admin ─────────────────────────────────────────────────────────────
        User::firstOrCreate(
            ['email' => 'admin@example.com'],
            [
                'name'     => 'مدير النظام',
                'password' => Hash::make('password'),
                'role'     => 'admin',
            ]
        );

        // ── Sales Reps ────────────────────────────────────────────────────────
        User::firstOrCreate(
            ['email' => 'rep1@example.com'],
            [
                'name'     => 'أحمد الحسن',
                'password' => Hash::make('password'),
                'role'     => 'rep',
            ]
        );

        User::firstOrCreate(
            ['email' => 'rep2@example.com'],
            [
                'name'     => 'محمد العلي',
                'password' => Hash::make('password'),
                'role'     => 'rep',
            ]
        );

        $this->command->info('✓ Users seeded (admin + 2 reps)');
    }
}

