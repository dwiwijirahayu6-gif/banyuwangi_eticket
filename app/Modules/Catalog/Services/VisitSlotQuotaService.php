<?php

namespace App\Modules\Catalog\Services;

use App\Models\Destination;
use App\Models\Tenant;
use App\Models\VisitSlot;
use Carbon\Carbon;
use DomainException;
use Illuminate\Support\Facades\DB;

class VisitSlotQuotaService
{
    /**
     * Tambah atau perbarui kuota jadwal kunjungan harian.
     */
    public function setQuota(Tenant $tenant, Destination $destination, string $visitDate, int $capacity): VisitSlot
    {
        if ($capacity < 1) {
            throw new DomainException('Kapasitas kuota minimal 1 pengunjung.');
        }

        if ($destination->tenant_id !== $tenant->id) {
            throw new DomainException('Destinasi tidak valid untuk tenant ini.');
        }

        return DB::transaction(function () use ($tenant, $destination, $visitDate, $capacity) {
            $slot = VisitSlot::where('destination_id', $destination->id)
                ->whereDate('visit_date', $visitDate)
                ->lockForUpdate()
                ->first();

            if ($slot) {
                $booked = $slot->quota_capacity - $slot->quota_remaining;
                if ($capacity < $booked) {
                    throw new DomainException(
                        "Kapasitas kuota ({$capacity}) tidak boleh lebih kecil dari tiket yang sudah dipesan ({$booked} tiket)."
                    );
                }

                $diff = $capacity - $slot->quota_capacity;
                $slot->quota_capacity = $capacity;
                $slot->quota_remaining = max(0, $slot->quota_remaining + $diff);
                $slot->save();

                return $slot;
            }

            return VisitSlot::create([
                'tenant_id' => $tenant->id,
                'destination_id' => $destination->id,
                'visit_date' => $visitDate,
                'quota_capacity' => $capacity,
                'quota_remaining' => $capacity,
                'is_active' => true,
            ]);
        });
    }

    /**
     * Tutup sementara atau buka kembali jadwal kunjungan.
     */
    public function toggleStatus(VisitSlot $slot): VisitSlot
    {
        $slot->is_active = ! ($slot->is_active ?? true);
        $slot->save();

        return $slot;
    }
}
