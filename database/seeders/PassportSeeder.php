<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Artisan;

/**
 * Creates the Passport Personal Access Client so that
 * `php artisan migrate:fresh --seed` produces a fully working API
 * without a separate `passport:client --personal` step.
 *
 * Safe to run multiple times — skips creation when a client already exists.
 */
class PassportSeeder extends Seeder
{
    public function run(): void
    {
        if (\DB::table('oauth_personal_access_clients')->exists()) {
            $this->command->getOutput()->writeln(
                '  <info>✓ Passport personal access client already exists — skipped.</info>'
            );
            return;
        }

        Artisan::call('passport:client', [
            '--personal'       => true,
            '--no-interaction' => true,
        ]);

        $this->command->getOutput()->writeln(
            '  <info>✓ Passport personal access client created.</info>'
        );
    }
}
