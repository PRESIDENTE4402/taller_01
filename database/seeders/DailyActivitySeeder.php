<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DailyActivitySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1. Create New Clients (1-3)
        $newClientes = \App\Models\Cliente::factory()->count(rand(1, 3))->create();

        // 2. Create Vehicles for some existing or new clients (2-4)
        $clientes = \App\Models\Cliente::inRandomOrder()->take(5)->get(); // Mix of new and old
        foreach ($clientes as $cliente) {
            if (rand(0, 1)) {
                \App\Models\Vehiculo::factory()->create(['cliente_id' => $cliente->id]);
            }
        }

        // 3. Create Appointments (Citas) for Today and Tomorrow (3-6)
        $vehiculos = \App\Models\Vehiculo::inRandomOrder()->take(10)->get();
        if ($vehiculos->isEmpty()) return;

        foreach (range(1, rand(3, 6)) as $i) {
            $vehiculo = $vehiculos->random();
            \App\Models\Cita::factory()->create([
                'cliente_id' => $vehiculo->cliente_id,
                'vehiculo_id' => $vehiculo->id,
                'fecha_programada' => \Carbon\Carbon::today()->addHours(rand(8, 17)),
                'estado' => 'pendiente' // Some will be converted to OT
            ]);
        }

        // 4. Create Work Orders (Ordenes de Trabajo) - "Walk-ins" or Processed Citas (2-4)
        // Let's create some direct Work Orders (Walk-ins)
        foreach (range(1, rand(2, 4)) as $i) {
            $vehiculo = $vehiculos->random();
            \App\Models\OrdenTrabajo::factory()->create([
                'cliente_id' => $vehiculo->cliente_id,
                'vehiculo_id' => $vehiculo->id,
                'cita_id' => null, // Walk-in
                'estado' => \Illuminate\Support\Arr::random(['abierta', 'en_proceso', 'espera_repuesto']),
                'fecha_recepcion' => \Carbon\Carbon::now()->subHours(rand(1, 4)),
            ]);
        }

        // 5. Convert some Citas to Orders (Simulate Arrival)
        $citas = \App\Models\Cita::whereDate('fecha_programada', \Carbon\Carbon::today())->take(2)->get();
        foreach ($citas as $cita) {
            if (rand(0, 1)) {
                $cita->update(['estado' => 'confirmada']); // Or 'atendida' if we create OT

                \App\Models\OrdenTrabajo::factory()->create([
                    'cliente_id' => $cita->cliente_id,
                    'vehiculo_id' => $cita->vehiculo_id,
                    'cita_id' => $cita->id,
                    'estado' => 'abierta',
                    'fecha_recepcion' => now(),
                    'falla_cliente' => $cita->motivo_cita,
                ]);

                $cita->update(['estado' => 'atendida']);
            }
        }
    }
}
