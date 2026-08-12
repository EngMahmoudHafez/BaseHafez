<?php

namespace App\Modules\Structure\database\seeders;

use App\Modules\Structure\Models\Structure;
use App\Modules\Structure\Support\SectionRegistry;
use Illuminate\Database\Seeder;

/**
 * Seeds the foundation content for each section that declares `defaults` in the
 * registry. Idempotent and registry-driven: adding a seeded section is a single
 * `defaults` entry in `Support/sections.php`.
 */
class StructureSeeder extends Seeder
{
    public function run(): void
    {
        foreach (SectionRegistry::seedDefaults() as $key => $content) {
            Structure::query()->updateOrCreate(
                ['key' => $key],
                ['content' => $content],
            );
        }
    }
}
