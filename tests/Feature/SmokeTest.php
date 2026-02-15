<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;

class SmokeTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
    }

    public function test_the_application_returns_a_successful_response(): void
    {
        $response = $this->get('/');
        $response->assertStatus(200);
    }

    public function test_login_page_loads(): void
    {
        $response = $this->get('/login');
        $response->assertStatus(200);
    }

    public function test_admin_can_login_and_access_dashboard(): void
    {
        // Simulate Admin User
        $user = User::where('email', 'admin@taller.com')->first();
        if (!$user) {
            $this->markTestSkipped('Admin user not found. Run seeders first.');
        }

        $response = $this->actingAs($user)->get('/panel');
        $response->assertStatus(200);
        $response->assertSee('Dashboard'); // Assuming 'Dashboard' is in the view
    }

    public function test_admin_can_access_citas_module(): void
    {
        $user = User::where('email', 'admin@taller.com')->first();
        if (!$user) return;

        $response = $this->actingAs($user)->get(route('panel.operaciones.citas.index'));
        $response->assertStatus(200);
    }

    public function test_admin_can_access_ordenes_trabajo_module(): void
    {
        $user = User::where('email', 'admin@taller.com')->first();
        if (!$user) return;

        $response = $this->actingAs($user)->get(route('panel.operaciones.ordenes_trabajo.index'));
        $response->assertStatus(200);
    }

    public function test_admin_can_access_repuestos_module(): void
    {
        $user = User::where('email', 'admin@taller.com')->first();
        if (!$user) return;

        $response = $this->actingAs($user)->get(route('panel.mantenimientos.repuestos.index'));
        $response->assertStatus(200);
    }

    public function test_admin_can_access_inventory_report(): void
    {
        $user = User::where('email', 'admin@taller.com')->first();
        if (!$user) return;

        $response = $this->actingAs($user)->get(route('panel.mantenimientos.reportes.inventario-por-sucursal'));
        $response->assertStatus(200);
    }
}
