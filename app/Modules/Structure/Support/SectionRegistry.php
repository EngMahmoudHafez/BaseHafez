<?php

namespace App\Modules\Structure\Support;

/**
 * Typed access to the declarative section registry (`Support/sections.php`).
 *
 * Every editable content section is a generic page defined once in that file.
 * This class exposes the definitions to the generic controller, request,
 * service, seeder and public serializer.
 */
final class SectionRegistry
{
    public const MAX_ITEMS = 20;

    /** @var array<string, mixed>|null */
    private static ?array $registry = null;

    /**
     * @return array<string, array<string, mixed>>
     */
    public static function all(): array
    {
        return self::registry()['sections'];
    }

    public static function has(string $key): bool
    {
        return array_key_exists($key, self::all());
    }

    /**
     * @return array<string, mixed>
     */
    public static function find(string $key): array
    {
        return self::all()[$key] ?? throw new \InvalidArgumentException("Unknown structure section [{$key}].");
    }

    /**
     * Section keys that get a dashboard route (index + store), in registry order.
     *
     * @return list<string>
     */
    public static function routableKeys(): array
    {
        return array_keys(self::all());
    }

    /**
     * @return list<string>
     */
    public static function locales(): array
    {
        return self::registry()['locales'];
    }

    public static function label(string $key): string
    {
        return self::find($key)['label'] ?? $key;
    }

    /**
     * Whether the section is exposed through the public page endpoint.
     */
    public static function isPublic(string $key): bool
    {
        return self::has($key) && (bool) (self::find($key)['public'] ?? false);
    }

    public static function isTranslated(string $key): bool
    {
        return (bool) (self::find($key)['translated'] ?? true);
    }

    /**
     * Foundation seed content declared per section, keyed by section key.
     *
     * @return array<string, array<string, mixed>>
     */
    public static function seedDefaults(): array
    {
        return array_filter(array_map(
            fn (array $section): ?array => $section['defaults'] ?? null,
            self::all(),
        ));
    }

    /**
     * Empty-but-shaped default content for one locale of a section, used as the
     * merge base so the editor always renders every declared field.
     *
     * @return array<string, mixed>
     */
    public static function pageLocalizedDefaults(string $key): array
    {
        $section = self::find($key);
        $defaults = self::emptyFieldGroup($section['fields'] ?? []);

        foreach ($section['groups'] ?? [] as $group => $definition) {
            $defaults[$group] = self::emptyFieldGroup($definition['fields'] ?? []);
        }

        foreach ($section['repeatables'] ?? [] as $name => $definition) {
            if (! ($definition['shared'] ?? false)) {
                $defaults[$name] = [];
            }
        }

        return $defaults;
    }

    /**
     * @param  array<string, mixed>  $fields
     * @return array<string, mixed>
     */
    private static function emptyFieldGroup(array $fields): array
    {
        return array_map(fn (): string => '', $fields);
    }

    /**
     * @return array<string, mixed>
     */
    private static function registry(): array
    {
        return self::$registry ??= require __DIR__ . '/sections.php';
    }
}
