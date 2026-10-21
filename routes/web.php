<?php

use App\Http\Controllers\BookingController;
use App\Http\Controllers\CheckoutController;
use App\Http\Controllers\DestinationController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\PaymentController;
use App\Models\UserTenantRole;
use App\Support\Routing\PortalRoutes;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

// --------------------------------------------------------------------------
// Portal Pelanggan / Publik (Katalog Wisata Banyuwangi)
// --------------------------------------------------------------------------
Route::get('/', [HomeController::class, 'index'])->name('home');

// Alias untuk test suite yang memanggil route('customer.home')
PortalRoutes::customer(function (): void {
    Route::get('/', [HomeController::class, 'index'])->name('home');
});

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

// --------------------------------------------------------------------------
// Dashboard Router & Fallback (Multitenant & Multi-role)
// --------------------------------------------------------------------------
Route::middleware(['auth', 'verified'])->get('/dashboard', function () {
    $user = Auth::user();

    if ($user && $user->role === 'super_admin') {
        return redirect()->route('admin.dashboard');
    }

    if ($user && $user->role === 'tenant') {
        $tenantRole = UserTenantRole::where('user_id', $user->id)->first();
        if ($tenantRole && $tenantRole->tenant) {
            return redirect()->route('tenant.dashboard', ['tenant' => $tenantRole->tenant->slug]);
        }
    }

    return view('dashboard');
})->name('dashboard');

// --------------------------------------------------------------------------
// Autentikasi User (Login, Register, Logout)
// --------------------------------------------------------------------------
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
        $user = Auth::user();

        // Redirect otomatis sesuai role setelah login
        if ($user && $user->role === 'admin') {
            return redirect()->intended(route('admin.dashboard'));
        }

        if ($user && $user->role === 'tenant') {
            $tenantRole = UserTenantRole::where('user_id', $user->id)->first();
            if ($tenantRole && $tenantRole->tenant) {
                return redirect()->intended(route('tenant.dashboard', ['tenant' => $tenantRole->tenant->slug]));
            }
        }

        return redirect()->intended('/');
    }

    return back()->withErrors(['email' => 'Email atau password salah.']);
});

Route::get('/register', function () {
    return view('auth.register');
})->name('register');

Route::post('/logout', function (Request $request) {
    Auth::logout();
    $request->session()->invalidate();
    $request->session()->regenerateToken();
    return redirect('/');
})->name('logout');