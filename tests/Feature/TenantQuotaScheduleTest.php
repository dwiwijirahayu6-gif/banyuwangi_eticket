<?php

namespace Tests\Feature;

use App\Models\Booking;
use App\Models\BookingItem;
use App\Models\Destination;
use App\Models\Tenant;
use App\Models\TicketType;
use App\Models\User;
use App\Models\UserTenantRole;
use App\Models\VisitSlot;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TenantQuotaScheduleTest extends TestCase
{
    use RefreshDatabase;

    private function createTenantEnvironment(): array
    {
        $tenant = Tenant::factory()->create([
            'name' => 'Wisata Air Terjun Jagir',
            'code' => 'TEN-JAGIR',
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
            'name' => 'Air Terjun Jagir',
            'slug' => 'air-terjun-jagir',
            'operating_hours' => '07:00 - 17:00 WIB',
        ]);

        return [$tenant, $user, $destination];
    }

    public function test_tenant_can_view_quota_and_schedule_page(): void
    {
        [$tenant, $user, $destination] = $this->createTenantEnvironment();

        VisitSlot::create([
            'tenant_id' => $tenant->id,
            'destination_id' => $destination->id,
            'visit_date' => Carbon::tomorrow('Asia/Jakarta')->toDateString(),
            'quota_capacity' => 200,
            'quota_remaining' => 150,
            'is_active' => true,
        ]);

        $response = $this->actingAs($user)
            ->get(route('tenant.jadwal-kuota', ['tenant' => $tenant->code]));

        $response->assertOk()
            ->assertSee('Manajemen Kuota dan Jadwal Kunjungan')
            ->assertSee('200')
            ->assertSee('150')
            ->assertSee(Carbon::tomorrow('Asia/Jakarta')->format('Y-m-d'));
    }

    public function test_tenant_can_create_and_update_quota(): void
    {
        [$tenant, $user, $destination] = $this->createTenantEnvironment();
        $targetDate = Carbon::today('Asia/Jakarta')->addDays(3)->toDateString();

        // 1. Simpan kuota baru
        $response = $this->actingAs($user)
            ->post(route('tenant.jadwal-kuota.store', ['tenant' => $tenant->code]), [
                'visit_date' => $targetDate,
                'quota_capacity' => 300,
            ]);

        $response->assertRedirect(route('tenant.jadwal-kuota', ['tenant' => $tenant->code]))
            ->assertSessionHas('success');

        $slot = VisitSlot::where('destination_id', $destination->id)
            ->whereDate('visit_date', $targetDate)
            ->firstOrFail();

        $this->assertSame(300, $slot->quota_capacity);
        $this->assertSame(300, $slot->quota_remaining);

        // 2. Perbarui kuota
        $updateResponse = $this->actingAs($user)
            ->put(route('tenant.jadwal-kuota.update', ['tenant' => $tenant->code, 'visitSlot' => $slot->id]), [
                'quota_capacity' => 450,
            ]);

        $updateResponse->assertRedirect(route('tenant.jadwal-kuota', ['tenant' => $tenant->code]))
            ->assertSessionHas('success');

        $slot->refresh();
        $this->assertSame(450, $slot->quota_capacity);
        $this->assertSame(450, $slot->quota_remaining);
    }

    public function test_tenant_cannot_reduce_quota_below_booked_tickets(): void
    {
        [$tenant, $user, $destination] = $this->createTenantEnvironment();
        $targetDate = Carbon::today('Asia/Jakarta')->addDays(5)->toDateString();

        $slot = VisitSlot::create([
            'tenant_id' => $tenant->id,
            'destination_id' => $destination->id,
            'visit_date' => $targetDate,
            'quota_capacity' => 100,
            'quota_remaining' => 40, // 60 sudah dipesan
            'is_active' => true,
        ]);

        // Coba turunkan kapasitas kuota menjadi 50 (padahal sudah dipesan 60)
        $response = $this->actingAs($user)
            ->put(route('tenant.jadwal-kuota.update', ['tenant' => $tenant->code, 'visitSlot' => $slot->id]), [
                'quota_capacity' => 50,
            ]);

        $response->assertSessionHas('error');

        $slot->refresh();
        $this->assertSame(100, $slot->quota_capacity); // Tidak boleh berubah
    }

    public function test_tenant_can_toggle_schedule_active_status(): void
    {
        [$tenant, $user, $destination] = $this->createTenantEnvironment();

        $slot = VisitSlot::create([
            'tenant_id' => $tenant->id,
            'destination_id' => $destination->id,
            'visit_date' => Carbon::tomorrow('Asia/Jakarta')->toDateString(),
            'quota_capacity' => 100,
            'quota_remaining' => 100,
            'is_active' => true,
        ]);

        $response = $this->actingAs($user)
            ->post(route('tenant.jadwal-kuota.toggle', ['tenant' => $tenant->code, 'visitSlot' => $slot->id]));

        $response->assertRedirect(route('tenant.jadwal-kuota', ['tenant' => $tenant->code]));

        $slot->refresh();
        $this->assertFalse($slot->is_active);
    }
}
