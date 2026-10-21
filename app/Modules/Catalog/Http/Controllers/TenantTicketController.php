<?php

namespace App\Modules\Catalog\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Tenant;
use App\Models\TicketType;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class TenantTicketController extends Controller
{
    use AuthorizesRequests;

    public function index(Tenant $tenant): JsonResponse
    {
        // Global scope memfilter ke tenant aktif dari TenantContext
        $tickets = TicketType::query()->orderBy('name')->get();
        return response()->json(['tenant' => $tenant->code, 'tickets' => $tickets]);    }

    public function show(Tenant $tenant, TicketType $ticketType): JsonResponse
    {
        $this->authorize('view', $ticketType);
        return response()->json($ticketType);
    }

    public function update(Request $request, Tenant $tenant, TicketType $ticketType): JsonResponse
    {
        $this->authorize('update', $ticketType);
        $data = $request->validate(['is_available' => ['required', 'boolean']]);
        $ticketType->update(['is_available' => $data['is_available']]);

        return response()->json(['ok' => true, 'is_available' => $ticketType->is_available]);
    }
}