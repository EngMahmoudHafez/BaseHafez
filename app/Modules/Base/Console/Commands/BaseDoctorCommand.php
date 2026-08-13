<?php

namespace App\Modules\Base\Console\Commands;

use App\Support\ModuleDiscovery;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use PDO;
use Throwable;

/**
 * Environment and structural health check. Run without options for a general
 * report; run with --production to fail the process on any unsafe production
 * setting so it can gate a deploy.
 */
class BaseDoctorCommand extends Command
{
    protected $signature = 'base:doctor {--production : Treat production-hardening problems as failures}';

    protected $description = 'Diagnose environment and module health; enforce production hardening with --production.';

    private const PASS = 'pass';

    private const WARN = 'warn';

    private const FAIL = 'fail';

    /**
     * Bootstrap passwords that must never survive into production.
     *
     * @var list<string>
     */
    private const KNOWN_WEAK_ADMIN_PASSWORDS = ['123123123', 'password', 'secret', 'admin', 'changeme'];

    /**
     * @var list<array{status: string, label: string, detail: string}>
     */
    private array $checks = [];

    public function handle(): int
    {
        $production = (bool) $this->option('production');

        $this->checkKeys();
        $this->checkDatabase();
        $this->checkModuleContract();

        if ($production) {
            $this->checkProductionHardening();
        }

        $this->render();

        $failed = collect($this->checks)->contains(fn (array $check): bool => $check['status'] === self::FAIL);

        if ($failed) {
            $this->newLine();
            $this->error('base:doctor found blocking problems.');

            return self::FAILURE;
        }

        $this->newLine();
        $this->info($production ? 'Production hardening checks passed.' : 'No blocking problems found.');

        return self::SUCCESS;
    }

    private function checkKeys(): void
    {
        $this->record(
            filled(config('app.key')) ? self::PASS : self::FAIL,
            'Application key',
            filled(config('app.key')) ? 'set' : 'missing — run php artisan base:setup',
        );

        $this->record(
            filled(config('jwt.secret')) ? self::PASS : self::FAIL,
            'JWT secret',
            filled(config('jwt.secret')) ? 'set' : 'missing — run php artisan jwt:secret',
        );
    }

    /**
     * This base is MySQL-official: a non-mysql driver is a hard failure, and the
     * connection is probed for real so a misconfigured or unreachable database is
     * surfaced here rather than only when the first query runs.
     */
    private function checkDatabase(): void
    {
        $driver = config('database.default');
        $isMysql = $driver === 'mysql';

        $this->record(
            $isMysql ? self::PASS : self::FAIL,
            'Database driver',
            match (true) {
                $isMysql => 'mysql',
                is_string($driver) && $driver !== '' => $driver . ' — this base is MySQL-official; set DB_CONNECTION=mysql',
                default => 'not set — set DB_CONNECTION=mysql',
            },
        );

        $host = $this->stringConfig('database.connections.mysql.host');
        $port = $this->stringConfig('database.connections.mysql.port');

        $this->record(
            $host !== '' ? self::PASS : self::FAIL,
            'Database host',
            match (true) {
                $host !== '' && $port !== '' => $host . ':' . $port,
                $host !== '' => $host,
                default => 'DB_HOST is not set',
            },
        );

        try {
            $pdo = DB::connection()->getPdo();
            $connectionError = null;
        } catch (Throwable $exception) {
            $pdo = null;
            $connectionError = $exception->getMessage();
        }

        $this->record(
            $pdo instanceof PDO ? self::PASS : self::FAIL,
            'Database connection',
            $pdo instanceof PDO ? 'connected' : 'cannot connect — ' . $connectionError,
        );

        $database = $this->stringConfig('database.connections.mysql.database');
        $this->record(
            $database !== '' ? self::PASS : self::FAIL,
            'Database name',
            $database !== '' ? $database : 'DB_DATABASE is not set',
        );

        $serverVersion = $pdo instanceof PDO ? $pdo->getAttribute(PDO::ATTR_SERVER_VERSION) : null;
        $serverVersion = is_scalar($serverVersion) ? (string) $serverVersion : '';
        $this->record(
            $serverVersion !== '' ? self::PASS : self::WARN,
            'MySQL server version',
            $serverVersion !== '' ? $serverVersion : 'unavailable — no connection',
        );
    }

    /**
     * Read a config value that is expected to be a string, coercing scalars and
     * treating anything else (null, arrays) as an empty string.
     */
    private function stringConfig(string $key): string
    {
        $value = config($key);

        return is_scalar($value) ? (string) $value : '';
    }

    /**
     * Every directory under app/Modules must resolve to a discoverable service
     * provider, otherwise it is "present but not loaded" — routes, migrations,
     * and views would silently never register.
     */
    private function checkModuleContract(): void
    {
        $moduleDirectories = collect(glob(base_path('app/Modules/*'), GLOB_ONLYDIR) ?: [])
            ->map(fn (string $path): string => basename($path));

        $loadedModules = collect(ModuleDiscovery::serviceProviders())
            ->map(fn (string $class): string => Str::before(Str::after($class, 'App\\Modules\\'), '\\'))
            ->push('Base')
            ->unique();

        foreach ($moduleDirectories as $module) {
            $loaded = $loadedModules->contains($module);

            $this->record(
                $loaded ? self::PASS : self::FAIL,
                "Module: {$module}",
                $loaded ? 'service provider discovered' : 'no discoverable ServiceProvider — module will not load',
            );
        }
    }

    private function checkProductionHardening(): void
    {
        $this->record(
            config('app.env') === 'production' ? self::PASS : self::WARN,
            'APP_ENV',
            (string) config('app.env'),
        );

        $this->record(
            config('app.debug') === false ? self::PASS : self::FAIL,
            'APP_DEBUG',
            config('app.debug') ? 'true — leaks stack traces in production' : 'false',
        );

        $wildcardCors = config('cors.allowed_origins') === ['*'];
        $this->record(
            $wildcardCors ? self::FAIL : self::PASS,
            'CORS allowed origins',
            $wildcardCors ? 'wildcard "*" — set CORS_ALLOWED_ORIGINS to an explicit allowlist' : 'explicit allowlist',
        );

        $adminPassword = config('foundation.admin.password');
        $adminPassword = is_string($adminPassword) ? $adminPassword : '';
        $adminPasswordWeak = $adminPassword !== ''
            && (mb_strlen($adminPassword) < 12 || in_array($adminPassword, self::KNOWN_WEAK_ADMIN_PASSWORDS, true));

        $this->record(
            $adminPassword === '' ? self::PASS : self::FAIL,
            'Bootstrap admin credentials',
            match (true) {
                $adminPasswordWeak => 'weak or well-known BASE_ADMIN_PASSWORD — rotate now and unset it after the first manager exists',
                $adminPassword !== '' => 'BASE_ADMIN_PASSWORD still set — unset it after the first manager exists',
                default => 'removed',
            },
        );

        $queueUnsafe = in_array(config('queue.default'), ['sync', null], true);
        $this->record(
            $queueUnsafe ? self::FAIL : self::PASS,
            'Queue connection',
            $queueUnsafe
                ? (config('queue.default') === 'sync' ? 'sync — jobs run inline on the request; use a real queue + worker' : 'not configured')
                : (string) config('queue.default'),
        );

        $mailUnsafe = in_array(config('mail.default'), ['log', 'array'], true);
        $this->record(
            $mailUnsafe ? self::FAIL : self::PASS,
            'Mail transport',
            $mailUnsafe ? config('mail.default') . ' — password recovery will not be delivered' : (string) config('mail.default'),
        );

        $this->record(
            in_array(config('session.driver'), ['array'], true) ? self::WARN : self::PASS,
            'Session driver',
            (string) config('session.driver'),
        );

        $this->record(
            config('session.secure') === true ? self::PASS : self::WARN,
            'Secure session cookie',
            config('session.secure') === true ? 'enabled' : 'SESSION_SECURE_COOKIE is not true — session cookies may travel over plain HTTP',
        );

        $appUrl = (string) config('app.url');
        $appUrlUnsafe = Str::contains($appUrl, ['localhost', '127.0.0.1']) || ! Str::startsWith($appUrl, 'https://');
        $this->record(
            $appUrlUnsafe ? self::FAIL : self::PASS,
            'APP_URL',
            $appUrlUnsafe ? $appUrl . ' — set an HTTPS, non-localhost URL in production' : $appUrl,
        );
    }

    private function record(string $status, string $label, string $detail): void
    {
        $this->checks[] = ['status' => $status, 'label' => $label, 'detail' => $detail];
    }

    private function render(): void
    {
        $rows = collect($this->checks)->map(fn (array $check): array => [
            match ($check['status']) {
                self::PASS => '<fg=green>PASS</>',
                self::WARN => '<fg=yellow>WARN</>',
                default => '<fg=red>FAIL</>',
            },
            $check['label'],
            $check['detail'],
        ])->all();

        $this->table(['Status', 'Check', 'Detail'], $rows);
    }
}
