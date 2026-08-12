<?php

use App\Modules\Structure\Http\Controllers\Dashboard\ContactMessageController;
use App\Modules\Structure\Http\Controllers\Dashboard\SectionController;
use App\Modules\Structure\Support\SectionRegistry;
use Illuminate\Support\Facades\Route;
use Mcamara\LaravelLocalization\Facades\LaravelLocalization;

Route::group([
    'prefix' => LaravelLocalization::setLocale() . '/dashboard',
    'middleware' => ['web', 'localeSessionRedirect', 'localizationRedirect', 'localeViewPath'],
], function () {
    Route::middleware(['auth:manager', 'manager.active'])->group(function (): void {

        // One controller renders and persists every content section. The section
        // is bound as the `section` route default; adding a section to the
        // registry publishes its index/store routes automatically.
        Route::prefix('/structures')->name('dashboard.structures.')->group(function (): void {
            foreach (SectionRegistry::routableKeys() as $key) {
                Route::get($key, [SectionController::class, 'index'])
                    ->defaults('section', $key)
                    ->middleware('permission:structures-read,guard:manager')
                    ->name("{$key}.index");

                Route::post($key, [SectionController::class, 'store'])
                    ->defaults('section', $key)
                    ->middleware('permission:structures-update,guard:manager')
                    ->name("{$key}.store");
            }
        });

        Route::prefix('/contact-messages')
            ->name('dashboard.contact-messages.')
            ->group(function (): void {
                Route::get('/', [ContactMessageController::class, 'index'])->name('index');
                Route::get('/{contactMessage}', [ContactMessageController::class, 'show'])->name('show');
                Route::patch('/{contactMessage}/toggle-read', [ContactMessageController::class, 'toggleRead'])->name('toggle-read');
                Route::delete('/{contactMessage}', [ContactMessageController::class, 'destroy'])->name('destroy');
            });
    });
});
