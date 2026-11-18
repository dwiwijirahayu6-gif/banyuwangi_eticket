<?php

namespace Tests\Feature;

use App\Models\Booking;
use App\Models\BookingItem;
use App\Models\Destination;
use App\Models\Tenant;
use App\Models\Ticket;
use App\Models\TicketType;
use App\Models\User;
use App\Models\UserTenantRole;
use App\Models\VisitSlot;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TenantDashboardTest extends TestCase
{
    use RefreshDatabase;

    private function createTenantEnvironment(string $name = 'Wisata Alam Banyuwangi', string $code = 'TEN-TEST1'): array
    {
        $tenant = Tenant::factory()->create([
            'name' => $name,
            'code' => $code,
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

        $destination = Destination::create([
            'tenant_id' => $tenant->id,
            'name' => $name,
            'slug' => str($name)->slug()->value(),
            'location' => 'Kecamatan Glagah, Banyuwangi',
            'operating_hours' => '08:00 - 16:00 WIB',
        ]);

        $ticketType = TicketType::create([
            'tenant_id' => $tenant->id,
            'destination_id' => $destination->id,
            'name' => 'Tiket Masuk Reguler',
            'price' => 25000,
        ]);

        $slot = VisitSlot::create([
            'tenant_id' => $tenant->id,
            'destination_id' => $destination->id,
            'visit_date' => Carbon::today('Asia/Jakarta')->toDateString(),
            'quota_capacity' => 100,
            'quota_remaining' => 100,
        ]);

        return [$tenant, $user, $destination, $ticketType, $slot];
    }

    public function test_tenant_can_view_dynamic_dashboard_with_kpi_and_recent_activities(): void
    {
        [$tenant, $user, $destination, $ticketType, $slot] = $this->createTenantEnvironment();

        // 1 Booking lunas dengan 2 tiket
        $paidBooking = Booking::create([
            'booking_code' => 'BWI-PAID-01',
            'customer_name' => 'Budi Santoso',
            'customer_email' => 'budi@example.com',
            'customer_phone' => '081234567890',
            'total_amount' => 50000,
            'status' => 'paid',
            'visit_date' => Carbon::today('Asia/Jakarta')->toDateString(),
            'paid_at' => now(),
        ]);

        $bookingItem = BookingItem::create([
            'booking_id' => $paidBooking->id,
            'tenant_id' => $tenant->id,
            'ticket_type_id' => $ticketType->id,
            'visit_slot_id' => $slot->id,
            'quantity' => 2,
            'unit_price_snapshot' => 25000,
            'subtotal' => 50000,
        ]);

        // 1 Tiket sudah checked_in, 1 tiket issued
        Ticket::create([
            'booking_id' => $paidBooking->id,
            'booking_item_id' => $bookingItem->id,
            'tenant_id' => $tenant->id,
            'ticket_code' => 'TCK-PAID-01',
            'status' => 'checked_in',
            'visit_date' => Carbon::today('Asia/Jakarta')->toDateString(),
            'verified_by_user_id' => $user->id,
        ]);

        Ticket::create([
            'booking_id' => $paidBooking->id,
            'booking_item_id' => $bookingItem->id,
            'tenant_id' => $tenant->id,
            'ticket_code' => 'TCK-PAID-02',
            'status' => 'issued',
            'visit_date' => Carbon::today('Asia/Jakarta')->toDateString(),
        ]);

        // 1 Booking pending payment
        Booking::create([
            'booking_code' => 'BWI-PEND-01',
            'customer_name' => 'Siti Rahma',
            'customer_email' => 'siti@example.com',
            'customer_phone' => '081298765432',
            'total_amount' => 25000,
            'status' => 'pending_payment',
            'visit_date' => Carbon::today('Asia/Jakarta')->toDateString(),
        ]);

        $response = $this->actingAs($user)
            ->get(route('tenant.dashboard', ['tenant' => $tenant->code]));

        $response->assertOk()
            ->assertSee($destination->name)
            ->assertSee('BWI-PAID-01')
            ->assertSee('Budi Santoso')
            ->assertSee('TCK-PAID-01')
            ->assertSee('Statistik Operasional 7 Hari Terakhir');

        // Pastikan variabel view berisi metrik akurat
        $response->assertViewHas('totalTicketsSold', 2);
        $response->assertViewHas('totalVisitors', 1);
        $response->assertViewHas('totalReservations', 1);
        $response->assertViewHas('paidReservations', 1);
        $response->assertViewHas('pendingReservations', 0);
    }

    public function test_tenant_dashboard_isolates_data_from_other_tenants(): void
    {
        [$tenantA, $userA, $destA, $ticketTypeA, $slotA] = $this->createTenantEnvironment('Wisata Kawah Ijen', 'TEN-IJEN');
        [$tenantB, $userB, $destB, $ticketTypeB, $slotB] = $this->createTenantEnvironment('Wisata Pulau Merah', 'TEN-MERAH');

        // Transaksi khusus milik Tenant B
        $bookingB = Booking::create([
            'booking_code' => 'BWI-MERAH-99',
            'customer_name' => 'Pengunjung Tenant B',
            'customer_email' => 'userb@example.com',
            'customer_phone' => '081233344455',
            'total_amount' => 100000,
            'status' => 'paid',
            'visit_date' => Carbon::today('Asia/Jakarta')->toDateString(),
            'paid_at' => now(),
        ]);

        $itemB = BookingItem::create([
            'booking_id' => $bookingB->id,
            'tenant_id' => $tenantB->id,
            'ticket_type_id' => $ticketTypeB->id,
            'visit_slot_id' => $slotB->id,
            'quantity' => 4,
            'unit_price_snapshot' => 25000,
            'subtotal' => 100000,
        ]);

        Ticket::create([
            'booking_id' => $bookingB->id,
            'booking_item_id' => $itemB->id,
            'tenant_id' => $tenantB->id,
            'ticket_code' => 'TCK-MERAH-99',
            'status' => 'checked_in',
            'visit_date' => Carbon::today('Asia/Jakarta')->toDateString(),
        ]);

        // Login sebagai User Tenant A
        $response = $this->actingAs($userA)
            ->get(route('tenant.dashboard', ['tenant' => $tenantA->code]));

        $response->assertOk();
        // Tenant A tidak boleh melihat data transaksi atau tiket Tenant B
        $response->assertDontSee('BWI-MERAH-99');
        $response->assertDontSee('Pengunjung Tenant B');
        $response->assertDontSee('TCK-MERAH-99');

        $response->assertViewHas('totalTicketsSold', 0);
        $response->assertViewHas('totalVisitors', 0);
        $response->assertViewHas('totalReservations', 0);
    }
}
