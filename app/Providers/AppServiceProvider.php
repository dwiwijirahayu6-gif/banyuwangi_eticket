<?php

namespace App\Providers;

use App\Support\Tenancy\TenantContext;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        // Registrasi konteks tenant terpusat
        $this->app->scoped(TenantContext::class, function () {
            return new TenantContext;
        });
    }

    public function boot(): void
    {
        //
    }
}
