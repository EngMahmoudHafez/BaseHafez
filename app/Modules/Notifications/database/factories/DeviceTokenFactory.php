<?php

namespace App\Modules\Notifications\database\factories;

use App\Modules\Notifications\Models\DeviceToken;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<DeviceToken>
 */
class DeviceTokenFactory extends Factory
{
    protected $model = DeviceToken::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'token' => fake()->unique()->sha256(),
            'platform' => fake()->randomElement([
                DeviceToken::PLATFORM_ANDROID,
                DeviceToken::PLATFORM_IOS,
                DeviceToken::PLATFORM_WEB,
            ]),
            'last_used_at' => now(),
        ];
    }
}
