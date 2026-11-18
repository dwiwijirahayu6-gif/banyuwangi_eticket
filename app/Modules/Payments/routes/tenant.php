<?php

use App\Modules\Payments\Http\Controllers\TenantWithdrawalController;
use App\Support\Routing\PortalRoutes;
use Illuminate\Support\Facades\Route;

PortalRoutes::tenant(function (): void {
    // Manajemen Saldo dan Penarikan Dana (FR-TEN-05, UC-14)
    Route::get('/saldo-penarikan', [TenantWithdrawalController::class, 'index'])->name('saldo-penarikan');
    Route::post('/saldo-penarikan', [TenantWithdrawalController::class, 'store'])->name('saldo-penarikan.store');
});
