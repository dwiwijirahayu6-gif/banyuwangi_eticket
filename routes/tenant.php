<?php

use App\Models\Tenant;
use App\Support\Routing\PortalRoutes;
use Illuminate\Support\Facades\Route;

PortalRoutes::tenant(function (): void {
    Route::get('/dashboard', fn (Tenant $tenant) => response('Tenant Dashboard'))
        ->name('dashboard');
});