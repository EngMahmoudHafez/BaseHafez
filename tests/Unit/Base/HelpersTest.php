<?php

use App\Helpers\Helpers;

afterEach(function () {
    unset($_COOKIE['admin-primaryColor']);

});

test('primary color css accepts only a six digit hex color', function () {
    $this->assertStringContainsString('--bs-primary: #336699', Helpers::generatePrimaryColorCSS('#336699'));
    expect(Helpers::generatePrimaryColorCSS('red;}</style><script>alert(1)</script>'))->toBe('');
});

test('layout configuration ignores an invalid color cookie', function () {
    $_COOKIE['admin-primaryColor'] = 'not-a-color';

    expect(Helpers::appClasses()['color'])->toBeNull();
});
