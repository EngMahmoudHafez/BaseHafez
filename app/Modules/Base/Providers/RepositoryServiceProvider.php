<?php

namespace App\Modules\Base\Providers;

use Illuminate\Support\ServiceProvider;

class RepositoryServiceProvider extends ServiceProvider
{
    public const AUTO_DISCOVER = false;

    public function register(): void
    {
        foreach (glob(base_path('app/Modules/*/Repositories/Eloquent/*Repository.php')) ?: [] as $path) {
            $implementation = $this->classNameFromPath($path);
            $interface = str_replace('\\Eloquent\\', '\\', $implementation) . 'Interface';

            if (interface_exists($interface) && class_exists($implementation)) {
                $this->app->bind($interface, $implementation);
            }
        }
    }

    private function classNameFromPath(string $path): string
    {
        $relativePath = str_replace(base_path() . DIRECTORY_SEPARATOR, '', $path);
        $classPath = preg_replace('/\.php$/', '', $relativePath) ?? $relativePath;

        return str_replace([DIRECTORY_SEPARATOR, 'app\\'], ['\\', 'App\\'], $classPath);
    }
}
