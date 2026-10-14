<?php

use Illuminate\Support\Facades\Route;

/**
 * Konteks PELANGGAN (publik). Prefix: wisata/{destination}, name: customer.*
 */
Route::get('/', function (string $destination) {
    return view('customer.home', ['destination' => $destination]);
})->name('home');