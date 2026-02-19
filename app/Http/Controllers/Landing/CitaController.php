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
            'placa' => 'required|string', // Indispensable para historial
            'marca' => 'required|string', // Texto del input
            'modelo' => 'nullable|string', // Texto del input
            'version' => 'nullable|string', // Texto del input
            'motivo' => 'required|string',
            'fecha' => 'required|date',
            'hora' => 'required|string',
            'sucursal_id' => 'required|exists:sucursales,id'
        ]);

        try {
            DB::beginTransaction();

            // 2. Gestión de CLIENTE (Buscar o Crear)
            // Si el cliente ya existe, se mantiene su situación actual.
            // Si es nuevo, se crea como 'prospecto'.
            $cliente = Cliente::firstOrCreate(
                ['email' => $validated['email']], // Búsqueda principal por email
                [
                    'nombre_completo' => $validated['nombre'],
                    'telefono' => $validated['telefono'],
                    'direccion' => 'Dirección pendiente', // Opcional
                    'situacion' => 'prospecto' // Nuevo campo
                ]
            );

            // Actualizar datos de contacto si el cliente existe (opcional, pero útil)
            if (!$cliente->wasRecentlyCreated) {
                // Solo actualizamos si es prospecto o si queremos mantener datos frescos
                // Por seguridad, actualizamos teléfono y nombre
                $cliente->update([
                    'nombre_completo' => $validated['nombre'],
                    'telefono' => $validated['telefono']
                ]);
            }

            // 3. Gestión de VEHÍCULO
            $nombreMarca = trim(strtoupper($validated['marca']));
            $nombreModelo = isset($validated['modelo']) ? trim(strtoupper($validated['modelo'])) : 'MODELO BASE';
            $nombreVersion = isset($request->version) ? trim(strtoupper($request->version)) : '';
            
            $mensajeAdicionalVehiculo = "";

            // Buscar si la marca existe en BD (para relacionarla correctamente)
            $marca = MarcaVehiculo::where('nombre', $nombreMarca)->first();

            if ($marca) {
                // Marca existe, procedemos normal con Modelo
                $modelo = ModeloVehiculo::firstOrCreate(
                    ['marca_id' => $marca->id, 'nombre' => $nombreModelo]
                );
                
                $versionId = null;
                if ($nombreVersion) {
                    $version = VersionVehiculo::firstOrCreate(
                        ['modelo_id' => $modelo->id, 'nombre' => $nombreVersion]
                    );
                    $versionId = $version->id;
                }
            } else {
                // Marca NO existe: Usar GENERICO
                $marca = MarcaVehiculo::firstOrCreate(['nombre' => 'GENERICA']); 
                $modelo = ModeloVehiculo::firstOrCreate(['marca_id' => $marca->id, 'nombre' => 'GENERICO']);
                $versionId = null; 

                // Guardamos el detalle real en el texto para que el asesor lo corrija después
                $mensajeAdicionalVehiculo = " [Vehículo Ingresado: $nombreMarca - $nombreModelo - $nombreVersion]";
            }

            // Crear/Buscar Vehículo
            $placa = strtoupper(str_replace([' ', '-'], '', $validated['placa']));

            $vehiculo = Vehiculo::firstOrCreate(
                ['placa' => $placa],
                [
                    'cliente_id' => $cliente->id,
                    'marca_id' => $marca->id,
                    'modelo_id' => $modelo->id,
                    'version_id' => $versionId,
                    'anio' => date('Y'),
                    'vin' => null,
                    'situacion' => 'prospecto' // Nuevo campo
                ]
            );

            // Si el vehículo ya existía pero estaba asignado a otro cliente (caso raro de venta),
            // lo reasignamos al cliente actual. O si es nuevo, aseguramos la relación.
            if ($vehiculo->cliente_id !== $cliente->id) {
                $vehiculo->update(['cliente_id' => $cliente->id]);
            }

            // 4. Crear CITA
            $fechaHora = Carbon::parse($validated['fecha'] . ' ' . $validated['hora'], 'America/Guatemala')->setTimezone('UTC');
            
            // Concatenar detalles al motivo
            $motivoFinal = $validated['motivo'] . $mensajeAdicionalVehiculo;

            $cita = Cita::create([
                'cliente_id' => $cliente->id,
                'vehiculo_id' => $vehiculo->id,
                'sucursal_id' => $validated['sucursal_id'],
                'fecha_programada' => $fechaHora,
                'motivo_cita' => $motivoFinal,
                'origen' => 'web',
                'estado' => 'pendiente'
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Cita agendada correctamente. Nos pondremos en contacto.',
                'cita_id' => $cita->id
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Error al procesar la cita: ' . $e->getMessage()
            ], 500);
        }
    }

    public function getBranches()
    {
        $sucursales = \App\Models\Sucursal::orderBy('nombre', 'asc')->get(['id', 'nombre']);
        return response()->json($sucursales);
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
