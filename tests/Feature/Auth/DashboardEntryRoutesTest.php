<?php

use App\Modules\Auth\Models\Manager;

test('root redirects guests to the manager login page', function () {
    $this->get('/')
        ->assertRedirect(route('login', absolute: false));
});

test('root redirects authenticated managers to dashboard', function () {
    $manager = Manager::factory()->make(['id' => 1]);

    $this->actingAs($manager, 'manager')
        ->get('/')
        ->assertRedirect(route('dashboard.home', absolute: false));
});
