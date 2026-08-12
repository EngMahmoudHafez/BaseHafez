<?php

namespace App\Modules\Auth\Http\Services\Api\V1\Auth;

use App\Modules\Auth\Enums\UserStatus;
use App\Modules\Auth\Models\Country;
use App\Modules\Auth\Models\User;

class AuthAccountService
{
    public function resolveCountry(array $validatedInput): Country
    {
        return isset($validatedInput['country_id'])
            ? Country::query()->findOrFail($validatedInput['country_id'])
            : Country::query()->where('dial_code', $validatedInput['country_code'])->firstOrFail();
    }

    public function normalizeContactNumber(?string $number, string $dialCode): ?string
    {
        if ($number === null || trim($number) === '') {
            return null;
        }

        $number = trim($number);

        if (str_starts_with($number, '+')) {
            return $number;
        }

        return $dialCode . ltrim($number, '0');
    }

    public function findUserByCountryPhone(Country $country, string $phone): ?User
    {
        return User::query()
            ->where('country_id', $country->id)
            ->where(function ($query) use ($phone) {
                $query->where('phone', $phone)
                    ->orWhere('whatsapp', $phone);
            })
            ->first();
    }

    public function pendingAuthUserAttributes(Country $country, array $signUpInput, string $phone): array
    {
        return [
            'country_id' => $country->id,
            'name' => $signUpInput['name'],
            'phone' => $phone,
            'whatsapp' => $phone,
            'email' => $signUpInput['email'] ?? null,
            'status' => UserStatus::Pending,
        ];
    }

    public function createOrUpdatePendingAuthUser(?User $user, array $userAttributes): User
    {
        if ($user) {
            $user->forceFill($userAttributes)->save();

            return $user;
        }

        return User::query()->create($userAttributes);
    }

    public function emailAlreadyBelongsToAnotherUser(string $email, ?int $currentUserId): bool
    {
        return User::query()
            ->where('email', $email)
            ->when($currentUserId, fn ($query) => $query->whereKeyNot($currentUserId))
            ->exists();
    }

    public function userPayload(User $user, ?string $otpToken = null, ?string $token = null): array
    {
        return array_filter([
            'id' => $user->id,
            'name' => $user->name,
            'username' => $user->name,
            'country_id' => $user->country_id,
            'phone' => $user->phone,
            'whatsapp' => $user->whatsapp,
            'email' => $user->email,
            'status' => $this->statusValue($user),
            'otp_token' => $otpToken,
            'token' => $token,
        ], fn ($value) => $value !== null);
    }

    public function maskedContact(?string $value): ?string
    {
        if ($value === null || $value === '') {
            return null;
        }

        $length = strlen($value);

        if ($length <= 6) {
            return str_repeat('*', $length);
        }

        return substr($value, 0, 4) . str_repeat('*', max(2, $length - 7)) . substr($value, -3);
    }

    public function isActive(User $user): bool
    {
        return $this->statusValue($user) === UserStatus::Active->value;
    }

    public function isBlocked(User $user): bool
    {
        return $this->statusValue($user) === UserStatus::Blocked->value;
    }

    public function isPending(User $user): bool
    {
        return $this->statusValue($user) === UserStatus::Pending->value;
    }

    public function statusValue(User $user): string
    {
        return $user->status instanceof UserStatus ? $user->status->value : (string) $user->status;
    }
}
