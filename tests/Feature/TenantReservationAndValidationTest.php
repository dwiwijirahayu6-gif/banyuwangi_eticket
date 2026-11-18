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

class TenantReservationAndValidationTest extends TestCase
{
    use RefreshDatabase;

    private function createTenantEnvironment(): array
    {
        $tenant = Tenant::factory()->create([
            'name' => 'Wisata Pulau Merah',
            'code' => 'TEN-PMERAH',
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
            'name' => 'Pantai Pulau Merah',
            'slug' => 'pantai-pulau-merah',
            'operating_hours' => '07:00 - 17:00 WIB',
        ]);

        $ticketType = TicketType::create([
            'tenant_id' => $tenant->id,
            'destination_id' => $destination->id,
            'name' => 'Tiket Masuk Reguler',
            'price' => 20000,
        ]);

        $slot = VisitSlot::create([
            'tenant_id' => $tenant->id,
            'destination_id' => $destination->id,
            'visit_date' => Carbon::today('Asia/Jakarta')->toDateString(),
            'quota_capacity' => 500,
            'quota_remaining' => 500,
        ]);

        return [$tenant, $user, $destination, $ticketType, $slot];
    }

    public function test_tenant_can_view_and_search_reservations(): void
    {
        [$tenant, $user, $destination, $ticketType, $slot] = $this->createTenantEnvironment();

        $booking = Booking::create([
            'booking_code' => 'BWI-RES-101',
            'customer_name' => 'Ahmad Dahlan',
            'customer_email' => 'ahmad@example.com',
            'customer_phone' => '081234567890',
            'total_amount' => 40000,
            'status' => 'paid',
            'visit_date' => Carbon::today('Asia/Jakarta')->toDateString(),
            'paid_at' => now(),
        ]);

        BookingItem::create([
            'booking_id' => $booking->id,
            'tenant_id' => $tenant->id,
            'ticket_type_id' => $ticketType->id,
            'visit_slot_id' => $slot->id,
            'quantity' => 2,
            'unit_price_snapshot' => 20000,
            'subtotal' => 40000,
        ]);

        // Kunjungi halaman reservasi
        $response = $this->actingAs($user)
            ->get(route('tenant.reservasi', ['tenant' => $tenant->code, 'search' => 'BWI-RES-101']));

        $response->assertOk()
            ->assertSee('BWI-RES-101')
            ->assertSee('Ahmad Dahlan')
            ->assertSee('Rp 40.000');

        // Buka halaman detail reservasi
        $detailResponse = $this->actingAs($user)
            ->get(route('tenant.reservasi.show', ['tenant' => $tenant->code, 'booking' => $booking->booking_code]));

        $detailResponse->assertOk()
            ->assertSee('BWI-RES-101')
            ->assertSee('Tiket Masuk Reguler');
    }

    public function test_tenant_can_search_and_verify_ticket_checkin(): void
    {
        [$tenant, $user, $destination, $ticketType, $slot] = $this->createTenantEnvironment();

        $booking = Booking::create([
            'booking_code' => 'BWI-CHECK-202',
            'customer_name' => 'Dewi Sartika',
            'customer_email' => 'dewi@example.com',
            'customer_phone' => '081398765432',
            'total_amount' => 20000,
            'status' => 'paid',
            'visit_date' => Carbon::today('Asia/Jakarta')->toDateString(),
            'paid_at' => now(),
        ]);

        $item = BookingItem::create([
            'booking_id' => $booking->id,
            'tenant_id' => $tenant->id,
            'ticket_type_id' => $ticketType->id,
            'visit_slot_id' => $slot->id,
            'quantity' => 1,
            'unit_price_snapshot' => 20000,
            'subtotal' => 20000,
        ]);

        $ticket = Ticket::create([
            'booking_id' => $booking->id,
            'booking_item_id' => $item->id,
            'tenant_id' => $tenant->id,
            'ticket_code' => 'TIX-VALID-77',
            'status' => 'issued',
            'visit_date' => Carbon::today('Asia/Jakarta')->toDateString(),
        ]);

        // 1. Cari tiket menggunakan kode
        $searchResponse = $this->actingAs($user)
            ->get(route('tenant.validasi-tiket', ['tenant' => $tenant->code, 'code' => 'TIX-VALID-77']));

        $searchResponse->assertOk()
            ->assertSee('TIX-VALID-77')
            ->assertSee('Dewi Sartika')
            ->assertSee('Verifikasi Masuk');

        // 2. Klik tombol verifikasi masuk
        $verifyResponse = $this->actingAs($user)
            ->post(route('tenant.validasi-tiket.verify', ['tenant' => $tenant->code]), [
                'ticket_id' => $ticket->id,
                'search_code' => 'TIX-VALID-77',
            ]);

        $verifyResponse->assertRedirect()
            ->assertSessionHas('success');

        $ticket->refresh();
        $this->assertSame('checked_in', $ticket->status);
        $this->assertSame($user->id, $ticket->verified_by_user_id);

        // 3. Verifikasi ulang tiket yang sudah digunakan harus gagal (mencegah check-in ganda)
        $reverifyResponse = $this->actingAs($user)
            ->post(route('tenant.validasi-tiket.verify', ['tenant' => $tenant->code]), [
                'ticket_id' => $ticket->id,
                'search_code' => 'TIX-VALID-77',
            ]);

        $reverifyResponse->assertSessionHas('error');
    }
}
