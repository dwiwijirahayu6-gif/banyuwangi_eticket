<?php

namespace Tests\Feature\Admin;

use App\Models\AuditLog;
use App\Models\CommissionScheme;
use App\Models\Destination;
use App\Models\Tenant;
use App\Models\User;
use App\Models\UserTenantRole;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class TenantOnboardingTest extends TestCase
{
    use RefreshDatabase;

    private function createSuperAdmin(): User
    {
        return User::factory()->create([
            'role'              => 'super_admin',
            'status'            => 'active',
            'email_verified_at' => now(),
        ]);
    }

    private function createTenantAdmin(?Tenant $tenant = null): User
    {
        $tenant ??= Tenant::factory()->create(['status' => 'active']);

        return User::factory()->create([
            'tenant_id'         => $tenant->id,
            'role'              => 'tenant_admin',
            'status'            => 'active',
            'email_verified_at' => now(),
        ]);
    }

    /**
     * 1. Guest tidak dapat mengakses halaman Super Admin.
     */
    public function test_guest_cannot_access_super_admin_pages(): void
    {
        $this->get(route('admin.tenants.index'))->assertRedirect(route('login'));
        $this->get(route('admin.tenants.create'))->assertRedirect(route('login'));
    }

    /**
     * 2. Tenant Admin tidak dapat membuka form maupun menambahkan tenant baru.
     */
    public function test_tenant_admin_cannot_access_or_create_tenant(): void
    {
        $tenantAdmin = $this->createTenantAdmin();

        $this->actingAs($tenantAdmin)
            ->get(route('admin.tenants.index'))
            ->assertForbidden();

        $this->actingAs($tenantAdmin)
            ->get(route('admin.tenants.create'))
            ->assertForbidden();

        $this->actingAs($tenantAdmin)
            ->post(route('admin.tenants.store'), [
                'destination_name' => 'Wisata Ilegal',
                'location'         => 'Lokasi X',
                'description'      => 'Deskripsi X',
                'admin_name'       => 'Hacker',
                'admin_email'      => 'hacker@example.com',
                'admin_password'   => 'password123',
            ])
            ->assertForbidden();
    }

    /**
     * 3. Super Admin berhasil menambahkan destinasi baru beserta seluruh relasinya secara otomatis.
     */
    public function test_super_admin_can_successfully_onboard_new_destination_and_tenant(): void
    {
        Storage::fake('public');
        $superAdmin = $this->createSuperAdmin();

        $file = UploadedFile::fake()->image('kalibendo.jpg');

        $response = $this->actingAs($superAdmin)->post(route('admin.tenants.store'), [
            'destination_name'  => 'Air Terjun Kalibendo',
            'code'              => 'TEN-KALIBENDO',
            'location'          => 'Kecamatan Glagah, Banyuwangi',
            'description'       => 'Air terjun asri di kawasan perkebunan cengkeh dan kopi.',
            'operating_hours'   => '07:00 - 17:00 WIB',
            'facilities'        => 'Musholla, Gazebo, Warung',
            'safety_guidelines' => 'Gunakan sepatu anti licin.',
            'image'             => $file,
            'admin_name'        => 'Budi Santoso',
            'admin_email'       => 'admin.kalibendo@wisata.test',
            'admin_phone'       => '081234567890',
            'admin_password'    => 'Rahasia123!',
            'commission_rate'   => 7.5,
            'status'            => 'active',
        ]);

        $response->assertRedirect();

        // 1. Tenant otomatis dibuat
        $tenant = Tenant::where('code', 'TEN-KALIBENDO')->firstOrFail();
        $this->assertSame('Pengelola Air Terjun Kalibendo', $tenant->name);
        $this->assertSame('active', $tenant->status);

        // 2. Destinasi otomatis dibuat dengan tenant_id yang benar
        $destination = Destination::where('tenant_id', $tenant->id)->firstOrFail();
        $this->assertSame('Air Terjun Kalibendo', $destination->name);
        $this->assertSame('air-terjun-kalibendo', $destination->slug);
        $this->assertSame('Kecamatan Glagah, Banyuwangi', $destination->location);
        $this->assertNotNull($destination->image);

        // 3. Akun Tenant Admin otomatis dibuat
        $admin = User::where('email', 'admin.kalibendo@wisata.test')->firstOrFail();
        $this->assertSame('Budi Santoso', $admin->name);
        $this->assertSame('tenant_admin', $admin->role);
        $this->assertSame($tenant->id, $admin->tenant_id);
        $this->assertTrue(Hash::check('Rahasia123!', $admin->password));
        $this->assertSame('active', $admin->status);

        // 4. Role 'owner' terikat pada user_tenant_roles
        $this->assertDatabaseHas('user_tenant_roles', [
            'user_id'   => $admin->id,
            'tenant_id' => $tenant->id,
            'role'      => 'owner',
        ]);

        // 5. Skema komisi platform dibuat
        $commission = CommissionScheme::where('tenant_id', $tenant->id)->firstOrFail();
        $this->assertEquals(0.075, (float) $commission->commission_rate);

        // 6. Audit log tercatat
        $this->assertDatabaseHas('audit_logs', [
            'action' => 'CREATE_TENANT',
        ]);

        // 7. Destinasi muncul pada katalog publik
        $this->get(route('home'))
            ->assertOk()
            ->assertSee('Air Terjun Kalibendo');
    }

    /**
     * 4. Pendaftaran menolak email duplikat.
     */
    public function test_onboarding_rejects_duplicate_admin_email(): void
    {
        $superAdmin = $this->createSuperAdmin();

        User::factory()->create([
            'email' => 'terdaftar@wisata.test',
        ]);

        $response = $this->actingAs($superAdmin)->post(route('admin.tenants.store'), [
            'destination_name' => 'Wisata Indah',
            'location'         => 'Banyuwangi',
            'description'      => 'Deskripsi',
            'admin_name'       => 'Pengelola',
            'admin_email'      => 'terdaftar@wisata.test', // Email sudah ada
            'admin_password'   => 'password123',
        ]);

        $response->assertSessionHasErrors(['admin_email']);
    }

    /**
     * 5. Slug duplikat ditangani dengan aman agar tetap unik.
     */
    public function test_duplicate_destination_names_generate_unique_slugs(): void
    {
        $superAdmin = $this->createSuperAdmin();

        // Daftarkan wisata pertama
        $this->actingAs($superAdmin)->post(route('admin.tenants.store'), [
            'destination_name' => 'Pantai Cemara',
            'location'         => 'Pakis, Banyuwangi',
            'description'      => 'Pantai rindang',
            'admin_name'       => 'Admin 1',
            'admin_email'      => 'cemara1@wisata.test',
            'admin_password'   => 'password123',
        ]);

        // Daftarkan wisata kedua dengan nama yang persis sama
        $this->actingAs($superAdmin)->post(route('admin.tenants.store'), [
            'destination_name' => 'Pantai Cemara',
            'location'         => 'Pakis 2, Banyuwangi',
            'description'      => 'Pantai rindang kedua',
            'admin_name'       => 'Admin 2',
            'admin_email'      => 'cemara2@wisata.test',
            'admin_password'   => 'password123',
        ]);

        $slugs = Destination::where('name', 'Pantai Cemara')->pluck('slug')->toArray();
        $this->assertCount(2, $slugs);
        $this->assertNotEquals($slugs[0], $slugs[1]);
        $this->assertSame('pantai-cemara', $slugs[0]);
        $this->assertStringStartsWith('pantai-cemara-', $slugs[1]);
    }

    /**
     * 6. Destinasi dengan status nonaktif tidak muncul di katalog utama.
     */
    public function test_inactive_destination_does_not_appear_on_public_catalog(): void
    {
        $superAdmin = $this->createSuperAdmin();

        $this->actingAs($superAdmin)->post(route('admin.tenants.store'), [
            'destination_name' => 'Wisata Tersembunyi',
            'location'         => 'Banyuwangi',
            'description'      => 'Belum buka untuk umum',
            'admin_name'       => 'Admin Rahasia',
            'admin_email'      => 'rahasia@wisata.test',
            'admin_password'   => 'password123',
            'status'           => 'inactive',
        ]);

        $this->get(route('home'))
            ->assertOk()
            ->assertDontSee('Wisata Tersembunyi');
    }

    /**
     * 7. Jika salah satu proses pendaftaran gagal, seluruh transaksi di-rollback.
     */
    public function test_transaction_rolls_back_if_any_step_fails(): void
    {
        $superAdmin = $this->createSuperAdmin();

        $initialTenants = Tenant::count();
        $initialDestinations = Destination::count();
        $initialUsers = User::count();

        // Kita coba memicu kegagalan dengan mengoper data yang menyebabkan error pada proses simpan
        // Contoh: email admin dibuat sama persis dengan yang sudah ada di database tanpa lolos validasi jika dipanggil langsung ke service
        $existingUser = User::factory()->create(['email' => 'bentrok@wisata.test']);

        try {
            app(\App\Modules\Admin\Services\CreateTenant::class)->handle([
                'destination_name' => 'Wisata Gagal',
                'admin_name'       => 'Admin Gagal',
                'admin_email'      => 'bentrok@wisata.test', // Unique constraint violation di DB
                'admin_password'   => 'password123',
            ]);
        } catch (\Throwable $e) {
            // Expected database exception
        }

        // Pastikan tidak ada data orphan yang tersimpan
        $this->assertSame($initialTenants, Tenant::count());
        $this->assertSame($initialDestinations, Destination::count());
    }

    /**
     * 8. Isolasi data Multi-Tenant: Tenant A tidak dapat mengakses data Tenant B.
     */
    public function test_tenant_a_cannot_access_tenant_b_portal(): void
    {
        $tenantA = Tenant::factory()->create(['code' => 'TEN-A', 'status' => 'active']);
        $tenantB = Tenant::factory()->create(['code' => 'TEN-B', 'status' => 'active']);

        $userA = $this->createTenantAdmin($tenantA);
        UserTenantRole::create([
            'user_id'   => $userA->id,
            'tenant_id' => $tenantA->id,
            'role'      => 'owner',
        ]);

        // User A mengakses portal Tenant A -> Berhasil
        $this->actingAs($userA)
            ->get(route('tenant.dashboard', ['tenant' => $tenantA->code]))
            ->assertOk();

        // User A mencoba mengakses portal Tenant B -> Forbidden (403)
        $this->actingAs($userA)
            ->get(route('tenant.dashboard', ['tenant' => $tenantB->code]))
            ->assertForbidden();
    }

    /**
     * 9. Mengubah status tenant (Aktifkan, Nonaktifkan, Suspend).
     */
    public function test_super_admin_can_toggle_tenant_status(): void
    {
        $superAdmin = $this->createSuperAdmin();
        $tenant = Tenant::factory()->create(['status' => 'active']);

        // 1. Nonaktifkan
        $this->actingAs($superAdmin)
            ->post(route('admin.tenants.status', $tenant), ['status' => 'inactive'])
            ->assertRedirect();
        $this->assertSame('inactive', $tenant->fresh()->status);

        // 2. Suspend
        $this->actingAs($superAdmin)
            ->post(route('admin.tenants.status', $tenant), ['status' => 'suspended'])
            ->assertRedirect();
        $this->assertSame('suspended', $tenant->fresh()->status);

        // 3. Aktifkan kembali
        $this->actingAs($superAdmin)
            ->post(route('admin.tenants.status', $tenant), ['status' => 'active'])
            ->assertRedirect();
        $this->assertSame('active', $tenant->fresh()->status);
    }
}

