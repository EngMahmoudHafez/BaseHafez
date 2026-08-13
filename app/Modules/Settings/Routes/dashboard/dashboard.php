<?php

use App\Modules\Settings\Http\Controllers\Dashboard\SettingController;
use Illuminate\Support\Facades\Route;
use Mcamara\LaravelLocalization\Facades\LaravelLocalization;

Route::group([
    'prefix' => LaravelLocalization::setLocale() . '/dashboard',
    'middleware' => ['web', 'localeSessionRedirect', 'localizationRedirect', 'localeViewPath', 'auth:manager', 'manager.active'],
], function (): void {
    Route::prefix('settings')->name('dashboard.settings.')->group(function (): void {
        Route::get('/', [SettingController::class, 'index'])->name('index');
        Route::get('/{setting}/edit', [SettingController::class, 'edit'])->whereNumber('setting')->name('edit');
        Route::put('/{setting}', [SettingController::class, 'update'])->whereNumber('setting')->name('update');
    });
});
