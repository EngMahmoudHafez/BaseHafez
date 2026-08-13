<?php

namespace App\Support;

use Illuminate\Support\Collection;
use ReflectionClass;

class ModuleDiscovery
{
    /**
     * @return array<int, class-string>
     */
    public static function serviceProviders(): array
    {
        return self::classesMatching('app/Modules/*/Providers/*ServiceProvider.php')
            ->reject(fn (string $class): bool => self::disablesAutoDiscovery($class))
            ->values()
            ->all();
    }

    /**
     * @return array<int, class-string>
     */
    public static function databaseSeeders(): array
    {
        $moduleSeeders = self::classesMatching('app/Modules/*/database/seeders/DatabaseSeeder.php');
        $modulesWithSeeder = $moduleSeeders
            ->map(fn (string $class): ?string => explode('\\', $class)[2] ?? null)
            ->filter()
            ->all();

        $standaloneSeeders = self::classesMatching('app/Modules/*/database/seeders/*Seeder.php')
            ->reject(fn (string $class): bool => str_ends_with($class, '\\DatabaseSeeder'))
            ->reject(fn (string $class): bool => in_array(explode('\\', $class)[2] ?? null, $modulesWithSeeder, true));

        return $moduleSeeders->merge($standaloneSeeders)->unique()->sort()->values()->all();
    }

    /**
     * @return Collection<int, class-string>
     */
    private static function classesMatching(string $pattern): Collection
    {
        return collect(glob(base_path($pattern)) ?: [])
            ->filter(fn (string $path): bool => is_file($path))
            ->map(fn (string $path): string => self::classNameFromPath($path))
            ->filter(fn (string $class): bool => class_exists($class))
            ->unique()
            ->sort()
            ->values();
    }

    private static function classNameFromPath(string $path): string
    {
        $relativePath = str_replace(base_path() . DIRECTORY_SEPARATOR, '', $path);
        $classPath = preg_replace('/\.php$/', '', $relativePath) ?? $relativePath;

        return str_replace([DIRECTORY_SEPARATOR, 'app\\'], ['\\', 'App\\'], $classPath);
    }

    private static function disablesAutoDiscovery(string $class): bool
    {
        if (! class_exists($class)) {
            return false;
        }

        $reflection = new ReflectionClass($class);

        if ($reflection->isAbstract()) {
            return true;
        }

        $constant = $reflection->getReflectionConstant('AUTO_DISCOVER');

        return $constant !== false
            && $constant->getDeclaringClass()->getName() === $class
            && $constant->getValue() === false;
    }
}
