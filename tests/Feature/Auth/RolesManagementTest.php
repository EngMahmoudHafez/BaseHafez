<?php

use App\Modules\Auth\Models\Manager;
use App\Modules\Auth\Models\Permission;
use App\Modules\Auth\Models\Role;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Mcamara\LaravelLocalization\Middleware\LaravelLocalizationRedirectFilter;
use Mcamara\LaravelLocalization\Middleware\LaravelLocalizationViewPath;
use Mcamara\LaravelLocalization\Middleware\LocaleSessionRedirect;

uses(RefreshDatabase::class);

test('authorized manager can create a role with permissions', function () {
    $admin = managerWithPermission('roles-create');
    $permission = Permission::query()->create(['name' => 'users-read', 'guard_name' => 'manager']);

    $this->actingAs($admin, 'manager')
        ->post(route('roles.store'), [
            'display_name_ar' => 'محرر المحتوى',
            'display_name_en' => 'Content Editor',
            'description' => 'Edits site content',
            'permissions' => [$permission->id],
        ])
        ->assertRedirect(route('roles.index'))
        ->assertSessionHasNoErrors();

    $this->assertDatabaseHas('roles', [
        'display_name_en' => 'Content Editor',
        'name' => 'content-editor',
    ]);

    $role = Role::query()->where('display_name_en', 'Content Editor')->firstOrFail();
    expect($role->permissions()->where('name', 'users-read')->exists())->toBeTrue();
});

test('creating a role requires both display names', function () {
    $admin = managerWithPermission('roles-create');

    $this->actingAs($admin, 'manager')
        ->post(route('roles.store'), ['description' => 'No names'])
        ->assertSessionHasErrors(['display_name_ar', 'display_name_en']);
});

test('authorized manager can update a role', function () {
    $admin = managerWithPermission('roles-update');
    $role = role('editor', 'Editor');

    $this->actingAs($admin, 'manager')
        ->put(route('roles.update', $role->id), [
            'display_name_ar' => 'محرّر أول',
            'display_name_en' => 'Senior Editor',
        ])
        ->assertRedirect(route('roles.index'))
        ->assertSessionHasNoErrors();

    $this->assertDatabaseHas('roles', ['id' => $role->id, 'display_name_en' => 'Senior Editor']);
});

test('authorized manager can delete a role', function () {
    $admin = managerWithPermission('roles-delete');
    $role = role('editor', 'Editor');

    $this->actingAs($admin, 'manager')
        ->delete(route('roles.destroy', $role->id))
        ->assertRedirect(route('roles.index'));

    $this->assertDatabaseMissing('roles', ['id' => $role->id]);
});

test('a manager without permission cannot open the roles list', function () {
    $manager = Manager::factory()->create();

    $this->actingAs($manager, 'manager')
        ->withoutMiddleware([
            LocaleSessionRedirect::class,
            LaravelLocalizationRedirectFilter::class,
            LaravelLocalizationViewPath::class,
        ])
        ->get(route('roles.index', absolute: false))
        ->assertForbidden();
});

function role(string $name, string $displayName): Role
{
    return Role::query()->create([
        'name' => $name,
        'guard_name' => 'manager',
        'display_name_ar' => $displayName,
        'display_name_en' => $displayName,
    ]);
}
