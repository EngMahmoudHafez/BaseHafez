<?php

use App\Modules\Auth\Enums\RoleName;
use App\Modules\Auth\Models\Manager;
use App\Modules\Auth\Models\Permission;
use App\Modules\Auth\Models\Role;
use Tests\TestCase;

/*
|--------------------------------------------------------------------------
| Test Case
|--------------------------------------------------------------------------
|
| Every test in the Feature and Unit suites runs through the application's
| base TestCase (which boots the Laravel app). Database-touching tests opt
| into a fresh schema per test with `uses(RefreshDatabase::class)` in the
| individual file — it is intentionally NOT applied globally so pure unit
| tests do not pay for migrations they never use.
|
*/

uses(TestCase::class)->in('Feature', 'Unit');

/*
|--------------------------------------------------------------------------
| Shared helpers
|--------------------------------------------------------------------------
*/

/**
 * Create a manager account granted a single dashboard permission on the
 * `manager` guard. Shared across the dashboard authorization feature tests.
 */
function managerWithPermission(string $permissionName): Manager
{
    $manager = Manager::factory()->create();

    $permission = Permission::query()->create([
        'name' => $permissionName,
        'guard_name' => 'manager',
    ]);

    $manager->permissions()->attach($permission);

    return $manager;
}

/**
 * Create a manager holding the top-rank `super_admin` role — full permissions
 * and authority to manage any other manager or assign any role.
 */
function superAdminManager(): Manager
{
    $manager = Manager::factory()->create();

    $role = Role::query()->firstOrCreate(
        ['name' => RoleName::SuperAdmin->value, 'guard_name' => 'manager'],
        ['display_name_ar' => 'مدير عام', 'display_name_en' => 'Super Admin'],
    );

    $manager->addRole($role);

    return $manager;
}
