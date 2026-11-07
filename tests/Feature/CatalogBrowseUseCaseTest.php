<?php

namespace Tests\Feature;

use App\Models\Booking;
use App\Models\BookingItem;
use App\Models\Destination;
use App\Models\Tenant;
use App\Models\TicketType;
use App\Modules\Catalog\Services\WaitTimeEstimator;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Livewire\Livewire;
use Tests\TestCase;

/**
 * UC-01 Telusuri Destinasi & Tiket Wisata (kelompok per destinasi, cari nama destinasi/kategori, chip kategori,
 * destinasi tutup) dan UC-11 Hitung Estimasi Waktu Tunggu/Pelayanan.
 */
class CatalogBrowseUseCaseTest extends TestCase
{
    use RefreshDatabase;

    private function ticket(Tenant $tenant, Destination $dest, string $category, array $attrs = []): TicketType
    {
        return TicketType::create(array_merge([
            'tenant_id' => $tenant->id,
            'destination_id' => $dest->id,
            'category' => $category,
            'price' => 25000,
            'is_available' => true,
            'stock_qty' => 50,
            'prep_minutes' => 5,
        ], $attrs));
    }

    public function test_catalog_groups_by_destination_and_filters_by_name_and_category(): void
    {
        $tenantA = Tenant::factory()->create(['name' => 'Pengelola Kawah Ijen', 'status' => 'active']);
        $destA = Destination::create([
            'tenant_id' => $tenantA->id,
            'name' => 'Kawah Ijen Banyuwangi',
            'slug' => 'kawah-ijen',
            'operating_hours' => '00:00 - 23:59 WIB',
        ]);

        $tenantB = Tenant::factory()->create(['name' => 'Pengelola Pulau Merah', 'status' => 'active']);
        $destB = Destination::create([
            'tenant_id' => $tenantB->id,
            'name' => 'Pantai Pulau Merah',
            'slug' => 'pulau-merah',
            'operating_hours' => '00:00 - 23:59 WIB',
        ]);

        $this->ticket($tenantA, $destA, 'Tiket Masuk', ['name' => 'Tiket Masuk Kawah Ijen', 'description' => 'Akses pendakian']);
        $this->ticket($tenantB, $destB, 'Wahana & Atraksi', ['name' => 'Sewa Selancar']);

        Livewire::test('catalog::ticket-catalog')
            ->assertSeeInOrder(['Kawah Ijen Banyuwangi', 'Tiket Masuk Kawah Ijen', 'Pantai Pulau Merah', 'Sewa Selancar'])
            ->set('search', 'Selancar')->assertSee('Sewa Selancar')->assertDontSee('Tiket Masuk Kawah Ijen')
            ->set('search', '')->call('filterCategory', 'Wahana & Atraksi')->assertSee('Sewa Selancar')->assertDontSee('Tiket Masuk Kawah Ijen');
    }

    public function test_closed_destination_shows_opening_time_and_items_are_not_sellable(): void
    {
        $tenant = Tenant::factory()->create(['status' => 'active']);
        $dest = Destination::create([
            'tenant_id' => $tenant->id,
            'name' => 'Bangsring Underwater',
            'slug' => 'bangsring-underwater',
            'operating_hours' => '08:00 - 16:00 WIB',
        ]);

        $this->ticket($tenant, $dest, 'Tiket Masuk', ['name' => 'Tiket Masuk Bangsring']);

        // Set waktu di luar jam operasional (pukul 20.00 malam)
        $this->travelTo(Carbon::parse('2026-09-24 20:00', 'Asia/Jakarta'));

        Livewire::test('catalog::ticket-catalog')
            ->assertSee('Tutup · Buka pukul 08.00')
            ->assertSeeInOrder(['data-sellable="0"', 'Tiket Masuk Bangsring'], false);
    }

    public function test_wait_estimate_uses_active_queue_divided_by_capacity(): void
    {
        $tenant = Tenant::factory()->create(['status' => 'active']);
        $dest = Destination::create([
            'tenant_id' => $tenant->id,
            'name' => 'De Djawatan',
            'slug' => 'de-djawatan',
            'operating_hours' => '08:00 - 17:00 WIB',
        ]);

        $ticket = $this->ticket($tenant, $dest, 'Tiket Masuk', ['name' => 'Tiket Reguler', 'prep_minutes' => 6]);
        $estimator = app(WaitTimeEstimator::class);

        // Alur 1a: Tanpa antrean aktif -> hanya estimasi waktu pelayanan tiket (6 mnt -> 6-9 mnt)
        $this->assertSame(['low' => 6, 'high' => 9], $estimator->forDestination($dest));

        // Buat pesanan aktif mengantre
        $slotId = DB::table('visit_slots')->insertGetId([
            'tenant_id' => $tenant->id,
            'destination_id' => $dest->id,
            'visit_date' => now()->toDateString(),
            'quota_capacity' => 100,
            'quota_remaining' => 100,
        ]);

        $booking = Booking::create([
            'booking_code' => 'BWX-'.Str::upper(Str::random(6)),
            'customer_name' => 'Wisatawan Antrean',
            'customer_email' => 'wisatawan@example.com',
            'customer_phone' => '081234567890',
            'total_amount' => 50000,
            'status' => 'paid',
        ]);

        BookingItem::create([
            'booking_id' => $booking->id,
            'tenant_id' => $tenant->id,
            'ticket_type_id' => $ticket->id,
            'visit_slot_id' => $slotId,
            'quantity' => 2,
            'unit_price_snapshot' => 25000,
            'subtotal' => 50000,
        ]);

        // Antrean 2 tiket x 4 mnt / kapasitas 2 = 4 menit antrean. Ditambah 6 menit = 10 mnt (10-15 mnt)
        $this->assertSame(4.0, $estimator->queueMinutes($dest));
        $this->assertSame(['low' => 10, 'high' => 15], $estimator->forDestination($dest));
        $this->assertSame('± 10–15 mnt', $estimator->label($dest));
    }
}
