<?php

namespace Tests\Feature;

use App\Models\Destination;
use App\Services\BookingService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class ExampleTest extends TestCase
{
    use RefreshDatabase;

    private function createTestDestination(): array
    {
        $tenantId = DB::table('tenants')->insertGetId([
            'name' => 'Tenant Pengujian',
            'code' => 'TEST001',
            'status' => 'active',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $destinationId = DB::table('destinations')->insertGetId([
            'tenant_id' => $tenantId,
            'name' => 'Destinasi Pengujian',
            'slug' => 'destinasi-pengujian',
            'image' => null,
            'description' => 'Destinasi untuk pengujian',
            'location' => 'Banyuwangi',
            'operating_hours' => '08:00 - 16:00 WIB',
            'created_at' => now(),
        ]);

        $ticketTypeId = DB::table('ticket_types')->insertGetId([
            'tenant_id' => $tenantId,
            'destination_id' => $destinationId,
            'name' => 'Tiket Reguler',
            'price' => 25000,
            'created_at' => now(),
        ]);

        $today = now('Asia/Jakarta')->toDateString();

        DB::table('visit_slots')->insert([
            'tenant_id' => $tenantId,
            'destination_id' => $destinationId,
            'visit_date' => $today,
            'quota_capacity' => 100,
            'quota_remaining' => 100,
        ]);

        return [
            'destination' => Destination::findOrFail($destinationId),
            'ticket_type_id' => $ticketTypeId,
        ];
    }

    public function test_checkout_rejects_missing_required_fields(): void
    {
        $data = $this->createTestDestination();

        $response = $this->from('/')->post(
            '/destinasi/destinasi-pengujian/checkout',
            []
        );

        $response->assertRedirect('/');
        $response->assertSessionHasErrors([
            'visit_date',
            'customer_name',
            'customer_email',
            'customer_phone',
            'quantities',
        ]);
    }

    public function test_checkout_creates_booking_payment_and_reduces_quota(): void
    {
        $data = $this->createTestDestination();

        $booking = app(BookingService::class)->checkout(
            $data['destination'],
            [
                'visit_date' => now('Asia/Jakarta')->toDateString(),
                'customer_name' => 'Pengunjung Test',
                'customer_email' => 'TEST@EXAMPLE.COM',
                'customer_phone' => '081234567890',
                'quantities' => [
                    $data['ticket_type_id'] => 2,
                ],
            ]
        );

        $this->assertSame('pending_payment', $booking->status);
        $this->assertSame('test@example.com', $booking->customer_email);
        $this->assertEquals(50000, $booking->total_amount);

        $this->assertDatabaseHas('booking_items', [
            'booking_id' => $booking->id,
            'ticket_type_id' => $data['ticket_type_id'],
            'quantity' => 2,
            'unit_price_snapshot' => 25000,
            'subtotal' => 50000,
        ]);

        $this->assertDatabaseHas('payments', [
            'booking_id' => $booking->id,
            'gross_amount' => 50000,
            'status' => 'pending',
        ]);

        $this->assertDatabaseHas('visit_slots', [
            'destination_id' => $data['destination']->id,
            'visit_date' => now('Asia/Jakarta')->toDateString(),
            'quota_remaining' => 98,
        ]);
    }

    public function test_successful_payment_issues_tickets(): void
    {
        $data = $this->createTestDestination();

        $service = app(BookingService::class);

        $booking = $service->checkout(
            $data['destination'],
            [
                'visit_date' => now('Asia/Jakarta')->toDateString(),
                'customer_name' => 'Pengunjung Test',
                'customer_email' => 'test@example.com',
                'customer_phone' => '081234567890',
                'quantities' => [
                    $data['ticket_type_id'] => 2,
                ],
            ]
        );

        $paidBooking = $service->markAsPaid($booking);

        $this->assertSame('paid', $paidBooking->status);
        $this->assertNotNull($paidBooking->paid_at);

        $this->assertDatabaseHas('payments', [
            'booking_id' => $booking->id,
            'status' => 'settlement',
        ]);

        $this->assertDatabaseCount('tickets', 2);

        $this->assertDatabaseHas('tickets', [
            'booking_id' => $booking->id,
            'status' => 'issued',
        ]);
    }

    public function test_repeated_payment_does_not_duplicate_tickets(): void
    {
        $data = $this->createTestDestination();

        $service = app(BookingService::class);

        $booking = $service->checkout(
            $data['destination'],
            [
                'visit_date' => now('Asia/Jakarta')->toDateString(),
                'customer_name' => 'Pengunjung Test',
                'customer_email' => 'test@example.com',
                'customer_phone' => '081234567890',
                'quantities' => [
                    $data['ticket_type_id'] => 2,
                ],
            ]
        );

        $service->markAsPaid($booking);
        $service->markAsPaid($booking->fresh());

        $this->assertDatabaseCount('tickets', 2);

        $this->assertDatabaseHas('bookings', [
            'id' => $booking->id,
            'status' => 'paid',
        ]);
    }

    public function test_cancelling_pending_booking_returns_quota(): void
    {
        $data = $this->createTestDestination();
        $service = app(BookingService::class);

        $booking = $service->checkout(
            $data['destination'],
            [
                'visit_date' => now('Asia/Jakarta')->toDateString(),
                'customer_name' => 'Pengunjung Test',
                'customer_email' => 'test@example.com',
                'customer_phone' => '081234567890',
                'quantities' => [
                    $data['ticket_type_id'] => 2,
                ],
            ]
        );

        $this->assertDatabaseHas('visit_slots', [
            'destination_id' => $data['destination']->id,
            'quota_remaining' => 98,
        ]);

        $cancelledBooking = $service->cancel($booking, 'Salah memilih tanggal');

        $this->assertSame('cancelled', $cancelledBooking->status);
        $this->assertNotNull($cancelledBooking->cancelled_at);
        $this->assertSame('Salah memilih tanggal', $cancelledBooking->cancel_reason);

        $this->assertDatabaseHas('payments', [
            'booking_id' => $booking->id,
            'status' => 'failed',
        ]);

        $this->assertDatabaseHas('visit_slots', [
            'destination_id' => $data['destination']->id,
            'quota_remaining' => 100,
        ]);
    }

    public function test_expiring_pending_booking_returns_quota(): void
    {
        $data = $this->createTestDestination();
        $service = app(BookingService::class);

        $booking = $service->checkout(
            $data['destination'],
            [
                'visit_date' => now('Asia/Jakarta')->toDateString(),
                'customer_name' => 'Pengunjung Test',
                'customer_email' => 'test@example.com',
                'customer_phone' => '081234567890',
                'quantities' => [
                    $data['ticket_type_id'] => 2,
                ],
            ]
        );

        $booking->update([
            'expires_at' => now()->subMinute(),
        ]);

        $expired = $service->expireIfNeeded($booking->fresh());

        $this->assertTrue($expired);

        $this->assertDatabaseHas('bookings', [
            'id' => $booking->id,
            'status' => 'expired',
        ]);

        $this->assertDatabaseHas('payments', [
            'booking_id' => $booking->id,
            'status' => 'expire',
        ]);

        $this->assertDatabaseHas('visit_slots', [
            'destination_id' => $data['destination']->id,
            'quota_remaining' => 100,
        ]);
    }

    public function test_cancelled_booking_cannot_be_cancelled_twice(): void
    {
        $data = $this->createTestDestination();
        $service = app(BookingService::class);

        $booking = $service->checkout(
            $data['destination'],
            [
                'visit_date' => now('Asia/Jakarta')->toDateString(),
                'customer_name' => 'Pengunjung Test',
                'customer_email' => 'test@example.com',
                'customer_phone' => '081234567890',
                'quantities' => [
                    $data['ticket_type_id'] => 2,
                ],
            ]
        );

        $service->cancel($booking);

        $this->expectException(\DomainException::class);

        $service->cancel($booking->fresh());
    }

    public function test_paid_booking_can_be_cancelled_before_visit_date(): void
    {
        $data = $this->createTestDestination();
        $service = app(BookingService::class);

        $visitDate = now('Asia/Jakarta')
            ->addDay()
            ->toDateString();

        $booking = $service->checkout(
            $data['destination'],
            [
                'visit_date' => $visitDate,
                'customer_name' => 'Pengunjung Test',
                'customer_email' => 'test@example.com',
                'customer_phone' => '081234567890',
                'quantities' => [
                    $data['ticket_type_id'] => 2,
                ],
            ]
        );

        $booking = $service->markAsPaid($booking);

        $this->assertDatabaseCount('tickets', 2);

        $cancelledBooking = $service->cancel(
            $booking,
            'Perubahan rencana'
        );

        $this->assertSame('cancelled', $cancelledBooking->status);

        $this->assertDatabaseHas('tickets', [
            'booking_id' => $booking->id,
            'status' => 'cancelled',
        ]);

        $this->assertDatabaseHas('visit_slots', [
            'destination_id' => $data['destination']->id,
            'visit_date' => $visitDate,
            'quota_remaining' => 100,
        ]);
    }

    public function test_paid_booking_cannot_be_cancelled_after_check_in(): void
    {
        $data = $this->createTestDestination();
        $service = app(BookingService::class);

        $booking = $service->checkout(
            $data['destination'],
            [
                'visit_date' => now('Asia/Jakarta')
                    ->addDay()
                    ->toDateString(),
                'customer_name' => 'Pengunjung Test',
                'customer_email' => 'test@example.com',
                'customer_phone' => '081234567890',
                'quantities' => [
                    $data['ticket_type_id'] => 2,
                ],
            ]
        );

        $booking = $service->markAsPaid($booking);

        $ticket = $booking->tickets()->firstOrFail();
        $ticket->update(['status' => 'checked_in']);

        try {
            $service->cancel($booking->fresh());
            $this->fail('Pembatalan seharusnya ditolak karena tiket sudah digunakan.');
        } catch (\DomainException $exception) {
            $this->assertStringContainsString(
                'sudah digunakan',
                $exception->getMessage()
            );
        }

        $this->assertDatabaseHas('bookings', [
            'id' => $booking->id,
            'status' => 'paid',
        ]);

        $this->assertDatabaseHas('visit_slots', [
            'destination_id' => $data['destination']->id,
            'visit_date' => $booking->visit_date->toDateString(),
            'quota_remaining' => 98,
        ]);
    }

    public function test_paid_booking_cannot_be_cancelled_on_visit_date(): void
    {
        $data = $this->createTestDestination();
        $service = app(BookingService::class);

        $booking = $service->checkout(
            $data['destination'],
            [
                'visit_date' => now('Asia/Jakarta')->toDateString(),
                'customer_name' => 'Pengunjung Test',
                'customer_email' => 'test@example.com',
                'customer_phone' => '081234567890',
                'quantities' => [
                    $data['ticket_type_id'] => 2,
                ],
            ]
        );

        $booking = $service->markAsPaid($booking);

        $this->expectException(\DomainException::class);
        $this->expectExceptionMessage('paling lambat H-1');

        $service->cancel($booking);
    }
}
