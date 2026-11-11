<?php

namespace Tests\Feature;

use App\Models\Destination;
use App\Models\Tenant;
use App\Models\TicketType;
use App\Modules\Catalog\Services\PublicCatalogQuery;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Livewire\Livewire;
use Tests\TestCase;

class CatalogPublicTest extends TestCase
{
    use RefreshDatabase;

    /** @return array{0: Destination, 1: Tenant} */
    private function destinationWithTenant(string $status = 'active', string $slug = 'kawah-ijen'): array
    {
        $tenant = Tenant::factory()->create(['status' => $status]);
        $destination = Destination::create([
            'tenant_id' => $tenant->id,
            'name' => 'Kawah Ijen',
            'slug' => $slug,
            'operating_hours' => '00:00 - 23:59 WIB',
        ]);

        return [$destination, $tenant];
    }

    private function ticket(Tenant $tenant, Destination $destination, array $attrs = []): TicketType
    {
        return TicketType::create(array_merge([
            'tenant_id' => $tenant->id,
            'destination_id' => $destination->id,
            'name' => 'Tiket Masuk Reguler',
            'category' => 'Tiket Masuk',
            'price' => 25000,
            'is_available' => true,
            'stock_qty' => 50,
            'prep_minutes' => 5,
        ], $attrs));
    }

    public function test_catalog_shows_only_active_tenant_and_available_tickets(): void
    {
        [$dest, $tenant] = $this->destinationWithTenant();
        $this->ticket($tenant, $dest, ['name' => 'Tiket Masuk Reguler']);
        $this->ticket($tenant, $dest, ['name' => 'Tiket Habis', 'is_available' => false]);
        $this->ticket($tenant, $dest, ['name' => 'Tiket Kuota Kosong', 'stock_qty' => 0]);

        // Tenant lain yang tidak aktif tidak boleh muncul
        [$otherDest, $otherTenant] = $this->destinationWithTenant(status: 'suspended', slug: 'wisata-tutup');
        $this->ticket($otherTenant, $otherDest, ['name' => 'Tiket Wisata Tutup']);

        Livewire::test('catalog::ticket-catalog', ['canteenSlug' => $dest->slug])
            ->assertSee('Tiket Masuk Reguler')
            ->assertSeeInOrder(['data-sellable="0"', 'Tiket Habis', 'HABIS'], false)
            ->assertSeeInOrder(['data-sellable="0"', 'Tiket Kuota Kosong', 'HABIS'], false)
            ->assertSeeInOrder(['data-sellable="1"', 'Tiket Masuk Reguler'], false)
            ->assertDontSee('Tiket Wisata Tutup');
    }

    public function test_search_filters_catalog(): void
    {
        [$dest, $tenant] = $this->destinationWithTenant();
        $this->ticket($tenant, $dest, ['name' => 'Tiket Sunrise Tour']);
        $this->ticket($tenant, $dest, ['name' => 'Parkir Motor']);

        Livewire::test('catalog::ticket-catalog', ['canteenSlug' => $dest->slug])
            ->set('search', 'Sunrise')
            ->assertSee('Tiket Sunrise Tour')
            ->assertDontSee('Parkir Motor');
    }

    public function test_public_catalog_query_is_free_of_n_plus_one(): void
    {
        [$dest, $tenant] = $this->destinationWithTenant();
        for ($i = 0; $i < 20; $i++) {
            $this->ticket($tenant, $dest, ['name' => "Tiket Atraksi {$i}"]);
        }

        DB::flushQueryLog();
        DB::enableQueryLog();

        $tickets = app(PublicCatalogQuery::class)->browse($dest);
        $tickets->each(function (TicketType $ticket): void {
            // Akses relasi yang di-eager load - tidak boleh memicu query tambahan
            $ticket->tenant->name;
            $ticket->destination->name;
        });

        $count = count(DB::getQueryLog());
        DB::disableQueryLog();

        $this->assertLessThanOrEqual(6, $count, "Query terlalu banyak ({$count}) — indikasi N+1");
    }

    public function test_customer_landing_page_renders(): void
    {
        [$dest] = $this->destinationWithTenant();
        $this->get(route('customer.home', ['destination' => $dest->slug]))->assertOk();
    }
}
