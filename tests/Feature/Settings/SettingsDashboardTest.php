<?php

use App\Modules\Auth\Models\Manager;
use App\Modules\Settings\Models\Setting;
use App\Modules\Settings\Services\SettingsService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Mcamara\LaravelLocalization\Middleware\LaravelLocalizationRedirectFilter;
use Mcamara\LaravelLocalization\Middleware\LaravelLocalizationViewPath;
use Mcamara\LaravelLocalization\Middleware\LocaleSessionRedirect;

uses(RefreshDatabase::class);

test('the dashboard index lists only settings flagged for the dashboard', function () {
    $manager = managerWithPermission('settings-read');

    Setting::factory()->create(['key' => 'visible_setting_key']);
    Setting::factory()->hidden()->create(['key' => 'hidden_setting_key']);

    $this->actingAs($manager, 'manager')
        ->withoutMiddleware([
            LocaleSessionRedirect::class,
            LaravelLocalizationRedirectFilter::class,
            LaravelLocalizationViewPath::class,
        ])
        ->get(route('dashboard.settings.index', absolute: false))
        ->assertOk()
        ->assertSee('visible_setting_key')
        ->assertDontSee('hidden_setting_key');
});

test('a manager without permission cannot open the settings list', function () {
    $manager = Manager::factory()->create();

    $this->actingAs($manager, 'manager')
        ->withoutMiddleware([
            LocaleSessionRedirect::class,
            LaravelLocalizationRedirectFilter::class,
            LaravelLocalizationViewPath::class,
        ])
        ->get(route('dashboard.settings.index', absolute: false))
        ->assertForbidden();
});

test('an authorized manager can update a setting value and notes', function () {
    $manager = managerWithPermission('settings-update');
    $setting = Setting::factory()->create(['key' => 'locked_key', 'value' => 'old']);

    $this->actingAs($manager, 'manager')
        ->put(route('dashboard.settings.update', $setting), [
            'value' => 'new value',
            'notes' => 'a helpful note',
        ])
        ->assertRedirect(route('dashboard.settings.index'))
        ->assertSessionHasNoErrors();

    $this->assertDatabaseHas('settings', [
        'id' => $setting->id,
        'key' => 'locked_key',
        'value' => 'new value',
        'notes' => 'a helpful note',
    ]);

    // The write must invalidate the cached map so the next read is fresh.
    expect(app(SettingsService::class)->get('locked_key'))->toBe('new value');
});

test('the update never changes the immutable key', function () {
    $manager = managerWithPermission('settings-update');
    $setting = Setting::factory()->create(['key' => 'immutable_key', 'value' => 'old']);

    $this->actingAs($manager, 'manager')
        ->put(route('dashboard.settings.update', $setting), [
            'key' => 'attacker_key',
            'value' => 'changed',
        ])
        ->assertRedirect(route('dashboard.settings.index'));

    $this->assertDatabaseHas('settings', ['id' => $setting->id, 'key' => 'immutable_key']);
    $this->assertDatabaseMissing('settings', ['key' => 'attacker_key']);
});

test('a manager with only read permission cannot update a setting', function () {
    $manager = managerWithPermission('settings-read');
    $setting = Setting::factory()->create(['value' => 'original']);

    $this->actingAs($manager, 'manager')
        ->put(route('dashboard.settings.update', $setting), ['value' => 'hacked'])
        ->assertForbidden();

    $this->assertDatabaseHas('settings', ['id' => $setting->id, 'value' => 'original']);
});
