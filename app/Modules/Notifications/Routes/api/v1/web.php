<?php

use App\Modules\Notifications\Http\Controllers\Api\V1\NotificationController;
use Illuminate\Support\Facades\Route;

Route::prefix('web/v1/notifications')
    ->middleware('auth:api')
    ->name('web.v1.notifications.')
    ->group(function (): void {
        Route::get('/', [NotificationController::class, 'index'])->name('index');
        Route::get('/unread', [NotificationController::class, 'unread'])->name('unread');
        Route::get('/unread-count', [NotificationController::class, 'unreadCount'])->name('unread-count');
        Route::post('/mark-all-read', [NotificationController::class, 'markAllAsRead'])->name('mark-all-read');
        Route::delete('/read', [NotificationController::class, 'deleteRead'])->name('delete-read');
        Route::get('/{id}', [NotificationController::class, 'show'])->whereNumber('id')->name('show');
        Route::delete('/{id}', [NotificationController::class, 'destroy'])->whereNumber('id')->name('destroy');
        Route::post('/{id}/read', [NotificationController::class, 'markAsRead'])->whereNumber('id')->name('mark-read');
    });
