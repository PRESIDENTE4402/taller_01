<?php

namespace App\Http\Controllers\Panel;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Cita;
use App\Models\Cliente;
use App\Models\Vehiculo;
use App\Models\MarcaVehiculo;
use App\Models\ModeloVehiculo;
use App\Models\VersionVehiculo;
use App\Models\OrdenTrabajo;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class CitaController extends Controller
{
    public function index()
    {
        return view('panel.operaciones.citas.index');
    }

    public function list(Request $request)
    {
        // Filtros
        $start = $request->get('start'); // Para FullCalendar si lo usamos después
        $end = $request->get('end');
        $estado = $request->get('estado');

        $query = Cita::with(['cliente', 'vehiculo.marca', 'vehiculo.modelo'])
            ->orderBy('fecha_programada', 'asc');

        if ($estado) {
            $query->where('estado', $estado);
        } else {
            // Por defecto no mostrar canceladas viejas en la vista inicial si no se pide
             // $query->where('estado', '!=', 'cancelada');
        }
        
        // Si hay rango de fechas (útil para agenda)
        if ($start && $end) {
            $query->whereBetween('fecha_programada', [$start, $end]);
        }

        $citas = $query->get()->map(function($cita) {
            $clienteNombre = $cita->cliente?->nombre_completo ?? 'Cliente Desconocido';
            $vehiculoTexto = 'Vehículo Desconocido';
            
            if ($cita->vehiculo) {
                $marca = $cita->vehiculo->marca?->nombre ?? '';
                $modelo = $cita->vehiculo->modelo?->nombre ?? '';
                $placa = $cita->vehiculo->placa ?? 'S/P';
                $vehiculoTexto = trim("$marca $modelo ($placa)");
            }

            return [
                'id' => $cita->id,
                'title' => "$clienteNombre - $vehiculoTexto",
                'start' => $cita->fecha_programada,
                'description' => $cita->motivo_cita,
                'estado' => $cita->estado,
                'cliente' => $clienteNombre,
                'telefono' => $cita->cliente?->telefono ?? 'N/A',
                'vehiculo' => $vehiculoTexto,
                'className' => 'fc-event-' . $cita->estado
            ];
        });

        return response()->json($citas);
    }

    public function store(Request $request)
    {
        try {
            DB::beginTransaction();

            $clienteId = $request->cliente_id;
            $vehiculoId = $request->vehiculo_id;

            // CASO 1: Cliente Nuevo (o no seleccionado de la lista)
            if ($request->modo_creacion === 'nuevo') {
                // 1. Validar datos básicos
                $request->validate([
                    'nombre_nuevo' => 'required|string',
                    'telefono_nuevo' => 'required|string',
                    'placa_nuevo' => 'required|string',
                    'marca_nuevo' => 'required|string',
                    'fecha' => 'required|date',
                    'hora' => 'required'
                ]);

                // 2. Cliente (Buscar por email/teléfono o Crear)
                // Intentamos buscar si ya existe un "casi duplicado" para no ensuciar la BD, 
                // pero si el usuario explícitamente pide nuevo, priorizamos creación o actualización.
                $cliente = Cliente::firstOrCreate(
                    ['telefono' => $request->telefono_nuevo], // Búsqueda simple
                    [
                        'nombre_completo' => $request->nombre_nuevo,
                        'email' => $request->email_nuevo // Opcional
                    ]
                );
                
                // Si ya existía pero con otro nombre, podríamos actualizarlo, pero mejor lo dejamos así por seguridad.
                $clienteId = $cliente->id;

                // 3. Vehiculo / Marca / Modelo / Version
                $nombreMarca = trim(strtoupper($request->marca_nuevo));
                $nombreModelo = $request->modelo_nuevo ? trim(strtoupper($request->modelo_nuevo)) : 'MODELO BASE';
                $nombreVersion = $request->version_nuevo ? trim(strtoupper($request->version_nuevo)) : null;

                $marca = MarcaVehiculo::firstOrCreate(['nombre' => $nombreMarca]);
                $modelo = ModeloVehiculo::firstOrCreate(['marca_id' => $marca->id, 'nombre' => $nombreModelo]);
                
                $versionId = null;
                if ($nombreVersion) {
                    $version = VersionVehiculo::firstOrCreate(
                        ['modelo_id' => $modelo->id, 'nombre' => $nombreVersion]
                    );
                    $versionId = $version->id;
                }

                $placa = strtoupper(str_replace([' ', '-'], '', $request->placa_nuevo));
                
                $vehiculo = Vehiculo::firstOrCreate(
                    ['placa' => $placa],
                    [
                        'cliente_id' => $cliente->id,
                        'marca_id' => $marca->id,
                        'modelo_id' => $modelo->id,
                        'version_id' => $versionId,
                        'anio' => $request->anio_nuevo ?? date('Y')
                    ]
                );
                
                // Asegurar pertenencia (simple)
                if($vehiculo->cliente_id !== $cliente->id) {
                    $vehiculo->update(['cliente_id' => $cliente->id]);
                }
                
                $vehiculoId = $vehiculo->id;

            } else {
                // CASO 2: Cliente Existente (Validación Estándar)
                $request->validate([
                    'cliente_id' => 'required|exists:clientes,id',
                    'vehiculo_id' => 'required|exists:vehiculos,id',
                    'fecha' => 'required|date',
                    'hora' => 'required',
                    'motivo' => 'required|string',
                ]);
            }

            // Crear la Cita
            $fechaHora = Carbon::parse($request->fecha . ' ' . $request->hora);

            $cita = Cita::create([
                'cliente_id' => $clienteId,
                'vehiculo_id' => $vehiculoId,
                'sucursal_id' => 1,
                'fecha_programada' => $fechaHora,
                'motivo_cita' => $request->motivo,
                'origen' => 'presencial',
                'estado' => 'confirmada', // Admin
                'notas_secretario' => 'Creada manualmente por panel'
            ]);
            
            DB::commit();

            return response()->json(['success' => true, 'message' => 'Cita agendada correctamente', 'data' => $cita]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => 'Error: ' . $e->getMessage()], 500);
        }
    }

    public function update(Request $request, $id)
    {
        $cita = Cita::findOrFail($id);
        
        // Acciones especiales
        if ($request->action === 'cambiar_estado') {
             $cita->estado = $request->estado;
             $cita->save();
             return response()->json(['success' => true, 'message' => 'Estado actualizado']);
        }
        
        // Editar normal
        $cita->update($request->all());
        return response()->json(['success' => true, 'message' => 'Cita actualizada']);
    }

    public function destroy($id)
    {
        $cita = Cita::findOrFail($id);
        $cita->delete();
        return response()->json(['success' => true, 'message' => 'Cita eliminada']);
    }

    // API Auxiliares para el Modal de Creación
    public function searchClients(Request $request) {
        $term = $request->term;
        $clientes = Cliente::where('nombre_completo', 'LIKE', "%$term%")
            ->orWhere('telefono', 'LIKE', "%$term%")
            ->orWhere('email', 'LIKE', "%$term%")
            ->take(10)
            ->get(['id', 'nombre_completo', 'telefono', 'email']);
        
        return response()->json($clientes);
    }

    public function getClientVehicles($clienteId) {
        $vehiculos = Vehiculo::where('cliente_id', $clienteId)
            ->with(['marca', 'modelo'])
            ->get();
            
        $data = $vehiculos->map(function($v) {
            return [
                'id' => $v->id,
                'texto' => ($v->marca?->nombre ?? '') . ' ' . ($v->modelo?->nombre ?? '') . ' - ' . $v->placa
            ];
        });

        return response()->json($data);
    }
    public function getBrands() {
        return response()->json(MarcaVehiculo::orderBy('nombre')->get(['id', 'nombre']));
    }
}
