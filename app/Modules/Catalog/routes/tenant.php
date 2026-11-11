<?php

use App\Models\Tenant;
use App\Modules\Catalog\Http\Controllers\TenantTicketController;
use App\Support\Routing\PortalRoutes;
use Illuminate\Support\Facades\Route;

/**
 * Route portal tenant milik modul Catalog.
 * {ticketType} ter-scope di bawah {tenant} (scopeBindings dari PortalRoutes)
 * dan otomatis difilter oleh global scope TenantContext.
 */
PortalRoutes::tenant(function (): void {
    Route::get('/ticket-types', [TenantTicketController::class, 'index'])->name('ticket-types.index');
    Route::get('/ticket-types/{ticketType}', [TenantTicketController::class, 'show'])->name('ticket-types.show');
    Route::patch('/ticket-types/{ticketType}', [TenantTicketController::class, 'update'])->name('ticket-types.update');
    Route::get('/ticket-manager', fn (Tenant $tenant) => view('catalog::tenant.ticket-manager', ['tenant' => $tenant]))
        ->name('ticket-manager');
    Route::get('/menu-manager', fn (Tenant $tenant) => view('catalog::tenant.ticket-manager', ['tenant' => $tenant]))
        ->name('menu-manager');
});
