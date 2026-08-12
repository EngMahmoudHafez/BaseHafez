<?php

use App\Modules\Auth\Models\Manager;
use App\Modules\Auth\Models\Role;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Mcamara\LaravelLocalization\Middleware\LaravelLocalizationRedirectFilter;
use Mcamara\LaravelLocalization\Middleware\LaravelLocalizationViewPath;
use Mcamara\LaravelLocalization\Middleware\LocaleSessionRedirect;

uses(RefreshDatabase::class);

test('deleting a role that still has managers assigned is blocked', function () {
    $actor = superAdminManager();
    $role = Role::query()->create([
        'name' => 'support-team',
        'guard_name' => 'manager',
        'display_name_ar' => 'الدعم',
        'display_name_en' => 'Support',
    ]);
    Manager::factory()->create()->addRole($role);

    $this->actingAs($actor, 'manager')
        ->from(route('roles.index'))
        ->delete(route('roles.destroy', $role->id))
        ->assertSessionHasErrors('role');

    expect(Role::query()->whereKey($role->id)->exists())->toBeTrue();
});

test('deleteImage clears the column before removing the file (no dangling reference)', function () {
    Storage::fake('public');
    $path = UploadedFile::fake()->image('avatar.jpg')->store('managers', 'public');
    $manager = Manager::factory()->create(['avatar' => $path]);

    $manager->deleteImage();

    expect($manager->fresh()->avatar)->toBeNull();
    Storage::disk('public')->assertMissing($path);
});

test('putImage stores the file and persists the column, replacing any previous file', function () {
    Storage::fake('public');
    $old = UploadedFile::fake()->image('old.jpg')->store('managers', 'public');
    $manager = Manager::factory()->create(['avatar' => $old]);

    $stored = $manager->putImage(UploadedFile::fake()->image('new.png'));

    Storage::disk('public')->assertExists($stored);
    Storage::disk('public')->assertMissing($old); // previous file removed only after a successful save
    expect($manager->fresh()->avatar)->toBe($stored);
});

test('manager export streams a csv without buffering the whole table', function () {
    $actor = superAdminManager();
    Manager::factory()->count(3)->create();

    $response = $this->actingAs($actor, 'manager')
        ->withoutMiddleware([
            LocaleSessionRedirect::class,
            LaravelLocalizationRedirectFilter::class,
            LaravelLocalizationViewPath::class,
        ])
        ->get(route('managers.export', absolute: false));

    $response->assertOk();
    expect($response->headers->get('content-type'))->toContain('text/csv');
    expect($response->streamedContent())->toContain($actor->email);
});
