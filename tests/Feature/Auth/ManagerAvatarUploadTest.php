<?php

use App\Modules\Auth\Models\Manager;
use App\Modules\Auth\Models\Role;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

uses(RefreshDatabase::class);

test('authorized manager can create a manager with an avatar', function () {
    Storage::fake('public');
    $admin = superAdminManager();
    $role = Role::query()->create(['name' => 'content-editor', 'guard_name' => 'manager']);

    $this->actingAs($admin, 'manager')
        ->post(route('managers.store'), [
            'role_id' => $role->id,
            'name' => 'New Manager',
            'email' => 'manager@example.com',
            'phone' => '+201001234567',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'image' => UploadedFile::fake()->image('avatar.webp'),
            'status' => 'active',
        ])
        ->assertRedirect(route('managers.index'))
        ->assertSessionHasNoErrors();

    $manager = Manager::query()->where('email', 'manager@example.com')->firstOrFail();

    expect($manager->hasRole('content-editor'))->toBeTrue();
    Storage::disk('public')->assertExists($manager->avatar);
});

test('authorized manager can replace avatar and old file is deleted', function () {
    Storage::fake('public');
    $admin = superAdminManager();
    $role = Role::query()->create(['name' => 'content-editor', 'guard_name' => 'manager']);
    $oldAvatar = UploadedFile::fake()->image('old.jpg')->store('managers', 'public');
    $manager = Manager::factory()->create([
        'avatar' => $oldAvatar,
        'email' => 'existing@example.com',
        'phone' => '+201001234568',
    ]);
    $manager->roles()->attach($role);

    $this->actingAs($admin, 'manager')
        ->put(route('managers.update', $manager), [
            'id' => $manager->id,
            'role_id' => $role->id,
            'name' => 'Updated Manager',
            'email' => 'existing@example.com',
            'phone' => '+201001234568',
            'image' => UploadedFile::fake()->image('new.png'),
            'status' => 'active',
        ])
        ->assertRedirect(route('managers.index'))
        ->assertSessionHasNoErrors();

    $manager->refresh();

    $this->assertNotSame($oldAvatar, $manager->avatar);
    Storage::disk('public')->assertMissing($oldAvatar);
    Storage::disk('public')->assertExists($manager->avatar);
});

test('manager avatar rejects non image files', function () {
    Storage::fake('public');
    $admin = superAdminManager();
    $role = Role::query()->create(['name' => 'content-editor', 'guard_name' => 'manager']);

    $this->actingAs($admin, 'manager')
        ->post(route('managers.store'), [
            'role_id' => $role->id,
            'name' => 'Invalid Avatar',
            'email' => 'invalid@example.com',
            'phone' => '+201001234569',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'image' => UploadedFile::fake()->create('avatar.pdf', 100, 'application/pdf'),
            'status' => 'active',
        ])
        ->assertSessionHasErrors('image');
});
