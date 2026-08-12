<?php

namespace App\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Console\Application as ConsoleApplication;
use Illuminate\Foundation\Console\RouteListCommand;
use Illuminate\Http\Request;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // Telescope is a dev-only tool: it is excluded from package auto-discovery
        // (see composer.json "dont-discover") and registered here for local only.
        if ($this->app->environment('local') && class_exists(\Laravel\Telescope\TelescopeServiceProvider::class)) {
            $this->app->register(\Laravel\Telescope\TelescopeServiceProvider::class);
            $this->app->register(TelescopeServiceProvider::class);
        }
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Paginator::useBootstrapFive();

        // Rate-limit dashboard authentication (login, forgot/reset password) to
        // blunt credential brute-force and reset flooding: tight per email+IP,
        // looser per IP.
        RateLimiter::for('dashboard-auth', function (Request $request): array {
            $email = (string) $request->input('email');

            return [
                Limit::perMinute(5)->by($email . '|' . (string) $request->ip()),
                Limit::perMinute(20)->by((string) $request->ip()),
            ];
        });

        // mcamara/laravel-localization replaces Laravel's `route:list` with a locale-aware
        // command whose signature is incompatible with Laravel 13's RouteListCommand, which
        // breaks `php artisan route:list`. Reclaim the framework command once the console
        // application has finished registering every package command.
        ConsoleApplication::starting(function (ConsoleApplication $artisan): void {
            $artisan->add($this->app->make(RouteListCommand::class));
        });
    }
}
