<?php

use App\Modules\Settings\Models\Setting;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('a setting can be created from its factory', function () {
    $setting = Setting::factory()->create();

    $this->assertDatabaseHas('settings', ['id' => $setting->id]);
});

test('the key column is unique', function () {
    Setting::factory()->create(['key' => 'duplicate']);

    expect(fn () => Setting::factory()->create(['key' => 'duplicate']))
        ->toThrow(QueryException::class);
});

test('show_in_dashboard is cast to a boolean', function () {
    $setting = Setting::factory()->create(['show_in_dashboard' => 1]);

    expect($setting->refresh()->show_in_dashboard)->toBeTrue();
});
