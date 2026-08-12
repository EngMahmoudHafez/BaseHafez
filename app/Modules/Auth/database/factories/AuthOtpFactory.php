<?php

namespace App\Modules\Auth\database\factories;

use App\Modules\Auth\Models\AuthOtp;
use App\Modules\Auth\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * @extends Factory<AuthOtp>
 */
class AuthOtpFactory extends Factory
{
    protected $model = AuthOtp::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'country_id' => null,
            'token' => Str::random(64),
            'identifier' => fake()->unique()->numerify('+9665########'),
            'code_hash' => Hash::make('1111'),
            'channel' => 'phone',
            'purpose' => 'login',
            'expires_at' => now()->addMinutes(5),
            'verified_at' => null,
            'attempts' => 0,
        ];
    }
}
