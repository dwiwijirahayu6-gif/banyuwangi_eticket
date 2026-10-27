<?php

namespace App\Support\Routing;

use Closure;
use Illuminate\Support\Facades\Route;

final class PortalRoutes
{
    /**
     * Route publik tanpa prefix portal (misal: scan QR /q/{token}).
     */
    public static function web(Closure|string $routes): void
    {
        Route::middleware('web')->group($routes);
    }

    /**
     * Portal pelanggan (publik, anonim).
     */
    public static function customer(Closure|string $routes): void
    {
        Route::middleware('web')
            ->prefix('wisata/{destination}')
            ->name('customer.')
            ->group($routes);
    }

    /**
     * Portal operator/pengelola wisata (internal).
     */
    public static function tenant(Closure|string $routes): void
    {
        // MENGGUNAKAN 'tenant' (SetTenantContext dari Modul 4), bukan 'role:tenant'
        Route::middleware(['web', 'auth', 'verified', 'tenant']) 
            ->prefix('tenant/{tenant}')
            ->name('tenant.')
            ->group($routes);
    }

    /**
     * Portal pengelola sistem (internal).
     */
    public static function admin(Closure|string $routes): void
    {
        Route::middleware(['web', 'auth', 'verified', 'role:admin'])
            ->prefix('admin')
            ->name('admin.')
            ->group($routes);
    }
}