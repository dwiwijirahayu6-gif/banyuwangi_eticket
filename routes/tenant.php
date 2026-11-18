<?php

use App\Http\Controllers\TenantProfileController;
use App\Modules\Catalog\Http\Controllers\TenantDashboardController;
use App\Support\Routing\PortalRoutes;
use Illuminate\Support\Facades\Route;

PortalRoutes::tenant(function (): void {
    Route::get('/dashboard', [TenantDashboardController::class, 'index'])
        ->name('dashboard');

    Route::get('/profil', [TenantProfileController::class, 'index'])
        ->name('profil');

    Route::put('/profil', [TenantProfileController::class, 'update'])
        ->name('profil.update');
});
