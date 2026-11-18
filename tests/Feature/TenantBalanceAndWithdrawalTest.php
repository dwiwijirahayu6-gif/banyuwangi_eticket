<?php

namespace Tests\Feature;

use App\Models\Booking;
use App\Models\BookingItem;
use App\Models\Destination;
use App\Models\LedgerEntry;
use App\Models\Tenant;
use App\Models\TenantBankAccount;
use App\Models\User;
use App\Models\UserTenantRole;
use App\Models\Withdrawal;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TenantBalanceAndWithdrawalTest extends TestCase
{
    use RefreshDatabase;

    private function createTenantEnvironment(): array
    {
        $tenant = Tenant::factory()->create([
            'name' => 'Wisata Bangsring Underwater',
            'code' => 'TEN-BANGSRING',
            'status' => 'active',
        ]);

        $user = User::factory()->create([
            'role' => 'tenant_admin',
            'status' => 'active',
            'email_verified_at' => now(),
        ]);

        UserTenantRole::create([
            'user_id' => $user->id,
            'tenant_id' => $tenant->id,
            'role' => 'operator',
        ]);

        $bankAccount = TenantBankAccount::create([
            'tenant_id' => $tenant->id,
            'bank_code' => 'BCA',
            'account_number_cipher' => 'sample-cipher-string',
            'account_last4' => '7890',
            'account_holder' => 'Pokdarwis Bangsring',
            'is_primary' => true,
        ]);

        return [$tenant, $user, $bankAccount];
    }

    public function test_tenant_can_view_balance_and_ledger(): void
    {
        [$tenant, $user, $bankAccount] = $this->createTenantEnvironment();

        LedgerEntry::create([
            'tenant_id' => $tenant->id,
            'transaction_type' => 'credit_sale',
            'amount' => 500000,
            'balance_after' => 500000,
        ]);

        $response = $this->actingAs($user)
            ->get(route('tenant.saldo-penarikan', ['tenant' => $tenant->code]));

        $response->assertOk()
            ->assertSee('Rp 500.000')
            ->assertSee('Pokdarwis Bangsring')
            ->assertSee('7890');
    }

    public function test_tenant_can_request_withdrawal_within_available_balance(): void
    {
        [$tenant, $user, $bankAccount] = $this->createTenantEnvironment();

        LedgerEntry::create([
            'tenant_id' => $tenant->id,
            'transaction_type' => 'credit_sale',
            'amount' => 200000,
            'balance_after' => 200000,
        ]);

        $response = $this->actingAs($user)
            ->post(route('tenant.saldo-penarikan.store', ['tenant' => $tenant->code]), [
                'amount' => 150000,
                'bank_account_id' => $bankAccount->id,
            ]);

        $response->assertRedirect(route('tenant.saldo-penarikan', ['tenant' => $tenant->code]))
            ->assertSessionHas('success');

        $withdrawal = Withdrawal::where('tenant_id', $tenant->id)->firstOrFail();
        $this->assertEquals(150000, $withdrawal->amount);
        $this->assertSame('pending', $withdrawal->status);
    }

    public function test_tenant_cannot_request_withdrawal_exceeding_available_balance(): void
    {
        [$tenant, $user, $bankAccount] = $this->createTenantEnvironment();

        LedgerEntry::create([
            'tenant_id' => $tenant->id,
            'transaction_type' => 'credit_sale',
            'amount' => 50000,
            'balance_after' => 50000,
        ]);

        $response = $this->actingAs($user)
            ->post(route('tenant.saldo-penarikan.store', ['tenant' => $tenant->code]), [
                'amount' => 100000, // Melebihi saldo 50.000
                'bank_account_id' => $bankAccount->id,
            ]);

        $response->assertSessionHas('error');

        $this->assertDatabaseMissing('withdrawals', [
            'tenant_id' => $tenant->id,
            'amount' => 100000,
        ]);
    }
}
