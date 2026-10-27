<?php

use App\Modules\Ordering\Http\Controllers\ResolveGateQrController;
use App\Support\Routing\PortalRoutes;
use Illuminate\Support\Facades\Route;

// Menggunakan PortalRoutes::web() agar tidak terkena prefix portal manapun
PortalRoutes::web(function (): void {
    Route::get('/q/{token}', ResolveGateQrController::class)
        ->middleware('throttle:qr-scan')
        ->name('customer.scan');
});