<?php

namespace App\Modules\Ordering;

use App\Modules\ModuleServiceProvider;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;

final class OrderingServiceProvider extends ModuleServiceProvider
{
    public function register(): void
    {
        //
    }

    protected function moduleAlias(): string
    {
        return 'ordering';
    }

    public function boot(): void
    {
        parent::boot();

        // Rate limiter untuk scan QR (maksimal 30 kali per menit per IP)
        // Ini mencegah orang menebak-nebak token QR secara paksa.
        RateLimiter::for('qr-scan', function (Request $request) {
            return Limit::perMinute(30)->by($request->ip() ?? 'unknown');
        });
    }
}