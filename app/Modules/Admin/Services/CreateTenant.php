<?php

namespace App\Modules\Admin\Services;

use App\Models\CommissionScheme;
use App\Models\Destination;
use App\Models\Tenant;
use App\Models\TenantBankAccount;
use App\Models\User;
use App\Models\UserTenantRole;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

final class CreateTenant
{
    public function __construct(private AuditLogger $audit) {}

    /**
     * Daftarkan tenant dan destinasi baru secara atomik.
     *
     * @param array<string, mixed> $data
     * @param UploadedFile|null $image
     * @return Tenant
     */
    public function handle(array $data, ?UploadedFile $image = null): Tenant
    {
        return DB::transaction(function () use ($data, $image): Tenant {
            $destinationName = trim((string) ($data['destination_name'] ?? $data['name'] ?? 'Destinasi Wisata'));

            // 1. Generate Kode Tenant Unik
            $tenantCode = ! empty($data['code'])
                ? strtoupper(trim((string) $data['code']))
                : $this->generateUniqueTenantCode($destinationName);

            // 2. Buat Record Tenant
            $tenant = new Tenant();
            $tenant->name = 'Pengelola ' . $destinationName;
            $tenant->code = $tenantCode;
            $tenant->status = $data['status'] ?? 'active';
            $tenant->save();

            // 3. Upload Gambar Destinasi (jika ada)
            $imagePath = null;
            if ($image instanceof UploadedFile) {
                $imagePath = 'storage/' . $image->store('destinations', 'public');
            } elseif (isset($data['image']) && $data['image'] instanceof UploadedFile) {
                $imagePath = 'storage/' . $data['image']->store('destinations', 'public');
            }

            // 4. Generate Slug Destinasi yang Unik
            $slug = $this->generateUniqueDestinationSlug($destinationName);

            // 5. Buat Record Destinasi Wisata (Katalog Publik)
            $destination = new Destination();
            $destination->tenant_id = $tenant->id;
            $destination->name = $destinationName;
            $destination->slug = $slug;
            $destination->description = $data['description'] ?? null;
            $destination->location = $data['location'] ?? null;
            $destination->operating_hours = ! empty($data['operating_hours']) ? $data['operating_hours'] : '08:00 - 16:00 WIB';
            $destination->facilities = $data['facilities'] ?? null;
            $destination->safety_guidelines = $data['safety_guidelines'] ?? null;
            $destination->image = $imagePath;
            $destination->save();

            // 6. Buat Akun Pengelola / Tenant Admin
            $adminPassword = ! empty($data['admin_password'])
                ? $data['admin_password']
                : Str::password(12);

            $admin = new User();
            $admin->tenant_id = $tenant->id;
            $admin->name = $data['admin_name'] ?? $data['pic_name'] ?? 'Admin ' . $destinationName;
            $admin->email = $data['admin_email'] ?? $data['pic_email'];
            $admin->password = Hash::make($adminPassword);
            $admin->role = 'tenant_admin';
            $admin->status = 'active';
            $admin->email_verified_at = now();
            $admin->save();

            // 7. Hubungkan Pengelola melalui UserTenantRole
            UserTenantRole::create([
                'user_id'   => $admin->id,
                'tenant_id' => $tenant->id,
                'role'      => 'owner',
            ]);

            // 8. Buat Skema Komisi Platform
            $commissionRatePercent = isset($data['commission_rate']) ? (float) $data['commission_rate'] : 5.0;
            $commission = new CommissionScheme();
            $commission->tenant_id = $tenant->id;
            if (\Illuminate\Support\Facades\Schema::hasColumn('commission_schemes', 'percentage')) {
                $commission->percentage = $commissionRatePercent;
            }
            $commission->commission_rate = $commissionRatePercent / 100;
            if (\Illuminate\Support\Facades\Schema::hasColumn('commission_schemes', 'is_active')) {
                $commission->is_active = 1;
            }
            $commission->valid_from = now();
            $commission->valid_to = null;
            $commission->save();

            // 9. Simpan Rekening Bank (jika diisi)
            if (! empty($data['bank_code']) && ! empty($data['account_number'])) {
                $account = new TenantBankAccount();
                $account->tenant_id = $tenant->id;
                $account->bank_code = strtoupper((string) $data['bank_code']);
                $account->account_holder = $data['account_holder'] ?? $admin->name;
                $account->account_last4 = Str::substr((string) $data['account_number'], -4);
                $account->account_number_cipher = (string) $data['account_number'];
                $account->status = 'verified';
                $account->is_primary = true;
                $account->save();
            }

            // 10. Catat Aktivitas ke Audit Log
            $this->audit->record('tenants', $tenant->id, 'CREATE_TENANT', null, [
                'name' => $tenant->name,
                'code' => $tenant->code,
                'status' => $tenant->status,
                'destination_name' => $destination->name,
                'admin_email' => $admin->email,
            ], $tenant->id);

            return $tenant;
        });
    }

    /**
     * Generate unique tenant code (e.g. TEN-KALIBENDO).
     */
    private function generateUniqueTenantCode(string $name): string
    {
        $clean = Str::slug(substr($name, 0, 15));
        $baseCode = 'TEN-' . strtoupper($clean ?: 'DEST');
        $code = $baseCode;
        $counter = 1;

        while (Tenant::where('code', $code)->exists()) {
            $code = $baseCode . '-' . $counter;
            $counter++;
        }

        return $code;
    }

    /**
     * Generate unique destination slug (e.g. air-terjun-kalibendo).
     */
    private function generateUniqueDestinationSlug(string $name): string
    {
        $baseSlug = Str::slug($name) ?: 'destinasi';
        $slug = $baseSlug;
        $counter = 1;

        while (Destination::where('slug', $slug)->exists()) {
            $slug = $baseSlug . '-' . $counter;
            $counter++;
        }

        return $slug;
    }
}
