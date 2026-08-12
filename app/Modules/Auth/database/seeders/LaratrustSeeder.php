<?php

namespace App\Modules\Auth\database\seeders;

use App\Modules\Auth\Models\Permission;
use App\Modules\Auth\Models\Role;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Config;

class LaratrustSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $seederConfig = $this->getSeederConfig();

        $config = $seederConfig['roles_structure'] ?? null;

        if ($config === null) {
            $this->command->error('The configuration has not been published. Did you run `php artisan vendor:publish --tag="laratrust-seeder"`');
            $this->command->line('');

            return;
        }

        $mapPermission = collect($seederConfig['permissions_map'] ?? []);
        foreach ($config as $key => $modules) {

            $role = Role::query()->firstOrCreate([
                'name' => $key,
                'guard_name' => 'manager',
            ]);
            $permissions = [];

            $this->command->info('Creating Role ' . strtoupper($key));

            // Reading role permission modules
            foreach ($modules as $module => $value) {

                foreach (explode(',', $value) as $perm) {

                    $permissionValue = $mapPermission->get($perm);

                    $permissions[] = Permission::query()->firstOrCreate([
                        'name' => $module . '-' . $permissionValue,
                        'guard_name' => 'manager',
                    ])->id;

                    $this->command->info('Creating Permission to ' . $permissionValue . ' for ' . $module);
                }
            }

            // Add all permissions to the role
            $role->permissions()->sync($permissions);
        }
    }

    private function getSeederConfig(): array
    {
        $config = Config::get('laratrust_seeder');

        if (is_array($config) && $config !== []) {
            return $config;
        }

        return require base_path('vendor/santigarcor/laratrust/config/laratrust_seeder.php');
    }
}
