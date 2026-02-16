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

    public function dashboard(Request $request)
    {
        // Filtros
        $fecha = $request->get('fecha', now()->format('Y-m-d'));
        $search = $request->get('search');

        // Query Base
        $query = Cita::with(['cliente', 'vehiculo.marca', 'vehiculo.modelo'])
            ->whereDoesntHave('ordenTrabajo') // Solo pendientes de recibir
            ->whereIn('estado', ['confirmada', 'concretada', 'pendiente']); // Ampliamos estados para que aparezcan más

        // Filtro por Fecha (siempre aplica, por defecto HOY)
        if ($fecha) {
            $query->whereDate('fecha_programada', $fecha);
        }

        // Filtro por Búsqueda (Cliente, Placa, Vehículo)
        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->whereHas('cliente', function ($qc) use ($search) {
                    $qc->where('nombre_completo', 'like', "%{$search}%")
                        ->orWhere('telefono', 'like', "%{$search}%");
                })
                    ->orWhereHas('vehiculo', function ($qv) use ($search) {
                        $qv->where('placa', 'like', "%{$search}%")
                            ->orWhereHas('marca', fn($qm) => $qm->where('nombre', 'like', "%{$search}%"))
                            ->orWhereHas('modelo', fn($qm) => $qm->where('nombre', 'like', "%{$search}%"));
                    });
            });
        }

        $citasHoy = $query->orderBy('fecha_programada', 'asc')->get();

        // Estadísticas rápidas (Totales del día seleccionado)
        $totalRecibidosHoy = OrdenTrabajo::whereDate('fecha_recepcion', $fecha)->count();

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
        } elseif ($request->has('cliente_id')) {
            $cliente = Cliente::with(['vehiculos.marca', 'vehiculos.modelo'])->findOrFail($request->cliente_id);
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

            // Si NO viene cliente_id, debe venir new_cliente. También si viene cliente_id pero la acción es 'update'
            $requireClientData = false;
            if (!$request->has('cliente_id') || empty($request->cliente_id)) {
                $requireClientData = true;
            } elseif ($request->input('accion_cliente') == 'update') {
                $requireClientData = true;
            }

            if ($requireClientData) {
                $rules['new_cliente.nombre'] = 'required|string';
                $rules['new_cliente.telefono'] = 'required|string';
            }

            // Si NO viene vehiculo_id, debe venir new_vehiculo (Ahora validamos siempre que vengan datos del vehiculo)
            $rules['new_vehiculo.placa'] = 'required|string';
            $rules['new_vehiculo.marca'] = 'required|string';
            $rules['new_vehiculo.modelo'] = 'required|string';
            $rules['new_vehiculo.anio'] = 'required|integer';

            $request->validate($rules);

            // 1. Resolver Cliente
            $clienteId = $request->cliente_id;

            // Si el usuario eligió "Crear Nuevo" explícitamente, ignoramos el ID
            if ($request->input('accion_cliente') == 'create') {
                $clienteId = null;
            }

            if (empty($clienteId)) {
                $newCliente = Cliente::create([
                    'nombre_completo' => $request->input('new_cliente.nombre'),
                    'telefono' => $request->input('new_cliente.telefono'),
                    'email' => $request->input('new_cliente.email'),
                    'tipo_cliente' => 'particular', // Default
                    'user_id' => Auth::id() // Quien lo registró
                ]);
                $clienteId = $newCliente->id;
            } elseif ($request->input('accion_cliente') == 'update') {
                // Update existing client
                $cliente = Cliente::find($clienteId);
                if ($cliente) {
                    $cliente->update([
                        'nombre_completo' => $request->input('new_cliente.nombre'),
                        'telefono' => $request->input('new_cliente.telefono'),
                        'email' => $request->input('new_cliente.email'),
                    ]);
                }
            }

            // 2. Resolver Vehículo (Marca, Modelo, Versión) - Reutilizable para Create y Update
            // Marca
            $marcaStr = mb_strtoupper(trim($request->input('new_vehiculo.marca')));
            $marca = MarcaVehiculo::where('nombre', $marcaStr)->first();
            if (!$marca) {
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

            // 3. Crear o Actualizar Vehículo
            $vehiculoId = $request->vehiculo_id;

            // Si el usuario eligió "Crear Nuevo" explícitamente, ignoramos el ID
            if ($request->input('accion_vehiculo') == 'create') {
                $vehiculoId = null;
            }

            if (!empty($vehiculoId)) {
                // Update Existing Vehicle
                $vehiculo = Vehiculo::find($vehiculoId);
                $vehiculo->update([
                    'cliente_id' => $clienteId, // Update Owner (Transfer)
                    'marca_id' => $marca->id,
                    'modelo_id' => $modelo->id,
                    'version_id' => $versionId,
                    'placa' => strtoupper($request->input('new_vehiculo.placa')),
                    // 'color' => $request->color, // Vehiculo table has no color column
                    'anio' => $request->input('new_vehiculo.anio'),
                ]);
            } else {
                // Create New Vehicle
                $newVehiculo = Vehiculo::create([
                    'cliente_id' => $clienteId,
                    'marca_id' => $marca->id,
                    'modelo_id' => $modelo->id,
                    'version_id' => $versionId,
                    'placa' => strtoupper($request->input('new_vehiculo.placa')),
                    // 'color' => $request->color, // Vehiculo table has no color column
                    'anio' => $request->input('new_vehiculo.anio'),
                    'tipo_transmision' => 'mecanica', // Default
                    'tipo_combustible' => 'gasolina', // Default
                ]);
                $vehiculoId = $newVehiculo->id;
            }

            // Generar Código (Ej: OT-2026-0001)
            $lastId = OrdenTrabajo::max('id') ?? 0;
            $codigo = 'OT-' . date('Y') . '-' . str_pad($lastId + 1, 4, '0', STR_PAD_LEFT);

            $orden = OrdenTrabajo::create([
                'sucursal_id' => Auth::user()->sucursales->first()?->id ?? 1,
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
                'danos_imagen_url' => null, // Placeholder
                'falla_cliente' => $request->falla_cliente,
                'estado' => 'abierta'
            ]);

            // Handle Base64 Image
            if ($request->has('danos_image') && !empty($request->danos_image)) {
                $image_parts = explode(";base64,", $request->danos_image);
                if (count($image_parts) >= 2) {
                    $image_type_aux = explode("image/", $image_parts[0]);
                    $image_type = $image_type_aux[1];
                    $image_base64 = base64_decode($image_parts[1]);
                    $fileName = 'orden_' . $orden->id . '_danos_' . time() . '.png';

                    \Illuminate\Support\Facades\Storage::disk('public')->put('ordenes/danos/' . $fileName, $image_base64);

                    $orden->danos_imagen_url = 'storage/ordenes/danos/' . $fileName;
                    $orden->save();
                }
            }

            // Handle Reception Photos (Multiple with Titles)
            if ($request->hasFile('fotos_recepcion')) {
                $titulos = $request->input('fotos_titulos', []);

                foreach ($request->file('fotos_recepcion') as $index => $foto) {
                    $path = $foto->store('ordenes/fotos', 'public');

                    // Get title if exists for this index
                    $titulo = isset($titulos[$index]) ? $titulos[$index] : null;

                    // Create related model
                    $orden->archivos()->create([
                        'url' => 'storage/' . $path,
                        'tipo' => 'recepcion',
                        'titulo' => $titulo
                    ]);
                }
            }

            // Si venía de una cita, actualizar estado de la cita
            if ($request->has('cita_id') && !empty($request->cita_id)) {
                $cita = Cita::find($request->cita_id);
                if ($cita) {
                    $cita->estado = 'concretada'; // Estado válido
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
