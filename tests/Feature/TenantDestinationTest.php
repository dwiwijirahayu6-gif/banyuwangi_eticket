<?php

namespace Tests\Feature;

use App\Models\Destination;
use App\Models\Tenant;
use App\Models\User;
use App\Models\UserTenantRole;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class TenantDestinationTest extends TestCase
{
    use RefreshDatabase;

    private function createTenantEnvironment(string $name = 'Wisata Pantai Boom', string $code = 'TEN-BOOM'): array
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
            'description' => 'Keindahan pantai ikonik Banyuwangi dengan pemandangan selat Bali.',
            'location' => 'Kampung Mandar, Banyuwangi',
            'operating_hours' => '06:00 - 18:00 WIB',
            'facilities' => 'Toilet, Musholla, Food Court, Gazebo',
            'safety_guidelines' => 'Dilarang berenang di zona ombak tinggi',
        ]);

        return [$tenant, $user, $destination];
    }

    public function test_tenant_can_view_destination_profile_page(): void
    {
        [$tenant, $user, $destination] = $this->createTenantEnvironment();

        $response = $this->actingAs($user)
            ->get(route('tenant.destinasi', ['tenant' => $tenant->code]));

        $response->assertOk()
            ->assertSee('Manajemen Profil Destinasi Wisata')
            ->assertSee($destination->name)
            ->assertSee($destination->operating_hours)
            ->assertSee($destination->location);
    }

    public function test_tenant_can_update_destination_profile_information(): void
    {
        [$tenant, $user, $destination] = $this->createTenantEnvironment();

        $response = $this->actingAs($user)
            ->put(route('tenant.destinasi.update', ['tenant' => $tenant->code]), [
                'name' => 'Wisata Pantai Boom Marina Banyuwangi',
                'description' => 'Kawasan marina terintegrasi dengan dermaga yacht modern.',
                'location' => 'Jl. Ikan Kerapu, Kampung Mandar, Banyuwangi',
                'operating_hours' => '05:30 - 21:00 WIB',
                'facilities' => 'Amphitheater, Dermaga Yacht, Kafe Terapung, Toilet Ramah Difabel',
                'safety_guidelines' => 'Gunakan jaket pelampung saat naik perahu wisata',
            ]);

        $response->assertRedirect(route('tenant.destinasi', ['tenant' => $tenant->code]))
            ->assertSessionHas('success');

        $destination->refresh();

        $this->assertSame('Wisata Pantai Boom Marina Banyuwangi', $destination->name);
        $this->assertSame('Kawasan marina terintegrasi dengan dermaga yacht modern.', $destination->description);
        $this->assertSame('05:30 - 21:00 WIB', $destination->operating_hours);
        $this->assertStringContainsString('Dermaga Yacht', $destination->facilities);
    }

    public function test_tenant_can_upload_main_image_and_gallery_photos(): void
    {
        Storage::fake('public');

        [$tenant, $user, $destination] = $this->createTenantEnvironment();

        $mainImage = UploadedFile::fake()->image('main.jpg', 800, 600);
        $galleryPhoto1 = UploadedFile::fake()->image('photo1.jpg', 800, 600);
        $galleryPhoto2 = UploadedFile::fake()->image('photo2.jpg', 800, 600);

        $response = $this->actingAs($user)
            ->put(route('tenant.destinasi.update', ['tenant' => $tenant->code]), [
                'name' => $destination->name,
                'operating_hours' => $destination->operating_hours,
                'image' => $mainImage,
                'gallery_images' => [$galleryPhoto1, $galleryPhoto2],
            ]);

        $response->assertRedirect(route('tenant.destinasi', ['tenant' => $tenant->code]));

        $destination->refresh();

        $this->assertNotNull($destination->image);
        $this->assertIsArray($destination->gallery);
        $this->assertCount(2, $destination->gallery);

        // Pastikan berkas tersimpan di storage public
        $storedMainPath = str_replace('storage/', '', $destination->image);
        Storage::disk('public')->assertExists($storedMainPath);

        foreach ($destination->gallery as $galleryItem) {
            $storedGalleryPath = str_replace('storage/', '', $galleryItem);
            Storage::disk('public')->assertExists($storedGalleryPath);
        }
    }

    public function test_tenant_can_delete_photo_from_gallery(): void
    {
        Storage::fake('public');

        [$tenant, $user, $destination] = $this->createTenantEnvironment();

        // Siapkan 2 foto galeri
        $photo1 = UploadedFile::fake()->image('gal1.jpg');
        $path1 = $photo1->store('destinations/gallery', 'public');
        $photo2 = UploadedFile::fake()->image('gal2.jpg');
        $path2 = $photo2->store('destinations/gallery', 'public');

        $destination->update([
            'gallery' => [
                'storage/'.$path1,
                'storage/'.$path2,
            ],
        ]);

        Storage::disk('public')->assertExists($path1);
        Storage::disk('public')->assertExists($path2);

        // Hapus foto pertama (index 0)
        $response = $this->actingAs($user)
            ->delete(route('tenant.destinasi.gallery.delete', ['tenant' => $tenant->code, 'index' => 0]));

        $response->assertRedirect(route('tenant.destinasi', ['tenant' => $tenant->code]))
            ->assertSessionHas('success');

        $destination->refresh();

        $this->assertCount(1, $destination->gallery);
        $this->assertSame('storage/'.$path2, $destination->gallery[0]);
        Storage::disk('public')->assertMissing($path1);
        Storage::disk('public')->assertExists($path2);
    }

    public function test_multi_tenant_isolation_prevents_unauthorized_destination_edit(): void
    {
        [$tenantA, $userA, $destA] = $this->createTenantEnvironment('Wisata Ijen', 'TEN-IJEN');
        [$tenantB, $userB, $destB] = $this->createTenantEnvironment('Wisata Alas Purwo', 'TEN-PURWO');

        // User A mencoba mengakses dan mengedit destinasi Tenant B
        $response = $this->actingAs($userA)
            ->get(route('tenant.destinasi', ['tenant' => $tenantB->code]));

        $response->assertForbidden();

        $updateResponse = $this->actingAs($userA)
            ->put(route('tenant.destinasi.update', ['tenant' => $tenantB->code]), [
                'name' => 'Nama Diretas Oleh Tenant Lain',
                'operating_hours' => '24 Jam',
            ]);

        $updateResponse->assertForbidden();

        $destB->refresh();
        $this->assertSame('Wisata Alas Purwo', $destB->name);
    }
}
