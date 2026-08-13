<?php

namespace App\Modules\Settings\database\seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            SettingsSeeder::class,
            // module-model-seeders
        ]);
    }
}
