<?php

namespace App\Modules\Admin\Services;

use App\Models\CommissionScheme;
use App\Models\Tenant;
use Carbon\CarbonInterface;
use DomainException;
use Illuminate\Support\Facades\DB;

final class ChangeCommissionSchedule
{
    public function __construct(private AuditLogger $audit) {}

    public function handle(Tenant $tenant, int|float|string $rate, CarbonInterface $effectiveAt): CommissionScheme
    {
        return DB::transaction(function () use ($tenant, $rate, $effectiveAt): CommissionScheme {
            // Kunci versi komisi yang sedang aktif saat ini
            $active = CommissionScheme::query()
                ->where('tenant_id', $tenant->id)
                ->whereNull('valid_to')
                ->lockForUpdate()
                ->first();

            if ($active !== null && $active->valid_from >= $effectiveAt) {
                throw new DomainException("Waktu efektif harus setelah versi aktif (mencegah overlap).");
            }

            // Tutup versi lama tepat pada waktu efektif versi baru
            if ($active) {
                $active->forceFill(['valid_to' => $effectiveAt])->save();
            }

            // Buat versi baru
            $new = (new CommissionScheme)->forceFill([
                'tenant_id' => $tenant->id,
                'commission_rate' => $rate,
                'valid_from' => $effectiveAt,
                'valid_to' => null,
            ]);
            $new->save();

            $this->audit->record('commission_scheme', $new->id, 'created',
                null, ['commission_rate' => $rate, 'valid_from' => $effectiveAt], $tenant->id);

            return $new;
        });
    }
}