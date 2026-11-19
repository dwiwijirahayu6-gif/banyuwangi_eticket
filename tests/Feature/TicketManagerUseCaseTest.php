<?php

namespace Tests\Feature;

use App\Models\Destination;
use App\Models\Tenant;
use App\Models\TicketType;
use App\Models\User;
use App\Models\UserTenantRole;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Tests\TestCase;

/**
 * UC-13 Kelola Tiket & Stok (foto WebP <= 2 MB, validasi per kolom, ubah, soft delete, cari/saring)
 * dan UC-14 Tandai Tiket Habis (sakelar satu ketukan).
 */
class TicketManagerUseCaseTest extends TestCase
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
            'name' => 'Taman Nasional Baluran',
            'slug' => 'tn-baluran',
            'operating_hours' => '07:30 - 16:00 WIB',
        ]);

        return [$tenant, $user, $dest];
    }

    public function test_photo_is_compressed_to_webp_and_description_is_saved(): void
    {
        Storage::fake('public');
        [$tenant, $user, $dest] = $this->tenantWithMember();

        Livewire::actingAs($user)
            ->test('catalog::ticket-manager', ['tenantId' => $tenant->id])
            ->set('name', 'Tiket Masuk Savana Bekol')
            ->set('description', 'Tiket masuk area savana dan pantai bama')
            ->set('destinationId', $dest->id)
            ->set('category', 'Tiket Masuk')
            ->set('basePrice', 30000)
            ->set('prepMinutes', 5)
            ->set('stockQty', 200)
            ->set('photo', UploadedFile::fake()->image('baluran.jpg', 1600, 1200))
            ->call('createTicket')
            ->assertHasNoErrors();

        $ticket = TicketType::withoutGlobalScope('tenant')->where('name', 'Tiket Masuk Savana Bekol')->firstOrFail();
        $this->assertSame('Tiket masuk area savana dan pantai bama', $ticket->description);
        $this->assertSame(5, $ticket->prep_minutes);
        $this->assertStringEndsWith('.webp', $ticket->photo_path);

        Storage::disk('public')->assertExists($ticket->photo_path);
        $this->assertSame('image/webp', mime_content_type(Storage::disk('public')->path($ticket->photo_path)));

        [$width] = getimagesize(Storage::disk('public')->path($ticket->photo_path));
        $this->assertSame(800, $width, 'Sisi terpanjang diperkecil ke 800 px');
    }

    public function test_validation_errors_are_per_field_and_input_is_kept(): void
    {
        Storage::fake('public');
        [$tenant, $user] = $this->tenantWithMember();

        Livewire::actingAs($user)
            ->test('catalog::ticket-manager', ['tenantId' => $tenant->id])
            ->set('name', 'Tiket Snorkeling')
            ->set('destinationId', null)
            ->set('basePrice', 0)
            ->set('photo', UploadedFile::fake()->create('besar.jpg', 3000, 'image/jpeg'))
            ->call('createTicket')
            ->assertHasErrors(['basePrice' => 'min', 'destinationId' => 'required', 'photo'])
            ->assertSet('name', 'Tiket Snorkeling');
    }

    public function test_edit_updates_ticket_and_delete_is_soft(): void
    {
        [$tenant, $user, $dest] = $this->tenantWithMember();
        $ticket = TicketType::create([
            'tenant_id' => $tenant->id,
            'destination_id' => $dest->id,
            'name' => 'Tiket Parkir Mobil',
            'category' => 'Parkir & Fasilitas',
            'price' => 10000,
            'stock_qty' => 50,
            'prep_minutes' => 2,
            'is_available' => true,
        ]);

        Livewire::actingAs($user)
            ->test('catalog::ticket-manager', ['tenantId' => $tenant->id])
            ->call('edit', $ticket->id)
            ->assertSet('name', 'Tiket Parkir Mobil')
            ->set('basePrice', 15000)
            ->call('updateTicket')
            ->assertHasNoErrors()
            ->call('deleteTicket', $ticket->id);

        $this->assertSame(15000, (int) TicketType::withTrashed()->withoutGlobalScope('tenant')->find($ticket->id)->price);
        $this->assertSoftDeleted('ticket_types', ['id' => $ticket->id]);
    }

    public function test_search_and_category_filter_and_one_tap_toggle(): void
    {
        [$tenant, $user, $dest] = $this->tenantWithMember();
        $reguler = TicketType::create([
            'tenant_id' => $tenant->id,
            'destination_id' => $dest->id,
            'name' => 'Tiket Masuk Domestik',
            'category' => 'Tiket Masuk',
            'price' => 25000,
            'stock_qty' => 100,
            'is_available' => true,
        ]);
        $parkir = TicketType::create([
            'tenant_id' => $tenant->id,
            'destination_id' => $dest->id,
            'name' => 'Parkir Bus',
            'category' => 'Parkir & Fasilitas',
            'price' => 20000,
            'stock_qty' => 10,
            'is_available' => true,
        ]);

        $component = Livewire::actingAs($user)
            ->test('catalog::ticket-manager', ['tenantId' => $tenant->id])
            ->set('search', 'Domestik')->assertSee('Tiket Masuk Domestik')->assertDontSee('Parkir Bus')
            ->set('search', '')->set('filterCategory', 'Parkir & Fasilitas')->assertSee('Parkir Bus')->assertDontSee('Tiket Masuk Domestik')
            ->set('filterCategory', null)
            ->call('toggle', $reguler->id);

        $this->assertFalse($reguler->fresh()->is_available);
        $component->assertSee('HABIS');
    }
}
