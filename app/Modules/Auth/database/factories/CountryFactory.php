<?php

namespace App\Modules\Auth\database\factories;

use App\Modules\Auth\Models\Country;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Country>
 */
class CountryFactory extends Factory
{
    protected $model = Country::class;

    public function definition(): array
    {
        return [
            'name' => fake()->country(),
            'iso2' => fake()->unique()->countryCode(),
            'dial_code' => '+' . fake()->unique()->numberBetween(1, 9999),
            'is_active' => true,
        ];
    }
}
