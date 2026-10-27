<?php

namespace App\Modules\Admin\Services;

use App\Models\CommissionScheme;
use App\Models\Tenant;
use App\Models\TenantBankAccount;
use App\Models\User;
use App\Models\UserTenantRole;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;

final class CreateTenant
{
    public function __construct(private AuditLogger $audit) {}

    public function handle(array $data): Tenant
    {
        return DB::transaction(function () use ($data): Tenant {
            // 1. Buat Tenant
            $tenant = new Tenant();
            $tenant->name = $data['name'];
            $tenant->code = $data['code'];
            $tenant->status = 'active';
            $tenant->save();

            // 2. Buat Komisi Awal
            $commission = new CommissionScheme();
            $commission->tenant_id = $tenant->id;
            $commission->commission_rate = (float) $data['commission_rate'] / 100;
            $commission->valid_from = now();
            $commission->valid_to = null;
            $commission->save();

            // 3. Buat Akun Owner (PIC)
            $owner = new User();
            $owner->name = $data['pic_name'];
            $owner->email = $data['pic_email'];
            $owner->password = bcrypt(Str::password(32)); // Password acak aman
            $owner->role = 'tenant_admin'; 
            $owner->status = 'active';
            $owner->email_verified_at = now();
            $owner->save();

            // Tugaskan sebagai Owner
            UserTenantRole::create([
                'user_id' => $owner->id,
                'tenant_id' => $tenant->id,
                'role' => 'owner'
            ]);

            // 4. Buat Rekening Bank
            $account = new TenantBankAccount();
            $account->tenant_id = $tenant->id;
            $account->bank_code = $data['bank_code'];
            $account->account_holder = $data['account_holder'];
            $account->account_last4 = Str::substr((string) $data['account_number'], -4);
            $account->account_number_cipher = (string) $data['account_number'];
            $account->status = 'verified'; // Langsung verified oleh admin
            $account->is_primary = true;
            $account->save();

            // 5. Audit
            $this->audit->record('tenant', $tenant->id, 'created', null, $tenant->only(['name', 'code', 'status']), $tenant->id);
            $this->audit->record('tenant_bank_account', $account->id, 'created', null, ['bank_code' => $data['bank_code'], 'account_last4' => $account->account_last4], $tenant->id);

            // 6. Kirim Email Reset Password setelah commit
            DB::afterCommit(function () use ($owner) {
                Password::broker()->sendResetLink(['email' => $owner->email]);
            });

            return $tenant;
        });
    }
}