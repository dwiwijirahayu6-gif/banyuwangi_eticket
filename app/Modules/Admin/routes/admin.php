<?php

use App\Modules\Admin\Http\Controllers\AdminTenantController;
use App\Support\Routing\PortalRoutes;
use Illuminate\Support\Facades\Route;

PortalRoutes::admin(function (): void {
    
    // 1. Route bawaan dari Modul 2
    Route::view('/dashboard', 'admin.dashboard')->name('dashboard');

    // 2. Route baru untuk Modul 5 (Manajemen Tenant/Destinasi)
    Route::get('/tenants', [AdminTenantController::class, 'index'])->name('tenants.index');
    Route::get('/tenants/create', [AdminTenantController::class, 'create'])->name('tenants.create');
    Route::post('/tenants', [AdminTenantController::class, 'store'])->name('tenants.store');
    Route::get('/tenants/{tenant}', [AdminTenantController::class, 'edit'])->name('tenants.edit');
    Route::post('/tenants/{tenant}/roles', [\App\Modules\Admin\Http\Controllers\AdminTenantRoleController::class, 'store'])->name('tenants.roles.store');
    Route::delete('/tenants/{tenant}/roles/{user}', [\App\Modules\Admin\Http\Controllers\AdminTenantRoleController::class, 'destroy'])->name('tenants.roles.destroy');
    Route::post('/tenants/{tenant}/commission', [\App\Modules\Admin\Http\Controllers\AdminCommissionController::class, 'store'])->name('tenants.commission.store');
    Route::post('/tenants/{tenant}/bank', [\App\Modules\Admin\Http\Controllers\AdminBankAccountController::class, 'store'])->name('tenants.bank.store');
    Route::post('/tenants/{tenant}/bank/{account}/verify', [\App\Modules\Admin\Http\Controllers\AdminBankAccountController::class, 'verify'])->name('tenants.bank.verify');
    Route::post('/tenants/{tenant}/bank/{account}/primary', [\App\Modules\Admin\Http\Controllers\AdminBankAccountController::class, 'makePrimary'])->name('tenants.bank.primary');
    });