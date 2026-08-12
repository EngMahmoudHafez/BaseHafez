<?php

use App\Modules\Auth\Http\Requests\Dashboard\Auth\ResetPasswordRequest;
use App\Modules\Auth\Http\Requests\Dashboard\Auth\SendResetLinkEmailRequest;

test('reset link request requires email', function () {
    $request = new SendResetLinkEmailRequest;

    expect($request->rules())->toBe([
        'email' => ['required', 'email'],
    ]);
});

test('reset password request requires token email and confirmed password', function () {
    $request = new ResetPasswordRequest;

    expect($request->rules())->toBe([
        'token' => ['required', 'string'],
        'email' => ['required', 'email'],
        'password' => ['required', 'confirmed', 'min:8'],
    ]);
});
