<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Models\Cliente;
use App\Models\Vehiculo;
use App\Models\MarcaVehiculo;
use App\Models\ModeloVehiculo;
use App\Models\Cita;

class WorkflowTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
        // dump(\App\Models\Sucursal::all()->toArray());
    }


    public function test_create_appointment_with_new_client_and_vehicle(): void
    {
        $admin = User::where('email', 'admin@taller.com')->first();

        $payload = [
            'modo_creacion' => 'nuevo',
            'nombre_nuevo' => 'Juan Perez',
            'telefono_nuevo' => '5551234567',
            'email_nuevo' => 'juan@test.com',
            'marca_nuevo' => 'Toyota',
            'modelo_nuevo' => 'Corolla', // Optional but good to test
            'placa_nuevo' => 'P-123XYZ',
            'anio_nuevo' => 2020,
            'fecha' => '2026-03-01',
            'hora' => '10:00',
            'motivo' => 'Servicio de Frenos'
        ];

        $response = $this->actingAs($admin)
            ->postJson(route('panel.operaciones.citas.store'), $payload);

        $response->assertStatus(200)
            ->assertJson(['success' => true]);

        // Verify Database
        $this->assertDatabaseHas('clientes', [
            'nombre_completo' => 'Juan Perez',
            'telefono' => '5551234567',
            'email' => 'juan@test.com'
        ]);

        $this->assertDatabaseHas('vehiculos', [
            'placa' => 'P123XYZ' // Logicuppercases and removes dashes
        ]);

        $this->assertDatabaseHas('citas', [
            'motivo_cita' => 'Servicio de Frenos',
            'fecha_programada' => '2026-03-01 10:00:00'
        ]);
    }

    public function test_create_appointment_with_existing_client_and_vehicle(): void
    {
        $admin = User::where('email', 'admin@taller.com')->first();

        // Create pre-existing data
        $cliente = Cliente::create([
            'nombre_completo' => 'Maria Lopez',
            'telefono' => '999888777'
        ]);

        $marca = MarcaVehiculo::firstOrCreate(['nombre' => 'Honda']);
        $modelo = ModeloVehiculo::firstOrCreate(['marca_id' => $marca->id, 'nombre' => 'Civic']);

        $vehiculo = Vehiculo::create([
            'cliente_id' => $cliente->id,
            'marca_id' => $marca->id,
            'modelo_id' => $modelo->id,
            'placa' => 'M-987ABC',
            'anio' => 2019
        ]);

        $payload = [
            'modo_creacion' => 'existente',
            'cliente_id' => $cliente->id,
            'vehiculo_id' => $vehiculo->id,
            'fecha' => '2026-03-02',
            'hora' => '14:00',
            'motivo' => 'Cambio de Aceite'
        ];

        $response = $this->actingAs($admin)
            ->postJson(route('panel.operaciones.citas.store'), $payload);

        $response->assertStatus(200)
            ->assertJson(['success' => true]);

        $this->assertDatabaseHas('citas', [
            'cliente_id' => $cliente->id,
            'vehiculo_id' => $vehiculo->id,
            'motivo_cita' => 'Cambio de Aceite'
        ]);
    }
    public function test_create_work_order_from_appointment(): void
    {
        $admin = User::where('email', 'admin@taller.com')->first();

        // Setup Data
        $cliente = Cliente::create(['nombre_completo' => 'Test User', 'telefono' => '1231235']);
        $marca = MarcaVehiculo::firstOrCreate(['nombre' => 'Ford']);
        $modelo = ModeloVehiculo::firstOrCreate(['marca_id' => $marca->id, 'nombre' => 'Ranger']);
        $vehiculo = Vehiculo::create([
            'cliente_id' => $cliente->id,
            'marca_id' => $marca->id,
            'modelo_id' => $modelo->id,
            'placa' => 'F-001',
            'anio' => 2022
        ]);

        $cita = Cita::create([
            'cliente_id' => $cliente->id,
            'vehiculo_id' => $vehiculo->id,
            'sucursal_id' => 1,
            'fecha_programada' => now(),
            'motivo_cita' => 'Servicio Completo',
            'estado' => 'confirmada'
        ]);

        $payload = [
            'cita_id' => $cita->id,
            'cliente_id' => $cliente->id,
            'vehiculo_id' => $vehiculo->id,
            'kilometraje' => 50000,
            'nivel_combustible' => 'Medio',
            'falla_cliente' => 'Ruido en motor',
            'color' => 'Rojo',
            'new_vehiculo' => [ // Controller validation requires these even if existing
                'placa' => 'F-001',
                'marca' => 'Ford',
                'modelo' => 'Ranger',
                'anio' => 2022
            ],
            'tipo_orden' => 'normal'
        ];

        try {
            $response = $this->actingAs($admin)
                ->postJson(route('panel.operaciones.ordenes_trabajo.store'), $payload);

            if ($response->status() !== 200) {
                dd($response->json());
            }

            $response->assertStatus(200)
                ->assertJson(['success' => true]);
        } catch (\Exception $e) {
            dump($e->getMessage());
            throw $e;
        }


        $this->assertDatabaseHas('ordenes_trabajo', [
            'cita_id' => $cita->id,
            'vehiculo_id' => $vehiculo->id,
            'falla_cliente' => 'Ruido en motor',
            'estado' => 'abierta'
        ]);
    }
}
