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

class TenantReportingAndExportTest extends TestCase
{
    use RefreshDatabase;

    private function createTenantEnvironment(): array
    {
        $tenant = Tenant::factory()->create([
            'name' => 'Wisata Teluk Hijau',
            'code' => 'TEN-THIJAU',
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
            'name' => 'Pantai Teluk Hijau',
            'slug' => 'pantai-teluk-hijau',
            'operating_hours' => '07:00 - 16:30 WIB',
        ]);

        $ticketType = TicketType::create([
            'tenant_id' => $tenant->id,
            'destination_id' => $destination->id,
            'name' => 'Tiket Masuk Teluk Hijau',
            'price' => 15000,
        ]);

        $slot = VisitSlot::create([
            'tenant_id' => $tenant->id,
            'destination_id' => $destination->id,
            'visit_date' => Carbon::today('Asia/Jakarta')->toDateString(),
            'quota_capacity' => 300,
            'quota_remaining' => 300,
        ]);

        return [$tenant, $user, $destination, $ticketType, $slot];
    }

    public function test_tenant_can_view_sales_report(): void
    {
        [$tenant, $user, $destination, $ticketType, $slot] = $this->createTenantEnvironment();

        $booking = Booking::create([
            'booking_code' => 'BWI-REP-01',
            'customer_name' => 'Rina Wijaya',
            'customer_email' => 'rina@example.com',
            'customer_phone' => '081234567890',
            'total_amount' => 30000,
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
            'unit_price_snapshot' => 15000,
            'subtotal' => 30000,
        ]);

        $response = $this->actingAs($user)
            ->get(route('tenant.laporan', ['tenant' => $tenant->code]));

        $response->assertOk()
            ->assertSee('Laporan Operasional')
            ->assertSee('Rp 30.000')
            ->assertSee('2');
    }

    public function test_tenant_can_export_report_to_excel_and_pdf(): void
    {
        [$tenant, $user, $destination, $ticketType, $slot] = $this->createTenantEnvironment();

        // 1. Ekspor Excel / CSV
        $excelResponse = $this->actingAs($user)
            ->get(route('tenant.laporan.export.excel', ['tenant' => $tenant->code]));

        $excelResponse->assertOk();
        $this->assertStringContainsString('text/csv', $excelResponse->headers->get('Content-Type'));

        // 2. Ekspor PDF
        $pdfResponse = $this->actingAs($user)
            ->get(route('tenant.laporan.export.pdf', ['tenant' => $tenant->code]));

        $pdfResponse->assertOk();
        $this->assertSame('application/pdf', $pdfResponse->headers->get('Content-Type'));
    }
}
