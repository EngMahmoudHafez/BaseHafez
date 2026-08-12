<?php

use App\Modules\Structure\Http\Controllers\Api\V1\StructurePageController;
use Illuminate\Support\Facades\Route;

/**
 * Structure Module - public content pages.
 * GET /web/v1/structures/{key}  (only sections marked `public` in the registry)
 */
Route::prefix('web/v1')
    ->middleware(['api'])
    ->group(function (): void {
        Route::get('structures/{key}', StructurePageController::class)
            ->where('key', '[a-z_]+')
            ->name('structures.page');
    });
