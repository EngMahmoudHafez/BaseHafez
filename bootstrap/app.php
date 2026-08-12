<?php

use App\Http\Middleware\SetJsonEncodingOptions;
use App\Modules\Auth\Http\Middleware\CheckManagerActive;
use App\Modules\Auth\Http\Middleware\EnsureApiUserActive;
use App\Modules\Base\Http\Responses\ApiResponse;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Middleware\HandleCors;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Laratrust\Middleware\Ability;
use Laratrust\Middleware\Permission;
use Laratrust\Middleware\Role;
use Mcamara\LaravelLocalization\Facades\LaravelLocalization;
use Mcamara\LaravelLocalization\Middleware\LaravelLocalizationRedirectFilter;
use Mcamara\LaravelLocalization\Middleware\LaravelLocalizationRoutes;
use Mcamara\LaravelLocalization\Middleware\LaravelLocalizationViewPath;
use Mcamara\LaravelLocalization\Middleware\LocaleCookieRedirect;
use Mcamara\LaravelLocalization\Middleware\LocaleSessionRedirect;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__ . '/../routes/web.php',
        commands: __DIR__ . '/../routes/console.php',
        channels: __DIR__ . '/../routes/channels.php',
        health: '/up',
        then: function () {
            // Module routes are loaded by each module service provider.
        },
    )
    ->withMiddleware(function (Middleware $middleware): void {
        // Enable CORS for all requests
        $middleware->prepend(HandleCors::class);

        // Apply JSON encoding options to all API responses
        $middleware->append(SetJsonEncodingOptions::class);

        // Restrict the trusted Host header to the application's own host (and its
        // subdomains) so an attacker cannot poison absolute URLs — e.g. the
        // password-reset link generated from the request. Skipped in local/testing
        // by the framework; active in production, where APP_URL must be set.
        $middleware->trustHosts(at: fn (): array => array_values(array_filter([
            parse_url((string) config('app.url'), PHP_URL_HOST),
        ])));

        // Redirect guests to localized login page
        $middleware->redirectGuestsTo(function ($request) {
            // Get locale from URL segment or default to app locale
            $locale = $request->segment(1);
            $supportedLocales = ['ar', 'en'];

            if (! in_array($locale, $supportedLocales)) {
                $locale = app()->getLocale();
            }

            // Redirect to login page with localization
            return LaravelLocalization::getLocalizedURL($locale, route('login', [], false));
        });

        // Custom redirect for authenticated users (dashboard)
        $middleware->redirectUsersTo(function () {
            $locale = app()->getLocale();

            return LaravelLocalization::getLocalizedURL(
                $locale,
                route('dashboard.home', [], false),
            );
        });

        // Register middleware aliases
        $middleware->alias([
            'localize' => LaravelLocalizationRoutes::class,
            'localizationRedirect' => LaravelLocalizationRedirectFilter::class,
            'localeSessionRedirect' => LocaleSessionRedirect::class,
            'localeCookieRedirect' => LocaleCookieRedirect::class,
            'localeViewPath' => LaravelLocalizationViewPath::class,
            'manager.active' => CheckManagerActive::class,
            'api.active' => EnsureApiUserActive::class,
            'role' => Role::class,
            'permission' => Permission::class,
            'ability' => Ability::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        // Every API (JSON) error uses the shared ApiResponse envelope
        // {status, message, data}; web requests fall back to the styled error
        // views under resources/views/errors.
        $exceptions->render(function (Throwable $e, Request $request): ?JsonResponse {
            if (! $request->expectsJson()) {
                return null;
            }

            if ($e instanceof ValidationException) {
                return ApiResponse::error(422, $e->validator->errors()->first(), [$e->errors()]);
            }

            if ($e instanceof AuthenticationException) {
                return ApiResponse::error(401, __('errors.unauthenticated'));
            }

            if ($e instanceof AuthorizationException) {
                return ApiResponse::error(403, __('errors.forbidden'));
            }

            if ($e instanceof ModelNotFoundException) {
                return ApiResponse::error(404, __('errors.not_found'));
            }

            $status = $e instanceof HttpExceptionInterface ? $e->getStatusCode() : 500;

            // Preserve the debug page for unexpected server errors locally.
            if ($status >= 500 && config('app.debug')) {
                return null;
            }

            $message = $status < 500 && $e->getMessage() !== ''
                ? $e->getMessage()
                : match ($status) {
                    401 => __('errors.unauthenticated'),
                    403 => __('errors.forbidden'),
                    404 => __('errors.not_found'),
                    default => __('errors.server_error'),
                };

            return ApiResponse::error($status, $message);
        });
    })->create();
