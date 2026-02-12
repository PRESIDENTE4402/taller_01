<?php

namespace App\Http\Controllers\Panel;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\OrdenTrabajo;
use App\Models\Cita;
use App\Models\Vehiculo;
use App\Models\Cliente;
use App\Models\MarcaVehiculo;
use App\Models\ModeloVehiculo;
use App\Models\VersionVehiculo;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class OrdenTrabajoController extends Controller
{
    public function index()
    {
        return view('panel.operaciones.ordenes_trabajo.index');
    }

    public function dashboard()
    {
        // Citas programadas para HOY que están 'confirmada' o 'concretada' (dependiendo de tu lógica) 
        // y que NO tienen aún una orden de trabajo asociada.
        $startOfDay = now()->startOfDay();
        $endOfDay = now()->endOfDay();

        $citasHoy = Cita::with(['cliente', 'vehiculo.marca', 'vehiculo.modelo'])
            ->whereBetween('fecha_programada', [$startOfDay, $endOfDay])
            ->whereIn('estado', ['confirmada', 'concretada']) // Ajusta según tus estados
            ->whereDoesntHave('ordenTrabajo')
            ->orderBy('fecha_programada', 'asc')
            ->get();

        // Estadísticas rápidas
        $totalRecibidosHoy = OrdenTrabajo::whereDate('created_at', today())->count();

        return view('panel.operaciones.ordenes_trabajo.dashboard', compact('citasHoy', 'totalRecibidosHoy'));
    }

    public function list(Request $request)
    {
        // 1. Órdenes Activas (No finalizadas/entregadas)
        $ordenes = OrdenTrabajo::with(['cliente', 'vehiculo.marca', 'vehiculo.modelo'])
            ->whereNotIn('estado', ['finalizada', 'entregada'])
            ->orderBy('created_at', 'desc')
            ->get();

        // 2. Citas "Concretadas" que NO tienen Orden de Trabajo aún
        // Obtenemos citas con estado 'concretada' que no estén referenciadas en la tabla ordenes_trabajo
        $citasPendientes = Cita::with(['cliente', 'vehiculo.marca', 'vehiculo.modelo'])
            ->where('estado', 'concretada')
            ->whereDoesntHave('ordenTrabajo') // Asumiendo relación en modelo Cita
            ->orderBy('fecha_programada', 'asc')
            ->get();

        return response()->json([
            'ordenes' => $ordenes,
            'citas_pendientes' => $citasPendientes
        ]);
    }

    public function create(Request $request)
    {
        $cita = null;
        $vehiculo = null;
        $cliente = null;

        if ($request->has('cita_id')) {
            $cita = Cita::with(['cliente', 'vehiculo.marca', 'vehiculo.modelo', 'vehiculo.version'])->findOrFail($request->cita_id);
            $vehiculo = $cita->vehiculo;
            $cliente = $cita->cliente;
        }

        return view('panel.operaciones.ordenes_trabajo.create', compact('cita', 'vehiculo', 'cliente'));
    }

    public function store(Request $request)
    {
        try {
            DB::beginTransaction();

            // Validación Condicional
            $rules = [
                'kilometraje' => 'required|integer',
                'nivel_combustible' => 'required|string',
                'falla_cliente' => 'required|string',
                'color' => 'required|string',
            ];

            // Si NO viene cliente_id, debe venir new_cliente
            if (!$request->has('cliente_id') || empty($request->cliente_id)) {
                $rules['new_cliente.nombre'] = 'required|string';
                $rules['new_cliente.telefono'] = 'required|string';
            }

            // Si NO viene vehiculo_id, debe venir new_vehiculo
            if (!$request->has('vehiculo_id') || empty($request->vehiculo_id)) {
                $rules['new_vehiculo.placa'] = 'required|string';
                $rules['new_vehiculo.marca'] = 'required|string';
                $rules['new_vehiculo.modelo'] = 'required|string';
                $rules['new_vehiculo.anio'] = 'required|integer';
            }

            $request->validate($rules);

            // 1. Resolver Cliente
            $clienteId = $request->cliente_id;
            if (empty($clienteId)) {
                $newCliente = Cliente::create([
                    'nombre_completo' => $request->input('new_cliente.nombre'),
                    'telefono' => $request->input('new_cliente.telefono'),
                    'email' => $request->input('new_cliente.email'),
                    'tipo_cliente' => 'particular', // Default
                    'user_id' => Auth::id() // Quien lo registró
                ]);
                $clienteId = $newCliente->id;
            }

            // 2. Resolver Vehículo
            $vehiculoId = $request->vehiculo_id;
            if (empty($vehiculoId)) {
                // Marca
                $marcaStr = mb_strtoupper(trim($request->input('new_vehiculo.marca')));
                // Búsqueda case-insensitive para evitar duplicados si la collation no lo maneja
                $marca = MarcaVehiculo::where('nombre', $marcaStr)->first();
                if (!$marca) {
                    // Intento secundario: buscar sin importar mayúsculas/minúsculas
                    $marca = MarcaVehiculo::whereRaw('LOWER(nombre) = ?', [mb_strtolower($marcaStr)])->first();
                }
                if (!$marca) {
                    $marca = MarcaVehiculo::create(['nombre' => $marcaStr]);
                }

                // Modelo
                $modeloStr = mb_strtoupper(trim($request->input('new_vehiculo.modelo')));
                $modelo = ModeloVehiculo::where('marca_id', $marca->id)
                    ->where(function ($query) use ($modeloStr) {
                        $query->where('nombre', $modeloStr)
                            ->orWhereRaw('LOWER(nombre) = ?', [mb_strtolower($modeloStr)]);
                    })->first();

                if (!$modelo) {
                    $modelo = ModeloVehiculo::create([
                        'nombre' => $modeloStr,
                        'marca_id' => $marca->id
                    ]);
                }

                // Version (Opcional)
                $versionId = null;
                if ($request->has('new_vehiculo.version') && !empty($request->input('new_vehiculo.version'))) {
                    $versionStr = mb_strtoupper(trim($request->input('new_vehiculo.version')));
                    $version = VersionVehiculo::where('modelo_id', $modelo->id)
                        ->where(function ($query) use ($versionStr) {
                            $query->where('nombre', $versionStr)
                                ->orWhereRaw('LOWER(nombre) = ?', [mb_strtolower($versionStr)]);
                        })->first();

                    if (!$version) {
                        $version = VersionVehiculo::create([
                            'nombre' => $versionStr,
                            'modelo_id' => $modelo->id
                        ]);
                    }
                    $versionId = $version->id;
                }

                // Crear Vehículo
                $newVehiculo = Vehiculo::create([
                    'cliente_id' => $clienteId,
                    'marca_id' => $marca->id,
                    'modelo_id' => $modelo->id,
                    'version_id' => $versionId,
                    'placa' => strtoupper($request->input('new_vehiculo.placa')),
                    'color' => $request->color, // Usamos el color del form principal
                    'anio' => $request->input('new_vehiculo.anio'),
                    'tipo_transmision' => 'mecanica', // Default o pedir en form
                    'tipo_combustible' => 'gasolina', // Default o pedir en form
                ]);
                $vehiculoId = $newVehiculo->id;
            }

            // Generar Código (Ej: OT-2026-0001)
            $lastId = OrdenTrabajo::max('id') ?? 0;
            $codigo = 'OT-' . date('Y') . '-' . str_pad($lastId + 1, 4, '0', STR_PAD_LEFT);

            $orden = OrdenTrabajo::create([
                'sucursal_id' => 1, // Hardcoded por ahora o Auth::user()->sucursal_id
                'codigo_orden' => $codigo,
                'tipo_orden' => $request->tipo_orden ?? 'normal',
                'vehiculo_id' => $vehiculoId,
                'cliente_id' => $clienteId,
                'cita_id' => $request->cita_id, // Nullable
                'receptor_id' => Auth::id() ?? 1,
                'fecha_recepcion' => now(),
                'color' => $request->color,
                'kilometraje_entrada' => $request->kilometraje,
                'nivel_combustible' => $request->nivel_combustible,
                'inventario_recepcion' => json_encode($request->inv ?? []), // Checklist (Array 'inv')
                'danos_reportados' => json_encode($request->danos ?? []),
                'danos_imagen_url' => $request->danos_image, // Base64 Canvas
                'falla_cliente' => $request->falla_cliente,
                'estado' => 'abierta'
            ]);

            // Si venía de una cita, actualizar estado de la cita
            if ($request->has('cita_id') && !empty($request->cita_id)) {
                $cita = Cita::find($request->cita_id);
                if ($cita) {
                    $cita->estado = 'atendida'; // O el estado que uses para cerrar cita
                    $cita->save();
                }
            }

            DB::commit();

            return response()->json(['success' => true, 'redirect' => route('panel.operaciones.ordenes_trabajo.index'), 'message' => 'Orden creada con éxito']);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => 'Error al crear orden: ' . $e->getMessage()], 500);
        }
    }
}
