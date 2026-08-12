<?php

namespace App\Modules\Base\Console\Commands;

use App\Support\ModuleDiscovery;
use Illuminate\Console\Command;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

/**
 * Make module auto-discovery visible: list every directory under app/Modules
 * with the service provider that loads it, its seeders, and its route files.
 * A module directory without a discoverable provider is flagged, so "present
 * but not loaded" is never silent.
 */
class ModulesListCommand extends Command
{
    protected $signature = 'base:modules';

    protected $description = 'List discovered modules, their service providers, seeders, and route files.';

    public function handle(): int
    {
        $providersByModule = collect(ModuleDiscovery::serviceProviders())
            ->groupBy(fn (string $class): string => $this->moduleOf($class));

        $seedersByModule = collect(ModuleDiscovery::databaseSeeders())
            ->groupBy(fn (string $class): string => $this->moduleOf($class));

        $rows = collect(glob(base_path('app/Modules/*'), GLOB_ONLYDIR) ?: [])
            ->map(fn (string $path): string => basename($path))
            ->sort()
            ->map(function (string $module) use ($providersByModule, $seedersByModule): array {
                $loaded = $module === 'Base' || $providersByModule->has($module);

                return [
                    $module,
                    $loaded ? '<fg=green>yes</>' : '<fg=red>no</>',
                    $this->providerLabel($module, $providersByModule),
                    (string) $seedersByModule->get($module, collect())->count(),
                    $this->routeLabel($module),
                ];
            })
            ->all();

        $this->table(['Module', 'Loaded', 'Provider', 'Seeders', 'Routes'], $rows);

        $unloaded = collect($rows)->filter(fn (array $row): bool => str_contains($row[1], 'no'));

        if ($unloaded->isNotEmpty()) {
            $this->newLine();
            $this->warn('Some modules have no discoverable ServiceProvider and will not load. Run php artisan base:doctor.');

            return self::FAILURE;
        }

        return self::SUCCESS;
    }

    private function moduleOf(string $class): string
    {
        return Str::before(Str::after($class, 'App\\Modules\\'), '\\');
    }

    /**
     * @param  Collection<array-key, Collection<int, class-string>>  $providersByModule
     */
    private function providerLabel(string $module, Collection $providersByModule): string
    {
        if ($module === 'Base') {
            return 'BaseServiceProvider (manual)';
        }

        return $providersByModule->get($module, collect())
            ->map(fn (string $class): string => class_basename($class))
            ->implode(', ') ?: '—';
    }

    private function routeLabel(string $module): string
    {
        $labels = [];

        if (is_file(base_path("app/Modules/{$module}/Routes/api/v1/web.php"))) {
            $labels[] = 'api';
        }

        if (is_file(base_path("app/Modules/{$module}/Routes/dashboard/dashboard.php"))) {
            $labels[] = 'dashboard';
        }

        return $labels === [] ? '—' : implode(' + ', $labels);
    }
}
