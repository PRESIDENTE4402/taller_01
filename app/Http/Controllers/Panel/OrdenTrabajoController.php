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
use App\Models\BitacoraTrabajo;
use App\Models\DetalleOrden;
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
        $ordenes = OrdenTrabajo::with(['cliente', 'vehiculo.marca', 'vehiculo.modelo', 'sucursal', 'bitacoras', 'receptor'])
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

        /** @var \App\Models\User $authUser */
        $authUser = Auth::user();
        $isAdmin = $authUser->hasRole('admin');
        $sucursales = $isAdmin ? \App\Models\Sucursal::all() : collect([]);

        return view('panel.operaciones.ordenes_trabajo.create', compact('cita', 'vehiculo', 'cliente', 'isAdmin', 'sucursales'));
    }

    public function cancelCita($id)
    {
        try {
            $cita = Cita::findOrFail($id);
            $cita->estado = 'no_asistio';
            $cita->save();

            return response()->json([
                'success' => true,
                'message' => 'Cita marcada como inasistencia correctamente.'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al cancelar la cita: ' . $e->getMessage()
            ], 500);
        }
    }

    public function edit($id)
    {
        $orden = OrdenTrabajo::with(['cliente', 'vehiculo.marca', 'vehiculo.modelo', 'vehiculo.version', 'archivos'])->findOrFail($id);
        $cliente = $orden->cliente;
        $vehiculo = $orden->vehiculo;
        $cita = $orden->cita;

        /** @var \App\Models\User $authUser */
        $authUser = Auth::user();
        $isAdmin = $authUser->hasRole('admin');
        $sucursales = $isAdmin ? \App\Models\Sucursal::all() : collect([]);

        return view('panel.operaciones.ordenes_trabajo.create', compact('orden', 'cliente', 'vehiculo', 'cita', 'isAdmin', 'sucursales'));
    }

    public function update(Request $request, $id)
    {
        try {
            $orden = OrdenTrabajo::findOrFail($id);
            DB::beginTransaction();

            // Similar validation but slightly relaxed for update? No, same rules.
            $rules = [
                'kilometraje' => 'required|integer',
                'nivel_combustible' => 'required|string',
                'falla_cliente' => 'required|string',
                'color' => 'required|string',
            ];

            // Client/Vehicle resolution logic is the same as store.
            // I'll keep it simple for now and update only order-specific fields if they changed.

            $orden->update([
                'tipo_orden' => $request->tipo_orden ?? $orden->tipo_orden,
                'color' => $request->color,
                'kilometraje_entrada' => $request->kilometraje,
                'nivel_combustible' => $request->nivel_combustible,
                'inventario_recepcion' => json_encode($request->inv ?? []),
                'danos_reportados' => json_encode($request->input('danos_reportados', '')),
                'falla_cliente' => $request->falla_cliente,
            ]);

            // --- Eliminación de Fotos y Daños Removidos en Frontend ---
            if ($request->has('retained_photos')) {
                $retainedIds = json_decode($request->input('retained_photos'), true) ?? [];
                $orden->archivos()->where('tipo', 'recepcion')->whereNotIn('id', $retainedIds)->delete();
            }

            if ($request->has('retained_damage_photos')) {
                $retainedDamageIds = json_decode($request->input('retained_damage_photos'), true) ?? [];
                $orden->archivos()->whereIn('tipo', ['otro', 'danos'])
                    ->whereNotIn('id', $retainedDamageIds)
                    ->delete();
            }

            // --- Lógica de Daños (fotos_danos y danos_image) ---
            if ($request->has('fotos_danos')) {
                foreach ($request->input('fotos_danos') as $index => $b64) {
                    $image_parts = explode(";base64,", $b64);
                    if (count($image_parts) >= 2) {
                        $image_base64 = base64_decode($image_parts[1]);
                        $fileName = 'orden_' . $orden->id . '_dano_upd_' . $index . '_' . time() . '.png';
                        \Illuminate\Support\Facades\Storage::disk('public')->put('ordenes/danos/' . $fileName, $image_base64);

                        $url = 'storage/ordenes/danos/' . $fileName;

                        // Si es la primera, guardarla como la principal si no hay o si se quiere sobreescribir
                        if ($index == 0) {
                            $orden->danos_imagen_url = $url;
                            $orden->save();
                        }

                        // Guardar en archivos relacionales
                        $orden->archivos()->create([
                            'url' => $url,
                            'tipo' => 'otro',
                            'titulo' => 'Daño ' . ($index + 1)
                        ]);
                    }
                }
            }

            // Handle Single Damage Image Update (canvas sin guardar a galería)
            if ($request->has('danos_image') && !empty($request->danos_image)) {
                $image_parts = explode(";base64,", $request->danos_image);
                if (count($image_parts) >= 2) {
                    $image_base64 = base64_decode($image_parts[1]);
                    $fileName = 'orden_' . $orden->id . '_danos_upd_' . time() . '.png';
                    \Illuminate\Support\Facades\Storage::disk('public')->put('ordenes/danos/' . $fileName, $image_base64);

                    $url = 'storage/ordenes/danos/' . $fileName;
                    $orden->danos_imagen_url = $url;
                    $orden->save();

                    // Guardar en archivos relacionales
                    $orden->archivos()->updateOrCreate(
                        ['url' => $url],
                        ['tipo' => 'otro', 'titulo' => 'Reporte de Daños Editado']
                    );
                }
            }

            // Handle New Photos
            if ($request->hasFile('fotos_recepcion')) {
                $titulos = $request->input('fotos_titulos', []);
                foreach ($request->file('fotos_recepcion') as $index => $foto) {
                    $path = $foto->store('ordenes/fotos', 'public');
                    $titulo = isset($titulos[$index]) ? $titulos[$index] : null;
                    $orden->archivos()->create([
                        'url' => 'storage/' . $path,
                        'tipo' => 'recepcion',
                        'titulo' => $titulo
                    ]);
                }
            }

            DB::commit();

            // Refresh order to get the latest updated files
            $orden->refresh();
            $archivos = $orden->archivos;

            return response()->json([
                'success' => true,
                'redirect' => route('panel.operaciones.ordenes_trabajo.index'),
                'message' => 'Orden actualizada con éxito',
                'archivos' => $archivos
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => 'Error al actualizar orden: ' . $e->getMessage()], 500);
        }
    }

    public function show($id)
    {
        $orden = OrdenTrabajo::with(['cliente', 'vehiculo.marca', 'vehiculo.modelo', 'vehiculo.version', 'archivos', 'detalles.repuesto', 'bitacoras.mecanico'])->findOrFail($id);

        // List of mechanics (mechanic role users)
        $mecanicos = \App\Models\User::whereHas('roles', function ($q) {
            $q->where('slug', 'mecanico');
        })->get();

        return view('panel.operaciones.ordenes_trabajo.show', compact('orden', 'mecanicos'));
    }

    public function getDetails($id)
    {
        $orden = OrdenTrabajo::with([
            'cliente',
            'vehiculo.marca',
            'vehiculo.modelo',
            'vehiculo.version',
            'archivos',
            'detalles.repuesto',
            'bitacoras.mecanico'
        ])->findOrFail($id);

        return response()->json($orden);
    }



    // Method to add Detail (Repuesto) via AJAX
    public function addDetail(Request $request, $id)
    {
        try {
            $orden = OrdenTrabajo::findOrFail($id);
            $detalle = $orden->detalles()->create([
                'repuesto_id' => $request->repuesto_id,
                'descripcion_manual' => $request->descripcion_manual,
                'cantidad' => $request->cantidad,
                'precio_unitario' => $request->precio_unitario,
                'suministrado_por' => $request->suministrado_por ?? 'taller',
                'notas' => $request->notas
            ]);

            return response()->json(['success' => true, 'message' => 'Detalle agregado', 'data' => $detalle]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    // Method to add Task (Bitacora) via AJAX
    public function addTask(Request $request, $id)
    {
        try {
            $orden = OrdenTrabajo::findOrFail($id);
            $tarea = $orden->bitacoras()->create([
                'user_id' => $request->user_id, // Mecánico
                'sucursal_id' => $orden->sucursal_id,
                'tipo_actividad' => 'mecanica',
                'descripcion' => $request->descripcion,
                'meta_minutos' => $request->meta_minutos,
                'estado' => 'en_pausa' // Default planned
            ]);

            // Al asignar la primera tarea, podríamos pasar a "en_proceso" automáticamente
            if ($orden->estado == 'abierta') {
                $orden->estado = 'en_proceso';
                $orden->save();
            }

            return response()->json(['success' => true, 'message' => 'Tarea asignada', 'data' => $tarea]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function updateStatus(Request $request, $id)
    {
        try {
            $orden = OrdenTrabajo::findOrFail($id);
            $orden->estado = $request->estado;

            if ($request->estado == 'finalizada') {
                $orden->fecha_finalizacion = now();
            }

            $orden->save();

            return response()->json(['success' => true, 'message' => 'Estado actualizado a ' . $request->estado]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
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
                'tipo_orden' => 'required|in:normal,garantia,cortesia',
                'fecha_recepcion_date' => 'required|date|after_or_equal:today|before_or_equal:today',
                'fecha_recepcion_time' => 'required',
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
                $rules['new_cliente.nit'] = 'nullable|string';
                $rules['new_cliente.direccion'] = 'nullable|string';
            }

            // Si NO viene vehiculo_id, debe venir new_vehiculo (Ahora validamos siempre que vengan datos del vehiculo)
            $rules['new_vehiculo.placa'] = 'required|string';
            $rules['new_vehiculo.marca'] = 'required|string';
            $rules['new_vehiculo.modelo'] = 'required|string';
            $rules['new_vehiculo.anio'] = 'required|integer';

            /** @var \App\Models\User $authUser */
            $authUser = Auth::user();
            if ($authUser->hasRole('admin')) {
                $rules['sucursal_id'] = 'required|exists:sucursales,id';
            }

            $request->validate($rules);

            // 1. Resolver Cliente
            $clienteId = $request->cliente_id;

            // Si el usuario eligió "Crear Nuevo" explícitamente, ignoramos el ID
            if ($request->input('accion_cliente') == 'create') {
                $clienteId = null;
            }

            if (empty($clienteId)) {
                // Verificar si el correo ya existe para evitar error 1062
                $emailIngresado = $request->input('new_cliente.email');
                $clienteExistente = null;

                if (!empty($emailIngresado)) {
                    $clienteExistente = Cliente::where('email', $emailIngresado)->first();
                }

                if ($clienteExistente) {
                    $clienteId = $clienteExistente->id;
                    // Actualizamos sus datos principales
                    $clienteExistente->update([
                        'nombre_completo' => $request->input('new_cliente.nombre'),
                        'telefono' => $request->input('new_cliente.telefono'),
                        'nit' => $request->input('new_cliente.nit') ? $request->input('new_cliente.nit') : $clienteExistente->nit,
                        'direccion' => $request->input('new_cliente.direccion') ? $request->input('new_cliente.direccion') : $clienteExistente->direccion,
                    ]);
                } else {
                    $newCliente = Cliente::create([
                        'nombre_completo' => $request->input('new_cliente.nombre'),
                        'telefono' => $request->input('new_cliente.telefono'),
                        'email' => $emailIngresado,
                        'nit' => $request->input('new_cliente.nit'),
                        'direccion' => $request->input('new_cliente.direccion'),
                        'es_empresa' => $request->input('new_cliente.es_empresa') ? true : false,
                        'empresa' => $request->input('new_cliente.es_empresa') ? strtoupper($request->input('new_cliente.empresa')) : null,
                        'tipo_cliente' => 'particular', // Default
                        'user_id' => Auth::id() // Quien lo registró
                    ]);
                    $clienteId = $newCliente->id;
                }
            } elseif ($request->input('accion_cliente') == 'update') {
                // Update existing client
                $cliente = Cliente::find($clienteId);
                if ($cliente) {
                    $cliente->update([
                        'nombre_completo' => $request->input('new_cliente.nombre'),
                        'telefono' => $request->input('new_cliente.telefono'),
                        'email' => $request->input('new_cliente.email'),
                        'nit' => $request->input('new_cliente.nit'),
                        'direccion' => $request->input('new_cliente.direccion'),
                        'es_empresa' => $request->input('new_cliente.es_empresa') ? true : false,
                        'empresa' => $request->input('new_cliente.es_empresa') ? strtoupper($request->input('new_cliente.empresa')) : null,
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
            $placaInput = strtoupper($request->input('new_vehiculo.placa'));

            // Si el usuario eligió "Crear Nuevo" explícitamente o no hay ID, buscamos por placa para evitar error SQL
            if ($request->input('accion_vehiculo') == 'create' || empty($vehiculoId)) {
                $vehiculo = Vehiculo::where('placa', $placaInput)->first();
                if ($vehiculo) {
                    $vehiculoId = $vehiculo->id;
                }
            }

            if (!empty($vehiculoId)) {
                $vehiculo = Vehiculo::find($vehiculoId);
                $vehiculo->update([
                    'cliente_id' => $clienteId, // Actualizar dueño en caso de cambio de propiedad
                    'marca_id' => $marca->id,
                    'modelo_id' => $modelo->id,
                    'version_id' => $versionId,
                    'placa' => $placaInput,
                    'color' => $request->color,
                    'vin' => strtoupper($request->input('new_vehiculo.vin')), // Motor/VIN
                    'anio' => $request->input('new_vehiculo.anio'),
                ]);
            } else {
                // Create New Vehicle
                $newVehiculo = Vehiculo::create([
                    'cliente_id' => $clienteId,
                    'marca_id' => $marca->id,
                    'modelo_id' => $modelo->id,
                    'version_id' => $versionId,
                    'placa' => $placaInput,
                    'color' => $request->color,
                    'vin' => strtoupper($request->input('new_vehiculo.vin')), // Motor/VIN
                    'anio' => $request->input('new_vehiculo.anio'),
                    'tipo_transmision' => 'mecanica', // Default
                    'tipo_combustible' => 'gasolina', // Default
                ]);
                $vehiculoId = $newVehiculo->id;
            }

            // Generar Código (Ej: OT-2026-0001)
            $lastId = OrdenTrabajo::max('id') ?? 0;
            $codigo = 'OT-' . date('Y') . '-' . str_pad($lastId + 1, 4, '0', STR_PAD_LEFT);

            // Determinar Sucursal
            /** @var \App\Models\User $authUser */
            $authUser = Auth::user();
            $sucursalId = $authUser->sucursales->first()?->id ?? 1;
            if ($authUser->hasRole('admin') && $request->has('sucursal_id')) {
                $sucursalId = $request->sucursal_id;
            }

            $orden = OrdenTrabajo::create([
                'sucursal_id' => $sucursalId,
                'codigo_orden' => $codigo,
                'tipo_orden' => $request->tipo_orden ?? 'normal',
                'vehiculo_id' => $vehiculoId,
                'cliente_id' => $clienteId,
                'cita_id' => $request->cita_id, // Nullable
                'receptor_id' => Auth::id() ?? 1,
                'fecha_recepcion' => \Carbon\Carbon::createFromFormat('Y-m-d H:i', $request->fecha_recepcion_date . ' ' . $request->fecha_recepcion_time),
                'color' => $request->color,
                'kilometraje_entrada' => $request->kilometraje,
                'nivel_combustible' => $request->nivel_combustible,
                'inventario_recepcion' => json_encode($request->inv ?? []), // Checklist (Array 'inv')
                'danos_reportados' => json_encode($request->input('danos_reportados', '')),
                'danos_imagen_url' => null, // Placeholder
                'falla_cliente' => $request->falla_cliente,
                'estado' => 'abierta'
            ]);

            // --- Lógica de Daños (fotos_danos y danos_image) ---
            if ($request->has('fotos_danos')) {
                foreach ($request->input('fotos_danos') as $index => $b64) {
                    $image_parts = explode(";base64,", $b64);
                    if (count($image_parts) >= 2) {
                        $image_base64 = base64_decode($image_parts[1]);
                        $fileName = 'orden_' . $orden->id . '_dano_' . $index . '_' . time() . '.png';
                        \Illuminate\Support\Facades\Storage::disk('public')->put('ordenes/danos/' . $fileName, $image_base64);

                        $url = 'storage/ordenes/danos/' . $fileName;

                        // Si es la primera, guardarla como la principal
                        if ($index == 0) {
                            $orden->danos_imagen_url = $url;
                            $orden->save();
                        }

                        // Guardar en archivos relacionales
                        $orden->archivos()->create([
                            'url' => $url,
                            'tipo' => 'otro',
                            'titulo' => 'Daño ' . ($index + 1)
                        ]);
                    }
                }
            }

            // Handle Single Base64 Image (si no usó la galería pero hay algo en canvas)
            if ($request->has('danos_image') && !empty($request->danos_image) && empty($orden->danos_imagen_url)) {
                $image_parts = explode(";base64,", $request->danos_image);
                if (count($image_parts) >= 2) {
                    $image_base64 = base64_decode($image_parts[1]);
                    $fileName = 'orden_' . $orden->id . '_danos_' . time() . '.png';
                    \Illuminate\Support\Facades\Storage::disk('public')->put('ordenes/danos/' . $fileName, $image_base64);

                    $url = 'storage/ordenes/danos/' . $fileName;
                    $orden->danos_imagen_url = $url;
                    $orden->save();

                    // Guardar en archivos relacionales
                    $orden->archivos()->create([
                        'url' => $url,
                        'tipo' => 'otro',
                        'titulo' => 'Reporte de Daños Principal'
                    ]);
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

            return response()->json(['success' => true, 'redirect' => route('panel.operaciones.ordenes_trabajo.show', $orden->id), 'message' => 'Orden creada con éxito']);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => 'Error al crear orden: ' . $e->getMessage()], 500);
        }
    }

    public function searchClients(Request $request)
    {
        $term = $request->term;
        $clientes = Cliente::where(function ($query) use ($term) {
            $query->where('nombre_completo', 'LIKE', "%$term%")
                ->orWhere('telefono', 'LIKE', "%$term%")
                ->orWhere('email', 'LIKE', "%$term%")
                ->orWhere('nit', 'LIKE', "%$term%");
        })
            ->take(10)
            ->get(['id', 'nombre_completo', 'telefono', 'email', 'nit', 'direccion', 'es_empresa', 'empresa']);

        return response()->json($clientes);
    }

    public function searchVehicles(Request $request)
    {
        $term = $request->term;
        $vehiculos = Vehiculo::with(['cliente', 'marca', 'modelo', 'version'])
            ->where('placa', 'LIKE', "%$term%")
            ->take(5)
            ->get();

        $data = $vehiculos->map(function ($v) {
            return [
                'id' => $v->id,
                'placa' => $v->placa,
                'marca' => $v->marca?->nombre ?? '',
                'modelo' => $v->modelo?->nombre ?? '',
                'version' => $v->version?->nombre ?? '',
                'color' => $v->color,
                'anio' => $v->anio,
                'vin' => $v->vin,
                'cliente' => $v->cliente ? [
                    'id' => $v->cliente->id,
                    'nombre_completo' => $v->cliente->nombre_completo,
                    'telefono' => $v->cliente->telefono,
                    'email' => $v->cliente->email,
                    'nit' => $v->cliente->nit,
                    'direccion' => $v->cliente->direccion,
                    'es_empresa' => $v->cliente->es_empresa,
                    'empresa' => $v->cliente->empresa
                ] : null,
                'texto' => $v->placa . ' - ' . ($v->marca?->nombre ?? '') . ' - ' . ($v->modelo?->nombre ?? '')
            ];
        });

        return response()->json($data);
    }

    public function getClientVehicles($clienteId)
    {
        $vehiculos = Vehiculo::where('cliente_id', $clienteId)
            ->with(['marca', 'modelo', 'version'])
            ->get();

        $data = $vehiculos->map(function ($v) {
            return [
                'id' => $v->id,
                'placa' => $v->placa,
                'marca' => $v->marca?->nombre ?? '',
                'modelo' => $v->modelo?->nombre ?? '',
                'version' => $v->version?->nombre ?? '',
                'color' => $v->color,
                'anio' => $v->anio,
                'vin' => $v->vin,
                'texto' => ($v->marca?->nombre ?? '') . ' ' . ($v->modelo?->nombre ?? '') . ' - ' . $v->placa
            ];
        });

        return response()->json($data);
    }

    public function print($id)
    {
        $orden = OrdenTrabajo::with([
            'cliente',
            'vehiculo.marca',
            'vehiculo.modelo',
            'vehiculo.version',
            'sucursal',
            'archivos',
            'receptor'
        ])->findOrFail($id);

        return view('panel.operaciones.ordenes_trabajo.print', compact('orden'));
    }
}
