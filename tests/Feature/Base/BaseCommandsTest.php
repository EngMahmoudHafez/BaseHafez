<?php

test('base modules lists the registered modules', function () {
    $this->artisan('base:modules')
        ->assertSuccessful()
        ->expectsOutputToContain('Auth')
        ->expectsOutputToContain('Base');
});

test('base doctor passes when keys are present', function () {
    config([
        'app.key' => 'base64:' . base64_encode(random_bytes(32)),
        'jwt.secret' => 'testing-secret',
    ]);

    $this->artisan('base:doctor')->assertSuccessful();
});

test('base doctor production fails on debug and wildcard cors', function () {
    config([
        'app.key' => 'base64:' . base64_encode(random_bytes(32)),
        'jwt.secret' => 'testing-secret',
        'app.debug' => true,
        'cors.allowed_origins' => ['*'],
    ]);

    $this->artisan('base:doctor --production')->assertFailed();
});
