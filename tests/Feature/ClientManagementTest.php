<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Cliente;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class ClientManagementTest extends TestCase
{
    use RefreshDatabase;

    protected $admin;

    protected function setUp(): void
    {
        parent::setUp();
        // Seed the database to get roles and permissions
        $this->seed();

        // Get admin user
        $this->admin = User::where('email', 'admin@taller.com')->first();
    }

    public function test_admin_can_view_client_index()
    {
        $response = $this->actingAs($this->admin)->get(route('panel.operaciones.clientes.index'));
        $response->assertStatus(200);
        $response->assertViewIs('panel.operaciones.clientes.index');
    }

    public function test_admin_can_list_clients_json()
    {
        Cliente::factory()->count(5)->create();

        $response = $this->actingAs($this->admin)->get(route('panel.operaciones.clientes.list'));

        $response->assertStatus(200)
            ->assertJsonStructure(['current_page', 'data']);

        $this->assertCount(5, $response->json('data'));
    }

    public function test_admin_can_create_client()
    {
        $data = [
            'nombre_completo' => 'Juan Perez',
            'telefono' => '555-1234',
            'email' => 'juan@example.com',
            'nit' => '12345678',
            'direccion' => 'Calle Falsa 123',
            'es_empresa' => false
        ];

        $response = $this->actingAs($this->admin)->postJson(route('panel.operaciones.clientes.store'), $data);

        $response->assertStatus(200)
            ->assertJson(['success' => true]);

        $this->assertDatabaseHas('clientes', ['email' => 'juan@example.com']);
    }

    public function test_admin_can_update_client()
    {
        $cliente = Cliente::create([
            'nombre_completo' => 'Juan Perez',
            'telefono' => '555-1234',
            'es_empresa' => false
        ]);

        $data = [
            'nombre_completo' => 'Juan Perez Actualizado',
            'telefono' => '555-9876',
            'es_empresa' => false
        ];

        $response = $this->actingAs($this->admin)->putJson(route('panel.operaciones.clientes.update', $cliente->id), $data);

        $response->assertStatus(200)
            ->assertJson(['success' => true]);

        $this->assertDatabaseHas('clientes', ['nombre_completo' => 'Juan Perez Actualizado', 'telefono' => '555-9876']);
    }

    public function test_admin_cannot_delete_client_with_vehicles()
    {
        $cliente = Cliente::create([
            'nombre_completo' => 'Cliente Con Auto',
            'telefono' => '555-5555',
            'es_empresa' => false
        ]);

        // Create a vehicle for this client
        // We need Marca and Modelo seeded, identifying closest or creating
        $marca = \App\Models\MarcaVehiculo::first();
        $modelo = \App\Models\ModeloVehiculo::where('marca_id', $marca->id)->first();

        \App\Models\Vehiculo::create([
            'cliente_id' => $cliente->id,
            'marca_id' => $marca->id,
            'modelo_id' => $modelo->id,
            'placa' => 'ABC-123',
            'anio' => 2020
        ]);

        $response = $this->actingAs($this->admin)->deleteJson(route('panel.operaciones.clientes.destroy', $cliente->id));

        $response->assertStatus(422) // Or 422 as defined in controller
            ->assertJson(['success' => false]);

        $this->assertDatabaseHas('clientes', ['id' => $cliente->id]);
    }

    public function test_admin_can_delete_client_without_vehicles()
    {
        $cliente = Cliente::create([
            'nombre_completo' => 'Cliente Sin Auto',
            'telefono' => '555-0000',
            'es_empresa' => false
        ]);

        $response = $this->actingAs($this->admin)->deleteJson(route('panel.operaciones.clientes.destroy', $cliente->id));

        $response->assertStatus(200)
            ->assertJson(['success' => true]);

        $this->assertDatabaseMissing('clientes', ['id' => $cliente->id]);
    }
}
