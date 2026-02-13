<?php

namespace App\Http\Controllers\Landing;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Cliente;
use App\Models\Vehiculo;
use App\Models\Cita;
use App\Models\MarcaVehiculo;
use App\Models\ModeloVehiculo;
use App\Models\VersionVehiculo;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class CitaController extends Controller
{
    public function store(Request $request)
    {
        // 1. Validación básica
        $validated = $request->validate([
            'nombre' => 'required|string',
            'email' => 'required|email',
            'telefono' => 'required|string',
            'placa' => 'required|string|min:3', // Indispensable para historial
            'marca' => 'required|string', // Texto del input
            'modelo' => 'nullable|string', // Texto del input
            'version' => 'nullable|string', // Texto del input
            'motivo' => 'required|string',
            'fecha' => 'required|date',
            'hora' => 'required|string',
            'valet' => 'boolean'
        ]);

        try {
            // 2. Gestión de CLIENTE (Buscar o Crear)
            $cliente = Cliente::firstOrCreate(
                ['email' => $validated['email']], // Búsqueda principal por email
                [
                    'nombre_completo' => $validated['nombre'],
                    'telefono' => $validated['telefono'],
                    'direccion' => $request->valet ? 'Dirección pendiente (Valet)' : null,
                    // 'es_empresa' => false // default
                ]
            );

            // Si el cliente ya existía, actualizamos datos de contacto recientes
            if (!$cliente->wasRecentlyCreated) {
                $cliente->update([
                    'nombre_completo' => $validated['nombre'],
                    'telefono' => $validated['telefono']
                ]);
            }

            // 3. Gestión de MARCA / MODELO (Buscar o Crear dinámicamente)
            // Normalizar texto
            $nombreMarca = trim(strtoupper($validated['marca']));
            $nombreModelo = isset($validated['modelo']) ? trim(strtoupper($validated['modelo'])) : 'MODELO BASE';

            $marca = MarcaVehiculo::firstOrCreate(['nombre' => $nombreMarca]);
            $modelo = ModeloVehiculo::firstOrCreate(
                ['marca_id' => $marca->id, 'nombre' => $nombreModelo]
            );

            $versionId = null;
            if ($request->version) {
                $nombreVersion = trim(strtoupper($request->version));
                $version = VersionVehiculo::firstOrCreate(
                    ['modelo_id' => $modelo->id, 'nombre' => $nombreVersion]
                );
                $versionId = $version->id;
            }

            // 4. Gestión de VEHÍCULO (Buscar por Placa o Crear)
            $placa = strtoupper(str_replace([' ', '-'], '', $validated['placa'])); // Limpiar placa

            $vehiculo = Vehiculo::firstOrCreate(
                ['placa' => $placa],
                [
                    'cliente_id' => $cliente->id,
                    'marca_id' => $marca->id,
                    'modelo_id' => $modelo->id,
                    'version_id' => $versionId,
                    'anio' => date('Y'), // Default, luego se corregirá en recepción
                    'vin' => null // Pendiente
                ]
            );

            // Si el vehículo existía pero se registra con otro cliente, ¿actualizamos dueño o lanzamos error?
            // Para "agendar cita", asumiremos que el usuario actual es el dueño legítimo (simplificación).
            if ($vehiculo->cliente_id !== $cliente->id) {
                // Opción A: Actualizar dueño (ej: venta de auto)
                // $vehiculo->update(['cliente_id' => $cliente->id]);
                // Opción B: No hacer nada, la cita queda a nombre del cliente, el auto puede tener otro dueño historico.
                // PERO la tabla citas relaciona cliente y vehiculo.
                // DE MOMENTO: Actualizamos el dueño para que coincida con quien agenda.
                $vehiculo->update(['cliente_id' => $cliente->id]);
            }

            // 5. Crear la CITA
            // Combinar fecha y hora
            $fechaHora = Carbon::parse($validated['fecha'] . ' ' . $validated['hora']);

            $cita = Cita::create([
                'cliente_id' => $cliente->id,
                'vehiculo_id' => $vehiculo->id,
                'sucursal_id' => 1, // Default Sucursal 1 por ahora, o enviar en request
                'fecha_programada' => $fechaHora,
                'motivo_cita' => $validated['motivo'] . ($request->valet ? ' (Solicitó Valet Service)' : ''),
                'origen' => 'web',
                'estado' => 'pendiente'
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Cita agendada correctamente. Nos pondremos en contacto.',
                'cita_id' => $cita->id
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al procesar la cita: ' . $e->getMessage()
            ], 500);
        }
    }

    public function getBrands()
    {
        $marcas = MarcaVehiculo::orderBy('nombre', 'asc')->get(['id', 'nombre']);
        return response()->json($marcas);
    }
    
    public function getModels($marcaId)
    {
        $modelos = ModeloVehiculo::where('marca_id', $marcaId)->orderBy('nombre', 'asc')->get(['id', 'nombre']);
        return response()->json($modelos);
    }

    public function getVersions($modeloId)
    {
        $versiones = VersionVehiculo::where('modelo_id', $modeloId)->orderBy('nombre', 'asc')->get(['id', 'nombre']);
        return response()->json($versiones);
    }
}
