<?php

namespace App\Modules\Base\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

/**
 * The single, idempotent entry point for preparing this base. The README,
 * the Composer `setup` script, and CI all call this command so the install
 * steps never drift between documentation and automation.
 */
class BaseInstallCommand extends Command
{
    protected $signature = 'base:install
        {--ci : Minimal, non-interactive setup for CI: env file plus application and JWT keys only}
        {--seed : Seed the database after migrating}
        {--force : Regenerate the application key and JWT secret even when they are already set}';

    protected $description = 'Idempotent first-time setup for this base (env file, keys, database, storage link).';

    public function handle(): int
    {
        $this->ensureEnvFile();
        $this->ensureAppKey();
        $this->ensureJwtSecret();

        if ($this->option('ci')) {
            $this->info('Environment prepared for CI (env file + application/JWT keys).');

            return self::SUCCESS;
        }

        $this->ensureSqliteDatabase();
        $this->linkStorage();

        if (! $this->migrate()) {
            return self::FAILURE;
        }

        $this->newLine();
        $this->info('Base install complete.');
        $this->line('Build the frontend with: npm ci && npm run build (or npm run dev).');

        if (! $this->envHasValue('BASE_ADMIN_EMAIL')) {
            $this->line('Set BASE_ADMIN_EMAIL and a 12+ character BASE_ADMIN_PASSWORD, then re-run with --seed to create the first manager.');
        }

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

    private function ensureSqliteDatabase(): void
    {
        if (config('database.default') !== 'sqlite') {
            return;
        }

        $database = config('database.connections.sqlite.database');

        if (! is_string($database) || $database === ':memory:' || File::exists($database)) {
            return;
        }

        File::ensureDirectoryExists(dirname($database));
        File::put($database, '');
        $this->info('Created SQLite database file: ' . $database);
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
