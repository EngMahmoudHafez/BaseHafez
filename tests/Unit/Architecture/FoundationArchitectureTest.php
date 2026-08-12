<?php

use App\Support\ModuleDiscovery;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Lang;
use Illuminate\Support\Facades\Route;

test('every module conforms to the module contract', function () {
    $registered = registeredModules();

    foreach (modules() as $module) {
        expect($module)->toMatch('/^[A-Z][A-Za-z0-9]*$/', "Module directory [{$module}] must be StudlyCase (letters and numbers only).");

        expect(glob(base_path("app/Modules/{$module}/Providers/*ServiceProvider.php")) ?: [])->not->toBeEmpty("Module [{$module}] must declare a service provider under Providers/.");

        $this->assertContains($module, $registered, "Module [{$module}] is present but not loaded: its ServiceProvider is not registered. "
        . 'Check its AUTO_DISCOVER contract and bootstrap/providers.php.');
    }
});

test('operating rules and dependency lock are not ignored', function () {
    $gitignore = file_get_contents(base_path('.gitignore'));

    expect(base_path('AGENTS.md'))->toBeFile();
    expect(base_path('composer.lock'))->toBeFile();
    $this->assertDoesNotMatchRegularExpression('/^\/?AGENTS\.md$/m', $gitignore);
    $this->assertDoesNotMatchRegularExpression('/^\/?composer\.lock$/m', $gitignore);
    $this->assertStringContainsString('/storage/framework/testing', $gitignore);
});

test('production code only references existing modules', function () {
    $modules = modules();

    foreach (phpFiles(['app', 'bootstrap', 'config', 'database', 'routes']) as $file) {
        preg_match_all('/App\\\\Modules\\\\([A-Za-z][A-Za-z0-9]*)\\\\/', file_get_contents($file->getPathname()), $matches);

        foreach (array_unique($matches[1]) as $module) {
            $this->assertContains($module, $modules, "{$file->getPathname()} references App\\Modules\\{$module}\\, but no such module directory exists.");
        }
    }
});

test('feature modules expose routes at canonical paths', function () {
    foreach (featureModules() as $module) {
        expect(is_file(base_path("app/Modules/{$module}/Routes/api/v1/web.php"))
            || is_file(base_path("app/Modules/{$module}/Routes/dashboard/dashboard.php")))->toBeTrue("Feature module [{$module}] must expose at least one canonical route file "
        . '(Routes/api/v1/web.php or Routes/dashboard/dashboard.php).');
    }
});

test('base does not own business dashboard screens', function () {
    $screens = filesIn(base_path('app/Modules/Base/Resources/views/dashboard'));

    expect($screens)->toBe([], 'Complete dashboard screens must live in their owning module.');
});

test('dashboard screens use the shared page header', function () {
    foreach (featureModules() as $module) {
        $path = base_path("app/Modules/{$module}/Resources/views/dashboard");

        foreach (filesIn($path, 'php') as $file) {
            $filePath = str_replace('\\', '/', $file->getPathname());
            $contents = file_get_contents($file->getPathname());

            if (
                ! str_contains($contents, '@extends(')
                || str_contains($filePath, '/auth/')
                || str_contains($filePath, '/partials/')
            ) {
                continue;
            }

            $this->assertStringContainsString(
                '<x-dashboard.page-header',
                $contents,
                "Dashboard screen [{$filePath}] must use the shared page header.",
            );
        }
    }
});

test('http surfaces use the canonical module paths', function () {
    $rules = [
        '/Http/Controllers/Api/' => '/Http/Controllers/Api/V1/',
        '/Http/Controllers/Dashboard/' => '/Http/Controllers/Dashboard/',
        '/Http/Requests/Api/' => '/Http/Requests/Api/V1/',
        '/Http/Requests/Dashboard/' => '/Http/Requests/Dashboard/',
        '/Http/Resources/' => '/Http/Resources/V1/',
        '/Http/Services/Api/' => '/Http/Services/Api/V1/',
        '/Http/Services/Dashboard/' => '/Http/Services/Dashboard/',
    ];

    foreach (phpFiles(['app/Modules']) as $file) {
        $path = str_replace('\\', '/', $file->getPathname());

        foreach ($rules as $surface => $canonicalPath) {
            if (str_contains($path, $surface)) {
                $this->assertStringContainsString(
                    $canonicalPath,
                    $path,
                    "Module surface [{$path}] is outside its canonical path.",
                );
            }
        }
    }
});

test('modules do not register legacy route files', function () {
    foreach (filesIn(base_path('app/Modules')) as $file) {
        $path = str_replace('\\', '/', $file->getPathname());

        if (! str_contains($path, '/Routes/') || $file->getExtension() !== 'php') {
            continue;
        }

        expect(str_ends_with($path, '/Routes/api/v1/web.php')
            || str_ends_with($path, '/Routes/dashboard/dashboard.php'))->toBeTrue("Legacy route file [{$path}] must be removed or moved.");
    }
});

test('foundation seeders are non destructive and have no fixed passwords', function () {
    foreach (phpFiles(['app/Modules']) as $file) {
        $path = str_replace('\\', '/', $file->getPathname());

        if (! str_contains($path, '/database/seeders/')) {
            continue;
        }

        $contents = file_get_contents($file->getPathname());

        $this->assertStringNotContainsString('->truncate(', $contents, "Seeder [{$path}] truncates data.");
        $this->assertDoesNotMatchRegularExpression(
            '/(?:bcrypt|Hash::make)\(\s*[\'\"][^\'\"]+[\'\"]\s*\)/',
            $contents,
            "Seeder [{$path}] contains a fixed password.",
        );
        $this->assertDoesNotMatchRegularExpression(
            '/[\'\"]password[\'\"]\s*=>\s*[\'\"][^\'\"]+[\'\"]/',
            $contents,
            "Seeder [{$path}] contains a fixed password value.",
        );
    }
});

test('every menu route and translation exists', function () {
    $menu = json_decode(file_get_contents(resource_path('menu/verticalMenu.json')), true, flags: JSON_THROW_ON_ERROR);

    foreach (flattenMenu($menu['menu'] ?? []) as $item) {
        if (isset($item['route'])) {
            expect(Route::has($item['route']))->toBeTrue("Menu route [{$item['route']}] is not registered.");
        }

        if (! isset($item['name'])) {
            continue;
        }

        foreach (['en', 'ar'] as $locale) {
            expect(Lang::has($item['name'], $locale))->toBeTrue("Menu translation [{$item['name']}] is missing for {$locale}.");
        }
    }
});

test('vite uses explicit application entries', function () {
    $config = file_get_contents(base_path('vite.config.js'));

    $this->assertStringNotContainsString('glob.sync', $config);
    $this->assertStringNotContainsString('resources/assets/**', $config);
    $this->assertStringContainsString("'resources/css/app.css'", $config);
    $this->assertStringContainsString("'resources/js/app.js'", $config);
});

test('blade views avoid the fragile inline php directive', function () {
    foreach ([base_path('app/Modules'), resource_path('views')] as $root) {
        foreach (filesIn($root, 'php') as $file) {
            $path = str_replace('\\', '/', $file->getPathname());

            if (! str_ends_with($path, '.blade.php')) {
                continue;
            }

            $this->assertDoesNotMatchRegularExpression(
                '/@php\s*\(/',
                file_get_contents($file->getPathname()),
                "Blade view [{$path}] uses the inline @php(...) form; use a @php ... @endphp block "
                . "so Blade's block regex cannot swallow the directives that follow it.",
            );
        }
    }
});

test('every blade view compiles to valid php', function () {
    $checked = 0;

    foreach ([base_path('app/Modules'), resource_path('views')] as $root) {
        foreach (filesIn($root, 'php') as $file) {
            $path = str_replace('\\', '/', $file->getPathname());

            if (! str_ends_with($path, '.blade.php')) {
                continue;
            }

            $compiled = Blade::compileString(file_get_contents($file->getPathname()));

            try {
                // Parse the compiled PHP; TOKEN_PARSE throws \ParseError on invalid output.
                token_get_all($compiled, TOKEN_PARSE); // @phpstan-ignore function.resultUnused
                $checked++;
            } catch (ParseError $exception) {
                $this->fail("Blade view [{$path}] compiles to invalid PHP: {$exception->getMessage()}");
            }
        }
    }

    expect($checked)->toBeGreaterThan(0, 'Expected to compile-check at least one Blade view.');
});

test('models use the casts method not the legacy property', function () {
    foreach (phpFiles(['app/Modules']) as $file) {
        $path = str_replace('\\', '/', $file->getPathname());

        if (! str_contains($path, '/Models/')) {
            continue;
        }

        $this->assertDoesNotMatchRegularExpression(
            '/protected\s+\$casts\s*=/',
            file_get_contents($file->getPathname()),
            "Model [{$path}] uses the legacy \$casts property; define a casts() method instead.",
        );
    }
});

/**
 * Every module directory under app/Modules, sorted.
 *
 * @return list<string>
 */
function modules(): array
{
    return collect(glob(base_path('app/Modules/*'), GLOB_ONLYDIR) ?: [])
        ->map(fn (string $path): string => basename($path))
        ->sort()
        ->values()
        ->all();
}

/**
 * Modules that own a feature slice — every module except the shared Base.
 *
 * @return list<string>
 */
function featureModules(): array
{
    return collect(modules())
        ->reject(fn (string $module): bool => $module === 'Base')
        ->values()
        ->all();
}

/**
 * Modules whose service provider is actually registered: the auto-discovered
 * ones, plus Base which is wired manually in bootstrap/providers.php.
 *
 * @return list<string>
 */
function registeredModules(): array
{
    return collect(ModuleDiscovery::serviceProviders())
        ->map(fn (string $class): string => explode('\\', $class)[2] ?? '')
        ->push('Base')
        ->filter()
        ->unique()
        ->values()
        ->all();
}

/**
 * @return list<SplFileInfo>
 */
function phpFiles(array $roots): array
{
    return collect($roots)
        ->flatMap(fn (string $root): array => filesIn(base_path($root), 'php'))
        ->all();
}

/**
 * @return list<SplFileInfo>|list<string>
 */
function filesIn(string $path, ?string $extension = null): array
{
    if (! is_dir($path)) {
        return [];
    }

    return collect(new RecursiveIteratorIterator(new RecursiveDirectoryIterator($path)))
        ->filter(fn (SplFileInfo $file): bool => $file->isFile() && ($extension === null || $file->getExtension() === $extension))
        ->values()
        ->all();
}

function flattenMenu(array $items): array
{
    return collect($items)
        ->flatMap(function (array $item): array {
            $children = flattenMenu($item['submenu'] ?? []);

            return [$item, ...$children];
        })
        ->all();
}
