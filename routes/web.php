<?php

use App\Http\Controllers\BookingController;
use App\Http\Controllers\CheckoutController;
use App\Http\Controllers\DashboardRedirectController;
use App\Http\Controllers\DestinationController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\PaymentController;
use App\Support\Auth\LoginLockout;
use App\Support\Routing\PortalRoutes;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use Illuminate\Validation\ValidationException;

// --------------------------------------------------------------------------
// 1. Portal Pelanggan / Publik (Katalog Wisata Banyuwangi)
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
// 2. Autentikasi User (Login, Register, Logout) - UC-19
// --------------------------------------------------------------------------
Route::middleware('guest')->group(function () {
    Route::get('/login', function () {
        return view('auth.login');
    })->name('login');

    Route::post('/login', function (Request $request) {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        $lockout = app(LoginLockout::class);
        $email = $credentials['email'];

        // Cek apakah akun terkunci (Tahap 8.2)
        if ($lockout->isLocked($email)) {
            throw ValidationException::withMessages([
                'email' => 'Akun terkunci sementara karena terlalu banyak percobaan gagal. Coba lagi dalam '.$lockout->minutesRemaining($email).' menit.',
            ]);
        }

        // Coba login
        if (Auth::attempt($credentials)) {
            $request->session()->regenerate();
            $lockout->clear($email); // Reset jika berhasil
            
            return redirect()->intended(route('dashboard'));
        }

        // Catat kegagalan jika password salah
        $lockout->recordFailure($email);

        return back()->withErrors(['email' => 'Surel atau kata sandi tidak sesuai.']);
    });

    Route::get('/register', function () {
        return view('auth.register');
    })->name('register');
});

Route::post('/logout', function (Request $request) {
    Auth::logout();
    $request->session()->invalidate();
    $request->session()->regenerateToken();
    return redirect('/');
})->name('logout');

// --------------------------------------------------------------------------
// 3. Dashboard Router (Tahap 8.3)
// --------------------------------------------------------------------------
// Ini menggantikan fungsi closure lama. Semua logika redirect per-role 
// sekarang ditangani oleh DashboardRedirectController.
Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('dashboard', DashboardRedirectController::class)->name('dashboard');
});