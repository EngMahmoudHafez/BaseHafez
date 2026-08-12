<?php

use App\Modules\Structure\Models\Structure;
use App\Modules\Structure\Repositories\StructureRepositoryInterface;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

function publishHero(string $subtitle, string $title = 'Plain title'): array
{
    app(StructureRepositoryInterface::class)->publish('hero', [
        'ar' => ['title_primary' => $title, 'subtitle' => $subtitle],
        'en' => ['title_primary' => $title, 'subtitle' => $subtitle],
    ]);

    return Structure::query()->where('key', 'hero')->firstOrFail()->content;
}

test('publishing strips scripts, event handlers and dangerous uris from rich-text fields', function () {
    $stored = publishHero(
        '<p>Hello</p><script>alert(1)</script><img src=x onerror=alert(1)>'
        . '<a href="javascript:alert(1)">bad</a><a href="https://ok.test">good</a>'
        . '<svg onload=alert(1)></svg><iframe src="javascript:alert(1)"></iframe>',
    );

    $subtitle = $stored['en']['subtitle'];

    expect($subtitle)
        ->toContain('<p>Hello</p>')
        ->toContain('https://ok.test')
        ->not->toContain('<script')
        ->not->toContain('onerror')
        ->not->toContain('onload')
        ->not->toContain('javascript:')
        ->not->toContain('<svg')
        ->not->toContain('<iframe');
});

test('publishing preserves the allowed rich-text formatting', function () {
    $stored = publishHero(
        '<p><strong>Bold</strong> and <em>italic</em></p><ul><li>one</li><li>two</li></ul>',
    );

    expect($stored['en']['subtitle'])
        ->toContain('<strong>Bold</strong>')
        ->toContain('<em>italic</em>')
        ->toContain('<ul>')
        ->toContain('<li>one</li>');
});

test('a plain-text field is left byte-for-byte intact (not mangled by the sanitizer)', function () {
    $stored = publishHero('<p>rich</p>', title: 'A < B & C');

    expect($stored['en']['title_primary'])->toBe('A < B & C');
});

test('the public api returns sanitized rich-text and hex-escapes html characters', function () {
    publishHero('<p>ok</p><script>alert(1)</script>');

    $response = $this->getJson('/web/v1/structures/hero')->assertOk();

    expect($response->json('data.subtitle'))
        ->toContain('<p>ok</p>')
        ->not->toContain('<script>');

    // The raw JSON body hex-escapes every HTML-significant character, so it can
    // never break out of an HTML/<script> embedding context on the consumer side.
    $raw = (string) $response->getContent();
    $escapedLt = trim((string) json_encode('<', JSON_HEX_TAG), '"'); // the escaped form of '<'
    expect($raw)->toContain($escapedLt);            // '<' arrives hex-escaped
    expect(str_contains($raw, '<'))->toBeFalse();   // no literal '<' survives in the body
});
