<?php

namespace App\Modules\Catalog\Services;

use App\Models\BookingItem;
use App\Models\Destination;
use App\Models\Tenant;
use App\Models\TicketType;

/**
 * UC-11 Hitung Estimasi Waktu Tunggu / Pelayanan Antrean Tiket Wisata.
 * Estimasi = (akumulasi durasi pelayanan tiket pada antrean aktif ÷ kapasitas paralel loket/gate)
 * + waktu pelayanan tiket yang dipilih.
 * Ditampilkan sebagai rentang menit (mis. "± 8–12 mnt") sesuai toleransi SRS.
 */
final class WaitTimeEstimator
{
    /** Jumlah loket/gate yang memproses pengunjung secara paralel. */
    public const PARALLEL_CAPACITY = 2;

    /** Status pesanan aktif yang mengantre. */
    public const ACTIVE_STATUSES = ['pending', 'paid', 'accepted', 'preparing'];

    /** Menit antrean aktif tenant / destinasi. */
    public function queueMinutes(Tenant|Destination $target): float
    {
        $tenantId = $target instanceof Tenant ? $target->id : $target->tenant_id;

        $itemCount = BookingItem::query()
            ->where('tenant_id', $tenantId)
            ->whereHas('booking', fn ($b) => $b->whereIn('status', self::ACTIVE_STATUSES))
            ->sum('quantity');

        if ($itemCount == 0) {
            return 0.0;
        }

        $totalPrep = (int) $itemCount * 4; // 4 menit per tiket pelayanan

        return $totalPrep / self::PARALLEL_CAPACITY;
    }

    /**
     * Rentang estimasi waktu tunggu untuk tenant/destinasi.
     *
     * @return array{low: int, high: int}
     */
    public function forTarget(Tenant|Destination $target, ?float $ticketPrep = null): array
    {
        $tenantId = $target instanceof Tenant ? $target->id : $target->tenant_id;

        $ticketPrep ??= (float) (TicketType::query()->withoutGlobalScope('tenant')
            ->where('tenant_id', $tenantId)->where('is_available', true)->avg('prep_minutes') ?? 5);

        $estimate = $this->queueMinutes($target) + $ticketPrep;
        $low = max(1, (int) round($estimate));

        return ['low' => $low, 'high' => max($low + 1, (int) ceil($estimate * 1.5))];
    }

    public function forTenant(Tenant $tenant, ?float $itemPrep = null): array
    {
        return $this->forTarget($tenant, $itemPrep);
    }

    public function forDestination(Destination $destination, ?float $itemPrep = null): array
    {
        return $this->forTarget($destination, $itemPrep);
    }

    /** Label siap tampil, mis. "± 8–12 mnt". */
    public function label(Tenant|Destination $target, ?float $itemPrep = null): string
    {
        $range = $this->forTarget($target, $itemPrep);

        return "± {$range['low']}–{$range['high']} mnt";
    }
}
