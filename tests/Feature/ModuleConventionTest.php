<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\View;
use Livewire\Livewire;
use Tests\Fixtures\Modules\Probe\ProbeServiceProvider;
use Tests\TestCase;

class ModuleConventionTest extends TestCase
{
    use RefreshDatabase;

    private function user(string $role): User
    {
        return User::factory()->create([
            'role' => $role,
            'status' => 'active',
            'email_verified_at' => now(),
        ]);
    }

    private function registerProbeModule(): void
    {
        $this->app->register(ProbeServiceProvider::class);
        Route::getRoutes()->refreshNameLookups();
    }

    public function test_every_module_registers_its_own_view_namespace(): void
    {
        $hints = View::getFinder()->getHints();

        foreach (['Admin', 'Catalog', 'Ordering', 'Payments', 'Ticketing', 'Reporting'] as $module) {
            $alias = strtolower($module);
            $this->assertArrayHasKey($alias, $hints, "Namespace view {$alias}:: tidak terdaftar");

            $expected = str_replace('\\', '/', app_path("Modules/{$module}/resources/views"));
            $actual = array_map(
                fn ($path) => str_replace('\\', '/', $path),
                $hints[$alias]
            );

            $this->assertContains($expected, $actual, "Path modul {$module} tidak cocok");
        }
    }

    public function test_module_route_file_is_loaded_inside_portal_group(): void
    {
        $this->registerProbeModule();

        $route = Route::getRoutes()->getByName('tenant.probe');
        $this->assertNotNull($route, 'Route modul tidak dimuat oleh provider');
        $this->assertSame('tenant/{tenant}/probe', $route->uri());

        foreach (['web', 'auth', 'verified', 'role:tenant'] as $middleware) {
            $this->assertContains($middleware, $route->gatherMiddleware());
        }
    }

    public function test_module_route_is_guarded_like_core_portal_routes(): void
    {
        $this->registerProbeModule();

        $url = route('tenant.probe', ['tenant' => 'demo']);

        $this->get($url)->assertRedirect(route('login'));

        $this->actingAs($this->user('super_admin'));
        $this->get($url)->assertForbidden();

        $this->actingAs($this->user('tenant_admin'));
        $this->get($url)->assertOk()->assertSee('Probe page');
    }

    public function test_module_livewire_component_is_resolved_through_module_namespace(): void
    {
        $this->registerProbeModule();

        Livewire::test('probe::counter')
            ->assertSee('Probe count: 0')
            ->call('increment')
            ->assertSee('Probe count: 1');
    }
}