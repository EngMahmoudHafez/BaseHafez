<?php

namespace App\Modules\Base\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use PDO;
use Throwable;

/**
 * The single, idempotent entry point for preparing this base. The README,
 * the Composer `setup` script, and CI all call this command so the setup
 * steps never drift between documentation and automation.
 *
 * This base is MySQL-official: setup validates a real MySQL connection before
 * touching the schema and never falls back to another driver.
 */
class BaseSetupCommand extends Command
{
    protected $signature = 'base:setup
        {--ci : Minimal, non-interactive setup for CI: env file, application/JWT keys, and a validated MySQL connection only}
        {--seed : Seed the database after migrating}
        {--force : Regenerate the application key and JWT secret even when they are already set}';

    protected $description = 'Idempotent first-time setup for this MySQL-first base (env file, keys, MySQL validation, storage link, migrations).';

    /**
     * The reported MySQL server version, captured once the connection succeeds.
     */
    private ?string $mysqlServerVersion = null;

    /**
     * A human-readable "host:port / database" descriptor for the final report.
     */
    private string $mysqlTarget = '';

    public function handle(): int
    {
        $this->ensureEnvFile();
        $this->ensureAppKey();
        $this->ensureJwtSecret();

        if (! $this->validateDatabase()) {
            return self::FAILURE;
        }

        if ($this->option('ci')) {
            $this->newLine();
            $this->info('Environment prepared for CI (env file, application/JWT keys, validated MySQL connection).');

            return self::SUCCESS;
        }

        $this->linkStorage();

        if (! $this->migrate()) {
            return self::FAILURE;
        }

        $this->newLine();
        $this->call('base:doctor');

        $this->printReport();

        return self::SUCCESS;
    }

    private function ensureEnvFile(): void
    {
        if (File::exists(base_path('.env'))) {
            $this->line('.env already exists — leaving it untouched.');

            return;
        }

        File::copy(base_path('.env.example'), base_path('.env'));
        $this->info('Created .env from .env.example.');
    }

    private function ensureAppKey(): void
    {
        if (! $this->option('force') && $this->envHasValue('APP_KEY')) {
            $this->line('APP_KEY already set — skipping.');

            return;
        }

        $this->call('key:generate', ['--force' => true]);
    }

    private function ensureJwtSecret(): void
    {
        if (! $this->option('force') && $this->envHasValue('JWT_SECRET')) {
            $this->line('JWT_SECRET already set — skipping.');

            return;
        }

        $this->call('jwt:secret', ['--force' => true]);
    }

    /**
     * Validate that the configured database is a reachable MySQL server before
     * any migration runs. This base is MySQL-official, so a non-mysql driver or
     * an unreachable server is a hard failure with an actionable message — never
     * a silent fallback and never an attempt to create the database.
     */
    private function validateDatabase(): bool
    {
        $connection = config('database.default');

        if (! is_string($connection) || $connection === '') {
            $this->error('DB_CONNECTION is not set — configure the database connection in your .env.');

            return false;
        }

        if ($connection !== 'mysql') {
            $this->error(sprintf('This base is MySQL-official, but DB_CONNECTION is "%s".', $connection));
            $this->line('Set DB_CONNECTION=mysql (with the matching DB_* credentials) in your .env, then re-run base:setup.');

            return false;
        }

        $host = $this->stringConfig('database.connections.mysql.host');
        $port = $this->stringConfig('database.connections.mysql.port');
        $database = $this->stringConfig('database.connections.mysql.database');
        $username = $this->stringConfig('database.connections.mysql.username');

        $missing = [];

        foreach (['DB_HOST' => $host, 'DB_PORT' => $port, 'DB_DATABASE' => $database, 'DB_USERNAME' => $username] as $key => $value) {
            if ($value === '') {
                $missing[] = $key;
            }
        }

        if ($missing !== []) {
            $this->error('Missing MySQL configuration: ' . implode(', ', $missing) . '.');
            $this->line('Set the listed DB_* keys in your .env, then re-run base:setup.');

            return false;
        }

        try {
            $pdo = DB::connection()->getPdo();
        } catch (Throwable $exception) {
            $this->error('Could not connect to MySQL — not running migrations.');
            $this->line(sprintf('  Host:     %s:%s', $host, $port));
            $this->line(sprintf('  Database: %s', $database));
            $this->line(sprintf('  Username: %s', $username));
            $this->line('  Error:    ' . $exception->getMessage());
            $this->newLine();
            $this->line('Check DB_HOST, DB_PORT, DB_DATABASE, DB_USERNAME and DB_PASSWORD in your .env, confirm the MySQL server is running and the database exists, then re-run base:setup.');

            return false;
        }

        $this->mysqlTarget = sprintf('%s:%s / %s', $host, $port, $database);

        $serverVersion = $pdo->getAttribute(PDO::ATTR_SERVER_VERSION);
        $this->mysqlServerVersion = is_scalar($serverVersion) ? (string) $serverVersion : null;

        $this->info(sprintf('Connected to MySQL at %s:%s, database "%s".', $host, $port, $database));

        if ($this->mysqlServerVersion !== null) {
            $this->line('MySQL server version: ' . $this->mysqlServerVersion);
        }

        return true;
    }

    private function linkStorage(): void
    {
        if (File::exists(public_path('storage'))) {
            $this->line('Storage symlink already exists — skipping.');

            return;
        }

        $this->call('storage:link');
    }

    private function migrate(): bool
    {
        $exitCode = $this->call('migrate', array_filter([
            '--force' => true,
            '--seed' => $this->option('seed'),
        ]));

        return $exitCode === self::SUCCESS;
    }

    private function printReport(): void
    {
        $this->newLine();
        $this->info('Base setup complete.');
        $this->line('Database:      mysql');
        $this->line('Connection:    ' . $this->mysqlTarget);

        if ($this->mysqlServerVersion !== null) {
            $this->line('MySQL version: ' . $this->mysqlServerVersion);
        }

        $this->newLine();
        $this->line('Build the frontend with: npm ci && npm run build (or npm run dev).');

        if (! $this->envHasValue('BASE_ADMIN_EMAIL')) {
            $this->line('Set BASE_ADMIN_EMAIL and a 12+ character BASE_ADMIN_PASSWORD, then re-run with --seed to create the first manager.');
        }
    }

    /**
     * Read a config value that is expected to be a string, coercing scalars and
     * treating anything else (null, arrays) as an empty string for validation.
     */
    private function stringConfig(string $key): string
    {
        $value = config($key);

        return is_scalar($value) ? (string) $value : '';
    }

    /**
     * Read the .env file directly so we never regenerate a key that already
     * has a committed-to value (config/env() reflect the boot-time snapshot).
     */
    private function envHasValue(string $key): bool
    {
        $path = base_path('.env');

        if (! File::exists($path)) {
            return false;
        }

        return (bool) preg_match('/^' . preg_quote($key, '/') . '=.+$/m', File::get($path));
    }
}
