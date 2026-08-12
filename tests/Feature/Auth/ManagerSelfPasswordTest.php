<?php

use App\Modules\Auth\Models\Manager;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;

uses(RefreshDatabase::class);

test('authenticated manager can change their own password with the correct current password', function () {
    $manager = Manager::factory()->create(['password' => 'current-secret']);

    $this->actingAs($manager, 'manager')
        ->from(route('settings.edit', absolute: false))
        ->post(route('update-password', absolute: false), [
            'current_password' => 'current-secret',
            'new_password' => 'new-strong-secret',
            'new_password_confirmation' => 'new-strong-secret',
        ])
        ->assertRedirect()
        ->assertSessionHas('success');

    expect(Hash::check('new-strong-secret', $manager->fresh()->password))->toBeTrue();
});

test('current password is validated against the manager guard, not the empty web guard', function () {
    // Regression: `current_password` (no guard) checked the default `web` guard, which has
    // no authenticated user on the dashboard, so the correct manager password was always
    // rejected and a manager could never change their own password.
    $manager = Manager::factory()->create(['password' => 'current-secret']);

    $this->actingAs($manager, 'manager')
        ->from(route('settings.edit', absolute: false))
        ->post(route('update-password', absolute: false), [
            'current_password' => 'wrong-secret',
            'new_password' => 'new-strong-secret',
            'new_password_confirmation' => 'new-strong-secret',
        ])
        ->assertSessionHasErrors('current_password');

    expect(Hash::check('current-secret', $manager->fresh()->password))->toBeTrue();
});

test('new password is required', function () {
    $manager = Manager::factory()->create(['password' => 'current-secret']);

    $this->actingAs($manager, 'manager')
        ->from(route('settings.edit', absolute: false))
        ->post(route('update-password', absolute: false), [
            'current_password' => 'current-secret',
        ])
        ->assertSessionHasErrors('new_password');

    expect(Hash::check('current-secret', $manager->fresh()->password))->toBeTrue();
});
