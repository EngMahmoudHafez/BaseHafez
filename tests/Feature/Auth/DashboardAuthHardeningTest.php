<?php

use App\Modules\Auth\Enums\UserStatus;
use App\Modules\Auth\Models\Manager;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;

uses(RefreshDatabase::class);

test('dashboard login does not reveal an inactive account when the password is wrong', function () {
    Manager::factory()->create([
        'email' => 'blocked@example.test',
        'password' => 'correct-password',
        'status' => UserStatus::Blocked,
    ]);

    $this->post(route('auth.login'), [
        'email' => 'blocked@example.test',
        'password' => 'wrong-password',
    ])->assertSessionHas('error', __('messages.Incorrect email or password'));
});

test('dashboard login returns the generic error for an unknown email', function () {
    $this->post(route('auth.login'), [
        'email' => 'nobody@example.test',
        'password' => 'whatever-password',
    ])->assertSessionHas('error', __('messages.Incorrect email or password'));
});

test('dashboard login reveals inactive status only after the correct password', function () {
    Manager::factory()->create([
        'email' => 'blocked2@example.test',
        'password' => 'correct-password',
        'status' => UserStatus::Blocked,
    ]);

    $this->post(route('auth.login'), [
        'email' => 'blocked2@example.test',
        'password' => 'correct-password',
    ])->assertSessionHas('error', __('messages.Account is inactive. Please contact the administration'));
});

test('dashboard forgot-password returns the same response for known and unknown emails', function () {
    Notification::fake();
    Manager::factory()->create(['email' => 'known@example.test']);

    $this->from(route('login'))
        ->post(route('auth.password.email'), ['email' => 'known@example.test'])
        ->assertSessionHas('status', __('auth.reset_link_sent'));

    $this->from(route('login'))
        ->post(route('auth.password.email'), ['email' => 'nobody@example.test'])
        ->assertSessionHas('status', __('auth.reset_link_sent'))
        ->assertSessionHasNoErrors();
});

test('dashboard login is throttled after repeated attempts', function () {
    $response = null;

    foreach (range(1, 6) as $ignored) {
        $response = $this->post(route('auth.login'), [
            'email' => 'brute@example.test',
            'password' => 'wrong-password',
        ]);
    }

    $response->assertStatus(429);
});
