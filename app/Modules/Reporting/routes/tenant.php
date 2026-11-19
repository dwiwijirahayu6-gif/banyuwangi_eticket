<?php

use App\Modules\Reporting\Http\Controllers\TenantReportController;
use App\Support\Routing\PortalRoutes;
use Illuminate\Support\Facades\Route;

PortalRoutes::tenant(function (): void {
    // Laporan Penjualan & Pengunjung (FR-TEN-04, FR-TEN-07)
    Route::get('/laporan', [TenantReportController::class, 'index'])->name('laporan');

    // Ekspor Laporan (FR-TEN-08)
    Route::get('/laporan/export/pdf', [TenantReportController::class, 'exportPdf'])->name('laporan.export.pdf');
    Route::get('/laporan/export/excel', [TenantReportController::class, 'exportExcel'])->name('laporan.export.excel');
});
