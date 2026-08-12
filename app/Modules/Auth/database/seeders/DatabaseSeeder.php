<?php

namespace App\Modules\Auth\database\seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            CountrySeeder::class,
            LaratrustSeeder::class,
            ManagerSeeder::class,
            // module-model-seeders
        ]);
    }
}
