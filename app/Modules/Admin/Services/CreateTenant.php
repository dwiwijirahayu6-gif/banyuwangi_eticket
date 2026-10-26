<?php

namespace App\Modules\Admin\Services;

use App\Models\CommissionScheme;
use App\Models\Tenant;
use Illuminate\Support\Facades\DB;

final class CreateTenant
{
    public function __construct(private AuditLogger $audit) {}

    public function handle(array $data): Tenant
    {
        return DB::transaction(function () use ($data): Tenant {
            // Buat data Tenant (Destinasi/Pengelola)
            $tenant = new Tenant();
            $tenant->name = $data['name'];
            $tenant->code = $data['code'];
            // $tenant->slug = $data['slug']; <-- DIHAPUS KARENA KOLOM SLUG TIDAK ADA
            $tenant->status = 'active';
            $tenant->save();

            // Buat Skema Komisi Awal
            $commission = new CommissionScheme();
            $commission->tenant_id = $tenant->id;
            // Karena DB menggunakan decimal(6,4), 10% harus dikirim sebagai 0.1000
            $commission->commission_rate = (float) $data['commission_rate'] / 100; 
            $commission->valid_from = now();
            $commission->valid_to = null;
            $commission->save();

            // Catat Audit (slug juga dihapus dari only)
            $this->audit->record(
                'tenant', 
                $tenant->id, 
                'created',
                null, 
                $tenant->only(['name', 'code', 'status']), 
                $tenant->id
            );

            return $tenant;
        });
    }
}