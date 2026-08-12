<?php

namespace App\Modules\Structure\database\seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            StructureSeeder::class,
            // module-model-seeders
        ]);
    }
}
