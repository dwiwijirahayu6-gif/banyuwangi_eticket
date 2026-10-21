<?php

namespace App\Support\Routing;

use Closure;
use Illuminate\Support\Facades\Route;

/**
 * Definisi TUNGGAL grup route per portal (prefix + name + middleware).
 * Konteks: Sistem Pemesanan Tiket Wisata Banyuwangi.
 */
final class PortalRoutes
{
    /**
     * Portal pelanggan (publik, anonim). Prefix: wisata/{destination}
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
     * Modul 04: Memakai middleware 'tenant' (SetTenantContext), {tenant:slug}, dan scopeBindings.
     */
    public static function tenant(Closure|string $routes): void
    {
        Route::middleware(['web', 'auth', 'verified', 'tenant'])
            ->prefix('tenant/{tenant:code}')
            ->name('tenant.')
            ->scopeBindings()
            ->group($routes);
    }

    /**
     * Portal pengelola sistem (internal). Prefix: admin
     */
    public static function admin(Closure|string $routes): void
    {
        Route::middleware(['web', 'auth', 'verified', 'role:admin'])
            ->prefix('admin')
            ->name('admin.')
            ->group($routes);
    }
}