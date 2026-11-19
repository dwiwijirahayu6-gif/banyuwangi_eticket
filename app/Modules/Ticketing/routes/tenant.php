<?php

use App\Modules\Ticketing\Http\Controllers\TenantReservationController;
use App\Modules\Ticketing\Http\Controllers\TenantTicketValidationController;
use App\Support\Routing\PortalRoutes;
use Illuminate\Support\Facades\Route;

PortalRoutes::tenant(function (): void {
    // Manajemen Reservasi (FR-TEN-05, UC-16)
    Route::get('/reservasi', [TenantReservationController::class, 'index'])->name('reservasi');
    Route::get('/reservasi/{booking}', [TenantReservationController::class, 'show'])->name('reservasi.show');

    // Validasi Tiket Digital / Check-in Manual (FR-TEN-03, FR-TEN-06)
    Route::get('/validasi-tiket', [TenantTicketValidationController::class, 'index'])->name('validasi-tiket');
    Route::post('/validasi-tiket/verifikasi', [TenantTicketValidationController::class, 'verify'])->name('validasi-tiket.verify');
});
