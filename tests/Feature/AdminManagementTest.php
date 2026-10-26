<?php

namespace Tests\Feature;

use App\Models\AuditLog;
use App\Models\CommissionScheme;
use App\Models\Tenant;
use App\Models\TenantBankAccount;
use App\Models\User;
use App\Modules\Admin\Services\ChangeCommissionSchedule;
use App\Modules\Admin\Services\ManageBankAccount;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class AdminManagementTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        // Buat user super_admin
        return User::factory()->create([
            'role' => 'super_admin',
            'status' => 'active',
            'email_verified_at' => now()
        ]);
    }

    public function test_only_admin_can_see_tenant_index(): void
    {
        $this->actingAs($this->admin());
        $this->get(route('admin.tenants.index'))->assertOk();

        // Operator biasa tidak boleh masuk
        $operator = User::factory()->create(['role' => 'tenant_admin', 'status' => 'active']);
        $this->actingAs($operator);
        $this->get(route('admin.tenants.index'))->assertForbidden();
    }

    public function test_creating_tenant_also_creates_active_commission(): void
    {
        $this->actingAs($this->admin());
        
        $this->post(route('admin.tenants.store'), [
            'name' => 'Wisata Kawah Ijen',
            'code' => 'IJEN',
            'commission_rate' => '15',
        ])->assertRedirect();

        $tenant = Tenant::where('code', 'IJEN')->firstOrFail();
        
        // Pastikan komisi awal terbuat
        $this->assertSame(1, CommissionScheme::where('tenant_id', $tenant->id)->whereNull('valid_to')->count());
    }

    public function test_commission_schedule_is_effective_dated(): void
    {
        $tenant = Tenant::factory()->create();
        
        CommissionScheme::create([
            'tenant_id' => $tenant->id,
            'commission_rate' => 0.15,
            'valid_from' => now()->subMonth(),
            'valid_to' => null
        ]);

        app(ChangeCommissionSchedule::class)->handle($tenant, 0.20, now()->addDay());

        $schemes = CommissionScheme::where('tenant_id', $tenant->id)->orderBy('valid_from')->get();
        
        $this->assertCount(2, $schemes);
        $this->assertNotNull($schemes[0]->valid_to, 'versi lama harus ditutup');
        $this->assertNull($schemes[1]->valid_to, 'versi baru harus aktif');
    }

    public function test_bank_account_is_stored_encrypted_with_last4(): void
    {
        $tenant = Tenant::factory()->create();
        
        $account = app(ManageBankAccount::class)->store($tenant, [
            'bank_code' => 'BCA', 
            'account_holder' => 'Budi', 
            'account_number' => '1234567890',
        ]);

        // Cek isi database mentah
        $raw = DB::table('tenant_bank_accounts')->where('id', $account->id)->first();
        
        // Nomor asli tidak boleh terlihat di database
        $this->assertStringNotContainsString('1234567890', (string) $raw->account_number_cipher, 'nomor mentah bocor di DB');
        $this->assertSame('7890', $raw->account_last4);
        
        // Pastikan bisa didekripsi kembali oleh Laravel
        $this->assertSame('1234567890', $account->fresh()->account_number_cipher);
    }
}