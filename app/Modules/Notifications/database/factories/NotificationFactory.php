<?php

namespace App\Modules\Notifications\database\factories;

use App\Modules\Auth\Models\User;
use App\Modules\Notifications\Models\Notification;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Notification>
 */
class NotificationFactory extends Factory
{
    protected $model = Notification::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'notifiable_type' => User::class,
            'notifiable_id' => User::factory(),
            'title_ar' => fake()->sentence(),
            'title_en' => fake()->sentence(),
            'body_ar' => fake()->paragraph(),
            'body_en' => fake()->paragraph(),
            'type' => fake()->randomElement([
                Notification::TYPE_GENERAL,
                Notification::TYPE_SYSTEM,
                Notification::TYPE_PROMOTION,
            ]),
            'data' => [],
            'is_read' => false,
            'sent_at' => now(),
        ];
    }

    public function read(): static
    {
        return $this->state(fn (): array => [
            'is_read' => true,
            'read_at' => now(),
        ]);
    }
}
