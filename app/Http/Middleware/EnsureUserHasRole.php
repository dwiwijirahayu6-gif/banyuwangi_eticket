<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

final class EnsureUserHasRole
{
    
public function handle(Request $request, Closure $next, string $role): Response
{
    $user = $request->user();

    $hasRole = $role === 'admin'
        ? in_array($user?->role, ['admin', 'super_admin'], true)
        : $user?->role === $role;

    if (! $user || ! $hasRole || $user->status !== 'active') {
        abort(403);
    }

    return $next($request);
}

}