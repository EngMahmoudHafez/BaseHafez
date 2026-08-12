<?php

use App\Modules\Notifications\Http\Controllers\Dashboard\NotificationController;
use Illuminate\Support\Facades\Route;
use Mcamara\LaravelLocalization\Facades\LaravelLocalization;

Route::group([
    'prefix' => LaravelLocalization::setLocale() . '/dashboard',
    'middleware' => ['web', 'localeSessionRedirect', 'localizationRedirect', 'localeViewPath', 'auth:manager', 'manager.active'],
], function (): void {
    Route::prefix('notifications')->name('dashboard.notifications.')->group(function (): void {
        Route::get('/', [NotificationController::class, 'index'])->name('index');
        Route::post('/broadcast', [NotificationController::class, 'broadcast'])->name('broadcast');
        Route::delete('/all', [NotificationController::class, 'deleteAll'])->name('delete-all');
        Route::get('/{notification}', [NotificationController::class, 'show'])->whereNumber('notification')->name('show');
        Route::delete('/{notification}', [NotificationController::class, 'destroy'])->whereNumber('notification')->name('destroy');
    });
});
