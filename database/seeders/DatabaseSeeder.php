<?php

namespace Database\Seeders;

use App\Support\ModuleDiscovery;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $seeders = ModuleDiscovery::databaseSeeders();

        if ($seeders === []) {
            return;
        }

        $this->call($seeders);
    }
}
