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

test('base does not depend on business modules', function () {
    // Base is the lowest shared layer: it must not reference any business module
    // (Auth, Notifications, Structure, Settings). Business modules depend on Base,
    // never the other way around.
    foreach (['Auth', 'Notifications', 'Structure', 'Settings'] as $module) {
        foreach (filesIn(base_path('app/Modules/Base'), 'php') as $file) {
            $path = str_replace('\\', '/', $file->getPathname());

            $this->assertDoesNotMatchRegularExpression(
                '/App\\\\Modules\\\\' . $module . '\\\\/',
                file_get_contents($file->getPathname()),
                "Base file [{$path}] references App\\Modules\\{$module}\\; Base must not depend on business modules.",
            );
        }
    }
});

test('the notification engine does not depend on auth account models', function () {
    // Scope: the delivery ENGINE only. It must stay decoupled from who it delivers
    // to, so it may not import App\Modules\Auth\Models\{User,Manager}. The dashboard
    // "broadcast to users" admin feature (Http/Services/Dashboard,
    // Http/Controllers/Dashboard), the NotificationFactory, and the Blade views
    // legitimately reference the User model and are therefore NOT scanned here.
    $engineRoots = [
        'app/Modules/Notifications/Models',
        'app/Modules/Notifications/Services',
        'app/Modules/Notifications/Repositories',
        'app/Modules/Notifications/DTOs',
        'app/Modules/Notifications/Http/Services/Api',
        'app/Modules/Notifications/Http/Resources',
    ];

    foreach ($engineRoots as $root) {
        foreach (filesIn(base_path($root), 'php') as $file) {
            $path = str_replace('\\', '/', $file->getPathname());

            $this->assertDoesNotMatchRegularExpression(
                '/App\\\\Modules\\\\Auth\\\\Models\\\\(User|Manager)\\b/',
                file_get_contents($file->getPathname()),
                "Notification engine file [{$path}] references an Auth account model; the engine must not depend on Auth.",
            );
        }
    }
});

test('the split file traits are not resurrected', function () {
    // HasImages and FileTrait were merged into InteractsWithFiles.php; the split
    // must never silently come back.
    foreach (['HasImages.php', 'FileTrait.php'] as $legacyTrait) {
        $this->assertFileDoesNotExist(
            base_path("app/Modules/Base/Concerns/{$legacyTrait}"),
            "Legacy concern [{$legacyTrait}] is back; it was merged into InteractsWithFiles.php — do not reintroduce the split.",
        );
    }
});

test('the setting model casts show_in_dashboard to a boolean', function () {
    $model = base_path('app/Modules/Settings/Models/Setting.php');

    expect(is_file($model))->toBeTrue('Settings module is missing its Setting model at app/Modules/Settings/Models/Setting.php.');

    $this->assertMatchesRegularExpression(
        '/[\'"]show_in_dashboard[\'"]\s*=>\s*[\'"](boolean|bool)[\'"]/',
        file_get_contents($model),
        'Setting model must cast show_in_dashboard to a boolean in its casts() method.',
    );
});

test('the settings table enforces a unique key', function () {
    $migrations = filesIn(base_path('app/Modules/Settings/database/migrations'), 'php');

    expect($migrations)->not->toBeEmpty('Settings module is missing its database migrations.');

    $contents = collect($migrations)
        ->map(fn (SplFileInfo $file): string => file_get_contents($file->getPathname()))
        ->implode("\n");

    // Accepts either a chained column unique (->string('key')->unique()) or an
    // explicit index (->unique('key')). A composite unique is intentionally not
    // accepted: the agreed API requires `key` to be globally unique.
    $declaresUniqueKey = preg_match('/[\'"]key[\'"][^;]*->\s*unique\s*\(/', $contents) === 1
        || preg_match('/->\s*unique\s*\(\s*[\'"]key[\'"]/', $contents) === 1;

    expect($declaresUniqueKey)->toBeTrue('The settings migration must declare a unique key column (settings.key must be unique).');
});

test('the dashboard settings listing is filtered by show_in_dashboard', function () {
    // The dashboard only lists settings flagged show_in_dashboard = true. Assert the
    // filter lives in the module's query path (service, repository, or a model scope)
    // rather than trusting each caller to remember it. Static scan keeps this test in
    // step with the rest of this pure-static, no-DB architecture file.
    $queryRoots = [
        'app/Modules/Settings/Http/Services/Dashboard',
        'app/Modules/Settings/Repositories',
        'app/Modules/Settings/Models',
    ];

    $existingRoots = collect($queryRoots)->filter(fn (string $root): bool => is_dir(base_path($root)));

    expect($existingRoots)->not->toBeEmpty('Settings module is missing its dashboard query layer (service/repository/model).');

    $filtersOnFlag = collect($queryRoots)
        ->flatMap(fn (string $root): array => filesIn(base_path($root), 'php'))
        ->map(fn (SplFileInfo $file): string => file_get_contents($file->getPathname()))
        ->contains(fn (string $file): bool => preg_match(
            '/where[A-Za-z]*\(\s*[\'"]show_in_dashboard[\'"]|whereShowInDashboard/',
            $file,
        ) === 1);

    expect($filtersOnFlag)->toBeTrue("The dashboard settings listing must filter on show_in_dashboard (e.g. where('show_in_dashboard', true)) so hidden settings never leak to the dashboard.");
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
