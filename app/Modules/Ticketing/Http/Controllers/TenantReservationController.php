<?php

namespace App\Modules\Ticketing\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Tenant;
use App\Models\Ticket;
use Illuminate\Http\Request;
use Illuminate\View\View;

class TenantReservationController extends Controller
{
    public function index(Request $request, Tenant $tenant): View
    {
        $search = trim((string) $request->get('search'));
        $visitDate = $request->get('visit_date');
        $paymentStatus = $request->get('status');
        $ticketStatus = $request->get('ticket_status');

        $query = Booking::query()
            ->whereHas('items', fn ($q) => $q->where('tenant_id', $tenant->id))
            ->with(['items' => function ($q) use ($tenant) {
                $q->where('tenant_id', $tenant->id)->with('ticketType');
            }, 'tickets' => function ($q) use ($tenant) {
                $q->where('tenant_id', $tenant->id);
            }]);

        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('booking_code', 'like', "%{$search}%")
                    ->orWhere('customer_name', 'like', "%{$search}%")
                    ->orWhere('customer_email', 'like', "%{$search}%")
                    ->orWhere('customer_phone', 'like', "%{$search}%");
            });
        }

        if ($visitDate) {
            $query->whereDate('visit_date', $visitDate);
        }

        if ($paymentStatus) {
            $query->where('status', $paymentStatus);
        }

        if ($ticketStatus) {
            if ($ticketStatus === 'used') {
                $query->whereHas('tickets', fn ($q) => $q->where('tenant_id', $tenant->id)->where('status', 'checked_in'));
            } elseif ($ticketStatus === 'unused') {
                $query->whereDoesntHave('tickets', fn ($q) => $q->where('tenant_id', $tenant->id)->where('status', 'checked_in'));
            }
        }

        $bookings = $query->latest('id')->paginate(15)->withQueryString();

        return view('ticketing::tenant.reservations.index', [
            'tenant' => $tenant,
            'bookings' => $bookings,
            'search' => $search,
            'visitDate' => $visitDate,
            'paymentStatus' => $paymentStatus,
            'ticketStatus' => $ticketStatus,
        ]);
    }

    public function show(Tenant $tenant, Booking $booking): View
    {
        // Isolasi ketat: pastikan booking memiliki item untuk tenant ini
        $hasItem = $booking->items()->where('tenant_id', $tenant->id)->exists();
        abort_unless($hasItem, 404);

        // Hanya load items dan tickets milik tenant ini
        $booking->load([
            'items' => fn ($q) => $q->where('tenant_id', $tenant->id)->with('ticketType'),
            'tickets' => fn ($q) => $q->where('tenant_id', $tenant->id)->with('verifiedBy'),
            'payment',
        ]);

        $tenantSubtotal = (float) $booking->items->sum('subtotal');

        return view('ticketing::tenant.reservations.show', [
            'tenant' => $tenant,
            'booking' => $booking,
            'tenantSubtotal' => $tenantSubtotal,
        ]);
    }
}
