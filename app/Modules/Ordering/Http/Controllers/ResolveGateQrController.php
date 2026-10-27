<?php

namespace App\Modules\Ordering\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Ordering\Services\ResolveGateScan;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

final class ResolveGateQrController extends Controller
{
    public function __construct(private ResolveGateScan $resolver) {}

    public function __invoke(Request $request, string $token): RedirectResponse
    {
        $result = $this->resolver->resolve($token);
        
        // Jika token tidak valid, tampilkan 404
        abort_if($result === null, 404);

        // Pasang cookie HttpOnly berisi token sesi
        $cookie = cookie(
            name: 'customer_session',
            value: $result['plain'],
            minutes: 240,
            path: '/',
            secure: $request->isSecure() || app()->isProduction(),
            httpOnly: true,
            sameSite: 'lax',
        );

        // Arahkan ke halaman destinasi
        return redirect()
            ->route('destinations.show', $result['destination']->slug)
            ->withCookie($cookie);
    }
}