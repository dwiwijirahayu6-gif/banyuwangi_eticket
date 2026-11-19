<?php

namespace App\Modules\Catalog\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\BookingItem;
use App\Models\Tenant;
use App\Models\Ticket;
use App\Services\LedgerService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\View\View;

class TenantDashboardController extends Controller
{
    public function __construct(private LedgerService $ledgerService)
    {
    }

    public function index(Request $request, Tenant $tenant): View
    {
        $destination = $tenant->destinations()->first();

        // Reservasi milik tenant
        $bookingsBaseQuery = Booking::whereHas('items', fn ($q) => $q->where('tenant_id', $tenant->id));

        $totalReservations = (clone $bookingsBaseQuery)->count();
        $pendingReservations = (clone $bookingsBaseQuery)->where('status', 'pending_payment')->count();
        $paidReservations = (clone $bookingsBaseQuery)->whereIn('status', ['paid', 'completed'])->count();

        // Tiket
        $ticketsBaseQuery = Ticket::where('tenant_id', $tenant->id);
        $totalTicketsSold = (clone $ticketsBaseQuery)
            ->whereHas('booking', fn ($q) => $q->whereIn('status', ['paid', 'completed']))
            ->count();
        $totalVisitors = (clone $ticketsBaseQuery)->where('status', 'checked_in')->count();
        $usedTicketsCount = $totalVisitors;

        // Keuangan
        $grossRevenue = (float) BookingItem::where('booking_items.tenant_id', $tenant->id)
            ->whereHas('booking', fn ($q) => $q->whereIn('status', ['paid', 'completed']))
            ->sum('subtotal');

        $commissionRate = $this->ledgerService->getCommissionRateForTenant($tenant->id);
        $commissionAmount = round($grossRevenue * ($commissionRate / 100), 4);
        $netRevenue = max(0, round($grossRevenue - $commissionAmount, 4));

        $balanceInfo = $this->ledgerService->getBalanceInfo($tenant->id);

        // Aktivitas terbaru: 5 reservasi terbaru
        $recentBookings = Booking::with(['items' => fn ($q) => $q->where('tenant_id', $tenant->id)->with('ticketType')])
            ->whereHas('items', fn ($q) => $q->where('tenant_id', $tenant->id))
            ->latest('id')
            ->limit(5)
            ->get();

        // 5 validasi tiket terakhir
        $recentValidations = Ticket::with(['booking', 'bookingItem.ticketType', 'verifiedBy'])
            ->where('tenant_id', $tenant->id)
            ->where('status', 'checked_in')
            ->latest('updated_at')
            ->limit(5)
            ->get();

        // Statistik 7 hari terakhir untuk visualisasi
        $chartData = [];
        for ($i = 6; $i >= 0; $i--) {
            $date = Carbon::today('Asia/Jakarta')->subDays($i)->toDateString();
            $label = Carbon::today('Asia/Jakarta')->subDays($i)->format('d M');

            $dailyGross = (float) BookingItem::where('booking_items.tenant_id', $tenant->id)
                ->whereHas('booking', fn ($q) => $q->whereIn('status', ['paid', 'completed'])->whereDate('created_at', $date))
                ->sum('subtotal');

            $dailyTickets = (int) BookingItem::where('booking_items.tenant_id', $tenant->id)
                ->whereHas('booking', fn ($q) => $q->whereIn('status', ['paid', 'completed'])->whereDate('created_at', $date))
                ->sum('quantity');

            $dailyVisitors = Ticket::where('tenant_id', $tenant->id)
                ->whereDate('visit_date', $date)
                ->where('status', 'checked_in')
                ->count();

            $chartData[] = [
                'date' => $label,
                'gross' => $dailyGross,
                'tickets' => $dailyTickets,
                'visitors' => $dailyVisitors,
            ];
        }

        return view('tenant.dashboard', [
            'tenant' => $tenant,
            'destination' => $destination,
            'totalTicketsSold' => $totalTicketsSold,
            'totalVisitors' => $totalVisitors,
            'totalReservations' => $totalReservations,
            'pendingReservations' => $pendingReservations,
            'paidReservations' => $paidReservations,
            'usedTicketsCount' => $usedTicketsCount,
            'netRevenue' => $netRevenue,
            'availableBalance' => $balanceInfo['available_balance'],
            'recentBookings' => $recentBookings,
            'recentValidations' => $recentValidations,
            'chartData' => $chartData,
        ]);
    }
}
