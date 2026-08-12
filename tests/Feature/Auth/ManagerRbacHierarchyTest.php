<?php

use App\Modules\Auth\Enums\RoleName;
use App\Modules\Auth\Enums\UserStatus;
use App\Modules\Auth\Models\Manager;
use App\Modules\Auth\Models\Role;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;

uses(RefreshDatabase::class);

function rbacRole(string $name): Role
{
    return Role::query()->firstOrCreate(
        ['name' => $name, 'guard_name' => 'manager'],
        ['display_name_ar' => $name, 'display_name_en' => $name],
    );
}

test('a managers-create manager cannot assign the super_admin role', function () {
    $superAdminRole = rbacRole(RoleName::SuperAdmin->value);
    $actor = managerWithPermission('managers-create');

    $this->actingAs($actor, 'manager')
        ->post(route('managers.store'), [
            'name' => 'Escalated',
            'email' => 'escalated@example.test',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'role_id' => $superAdminRole->id,
            'status' => UserStatus::Active->value,
        ])
        ->assertSessionHasErrors('role_id');

    expect(Manager::query()->where('email', 'escalated@example.test')->exists())->toBeFalse();
});

test('a lower-rank manager cannot reset a higher-rank managers password', function () {
    $actor = managerWithPermission('managers-update');
    $target = superAdminManager();

    $this->actingAs($actor, 'manager')
        ->put(route('managers.password.update', $target), [
            'password' => 'brand-new-password',
            'password_confirmation' => 'brand-new-password',
        ])
        ->assertSessionHasErrors('manager');

    expect(Hash::check('brand-new-password', $target->fresh()->password))->toBeFalse();
});

test('a lower-rank manager cannot block a super admin', function () {
    $actor = managerWithPermission('managers-update');
    $target = superAdminManager();

    $this->actingAs($actor, 'manager')
        ->patch(route('managers.toggle', $target))
        ->assertSessionHasErrors('manager');

    expect($target->fresh()->status)->toBe(UserStatus::Active);
});

test('the last active super admin cannot be demoted to a lower role', function () {
    $actor = superAdminManager();
    $supportRole = rbacRole(RoleName::Support->value);

    $this->actingAs($actor, 'manager')
        ->put(route('managers.update', $actor), [
            'name' => $actor->name,
            'email' => $actor->email,
            'role_id' => $supportRole->id,
            'status' => UserStatus::Active->value,
        ])
        ->assertSessionHasErrors('manager');

    expect($actor->fresh()->isSuperAdmin())->toBeTrue();
});

test('a roles-update manager cannot modify a core role', function () {
    $actor = managerWithPermission('roles-update');
    $superAdminRole = rbacRole(RoleName::SuperAdmin->value);

    $this->actingAs($actor, 'manager')
        ->put(route('roles.update', $superAdminRole->id), [
            'display_name_ar' => 'محاولة',
            'display_name_en' => 'Attempt',
            'permissions' => [],
        ])
        ->assertSessionHasErrors('role');
});

test('a roles-delete manager cannot delete a core role', function () {
    $actor = managerWithPermission('roles-delete');
    $adminRole = rbacRole(RoleName::Admin->value);

    $this->actingAs($actor, 'manager')
        ->delete(route('roles.destroy', $adminRole->id))
        ->assertSessionHasErrors('role');

    expect(Role::query()->whereKey($adminRole->id)->exists())->toBeTrue();
});

test('a super admin can create a manager with the super_admin role', function () {
    $actor = superAdminManager();
    $superAdminRole = Role::query()->where('name', RoleName::SuperAdmin->value)->firstOrFail();

    $this->actingAs($actor, 'manager')
        ->post(route('managers.store'), [
            'name' => 'Second Admin',
            'email' => 'second@example.test',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'role_id' => $superAdminRole->id,
            'status' => UserStatus::Active->value,
        ])
        ->assertRedirect(route('managers.index'))
        ->assertSessionHasNoErrors();

    $created = Manager::query()->where('email', 'second@example.test')->firstOrFail();
    expect($created->hasRole(RoleName::SuperAdmin->value))->toBeTrue();
});
