<?php

use App\Modules\Auth\Models\Manager;
use App\Modules\Auth\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Mcamara\LaravelLocalization\Middleware\LaravelLocalizationRedirectFilter;
use Mcamara\LaravelLocalization\Middleware\LaravelLocalizationViewPath;
use Mcamara\LaravelLocalization\Middleware\LocaleSessionRedirect;

uses(RefreshDatabase::class);

test('authorized manager can create a user', function () {
    $admin = managerWithPermission('users-create');

    $this->actingAs($admin, 'manager')
        ->post(route('users.store'), [
            'name' => 'New User',
            'whatsapp' => '+201001234567',
            'status' => 'active',
        ])
        ->assertRedirect(route('users.index'))
        ->assertSessionHasNoErrors();

    $this->assertDatabaseHas('users', [
        'name' => 'New User',
        'whatsapp' => '+201001234567',
    ]);
});

test('creating a user requires a whatsapp number', function () {
    $admin = managerWithPermission('users-create');

    $this->actingAs($admin, 'manager')
        ->post(route('users.store'), [
            'name' => 'No Contact',
            'status' => 'active',
        ])
        ->assertSessionHasErrors('whatsapp');
});

test('authorized manager can delete a user', function () {
    $admin = managerWithPermission('users-delete');
    $user = User::factory()->create();

    $this->actingAs($admin, 'manager')
        ->delete(route('users.destroy', $user->id))
        ->assertRedirect(route('users.index'));

    $this->assertDatabaseMissing('users', ['id' => $user->id]);
});

test('a manager without permission cannot open the users list', function () {
    $manager = Manager::factory()->create();

    $this->actingAs($manager, 'manager')
        ->withoutMiddleware([
            LocaleSessionRedirect::class,
            LaravelLocalizationRedirectFilter::class,
            LaravelLocalizationViewPath::class,
        ])
        ->get(route('users.index', absolute: false))
        ->assertForbidden();
});
