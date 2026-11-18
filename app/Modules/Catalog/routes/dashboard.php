<?php

use App\Modules\Catalog\Http\Controllers\TenantDashboardController;
use App\Support\Routing\PortalRoutes;
use Illuminate\Support\Facades\Route;

PortalRoutes::tenant(function (): void {
    Route::get('/dashboard', [TenantDashboardController::class, 'index'])
        ->name('dashboard');
});