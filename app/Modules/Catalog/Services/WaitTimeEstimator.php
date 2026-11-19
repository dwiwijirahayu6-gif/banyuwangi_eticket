<?php

namespace App\Modules\Catalog\Services;

use App\Models\BookingItem;
use App\Models\Destination;
use App\Models\Tenant;
use App\Models\TicketType;
use Illuminate\Support\Facades\DB;

/**
 * UC-11 Hitung Estimasi Waktu Tunggu / Pelayanan Antrean Tiket Wisata.
 *
 * Estimasi =
 * (akumulasi durasi pelayanan tiket pada antrean aktif
 * ÷ kapasitas paralel loket/gate)
 * + waktu pelayanan tiket yang dipilih.
 *
 * Ditampilkan sebagai rentang menit (mis. "± 8–12 mnt").
 */
final class WaitTimeEstimator
{
    /**
     * Jumlah loket/gate yang memproses pengunjung secara paralel.
     */
    public const PARALLEL_CAPACITY = 2;

    /**
     * Status pesanan aktif yang mengantre.
     */
    public const ACTIVE_STATUSES = ['paid'];

    /**
     * Menghitung total estimasi antrean aktif tenant/destinasi.
     */
    public function queueMinutes(Tenant|Destination $target): float
    {
        $tenantId = $target instanceof Tenant
            ? $target->id
            : $target->tenant_id;

        $today = now('Asia/Jakarta')->toDateString();

        $query = BookingItem::query()
            ->join(
                'bookings',
                'bookings.id',
                '=',
                'booking_items.booking_id'
            )
            ->join(
                'ticket_types',
                'ticket_types.id',
                '=',
                'booking_items.ticket_type_id'
            )
            ->join(
                'visit_slots',
                'visit_slots.id',
                '=',
                'booking_items.visit_slot_id'
            )
            ->where('booking_items.tenant_id', $tenantId)
            ->whereIn('bookings.status', self::ACTIVE_STATUSES)
            ->whereDate('visit_slots.visit_date', $today);

        // Jika target berupa destinasi,
        // antrean hanya dihitung dari tiket destinasi tersebut.
        if ($target instanceof Destination) {
            $query->where(
                'ticket_types.destination_id',
                $target->id
            );
        }

        // Akumulasi waktu persiapan seluruh tiket dalam antrean.
        $totalPrep = $query->sum(
            DB::raw(
                'booking_items.quantity * COALESCE(ticket_types.prep_minutes, 4)'
            )
        );

        // Waktu antrean dibagi kapasitas pelayanan paralel.
        return (float) $totalPrep / self::PARALLEL_CAPACITY;
    }

    /**
     * Menghitung rentang estimasi waktu tunggu tenant/destinasi.
     *
     * @return array{low: int, high: int}
     */
    public function forTarget(
        Tenant|Destination $target,
        ?float $ticketPrep = null
    ): array {
        $tenantId = $target instanceof Tenant
            ? $target->id
            : $target->tenant_id;

        // Ambil rata-rata waktu pelayanan tiket yang tersedia
        // dan masih memiliki stok.
        $query = TicketType::query()
            ->withoutGlobalScope('tenant')
            ->where('tenant_id', $tenantId)
            ->where('is_available', true)
            ->where('stock_qty', '>', 0);

        // Jika target berupa destinasi,
        // hanya gunakan tiket dari destinasi tersebut.
        if ($target instanceof Destination) {
            $query->where('destination_id', $target->id);
        }

        // Gunakan 5 menit sebagai nilai default
        // apabila tidak ada tiket yang memenuhi kriteria.
        $ticketPrep ??= (float) (
            $query->avg('prep_minutes') ?? 5
        );

        // Total estimasi = waktu antrean + waktu pelayanan tiket.
        $estimate = $this->queueMinutes($target) + $ticketPrep;

        // Batas bawah estimasi minimal 1 menit.
        $low = max(1, (int) round($estimate));

        // Batas atas menggunakan toleransi 1,5 kali estimasi.
        $high = max(
            $low + 1,
            (int) ceil($estimate * 1.5)
        );

        return [
            'low' => $low,
            'high' => $high,
        ];
    }

    /**
     * Menghitung estimasi waktu untuk tenant.
     */
    public function forTenant(
        Tenant $tenant,
        ?float $itemPrep = null
    ): array {
        return $this->forTarget($tenant, $itemPrep);
    }

    /**
     * Menghitung estimasi waktu untuk destinasi.
     */
    public function forDestination(
        Destination $destination,
        ?float $itemPrep = null
    ): array {
        return $this->forTarget($destination, $itemPrep);
    }

    /**
     * Menghasilkan label estimasi siap tampil.
     *
     * Contoh: "± 8–12 mnt".
     */
    public function label(
        Tenant|Destination $target,
        ?float $itemPrep = null
    ): string {
        $range = $this->forTarget($target, $itemPrep);

        return "± {$range['low']}–{$range['high']} mnt";
    }
}