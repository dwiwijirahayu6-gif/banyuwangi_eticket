<?php

use App\Http\Middleware\EnsureUserHasRole;
use App\Http\Middleware\SetTenantContext;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'role' => EnsureUserHasRole::class,   // <-- Pastikan baris ini ada
            'tenant' => SetTenantContext::class, // <-- Pastikan baris ini ada
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();