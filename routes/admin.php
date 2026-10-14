<?php

use Illuminate\Support\Facades\Route;

/**
 * Konteks ADMIN SISTEM (internal). Prefix: admin, name: admin.*
 */
Route::get('/dashboard', function () {
    return view('admin.dashboard');
})->name('dashboard');