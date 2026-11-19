<?php

namespace App\Modules\Ticketing\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Tenant;
use App\Models\Ticket;
use App\Modules\Ticketing\Services\TicketValidationService;
use DomainException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class TenantTicketValidationController extends Controller
{
    public function __construct(private TicketValidationService $validationService) {}

    public function index(Request $request, Tenant $tenant): View
    {
        $code = trim((string) $request->get('code'));
        $tickets = collect();

        if ($code !== '') {
            $tickets = $this->validationService->search($tenant->id, $code);
        }

        // Histori verifikasi terbaru hari ini
        $recentValidations = Ticket::with(['booking', 'bookingItem.ticketType', 'verifiedBy'])
            ->where('tenant_id', $tenant->id)
            ->where('status', 'checked_in')
            ->latest('updated_at')
            ->limit(10)
            ->get();

        return view('ticketing::tenant.validation.index', [
            'tenant' => $tenant,
            'code' => $code,
            'tickets' => $tickets,
            'recentValidations' => $recentValidations,
        ]);
    }

    public function verify(Request $request, Tenant $tenant): RedirectResponse
    {
        $data = $request->validate([
            'ticket_id' => ['required', 'integer'],
            'search_code' => ['nullable', 'string'],
        ]);

        try {
            $ticket = $this->validationService->verifyEntrance(
                $tenant->id,
                (int) $data['ticket_id'],
                (int) auth()->id()
            );

            return redirect()->route('tenant.validasi-tiket', [
                'tenant' => $tenant->code,
                'code' => $data['search_code'] ?? $ticket->ticket_code,
            ])->with('success', "Tiket {$ticket->ticket_code} ({$ticket->bookingItem->ticketType->name}) berhasil diverifikasi masuk!");
        } catch (DomainException $e) {
            return redirect()->route('tenant.validasi-tiket', [
                'tenant' => $tenant->code,
                'code' => $data['search_code'] ?? '',
            ])->with('error', $e->getMessage());
        }
    }
}
