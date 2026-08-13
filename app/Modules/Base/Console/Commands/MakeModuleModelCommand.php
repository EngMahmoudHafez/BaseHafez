<?php

namespace App\Modules\Base\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class MakeModuleModelCommand extends Command
{
    protected $signature = 'make:module-model {module : Existing module name} {model : StudlyCase model name}
        {--migration} {--factory} {--seeder} {--repository} {--services}
        {--requests} {--resources} {--controllers} {--tests} {--all} {--force}';

    protected $description = 'Add a model and selected canonical surfaces to an existing module';

    private string $module;

    private string $model;

    public function handle(): int
    {
        $this->module = Str::studly((string) $this->argument('module'));
        $this->model = Str::studly((string) $this->argument('model'));

        if (! $this->validName($this->module) || ! $this->validName($this->model)) {
            $this->error('Module and model names must be StudlyCase.');

            return self::FAILURE;
        }

        if (! File::isDirectory($this->modulePath())) {
            $this->error("Module {$this->module} does not exist. Run make:module first.");

            return self::FAILURE;
        }

        $all = (bool) $this->option('all');
        $withFactory = $all || $this->option('factory');
        $this->write("Models/{$this->model}.php", $this->modelStub($withFactory));

        if ($all || $this->option('migration')) {
            $this->writeMigration();
        }
        if ($withFactory) {
            $this->write("database/factories/{$this->model}Factory.php", $this->factoryStub());
        }
        if ($all || $this->option('seeder')) {
            if (! $this->seederCanBeRegistered()) {
                return self::FAILURE;
            }

            $this->write("database/seeders/{$this->model}Seeder.php", $this->seederStub());
            $this->registerSeeder();
        }
        if ($all || $this->option('repository')) {
            $this->write("Repositories/{$this->model}RepositoryInterface.php", $this->repositoryInterfaceStub());
            $this->write("Repositories/Eloquent/{$this->model}Repository.php", $this->repositoryStub());
        }
        if ($all || $this->option('services')) {
            $this->write("Http/Services/Api/V1/{$this->model}Service.php", $this->serviceStub('Api\\V1'));
            $this->write("Http/Services/Dashboard/{$this->model}Service.php", $this->serviceStub('Dashboard'));
        }
        if ($all || $this->option('requests')) {
            $this->write("Http/Requests/Api/V1/Store{$this->model}Request.php", $this->requestStub('Api\\V1', "Store{$this->model}Request"));
            $this->write("Http/Requests/Api/V1/Update{$this->model}Request.php", $this->requestStub('Api\\V1', "Update{$this->model}Request"));
            $this->write("Http/Requests/Dashboard/Store{$this->model}Request.php", $this->requestStub('Dashboard', "Store{$this->model}Request"));
            $this->write("Http/Requests/Dashboard/Update{$this->model}Request.php", $this->requestStub('Dashboard', "Update{$this->model}Request"));
        }
        if ($all || $this->option('resources')) {
            $this->write("Http/Resources/V1/{$this->model}Resource.php", $this->resourceStub('full'));
            $this->write("Http/Resources/V1/{$this->model}SummaryResource.php", $this->resourceStub('summary'));
        }
        if ($all || $this->option('controllers')) {
            $this->write("Http/Controllers/Api/V1/{$this->model}Controller.php", $this->controllerStub('Api\\V1'));
            $this->write("Http/Controllers/Dashboard/{$this->model}Controller.php", $this->controllerStub('Dashboard'));
        }
        if ($all || $this->option('tests')) {
            $this->writeTest();
        }

        $this->info("{$this->model} surfaces created in {$this->module}.");
        $this->line('Define business fields, validation, routes, views, and tests before shipping.');

        return self::SUCCESS;
    }

    private function validName(string $name): bool
    {
        return (bool) preg_match('/^[A-Z][A-Za-z0-9]*$/', $name);
    }

    private function modulePath(string $relativePath = ''): string
    {
        return base_path("app/Modules/{$this->module}" . ($relativePath ? "/{$relativePath}" : ''));
    }

    private function write(string $relativePath, string $contents): void
    {
        $path = $this->modulePath($relativePath);
        File::ensureDirectoryExists(dirname($path));

        if (File::exists($path) && ! $this->option('force')) {
            $this->warn("Skipped existing file: {$relativePath}");

            return;
        }

        File::put($path, $contents . PHP_EOL);
        $this->line("Created: {$relativePath}");
    }

    private function writeMigration(): void
    {
        $table = Str::snake(Str::pluralStudly($this->model));
        $suffix = now()->format('Y_m_d_His');
        $relativePath = "database/migrations/{$suffix}_create_{$table}_table.php";
        $this->write($relativePath, $this->replace($this->migrationStub(), ['table' => $table]));
    }

    private function writeTest(): void
    {
        $relativePath = "tests/Feature/{$this->module}/{$this->model}Test.php";
        $path = base_path($relativePath);
        File::ensureDirectoryExists(dirname($path));

        if (File::exists($path) && ! $this->option('force')) {
            $this->warn("Skipped existing file: {$relativePath}");

            return;
        }

        File::put($path, $this->testStub() . PHP_EOL);
        $this->line("Created: {$relativePath}");
    }

    private function testStub(): string
    {
        return $this->replace(
            <<<'PHP'
<?php

namespace Tests\Feature\{{ module }};

use App\Modules\{{ module }}\Models\{{ model }};
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class {{ model }}Test extends TestCase
{
    use RefreshDatabase;

    public function test_{{ variable }}_can_be_created_from_its_factory(): void
    {
        ${{ variable }} = {{ model }}::factory()->create();

        $this->assertDatabaseHas('{{ table }}', ['id' => ${{ variable }}->id]);
    }

    // TODO: cover the dashboard index/create/store/update/destroy flow —
    // validation, permission authorization, and persistence — following the
    // reference tests under tests/Feature/Auth.
}
PHP
        );
    }

    private function seederCanBeRegistered(): bool
    {
        $databaseSeederPath = $this->modulePath('database/seeders/DatabaseSeeder.php');

        if (! File::exists($databaseSeederPath)) {
            $this->error('The module DatabaseSeeder is missing. Run make:module or add the canonical seeder first.');

            return false;
        }

        $contents = File::get($databaseSeederPath);
        $seederClass = "{$this->model}Seeder::class";

        if (! str_contains($contents, $seederClass)
            && ! str_contains($contents, '// module-model-seeders')) {
            $this->error('DatabaseSeeder must contain the module-model-seeders marker before generating seeders.');

            return false;
        }

        return true;
    }

    private function registerSeeder(): void
    {
        $databaseSeederPath = $this->modulePath('database/seeders/DatabaseSeeder.php');
        $contents = File::get($databaseSeederPath);
        $seederClass = "{$this->model}Seeder::class";

        if (str_contains($contents, $seederClass)) {
            return;
        }

        $marker = '            // module-model-seeders';

        File::put(
            $databaseSeederPath,
            str_replace($marker, "            {$seederClass},\n{$marker}", $contents),
        );
        $this->line('Registered seeder: database/seeders/DatabaseSeeder.php');
    }

    /** @param array<string, string> $extra */
    private function replace(string $stub, array $extra = []): string
    {
        $values = [
            'module' => $this->module,
            'model' => $this->model,
            'variable' => Str::camel($this->model),
            'permission' => Str::kebab(Str::pluralStudly($this->model)),
            'table' => Str::snake(Str::pluralStudly($this->model)),
            ...$extra,
        ];

        return str_replace(
            array_map(fn (string $key): string => "{{ {$key} }}", array_keys($values)),
            array_values($values),
            $stub,
        );
    }

    private function modelStub(bool $withFactory): string
    {
        $factoryImports = $withFactory
            ? "use App\\Modules\\{{ module }}\\database\\factories\\{{ model }}Factory;\nuse Illuminate\\Database\\Eloquent\\Factories\\HasFactory;\n"
            : '';
        $factoryTrait = $withFactory ? "    use HasFactory;\n\n" : '';
        $factoryMethod = $withFactory ? <<<'PHP'

    protected static function newFactory(): {{ model }}Factory
    {
        return {{ model }}Factory::new();
    }
PHP : '';

        return $this->replace(
            <<<PHP
<?php

namespace App\Modules\{{ module }}\Models;

{$factoryImports}use Illuminate\Database\Eloquent\Model;

class {{ model }} extends Model
{
{$factoryTrait}    protected \$fillable = ['name'];
{$factoryMethod}
}
PHP
        );
    }

    private function migrationStub(): string
    {
        return <<<'PHP'
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('{{ table }}', function (Blueprint $table): void {
            $table->id();
            $table->string('name');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('{{ table }}');
    }
};
PHP;
    }

    private function factoryStub(): string
    {
        return $this->replace(
            <<<'PHP'
<?php

namespace App\Modules\{{ module }}\database\factories;

use App\Modules\{{ module }}\Models\{{ model }};
use Illuminate\Database\Eloquent\Factories\Factory;

class {{ model }}Factory extends Factory
{
    protected $model = {{ model }}::class;

    public function definition(): array
    {
        return ['name' => fake()->words(3, true)];
    }
}
PHP
        );
    }

    private function seederStub(): string
    {
        return $this->replace(
            <<<'PHP'
<?php

namespace App\Modules\{{ module }}\database\seeders;

use Illuminate\Database\Seeder;

class {{ model }}Seeder extends Seeder
{
    public function run(): void
    {
        // Intentionally empty. Add deterministic base data only when the module requires it.
    }
}
PHP
        );
    }

    private function repositoryInterfaceStub(): string
    {
        return $this->replace(
            <<<'PHP'
<?php

namespace App\Modules\{{ module }}\Repositories;

use App\Modules\Base\Repositories\RepositoryInterface;

interface {{ model }}RepositoryInterface extends RepositoryInterface {}
PHP
        );
    }

    private function repositoryStub(): string
    {
        return $this->replace(
            <<<'PHP'
<?php

namespace App\Modules\{{ module }}\Repositories\Eloquent;

use App\Modules\Base\Repositories\Eloquent\Repository;
use App\Modules\{{ module }}\Models\{{ model }};
use App\Modules\{{ module }}\Repositories\{{ model }}RepositoryInterface;

/**
 * @extends Repository<{{ model }}>
 */
class {{ model }}Repository extends Repository implements {{ model }}RepositoryInterface
{
    public function __construct({{ model }} $model)
    {
        parent::__construct($model);
    }
}
PHP
        );
    }

    private function serviceStub(string $surface): string
    {
        return $this->replace(str_replace(
            '{{ surface }}',
            $surface,
            <<<'PHP'
<?php

namespace App\Modules\{{ module }}\Http\Services\{{ surface }};

use App\Modules\{{ module }}\Repositories\{{ model }}RepositoryInterface;

class {{ model }}Service
{
    public function __construct(
        private readonly {{ model }}RepositoryInterface $repository,
    ) {}
}
PHP
        ));
    }

    private function requestStub(string $surface, string $class): string
    {
        $authorization = $surface === 'Dashboard'
            ? "return auth('manager')->check();"
            : 'return true;';
        $stub = str_replace(
            ['{{ surface }}', '{{ class }}', '{{ authorization }}'],
            [$surface, $class, $authorization],
            <<<'PHP'
<?php

namespace App\Modules\{{ module }}\Http\Requests\{{ surface }};

use Illuminate\Foundation\Http\FormRequest;

class {{ class }} extends FormRequest
{
    public function authorize(): bool
    {
        {{ authorization }}
    }

    public function rules(): array
    {
        return ['name' => ['required', 'string', 'max:255']];
    }
}
PHP
        );

        return $this->replace($stub);
    }

    private function resourceStub(string $kind): string
    {
        $class = '{{ model }}' . ($kind === 'summary' ? 'SummaryResource' : 'Resource');
        $body = $kind === 'summary'
            ? "['id' => \$this->id, 'name' => \$this->name]"
            : "['id' => \$this->id, 'name' => \$this->name, 'created_at' => \$this->created_at?->toISOString()]";

        return $this->replace(str_replace(
            ['{{ class }}', '{{ body }}'],
            [$class, $body],
            <<<'PHP'
<?php

namespace App\Modules\{{ module }}\Http\Resources\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class {{ class }} extends JsonResource
{
    /** @return array<string, mixed> */
    public function toArray(Request $request): array
    {
        return {{ body }};
    }
}
PHP
        ));
    }

    private function controllerStub(string $surface): string
    {
        if ($surface === 'Dashboard') {
            return $this->replace(
                <<<'PHP'
<?php

namespace App\Modules\{{ module }}\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Modules\{{ module }}\Http\Services\Dashboard\{{ model }}Service;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;

class {{ model }}Controller extends Controller implements HasMiddleware
{
    public function __construct(
        private readonly {{ model }}Service $service,
    ) {}

    /**
     * @return list<Middleware>
     */
    public static function middleware(): array
    {
        return [
            new Middleware('permission:{{ permission }}-read', only: ['index', 'show']),
            new Middleware('permission:{{ permission }}-create', only: ['create', 'store']),
            new Middleware('permission:{{ permission }}-update', only: ['edit', 'update']),
            new Middleware('permission:{{ permission }}-delete', only: ['destroy']),
        ];
    }
}
PHP
            );
        }

        return $this->replace(
            <<<'PHP'
<?php

namespace App\Modules\{{ module }}\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Modules\{{ module }}\Http\Services\Api\V1\{{ model }}Service;

class {{ model }}Controller extends Controller
{
    public function __construct(
        private readonly {{ model }}Service $service,
    ) {}
}
PHP
        );
    }
}
