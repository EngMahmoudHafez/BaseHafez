<?php

use App\Modules\Auth\database\seeders\ManagerSeeder;
use App\Modules\Auth\Enums\RoleName;
use App\Modules\Auth\Models\Manager;
use App\Modules\Auth\Models\Role;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;

uses(RefreshDatabase::class);

test('config exposes no fallback bootstrap admin credentials', function () {
    // BASE_ADMIN_* is unset in the test environment, so the effective config must be
    // null — never a hardcoded, publicly-known default.
    expect(config('foundation.admin.email'))->toBeNull();
    expect(config('foundation.admin.password'))->toBeNull();
});

test('manager seeder skips creation when the admin email is not configured', function () {
    config([
        'foundation.admin.email' => null,
        'foundation.admin.password' => 'a-sufficiently-long-password',
    ]);

    $this->seed(ManagerSeeder::class);

    expect(Manager::query()->count())->toBe(0);
});

test('manager seeder skips creation when the admin password is shorter than 12 characters', function () {
    config([
        'foundation.admin.email' => 'root@example.test',
        'foundation.admin.password' => 'eleven-char', // 11 chars — one below the minimum
    ]);

    $this->seed(ManagerSeeder::class);

    expect(Manager::query()->count())->toBe(0);
});

test('manager seeder creates a super admin from valid configured credentials', function () {
    Role::query()->create([
        'name' => RoleName::SuperAdmin->value,
        'guard_name' => 'manager',
        'display_name_ar' => 'مدير عام',
        'display_name_en' => 'Super Admin',
    ]);

    config([
        'foundation.admin.email' => 'root@example.test',
        'foundation.admin.password' => 'a-very-strong-secret-value',
    ]);

    $this->seed(ManagerSeeder::class);

    $manager = Manager::query()->where('email', 'root@example.test')->first();

    expect($manager)->not->toBeNull();
    expect($manager->hasRole(RoleName::SuperAdmin->value))->toBeTrue();
    expect(Hash::check('a-very-strong-secret-value', $manager->password))->toBeTrue();
});

test('production doctor passes with a hardened profile and no bootstrap password', function () {
    hardenedProductionConfig();
    config(['foundation.admin.password' => null]);

    $this->artisan('base:doctor', ['--production' => true])->assertSuccessful();
});

test('production doctor fails on a weak bootstrap admin password', function () {
    hardenedProductionConfig();
    config(['foundation.admin.password' => '123123123']);

    $this->artisan('base:doctor', ['--production' => true])->assertFailed();
});

/**
 * Force every production-hardening check other than the admin credential to pass,
 * so a doctor failure is decisively attributable to the bootstrap password.
 */
function hardenedProductionConfig(): void
{
    config([
        'app.env' => 'production',
        'app.debug' => false,
        'app.key' => 'base64:' . base64_encode(random_bytes(32)),
        'app.url' => 'https://app.example.test',
        'jwt.secret' => 'testing-secret-value',
        'cors.allowed_origins' => ['https://app.example.test'],
        'queue.default' => 'database',
        'mail.default' => 'smtp',
        'session.driver' => 'database',
        'session.secure' => true,
    ]);
}
