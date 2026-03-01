<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Cliente;
use App\Models\Vehiculo;
use App\Models\Cita;
use App\Models\OrdenTrabajo;
use App\Models\BitacoraTrabajo;
use App\Models\User;
use App\Models\Sucursal;
use App\Models\Role;
use Carbon\Carbon;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;

class DailyActivitySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1. Obtener Entidades Base (Sucursal, Usuarios)
        $sucursal = Sucursal::first() ?? Sucursal::factory()->create();

        $roleSecretario = Role::where('slug', 'secretario')->first();
        $secretarios = User::whereHas('roles', function ($q) use ($roleSecretario) {
            $q->where('roles.id', $roleSecretario->id ?? -1);
        })->get();

        $receptor = $secretarios->count() > 0 ? $secretarios->random() : User::first();

        $roleMecanico = Role::where('slug', 'mecanico')->first();
        $mecanicos = User::whereHas('roles', function ($q) use ($roleMecanico) {
            $q->where('roles.id', $roleMecanico->id ?? -1);
        })->get();

        // 2. Crear Clientes y sus Vehículos (Si no hay suficientes, creamos nuevos)
        $clientes = Cliente::inRandomOrder()->take(5)->get();
        if ($clientes->count() < 3) {
            $clientes = Cliente::factory()->count(5)->create(['situacion' => 'activo']);
        }

        $vehiculosCreados = collect();
        foreach ($clientes as $cliente) {
            if ($cliente->vehiculos->count() == 0 || rand(0, 1)) {
                $v = Vehiculo::factory()->create([
                    'cliente_id' => $cliente->id,
                    'situacion' => 'activo'
                ]);
                $vehiculosCreados->push($v);
            } else {
                $vehiculosCreados->push($cliente->vehiculos->random());
            }
        }

        // 3. Crear Citas Agendadas (Para Hoy y Mañana)
        foreach (range(1, 5) as $i) {
            $vehiculo = $vehiculosCreados->random();
            $dias = rand(0, 1); // 0 = Hoy, 1 = Mañana
            $hora = rand(8, 16);

            Cita::factory()->create([
                'cliente_id' => $vehiculo->cliente_id,
                'vehiculo_id' => $vehiculo->id,
                'sucursal_id' => $sucursal->id,
                'fecha_programada' => Carbon::today()->addDays($dias)->addHours($hora),
                'estado' => rand(0, 1) ? 'confirmada' : 'pendiente',
                'origen' => Arr::random(['web', 'telefono', 'presencial']),
            ]);
        }

        // 4. Crear Órdenes de Trabajo (Walk-ins y Citas Atendidas Hoy)

        $lastId = OrdenTrabajo::max('id') ?? 0;

        // Simular 2 clientes que llegaron directo (Walk-in)
        foreach (range(1, 2) as $i) {
            $vehiculo = $vehiculosCreados->random();
            $lastId++;
            $codigo = 'OT-' . date('Y') . '-' . str_pad($lastId, 4, '0', STR_PAD_LEFT);

            $orden = OrdenTrabajo::factory()->create([
                'sucursal_id' => $sucursal->id,
                'codigo_orden' => $codigo,
                'cliente_id' => $vehiculo->cliente_id,
                'vehiculo_id' => $vehiculo->id,
                'cita_id' => null,
                'receptor_id' => $receptor->id,
                'estado' => Arr::random(['abierta', 'en_proceso']),
                'fecha_recepcion' => Carbon::today()->addHours(rand(7, 10)),
                'color' => Arr::random(['Blanco', 'Rojo', 'Negro', 'Gris', 'Azul']),
                'kilometraje_entrada' => rand(15000, 150000),
                'nivel_combustible' => Arr::random(['E', '1/4', '1/2', '3/4', 'F']),
                'inventario_recepcion' => json_encode(['tarjeta_circulacion' => true, 'llave_repuesto' => false, 'radio' => true]),
                'danos_reportados' => json_encode("Llegó con rayón leve en bumper delantero"),
            ]);

            // Asignar a un mecánico si la orden ya está en proceso
            if ($orden->estado === 'en_proceso' && $mecanicos->count() > 0) {
                BitacoraTrabajo::create([
                    'user_id' => $mecanicos->random()->id,
                    'sucursal_id' => $sucursal->id,
                    'orden_trabajo_id' => $orden->id,
                    'tipo_actividad' => 'diagnostico',
                    'descripcion' => 'Revisión inicial del vehículo por ruidos en motor.',
                    'inicio' => Carbon::now()->subMinutes(rand(30, 90)),
                    'estado' => 'en_progreso'
                ]);
            }
        }

        // Simular que 3 Citas de Hoy ya llegaron y se concretaron (Convirtiéndose en OT)
        $citasHoy = Cita::whereDate('fecha_programada', Carbon::today())
            ->whereIn('estado', ['confirmada', 'pendiente'])
            ->take(3)
            ->get();

        foreach ($citasHoy as $cita) {
            $lastId++;
            $codigo = 'OT-' . date('Y') . '-' . str_pad($lastId, 4, '0', STR_PAD_LEFT);

            // Determinar un estado para esta orden (alguna finalizada, otras en proceso)
            $estadoOT = Arr::random(['en_proceso', 'espera_repuesto', 'finalizada']);

            $fechaRecepcion = $cita->fecha_programada->copy()->subMinutes(rand(0, 15));
            $fechaFin = $estadoOT === 'finalizada' ? $fechaRecepcion->copy()->addHours(rand(2, 5)) : null;

            $orden = OrdenTrabajo::factory()->create([
                'sucursal_id' => $sucursal->id,
                'codigo_orden' => $codigo,
                'cliente_id' => $cita->cliente_id,
                'vehiculo_id' => $cita->vehiculo_id,
                'cita_id' => $cita->id,
                'receptor_id' => $receptor->id,
                'estado' => $estadoOT,
                'fecha_recepcion' => $fechaRecepcion,
                'fecha_finalizacion' => $fechaFin,
                'falla_cliente' => $cita->motivo_cita,
                'color' => Arr::random(['Plateado', 'Negro', 'Azul', 'Verde']),
                'kilometraje_entrada' => rand(5000, 120000),
                'nivel_combustible' => Arr::random(['1/2', '3/4', 'F']),
                'inventario_recepcion' => json_encode(['alfombras' => 4, 'llanta_repuesto' => 1]),
                'danos_reportados' => json_encode("Ningún daño aparente reportado en recepción."),
            ]);

            // Actualizar la Cita
            $cita->update(['estado' => 'concretada']);

            // Crear Historial del Mecánico
            if ($mecanicos->count() > 0) {
                $mecanico = $mecanicos->random();

                // Bitácora de Diagnóstico Competa
                BitacoraTrabajo::create([
                    'user_id' => $mecanico->id,
                    'sucursal_id' => $sucursal->id,
                    'orden_trabajo_id' => $orden->id,
                    'tipo_actividad' => 'diagnostico',
                    'descripcion' => 'Inspección multipunto completada.',
                    'inicio' => $fechaRecepcion->copy()->addMinutes(10),
                    'fin' => $fechaRecepcion->copy()->addMinutes(40),
                    'minutos_totales' => 30,
                    'estado' => 'completado'
                ]);

                // Si está finalizada o en proceso, crear trabajo mecánico
                if (in_array($estadoOT, ['en_proceso', 'finalizada'])) {
                    BitacoraTrabajo::create([
                        'user_id' => $mecanico->id,
                        'sucursal_id' => $sucursal->id,
                        'orden_trabajo_id' => $orden->id,
                        'tipo_actividad' => 'mecanica',
                        'descripcion' => 'Reparación según diagnóstico inicial.',
                        'inicio' => $fechaRecepcion->copy()->addMinutes(45),
                        'fin' => $estadoOT === 'finalizada' ? $fechaFin : null,
                        'estado' => $estadoOT === 'finalizada' ? 'completado' : 'en_progreso'
                    ]);
                }
            }
        }
    }
}
