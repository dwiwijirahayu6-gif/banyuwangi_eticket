<?php

use App\Http\Controllers\BookingController;
use App\Http\Controllers\CheckoutController;
use App\Http\Controllers\DestinationController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\PaymentController;
use Illuminate\Support\Facades\Route;

// Rute Halaman Utama Katalog Wisata Banyuwangi
Route::get('/', [HomeController::class, 'index'])->name('home');

// Informasi tiket & detail destinasi
Route::get('/informasi-tiket', [DestinationController::class, 'index'])->name('tickets.info');
Route::get('/destinasi/{destination:slug}', [DestinationController::class, 'show'])->name('destinations.show');

// Checkout
Route::post('/destinasi/{destination:slug}/checkout', [CheckoutController::class, 'store'])->name('checkout.store');

// Pembayaran
Route::get('/pesanan/{booking:booking_code}/bayar', [PaymentController::class, 'show'])->name('payment.show');
Route::post('/pesanan/{booking:booking_code}/bayar/simulasi', [PaymentController::class, 'simulate'])->name('payment.simulate');

// Cek pesanan & pembatalan tiket
Route::get('/pembatalan', [BookingController::class, 'lookupForm'])->name('bookings.lookup');
Route::post('/pembatalan', [BookingController::class, 'lookup'])->middleware('throttle:10,1')->name('bookings.lookup.submit');
Route::get('/pesanan/{booking:booking_code}', [BookingController::class, 'show'])->name('bookings.show');
Route::post('/pesanan/{booking:booking_code}/batal', [BookingController::class, 'cancel'])->name('bookings.cancel');

// Login sederhana (Modul 2 – interim, akan diganti dengan Breeze/Fortify)
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;

Route::get('/login', function () {
    return view('auth.login');
})->name('login');

Route::post('/login', function (Request $request) {
    $credentials = $request->validate([
        'email' => ['required', 'email'],
        'password' => ['required'],
    ]);

    if (Auth::attempt($credentials)) {
        $request->session()->regenerate();
        return redirect()->intended('/');
    }

    return back()->withErrors(['email' => 'Email atau password salah.']);
});

Route::post('/logout', function (Request $request) {
    Auth::logout();
    $request->session()->invalidate();
    $request->session()->regenerateToken();
    return redirect('/');
})->name('logout');