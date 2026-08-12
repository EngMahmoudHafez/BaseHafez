<?php

use App\Modules\Structure\Models\Structure;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('returns the content of a public page', function () {
    Structure::query()->create([
        'key' => 'hero',
        'content' => [
            'ar' => ['title_primary' => 'Shared Title', 'subtitle' => 'Shared Subtitle'],
            'en' => ['title_primary' => 'Shared Title', 'subtitle' => 'Shared Subtitle'],
        ],
    ]);

    $this->getJson('/web/v1/structures/hero')
        ->assertOk()
        ->assertJsonPath('data.title_primary', 'Shared Title')
        ->assertJsonPath('data.subtitle', 'Shared Subtitle');
});

it('returns 404 for a non public page', function () {
    $this->getJson('/web/v1/structures/about')->assertNotFound();
});

it('returns 404 for an unknown key', function () {
    $this->getJson('/web/v1/structures/does_not_exist')->assertNotFound();
});
