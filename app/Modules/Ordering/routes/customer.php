<?php

use App\Modules\Ordering\Http\Controllers\OrderStatusController;
use App\Support\Routing\PortalRoutes;
use Illuminate\Support\Facades\Route;

PortalRoutes::customer(function (): void {
    
    // Route dari Modul 6 (SEMENTARA DINONAKTIFKAN KARENA CONTROLLER BELUM ADA)
    // Route::get('/meja', \App\Modules\Ordering\Http\Controllers\CustomerWelcomeController::class)->name('welcome');
    
    // Halaman Checkout (Modul 9)
    Route::get('/checkout', function (string $destination) {
        return view('ordering::checkout', ['destination' => $destination]);
    })->name('checkout');
    
    // Halaman Status Pesanan (Modul 9) - Pastikan Controller ini sudah kamu buat
    // Route::get('/order', OrderStatusController::class)->name('order.show');
});