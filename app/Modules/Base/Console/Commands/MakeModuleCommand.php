<?php

namespace App\Modules\Base\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class MakeModuleCommand extends Command
{
    protected $signature = 'make:module {name : StudlyCase module name}
        {--model= : Optionally scaffold a model and its standard surfaces}
        {--force : Overwrite generated files that already exist}';

    protected $description = 'Create a module using the project canonical layout';

    public function handle(): int
    {
        $module = Str::studly((string) $this->argument('name'));

        if (! preg_match('/^[A-Z][A-Za-z0-9]*$/', $module)) {
            $this->error('The module name must be StudlyCase and contain only letters and numbers.');

            return self::FAILURE;
        }

        $this->createDirectories($module);
        $this->write($this->path($module, "Providers/{$module}ServiceProvider.php"), $this->provider($module));
        $this->write($this->path($module, 'Routes/api/v1/web.php'), "<?php\n\nuse Illuminate\\Support\\Facades\\Route;\n");
        $this->write($this->path($module, 'Routes/dashboard/dashboard.php'), "<?php\n\nuse Illuminate\\Support\\Facades\\Route;\n");
        $this->write($this->path($module, 'database/seeders/DatabaseSeeder.php'), $this->databaseSeeder($module));

        if ($model = $this->option('model')) {
            $exitCode = $this->call('make:module-model', [
                'module' => $module,
                'model' => Str::studly((string) $model),
                '--all' => true,
                '--force' => (bool) $this->option('force'),
            ]);

            if ($exitCode !== self::SUCCESS) {
                return self::FAILURE;
            }
        }

        $this->info("Module {$module} is ready.");
        $this->line('Add routes explicitly, then add the matching menu entry and tests.');

        return self::SUCCESS;
    }

    private function createDirectories(string $module): void
    {
        foreach ([
            'Models',
            'Providers',
            'Repositories/Eloquent',
            'Http/Controllers/Api/V1',
            'Http/Controllers/Dashboard',
            'Http/Requests/Api/V1',
            'Http/Requests/Dashboard',
            'Http/Resources/V1',
            'Http/Services/Api/V1',
            'Http/Services/Dashboard',
            'Routes/api/v1',
            'Routes/dashboard',
            'Resources/views/dashboard',
            'database/migrations',
            'database/factories',
            'database/seeders',
        ] as $directory) {
            File::ensureDirectoryExists($this->path($module, $directory));
        }
    }

    private function write(string $path, string $contents): void
    {
        if (File::exists($path) && ! $this->option('force')) {
            $this->warn('Skipped existing file: ' . $this->relativePath($path));

            return;
        }

        File::put($path, $contents);
        $this->line('Created: ' . $this->relativePath($path));
    }

    private function path(string $module, string $relativePath): string
    {
        return base_path("app/Modules/{$module}/{$relativePath}");
    }

    private function relativePath(string $path): string
    {
        return Str::after($path, base_path() . DIRECTORY_SEPARATOR);
    }

    private function provider(string $module): string
    {
        return str_replace(
            '{{ module }}',
            $module,
            <<<'PHP'
<?php

namespace App\Modules\{{ module }}\Providers;

use App\Modules\Base\Providers\ModuleServiceProvider;

class {{ module }}ServiceProvider extends ModuleServiceProvider {}
PHP
        ) . PHP_EOL;
    }

    private function databaseSeeder(string $module): string
    {
        return str_replace(
            '{{ module }}',
            $module,
            <<<'PHP'
<?php

namespace App\Modules\{{ module }}\database\seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            // module-model-seeders
        ]);
    }
}
PHP
        ) . PHP_EOL;
    }
}
