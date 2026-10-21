<?php

use App\Support\Routing\PortalRoutes;
use Illuminate\Support\Facades\Route;

PortalRoutes::admin(function (): void {
    Route::get('/dashboard', fn () => response('Admin Dashboard'))
        ->name('dashboard');
});