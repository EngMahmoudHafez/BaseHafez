<?php

namespace App\Modules\Settings\database\factories;

use App\Modules\Settings\Models\Setting;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Setting>
 */
class SettingFactory extends Factory
{
    protected $model = Setting::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'key' => fake()->unique()->slug(2),
            'value' => fake()->sentence(),
            'show_in_dashboard' => true,
            'notes' => fake()->optional()->sentence(),
        ];
    }

    public function hidden(): static
    {
        return $this->state(fn (): array => ['show_in_dashboard' => false]);
    }
}
