<?php

namespace Tests\Feature;

use App\Models\Destination;
use App\Models\Tenant;
use App\Models\TicketType;
use App\Models\User;
use App\Models\UserTenantRole;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Livewire\Livewire;
use Tests\TestCase;

class TenantTicketManagerTest extends TestCase
{
    use RefreshDatabase;

    /** @return array{0: Tenant, 1: User, 2: Destination} */
    private function tenantWithMember(): array
    {
        $tenant = Tenant::factory()->create(['status' => 'active']);
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
        $dest = Destination::create([
            'tenant_id' => $tenant->id,
            'name' => 'Pantai Pulau Merah',
            'slug' => 'pantai-pulau-merah',
            'operating_hours' => '07:00 - 17:00 WIB',
        ]);

        return [$tenant, $user, $dest];
    }

    public function test_ticket_manager_page_renders_catalog_module_component(): void
    {
        [$tenant, $user] = $this->tenantWithMember();

        $this->actingAs($user)
            ->get(route('tenant.ticket-manager', ['tenant' => $tenant->code]))
            ->assertOk()
            ->assertSee('Kelola Tiket Wisata')
            ->assertSeeLivewire('catalog::ticket-manager');
    }

    public function test_member_creates_ticket_with_tenant_id_from_context(): void
    {
        [$tenant, $user, $dest] = $this->tenantWithMember();

        Livewire::actingAs($user)
            ->test('catalog::ticket-manager', ['tenantId' => $tenant->id])
            ->set('name', 'Tiket Masuk Weekend')
            ->set('destinationId', $dest->id)
            ->set('category', 'Tiket Masuk')
            ->set('basePrice', 20000)
            ->set('prepMinutes', 5)
            ->set('stockQty', 100)
            ->call('createTicket')
            ->assertHasNoErrors();

        $ticket = TicketType::withoutGlobalScope('tenant')->where('name', 'Tiket Masuk Weekend')->firstOrFail();
        $this->assertSame($tenant->id, $ticket->tenant_id, 'tenant_id harus berasal dari context, bukan input klien');
        $this->assertSame(20000, (int) $ticket->price);
    }

    public function test_toggle_and_restock_are_atomic_and_audited(): void
    {
        [$tenant, $user, $dest] = $this->tenantWithMember();
        $ticket = TicketType::create([
            'tenant_id' => $tenant->id,
            'destination_id' => $dest->id,
            'name' => 'Tiket Camping',
            'category' => 'Wahana & Atraksi',
            'price' => 50000,
            'stock_qty' => 5,
            'is_available' => true,
            'prep_minutes' => 5,
        ]);

        Livewire::actingAs($user)
            ->test('catalog::ticket-manager', ['tenantId' => $tenant->id])
            ->call('toggle', $ticket->id)
            ->call('restock', $ticket->id, 10);

        $ticket->refresh();
        $this->assertFalse($ticket->is_available);
        $this->assertSame(15, (int) $ticket->stock_qty);

        // Movement kuota/stok tercatat idempoten
        $this->assertSame(1, DB::table('ticket_stock_movements')->where('ticket_type_id', $ticket->id)->count());

        // Audit log tercatat
        $this->assertTrue(
            DB::table('audit_logs')
                ->where('entity', 'ticket_type')
                ->where('action', 'toggle_availability')
                ->exists()
        );
    }

    public function test_tampered_tenant_id_for_non_member_is_forbidden(): void
    {
        [$tenantA, $userA] = $this->tenantWithMember();
        $tenantB = Tenant::factory()->create(['status' => 'active']);

        // userA bukan anggota tenantB -> booted() harus abort 403
        Livewire::actingAs($userA)
            ->test('catalog::ticket-manager', ['tenantId' => $tenantB->id])
            ->assertForbidden();
    }
}
