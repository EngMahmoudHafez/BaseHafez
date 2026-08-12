<?php

namespace App\Modules\Base\Support;

final class CsvCell
{
    public static function escape(mixed $value): mixed
    {
        if (! is_string($value) || $value === '') {
            return $value;
        }

        return preg_match('/^[=+\-@\t\r]/', $value) === 1
            ? "'{$value}"
            : $value;
    }

    /**
     * @param  list<mixed>  $row
     * @return list<mixed>
     */
    public static function escapeRow(array $row): array
    {
        return array_map(self::escape(...), $row);
    }
}
