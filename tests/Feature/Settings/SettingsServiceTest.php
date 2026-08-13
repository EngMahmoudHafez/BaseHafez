<?php

use App\Modules\Settings\Models\Setting;
use App\Modules\Settings\Services\SettingsService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;

uses(RefreshDatabase::class);

test('get returns the provided default when the key is missing', function () {
    $service = app(SettingsService::class);

    expect($service->get('missing', 'fallback'))->toBe('fallback');
    expect($service->get('missing'))->toBeNull();
});

test('set stores a value that get can read back', function () {
    $service = app(SettingsService::class);

    $service->set('support_email', 'help@example.com');

    expect($service->get('support_email'))->toBe('help@example.com');
    $this->assertDatabaseHas('settings', [
        'key' => 'support_email',
        'value' => 'help@example.com',
    ]);
});

test('set upserts by key without creating duplicates', function () {
    $service = app(SettingsService::class);

    $service->set('site_name', 'Alpha');
    $service->set('site_name', 'Beta');

    expect(Setting::query()->where('key', 'site_name')->count())->toBe(1);
    expect($service->get('site_name'))->toBe('Beta');
});

test('has reports whether a key exists', function () {
    $service = app(SettingsService::class);

    Setting::factory()->create(['key' => 'known']);

    expect($service->has('known'))->toBeTrue();
    expect($service->has('unknown'))->toBeFalse();
});

test('forget removes a stored setting', function () {
    $service = app(SettingsService::class);

    $service->set('temporary', 'value');
    expect($service->has('temporary'))->toBeTrue();

    $service->forget('temporary');

    expect($service->has('temporary'))->toBeFalse();
    $this->assertDatabaseMissing('settings', ['key' => 'temporary']);
});

test('set invalidates the cached settings map', function () {
    $service = app(SettingsService::class);

    $service->set('theme', 'light');
    // Warm the cache through a read.
    expect($service->get('theme'))->toBe('light');
    expect(Cache::has('settings.all'))->toBeTrue();

    $service->set('theme', 'dark');

    // A stale cache would still report "light"; the write must have flushed it.
    expect($service->get('theme'))->toBe('dark');
});

test('forget invalidates the cached settings map', function () {
    $service = app(SettingsService::class);

    $service->set('flag', 'on');
    expect($service->has('flag'))->toBeTrue();
    expect(Cache::has('settings.all'))->toBeTrue();

    $service->forget('flag');

    expect(Cache::has('settings.all'))->toBeFalse();
    expect($service->has('flag'))->toBeFalse();
});
