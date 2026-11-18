<?php

use App\Models\Tenant;
use App\Modules\Catalog\Http\Controllers\TenantDestinationController;
use App\Modules\Catalog\Http\Controllers\TenantTicketController;
use App\Modules\Catalog\Http\Controllers\TenantVisitSlotController;
use App\Support\Routing\PortalRoutes;
use Illuminate\Support\Facades\Route;

/**
 * Route portal tenant milik modul Catalog.
 * {ticketType} ter-scope di bawah {tenant} (scopeBindings dari PortalRoutes)
 * dan otomatis difilter oleh global scope TenantContext.
 */
PortalRoutes::tenant(function (): void {
    // Profil Destinasi Wisata (FR-TEN-01 & UC-10)
    Route::get('/destinasi', [TenantDestinationController::class, 'edit'])->name('destinasi');
    Route::put('/destinasi', [TenantDestinationController::class, 'update'])->name('destinasi.update');
    Route::delete('/destinasi/gallery/{index}', [TenantDestinationController::class, 'deleteGalleryImage'])->name('destinasi.gallery.delete');

    // Manajemen Tiket (Modul 7 - FR-TEN-02, FR-TEN-03, UC-11)
    Route::get('/ticket-types', [TenantTicketController::class, 'index'])->name('ticket-types.index');
    Route::get('/ticket-types/{ticketType}', [TenantTicketController::class, 'show'])->name('ticket-types.show');
    Route::patch('/ticket-types/{ticketType}', [TenantTicketController::class, 'update'])->name('ticket-types.update');
    Route::get('/ticket-manager', fn (Tenant $tenant) => view('catalog::tenant.ticket-manager', ['tenant' => $tenant]))
        ->name('ticket-manager');
    Route::get('/menu-manager', fn (Tenant $tenant) => view('catalog::tenant.ticket-manager', ['tenant' => $tenant]))
        ->name('menu-manager');

    // Jadwal dan Kuota Harian (FR-TEN-02, UC-11, FR-TEN-04)
    Route::get('/jadwal-kuota', [TenantVisitSlotController::class, 'index'])->name('jadwal-kuota');
    Route::post('/jadwal-kuota', [TenantVisitSlotController::class, 'store'])->name('jadwal-kuota.store');
    Route::put('/jadwal-kuota/{visitSlot}', [TenantVisitSlotController::class, 'update'])->name('jadwal-kuota.update');
    Route::post('/jadwal-kuota/{visitSlot}/toggle', [TenantVisitSlotController::class, 'toggle'])->name('jadwal-kuota.toggle');

    // Pengaturan Akun Operator (Profil Tenant)
    Route::get('/profil', [\App\Http\Controllers\TenantProfileController::class, 'index'])->name('profil');
    Route::put('/profil', [\App\Http\Controllers\TenantProfileController::class, 'update'])->name('profil.update');
});
