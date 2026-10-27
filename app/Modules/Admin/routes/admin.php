<?php

use App\Modules\Admin\Http\Controllers\AdminBankAccountController;
use App\Modules\Admin\Http\Controllers\AdminCommissionController;
use App\Modules\Admin\Http\Controllers\AdminGateController;
use App\Modules\Admin\Http\Controllers\AdminTenantController;
use App\Modules\Admin\Http\Controllers\AdminTenantRoleController;
use App\Modules\Admin\Http\Controllers\AdminTenantStatusController;
use App\Support\Routing\PortalRoutes;
use Illuminate\Support\Facades\Route;

PortalRoutes::admin(function (): void {
    
    // 1. Route bawaan dari Modul 2
    Route::view('/dashboard', 'admin.dashboard')->name('dashboard');

    // 2. Route Manajemen Tenant (Modul 5)
    Route::get('/tenants', [AdminTenantController::class, 'index'])->name('tenants.index');
    Route::get('/tenants/create', [AdminTenantController::class, 'create'])->name('tenants.create');
    Route::post('/tenants', [AdminTenantController::class, 'store'])->name('tenants.store');
    Route::get('/tenants/{tenant}', [AdminTenantController::class, 'edit'])->name('tenants.edit');

    // 3. Route Ubah Status (Aktif/Nonaktif)
    Route::post('/tenants/{tenant}/status', AdminTenantStatusController::class)->name('tenants.status');

    // 4. Route Role / Operator
    Route::post('/tenants/{tenant}/roles', [AdminTenantRoleController::class, 'store'])->name('tenants.roles.store');
    Route::delete('/tenants/{tenant}/roles/{user}', [AdminTenantRoleController::class, 'destroy'])->name('tenants.roles.destroy');

    // 5. Route Komisi
    Route::post('/tenants/{tenant}/commission', [AdminCommissionController::class, 'store'])->name('tenants.commission.store');

    // 6. Route Rekening Bank
    Route::post('/tenants/{tenant}/bank', [AdminBankAccountController::class, 'store'])->name('tenants.bank.store');
    Route::post('/tenants/{tenant}/bank/{account}/verify', [AdminBankAccountController::class, 'verify'])->name('tenants.bank.verify');
    Route::post('/tenants/{tenant}/bank/{account}/primary', [AdminBankAccountController::class, 'makePrimary'])->name('tenants.bank.primary');

    // 7. Route Manajemen Gerbang & QR (Modul 6)
    Route::get('/gates', [AdminGateController::class, 'index'])->name('gates.index');
    Route::post('/gates', [AdminGateController::class, 'store'])->name('gates.store');
    Route::post('/gates/{gate}/rotate', [AdminGateController::class, 'rotate'])->name('gates.rotate');
    Route::get('/gates/{gate}/qr', [AdminGateController::class, 'qr'])->name('gates.qr');
    
});