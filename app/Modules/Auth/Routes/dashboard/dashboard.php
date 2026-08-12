<?php

use App\Modules\Auth\Http\Controllers\Dashboard\Auth\AuthController;
use App\Modules\Auth\Http\Controllers\Dashboard\Home\HomeController;
use App\Modules\Auth\Http\Controllers\Dashboard\Managers\ManagerController;
use App\Modules\Auth\Http\Controllers\Dashboard\Roles\RoleController;
use App\Modules\Auth\Http\Controllers\Dashboard\Settings\SettingController;
use App\Modules\Auth\Http\Controllers\Dashboard\User\UserController;
use Illuminate\Support\Facades\Route;
use Mcamara\LaravelLocalization\Facades\LaravelLocalization;

// Dashboard routes with localization
Route::group([
    'prefix' => LaravelLocalization::setLocale() . '/dashboard',
    'middleware' => ['web', 'localeSessionRedirect', 'localizationRedirect', 'localeViewPath'],
], function () {
    Route::middleware('guest:manager')->group(function (): void {
        Route::get('login', [AuthController::class, 'showLoginForm'])->name('login');
        Route::post('login', [AuthController::class, 'login'])->name('auth.login')->middleware('throttle:dashboard-auth');

        Route::group(['prefix' => 'auth', 'as' => 'auth.'], function (): void {
            Route::get('forgot-password', [AuthController::class, 'showForgotPasswordForm'])->name('password.request');
            Route::post('forgot-password', [AuthController::class, 'sendResetLinkEmail'])->name('password.email')->middleware('throttle:dashboard-auth');
            Route::get('reset-password/{token}', [AuthController::class, 'showResetPasswordForm'])->name('password.reset');
            Route::post('reset-password', [AuthController::class, 'resetPassword'])->name('password.update')->middleware('throttle:dashboard-auth');
        });
    });

    Route::middleware(['auth:manager', 'manager.active'])->group(function (): void {
        Route::post('logout', [AuthController::class, 'logout'])->name('auth.logout');
        Route::get('/home', [HomeController::class, 'index'])->name('dashboard.home');

        // Users routes - with ID constraint to prevent conflicts
        Route::get('users', [UserController::class, 'index'])->name('users.index');
        Route::get('users/export', [UserController::class, 'export'])->name('users.export');
        Route::get('users/create', [UserController::class, 'create'])->name('users.create');
        Route::post('users', [UserController::class, 'store'])->name('users.store');
        Route::get('users/{id}/edit', [UserController::class, 'edit'])->name('users.edit')->where('id', '[0-9]+');
        Route::get('users/{id}', [UserController::class, 'show'])->name('users.show')->where('id', '[0-9]+');
        Route::patch('users/{id}/toggle', [UserController::class, 'toggle'])->name('users.toggle')->where('id', '[0-9]+');
        Route::put('users/{id}', [UserController::class, 'update'])->name('users.update')->where('id', '[0-9]+');
        Route::delete('users/{id}', [UserController::class, 'destroy'])->name('users.destroy')->where('id', '[0-9]+');

        // Settings routes
        Route::get('profile', [SettingController::class, 'edit'])->name('settings.edit');
        Route::put('profile', [SettingController::class, 'update'])->name('settings.update');
        Route::post('update-password', [SettingController::class, 'updatePassword'])->name('update-password');

        // Roles routes
        Route::get('roles', [RoleController::class, 'index'])->name('roles.index');
        Route::get('roles/create', [RoleController::class, 'create'])->name('roles.create');
        Route::post('roles', [RoleController::class, 'store'])->name('roles.store');
        Route::get('roles/{id}/edit', [RoleController::class, 'edit'])->name('roles.edit')->where('id', '[0-9]+');
        Route::get('roles/{id}', [RoleController::class, 'show'])->name('roles.show')->where('id', '[0-9]+');
        Route::put('roles/{id}', [RoleController::class, 'update'])->name('roles.update')->where('id', '[0-9]+');
        Route::delete('roles/{id}', [RoleController::class, 'destroy'])->name('roles.destroy')->where('id', '[0-9]+');
        Route::get('role/{id}/managers', [RoleController::class, 'managers'])->name('roles.managers');

        // Managers routes
        Route::get('managers', [ManagerController::class, 'index'])->name('managers.index');
        Route::controller(ManagerController::class)->prefix('managers')->name('managers.')->group(function () {
            Route::get('/create', 'create')->name('create');
            Route::get('/{role}/create', 'createWithRole')->name('createWithRole');
            Route::get('/export', 'export')->name('export');
            Route::post('/', 'store')->name('store');
            Route::patch('/{manager}/toggle', 'toggle')->name('toggle');
            Route::get('/{manager}/password/edit', 'editPassword')->name('password.edit');
            Route::put('/{manager}/password', 'updatePassword')->name('password.update');
            Route::get('/{manager}', 'show')->name('show');
            Route::get('/{manager}/edit', 'edit')->name('edit');
            Route::put('/{manager}', 'update')->name('update');
            Route::delete('/{manager}', 'destroy')->name('destroy');
        });
    });
});
