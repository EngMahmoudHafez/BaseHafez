<?php

namespace App\Modules\Structure\database\factories;

use App\Modules\Structure\Models\Structure;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Structure>
 */
class StructureFactory extends Factory
{
    protected $model = Structure::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'key' => fake()->unique()->slug(2),
            'content' => [
                'ar' => ['title' => fake()->sentence()],
                'en' => ['title' => fake()->sentence()],
            ],
        ];
    }
}
