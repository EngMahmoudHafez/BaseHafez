<?php

use App\Modules\Auth\Enums\UserStatus;
use App\Modules\Auth\Models\Manager;
use App\Modules\Auth\Models\Role;
use App\Modules\Auth\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Mcamara\LaravelLocalization\Middleware\LaravelLocalizationRedirectFilter;
use Mcamara\LaravelLocalization\Middleware\LaravelLocalizationViewPath;
use Mcamara\LaravelLocalization\Middleware\LocaleSessionRedirect;

uses(RefreshDatabase::class);

test('authenticated manager can open the generic dashboard home', function () {
    $adminRole = Role::query()->create(['name' => 'admin', 'guard_name' => 'manager']);
    $manager = Manager::factory()->create();
    $manager->addRole($adminRole);
    User::factory()->create(['name' => 'Recent Foundation User', 'status' => UserStatus::Active]);
    User::factory()->create(['status' => UserStatus::Blocked]);

    $response = $this->actingAs($manager->fresh(), 'manager')
        ->withoutMiddleware([
            LocaleSessionRedirect::class,
            LaravelLocalizationRedirectFilter::class,
            LaravelLocalizationViewPath::class,
        ])
        ->get(route('dashboard.home', absolute: false));

    $response->assertOk()
        ->assertSee('Recent Foundation User')
        ->assertViewHas('statCards')
        ->assertViewHas('recentUsers');
});
