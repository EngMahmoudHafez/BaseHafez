<?php

use App\Modules\Auth\Http\Controllers\Api\V1\Auth\AuthController;
use App\Modules\Auth\Http\Controllers\Api\V1\Auth\Password\PasswordController;
use App\Modules\Auth\Http\Controllers\Api\V1\UserProfileController;
use Illuminate\Support\Facades\Route;

Route::prefix('web/v1')->group(function () {
    Route::controller(AuthController::class)->prefix('auth')->name('auth.')->group(function () {
        Route::post('sign-up', 'signUp')->name('sign-up')->middleware('throttle:5,1');
        Route::post('verify', 'verifySignUpOtp')->name('verify')->middleware('throttle:10,1');
        Route::post('resend', 'resendSignUpOtp')->name('resend')->middleware('throttle:5,1');
        Route::post('sign-in', 'signIn')->name('sign-in')->middleware('throttle:15,1');
    });

    Route::controller(PasswordController::class)->prefix('password')->name('password.')->group(function () {
        Route::post('forgot', 'forgot')->name('forgot')->middleware('throttle:3,1');
        Route::post('verify-otp', 'verifyOtp')->name('verify-otp')->middleware('throttle:5,1');
        Route::post('reset', 'reset')->name('reset')->middleware('throttle:5,1');
    });
});

Route::middleware(['auth:api', 'api.active'])->prefix('web/v1')->group(function () {
    Route::controller(AuthController::class)->prefix('auth')->name('auth.')->group(function () {
        Route::post('sign-out', 'signOut')->name('sign-out');
    });

    Route::controller(PasswordController::class)->prefix('password')->name('password.')->group(function () {
        Route::post('update', 'updatePassword')->name('update');
    });

    Route::controller(UserProfileController::class)
        ->prefix('profile')
        ->name('profile.')->group(function () {
            Route::get('/', 'getProfile')->name('get');
            Route::match(['put', 'patch', 'post'], '/', 'updateProfile')->name('update');
            Route::post('/profile-image', 'uploadProfileImage')->name('profile-image');
        });

});
