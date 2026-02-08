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
        $start = $request->get('start'); 
        $end = $request->get('end');
        $estado = $request->get('estado');

        $query = Cita::with(['cliente', 'vehiculo.marca', 'vehiculo.modelo'])
            ->orderBy('fecha_programada', 'asc');

        // Si hay filtro de fecha exacto o rango
        if ($start) {
            // Si viene fullcalendar o rango manual
            $endDate = $end ?? $start; // Si no hay end, es un solo dia
            
            // Ajustar el fin del día si es fecha simple Y-m-d
            if (strlen($endDate) <= 10) {
                 $endDate .= ' 23:59:59';
            }
            if (strlen($start) <= 10) {
                 $start .= ' 00:00:00';
            }

            $query->whereBetween('fecha_programada', [$start, $endDate]);
        }

        if ($estado && $estado !== 'all') {
            $query->where('estado', $estado);
        } else if ($estado === 'all') {
            // "cuando le de a ver todas que muestre el de todos los estados"
            // No filter applied, show all statuses.
        } else {
            // Default (Initial Load): "por default muestre las citas de la semana" (controller defines status, js defines date)
            // Implicitly we usually show active work.
            $query->whereIn('estado', ['pendiente', 'confirmada']);
        }

        // 1. Calculate Counts (Respect Date, Ignore Status)
        $countsQuery = Cita::query();
        if ($start) {
            $endDateForCounts = $end ?? $start;
            if (strlen($endDateForCounts) <= 10) $endDateForCounts .= ' 23:59:59';
            if (strlen($start) <= 10) $startClone = $start . ' 00:00:00'; // Avoid overwrite
            else $startClone = $start;

            $countsQuery->whereBetween('fecha_programada', [$startClone, $endDateForCounts]);
        }
        $counts = $countsQuery->select('estado', DB::raw('count(*) as total'))
            ->groupBy('estado')
            ->pluck('total', 'estado');

        // 2. Count Today (Independent of filters)
        $countToday = Cita::whereDate('fecha_programada', now()->toDateString())
            ->whereNotIn('estado', ['cancelada', 'no_asistio']) // Only active work
            ->count();

        // 3. Get Citas (Respect Date AND Status)
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

        return response()->json([
            'citas' => $citas,
            'counts' => $counts,
            'count_today' => $countToday
        ]);
    }
    
    // API para el Calendario (Puntos Verdes/Rojos)
    public function getCalendarCounts(Request $request) {
        $month = $request->get('month'); // "2026-02"
        
        if (!$month) return response()->json([]);

        $startOfMonth = Carbon::parse($month . '-01')->startOfMonth();
        $endOfMonth = Carbon::parse($month . '-01')->endOfMonth();

        // Contar citas por día
        // "que las citas que ya estan aceptadas y que ya estan en taller ya no se muestren"
        // Excluir 'concretada' y 'cancelada'/'no_asistio'
        $counts = Cita::select(DB::raw('DATE(fecha_programada) as date'), DB::raw('count(*) as count'))
            ->whereBetween('fecha_programada', [$startOfMonth, $endOfMonth])
            ->whereIn('estado', ['pendiente', 'confirmada']) // Only active pending/confirmed
            ->groupBy('date')
            ->get()
            ->keyBy('date'); // Indexar por fecha

        return response()->json($counts);
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
                    'placa_nuevo' => 'nullable|string', // AHORA ES OPCIONAL
                    'marca_nuevo' => 'required|string',
                    'fecha' => 'required|date',
                    'hora' => 'required'
                ]);

                // 2. Cliente (Buscar por email/teléfono o Crear)
                // Si el cliente ya existe por teléfono, actualizamos sus datos con los nuevos ingresados.
                $telefono = trim($request->telefono_nuevo);
                $cliente = Cliente::where('telefono', $telefono)->first();

                if (!$cliente) {
                    $cliente = new Cliente();
                    $cliente->telefono = $telefono;
                }

                $cliente->nombre_completo = trim($request->nombre_nuevo);
                
                // Handle Email: Empty string should be NULL to avoid Unique constraint checks on empty strings
                $email = trim($request->email_nuevo);
                $cliente->email = $email === '' ? null : $email;
                
                $cliente->save();
                
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

                $placaRaw = $request->placa_nuevo;
                if (!$placaRaw) {
                    $placa = 'S/P-' . time() . '-' . rand(100,999);
                } else {
                    $placa = strtoupper(str_replace([' ', '-'], '', $placaRaw));
                }

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
                // CASO 2: Cliente Existente
                // 2.1 check if creating NEW vehicle for existing client
                if ($request->vehiculo_id === 'new_vehicle') {
                     $request->validate([
                        'cliente_id' => 'required|exists:clientes,id',
                        'marca_nuevo' => 'required|string',
                        'fecha' => 'required|date',
                        'hora' => 'required',
                        'motivo' => 'required|string',
                    ]);

                    $clienteId = $request->cliente_id;
                    
                    // Logic Identical to Case 1
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

                    $placaRaw = $request->placa_nuevo;
                    if (!$placaRaw) {
                        $placa = 'S/P-' . time() . '-' . rand(100,999);
                    } else {
                        $placa = strtoupper(str_replace([' ', '-'], '', $placaRaw));
                    }

                    $vehiculo = Vehiculo::firstOrCreate(
                        ['placa' => $placa],
                        [
                            'cliente_id' => $clienteId,
                            'marca_id' => $marca->id,
                            'modelo_id' => $modelo->id,
                            'version_id' => $versionId,
                            'anio' => $request->anio_nuevo ?? date('Y'),
                            'color' => $request->color_nuevo ?? 'No especificado'
                        ]
                    );

                    // Ensure ownership
                    if($vehiculo->cliente_id != $clienteId) {
                         // Optional: Handle if vehicle already exists but belongs to someone else?
                         // For now, assuming standard logic or update owner
                         $vehiculo->update(['cliente_id' => $clienteId]);
                    }

                    $vehiculoId = $vehiculo->id;
                    // End of Vehicle Creation

                } else {
                    // Standard Existing Vehicle
                    $request->validate([
                        'cliente_id' => 'required|exists:clientes,id',
                        'vehiculo_id' => 'required|exists:vehiculos,id',
                        'fecha' => 'required|date',
                        'hora' => 'required',
                        'motivo' => 'required|string',
                    ]);
                }
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
