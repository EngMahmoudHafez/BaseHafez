<?php

namespace App\Modules\Auth\database\factories;

use App\Modules\Auth\Enums\UserStatus;
use App\Modules\Auth\Models\Manager;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Manager>
 */
class ManagerFactory extends Factory
{
    protected $model = Manager::class;

    public function definition(): array
    {
        return [
            'country_id' => null,
            'name' => fake()->words(3, true),
            'email' => fake()->unique()->safeEmail(),
            'phone' => fake()->unique()->numerify('+9665########'),
            'whatsapp' => fake()->unique()->numerify('+9665########'),
            'password' => bcrypt('password'),
            'avatar' => 'files/' . fake()->uuid() . '.pdf',
            'status' => UserStatus::Active,
            'last_login_at' => null,
        ];
    }
}
