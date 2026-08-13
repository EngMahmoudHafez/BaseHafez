<?php

namespace App\Support;

final class PersonalName
{
    public static function combine(?string $firstName, ?string $lastName): ?string
    {
        $name = trim(implode(' ', array_filter([
            self::normalizePart($firstName),
            self::normalizePart($lastName),
        ])));

        return $name !== '' ? $name : null;
    }

    /** @return array{string|null, string|null} */
    public static function split(?string $fullName): array
    {
        $name = self::normalizePart($fullName);

        if ($name === null) {
            return [null, null];
        }

        $parts = preg_split('/\s+/', $name) ?: [];
        $firstName = $parts[0] ?? null;
        $lastName = count($parts) > 1 ? implode(' ', array_slice($parts, 1)) : null;

        return [$firstName, $lastName];
    }

    private static function normalizePart(?string $value): ?string
    {
        $value = trim((string) $value);

        return $value !== '' ? $value : null;
    }
}
