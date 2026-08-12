<?php

namespace App\Modules\Auth\database\factories;

use App\Modules\Auth\Enums\UserStatus;
use App\Modules\Auth\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<User>
 */
class UserFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = User::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'country_id' => null,
            'name' => fake()->name(),
            'phone' => '+20' . fake()->unique()->numberBetween(100000000, 199999999),
            'whatsapp' => '+20' . fake()->unique()->numberBetween(200000000, 299999999),
            'email' => fake()->unique()->safeEmail(),
            'password' => bcrypt('password'),
            'avatar' => null,
            'status' => UserStatus::Active,
            'last_login_at' => null,
        ];
    }
}
