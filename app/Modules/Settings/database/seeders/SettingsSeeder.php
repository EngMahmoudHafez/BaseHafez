<?php

namespace App\Modules\Settings\database\seeders;

use Illuminate\Database\Seeder;

class SettingsSeeder extends Seeder
{
    public function run(): void
    {
        // Intentionally empty. The base ships no business settings. Seed deterministic
        // entries with firstOrCreate() (never truncate) only when a project needs them.
    }
}
