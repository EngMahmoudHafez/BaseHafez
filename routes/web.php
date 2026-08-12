<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    if (auth('manager')->check()) {
        return redirect()->route('dashboard.home');
    }

    return redirect()->route('login');
});
