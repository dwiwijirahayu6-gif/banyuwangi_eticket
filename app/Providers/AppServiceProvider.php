<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Support\Tenancy\TenantContext;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        // Registrasi konteks tenant terpusat
        $this->app->scoped(TenantContext::class, function () {
            return new TenantContext();
        });
    }

    public function boot(): void
    {
        //
    }
}