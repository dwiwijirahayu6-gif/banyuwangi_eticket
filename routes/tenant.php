<?php

use Illuminate\Support\Facades\Route;

/**
 * Konteks PENGELOLA WISATA (internal). Prefix: tenant/{tenant}, name: tenant.*
 */
Route::get('/dashboard', function (string $tenant) {
    return view('tenant.dashboard', ['tenant' => $tenant]);
})->name('dashboard');