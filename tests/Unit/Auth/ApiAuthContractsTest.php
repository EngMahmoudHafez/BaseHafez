<?php

use App\Modules\Auth\Http\Requests\Api\V1\Auth\SignUpRequest;
use App\Modules\Auth\Http\Requests\Api\V1\Auth\VerifyOtpRequest;

test('sign up request requires phone and terms acceptance', function () {
    $rules = (new SignUpRequest)->rules();

    expect($rules)->toHaveKey('phone');
    expect($rules['terms_accepted'])->toBe(['required', 'accepted']);
});

test('verify otp request requires otp token and code', function () {
    $rules = (new VerifyOtpRequest)->rules();

    expect($rules)->toHaveKey('phone');
    expect($rules)->toHaveKey('otp_token');
    expect($rules['otp_token'])->toBe('required|string');
    expect($rules['code'])->toBe('required|string|min:4|max:6');
});
