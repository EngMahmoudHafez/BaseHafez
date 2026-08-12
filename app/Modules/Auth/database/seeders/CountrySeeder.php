<?php

namespace App\Modules\Auth\database\seeders;

use App\Modules\Auth\Models\Country;
use Illuminate\Database\Seeder;

class CountrySeeder extends Seeder
{
    public function run(): void
    {
        $countries = [
            ['name' => 'المملكة العربية السعودية', 'iso2' => 'SA', 'dial_code' => '+966'],
            ['name' => 'الإمارات العربية المتحدة', 'iso2' => 'AE', 'dial_code' => '+971'],
            ['name' => 'الكويت', 'iso2' => 'KW', 'dial_code' => '+965'],
            ['name' => 'قطر', 'iso2' => 'QA', 'dial_code' => '+974'],
            ['name' => 'البحرين', 'iso2' => 'BH', 'dial_code' => '+973'],
            ['name' => 'عمان', 'iso2' => 'OM', 'dial_code' => '+968'],
            ['name' => 'مصر', 'iso2' => 'EG', 'dial_code' => '+20'],
            ['name' => 'المغرب', 'iso2' => 'MA', 'dial_code' => '+212'],
            ['name' => 'الجزائر', 'iso2' => 'DZ', 'dial_code' => '+213'],
            ['name' => 'تونس', 'iso2' => 'TN', 'dial_code' => '+216'],
        ];

        foreach ($countries as $country) {
            Country::query()->updateOrCreate(
                ['iso2' => $country['iso2']],
                [
                    'name' => $country['name'],
                    'dial_code' => $country['dial_code'],
                    'is_active' => true,
                ],
            );
        }
    }
}
