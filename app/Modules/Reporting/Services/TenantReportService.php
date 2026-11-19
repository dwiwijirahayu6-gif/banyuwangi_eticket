<?php

namespace App\Modules\Reporting\Services;

use App\Models\Booking;
use App\Models\BookingItem;
use App\Models\LedgerEntry;
use App\Models\Ticket;
use App\Services\LedgerService;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class TenantReportService
{
    public function __construct(private LedgerService $ledgerService) {}

    /**
     * Dapatkan ringkasan laporan operasional & keuangan tenant berdasarkan filter tanggal.
     *
     * @return array{
     *     period_label: string,
     *     from_date: ?string,
     *     to_date: ?string,
     *     total_tickets_sold: int,
     *     total_visitors: int,
     *     total_bookings: int,
     *     total_paid_bookings: int,
     *     total_used_tickets: int,
     *     gross_revenue: float,
     *     platform_commission: float,
     *     net_revenue: float,
     *     available_balance: float,
     *     daily_summary: Collection
     * }
     */
    public function getReport(int $tenantId, ?string $period = 'this_month', ?string $fromDate = null, ?string $toDate = null): array
    {
        [$start, $end, $periodLabel] = $this->resolveDateRange($period, $fromDate, $toDate);

        // Query booking items milik tenant
        $bookingItemsQuery = BookingItem::query()
            ->where('booking_items.tenant_id', $tenantId)
            ->join('bookings', 'booking_items.booking_id', '=', 'bookings.id')
            ->when($start, fn ($q) => $q->whereDate('bookings.created_at', '>=', $start))
            ->when($end, fn ($q) => $q->whereDate('bookings.created_at', '<=', $end));

        // Total bookings unik
        $totalBookings = (clone $bookingItemsQuery)->distinct('bookings.id')->count('bookings.id');

        // Booking items yang berstatus paid / completed
        $paidItemsQuery = (clone $bookingItemsQuery)
            ->whereIn('bookings.status', ['paid', 'completed']);

        $totalPaidBookings = (clone $paidItemsQuery)->distinct('bookings.id')->count('bookings.id');
        $totalTicketsSold = (int) (clone $paidItemsQuery)->sum('booking_items.quantity');
        $grossRevenue = (float) (clone $paidItemsQuery)->sum('booking_items.subtotal');

        $commissionRate = $this->ledgerService->getCommissionRateForTenant($tenantId);
        $platformCommission = round($grossRevenue * ($commissionRate / 100), 4);
        $netRevenue = max(0, round($grossRevenue - $platformCommission, 4));

        // Tiket yang digunakan (checked_in)
        $ticketsQuery = Ticket::query()
            ->where('tenant_id', $tenantId)
            ->when($start, fn ($q) => $q->whereDate('visit_date', '>=', $start))
            ->when($end, fn ($q) => $q->whereDate('visit_date', '<=', $end));

        $totalUsedTickets = (clone $ticketsQuery)->where('status', 'checked_in')->count();
        $totalVisitors = $totalUsedTickets; // Pengunjung adalah mereka yang tiketnya sudah checked_in

        // Saldo tersedia
        $balanceInfo = $this->ledgerService->getBalanceInfo($tenantId);

        // Ringkasan harian (Daily breakdown)
        $dailySummary = (clone $paidItemsQuery)
            ->selectRaw('
                DATE(bookings.created_at) as trx_date,
                COUNT(DISTINCT bookings.id) as booking_count,
                SUM(booking_items.quantity) as tickets_sold,
                SUM(booking_items.subtotal) as daily_gross
            ')
            ->groupBy(DB::raw('DATE(bookings.created_at)'))
            ->orderBy(DB::raw('DATE(bookings.created_at)'), 'desc')
            ->get()
            ->map(function ($row) use ($commissionRate, $tenantId) {
                $gross = (float) $row->daily_gross;
                $comm = round($gross * ($commissionRate / 100), 4);
                $net = $gross - $comm;

                $visitors = Ticket::where('tenant_id', $tenantId)
                    ->whereDate('visit_date', $row->trx_date)
                    ->where('status', 'checked_in')
                    ->count();

                return [
                    'date' => $row->trx_date,
                    'booking_count' => (int) $row->booking_count,
                    'tickets_sold' => (int) $row->tickets_sold,
                    'visitors' => $visitors,
                    'gross_revenue' => $gross,
                    'platform_commission' => $comm,
                    'net_revenue' => $net,
                ];
            });

        return [
            'period_label' => $periodLabel,
            'from_date' => $start?->toDateString(),
            'to_date' => $end?->toDateString(),
            'total_tickets_sold' => $totalTicketsSold,
            'total_visitors' => $totalVisitors,
            'total_bookings' => $totalBookings,
            'total_paid_bookings' => $totalPaidBookings,
            'total_used_tickets' => $totalUsedTickets,
            'gross_revenue' => $grossRevenue,
            'platform_commission' => $platformCommission,
            'net_revenue' => $netRevenue,
            'available_balance' => $balanceInfo['available_balance'],
            'daily_summary' => $dailySummary,
        ];
    }

    /**
     * @return array{0: ?Carbon, 1: ?Carbon, 2: string}
     */
    private function resolveDateRange(?string $period, ?string $fromDate, ?string $toDate): array
    {
        $today = Carbon::today('Asia/Jakarta');

        return match ($period) {
            'today' => [$today->copy()->startOfDay(), $today->copy()->endOfDay(), 'Hari Ini ('.$today->format('d M Y').')'],
            'this_week' => [$today->copy()->startOfWeek(), $today->copy()->endOfWeek(), 'Minggu Ini'],
            'this_month' => [$today->copy()->startOfMonth(), $today->copy()->endOfMonth(), 'Bulan Ini ('.$today->translatedFormat('F Y').')'],
            'custom' => [
                $fromDate ? Carbon::parse($fromDate, 'Asia/Jakarta')->startOfDay() : null,
                $toDate ? Carbon::parse($toDate, 'Asia/Jakarta')->endOfDay() : null,
                ($fromDate && $toDate) ? "Rentang {$fromDate} s/d {$toDate}" : 'Kustom',
            ],
            default => [$today->copy()->startOfMonth(), $today->copy()->endOfMonth(), 'Bulan Ini'],
        };
    }
}
