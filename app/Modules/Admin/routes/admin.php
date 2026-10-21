<?php

use App\Support\Routing\PortalRoutes;
use Illuminate\Support\Facades\Route;

PortalRoutes::admin(function (): void {
Route::view('/dashboard', 'admin.dashboard')
    ->name('dashboard');});