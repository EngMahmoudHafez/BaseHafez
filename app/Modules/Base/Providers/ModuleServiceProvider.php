<?php

namespace App\Modules\Base\Providers;

use Illuminate\Console\Command;
use Illuminate\Support\ServiceProvider;

abstract class ModuleServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $modulePath = $this->modulePath();

        $this->loadRouteIfPresent($modulePath . '/Routes/api/v1/web.php');
        $this->loadRouteIfPresent($modulePath . '/Routes/dashboard/dashboard.php');
        $this->loadMigrationsFrom($modulePath . '/database/migrations');
        $this->loadViewsFrom($modulePath . '/Resources/views', $this->moduleViewNamespace());

        $translationsPath = $modulePath . '/Resources/lang';
        if (is_dir($translationsPath)) {
            $this->loadTranslationsFrom($translationsPath, $this->moduleViewNamespace());
        }

        $this->registerModuleCommands($modulePath);
        $this->bootModule();
    }

    protected function bootModule(): void {}

    protected function moduleViewNamespace(): string
    {
        return strtolower($this->moduleName());
    }

    private function moduleName(): string
    {
        return explode('\\', static::class)[2];
    }

    private function modulePath(): string
    {
        return base_path('app/Modules/' . $this->moduleName());
    }

    private function loadRouteIfPresent(string $path): void
    {
        if (is_file($path)) {
            $this->loadRoutesFrom($path);
        }
    }

    private function registerModuleCommands(string $modulePath): void
    {
        if (! $this->app->runningInConsole()) {
            return;
        }

        $commandFiles = glob($modulePath . '/Console/Commands/*.php') ?: [];
        $commands = collect($commandFiles)
            ->map(fn (string $path): string => $this->classNameFromPath($path))
            ->filter(fn (string $class): bool => class_exists($class) && is_subclass_of($class, Command::class))
            ->values()
            ->all();

        if ($commands !== []) {
            $this->commands($commands);
        }
    }

    private function classNameFromPath(string $path): string
    {
        $relativePath = str_replace(base_path() . DIRECTORY_SEPARATOR, '', $path);
        $classPath = preg_replace('/\.php$/', '', $relativePath) ?? $relativePath;

        return str_replace([DIRECTORY_SEPARATOR, 'app\\'], ['\\', 'App\\'], $classPath);
    }
}
