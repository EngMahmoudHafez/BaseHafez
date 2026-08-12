<?php

test('base:install --ci is idempotent and never regenerates an existing key', function () {
    // The dev .env already carries APP_KEY/JWT_SECRET, so --ci must be a pure
    // no-op: it may run any number of times without rotating secrets.
    $appKeyBefore = config('app.key');
    $jwtSecretBefore = config('jwt.secret');

    $this->artisan('base:install --ci')->assertSuccessful();
    $this->artisan('base:install --ci')->assertSuccessful();

    expect(config('app.key'))->toBe($appKeyBefore);
    expect(config('jwt.secret'))->toBe($jwtSecretBefore);
});
