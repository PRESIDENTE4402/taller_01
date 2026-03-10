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
use App\Models\InventarioRecepcionItem;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use App\Mail\OrdenTrabajoStatusNotification;
use App\Models\User;
use App\Notifications\ActividadTaller;

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
        $query = Cita::with(['cliente', 'vehiculo.marca', 'vehiculo.modelo', 'sucursal'])
            ->whereDoesntHave('ordenTrabajo') // Solo pendientes de recibir
            ->whereIn('estado', ['confirmada', 'concretada', 'pendiente']); // Ampliamos estados para que aparezcan más

        // Filtro por Sucursal (No admin solo ve sus sucursales)
        if (!Auth::user()->hasRole('admin')) {
            $userSids = Auth::user()->sucursales->pluck('id');
            $query->whereIn('sucursal_id', $userSids);
        }

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
        $user = Auth::user();
        $isAdmin = $user->hasRole('admin');
        $userSids = $user->sucursales->pluck('id');

        // 1. Todas las Órdenes (Filtradas si no es admin)
        $queryOrdenes = OrdenTrabajo::with(['cliente', 'vehiculo.marca', 'vehiculo.modelo', 'sucursal', 'bitacoras', 'receptor'])
            ->orderBy('created_at', 'desc');

        if (!$isAdmin) {
            $queryOrdenes->whereIn('sucursal_id', $userSids);
        }

        $ordenes = $queryOrdenes->get();

        // 2. Citas "Concretadas" que NO tienen Orden de Trabajo aún (Filtradas si no es admin)
        $queryCitas = Cita::with(['cliente', 'vehiculo.marca', 'vehiculo.modelo', 'sucursal'])
            ->where('estado', 'concretada')
            ->whereDoesntHave('ordenTrabajo')
            ->orderBy('fecha_programada', 'asc');

        if (!$isAdmin) {
            $queryCitas->whereIn('sucursal_id', $userSids);
        }

        $citasPendientes = $queryCitas->get();

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

        $inventoryItems = InventarioRecepcionItem::where('activo', true)->orderBy('orden')->get();

        return view('panel.operaciones.ordenes_trabajo.create', compact('cita', 'vehiculo', 'cliente', 'isAdmin', 'sucursales', 'inventoryItems'));
    }

    public function cancelCita($id)
    {
        try {
            $cita = Cita::findOrFail($id);
            $cita->estado = 'no_asistio';
            $cita->save();

            // Notificar Administradores
            $admins = User::whereHas('roles', fn($q) => $q->where('slug', 'admin'))->get();
            foreach ($admins as $admin) {
                /** @var \App\Models\User $admin */
                $admin->notify(new ActividadTaller(
                    "Cita de {$cita->cliente->nombre_completo} marcada como inasistencia",
                    route('panel.operaciones.citas.index'),
                    'cita_cancelada'
                ));
            }

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

        $inventoryItems = InventarioRecepcionItem::where('activo', true)->orderBy('orden')->get();

        return view('panel.operaciones.ordenes_trabajo.create', compact('orden', 'cliente', 'vehiculo', 'cita', 'isAdmin', 'sucursales', 'inventoryItems'));
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
        $orden = OrdenTrabajo::with(['cliente', 'vehiculo.marca', 'vehiculo.modelo', 'vehiculo.version', 'archivos', 'detalles.repuesto', 'bitacoras.mecanico', 'pagos'])->findOrFail($id);

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
            'bitacoras.mecanico',
            'pagos'
        ])->findOrFail($id);

        return response()->json($orden);
    }



    // Method to add Detail (Repuesto) via AJAX
    public function addDetail(Request $request, $id)
    {
        try {
            $orden = OrdenTrabajo::findOrFail($id);

            // 1. Check stock si es del taller y viene de inventario
            if ($request->suministrado_por === 'taller' && $request->repuesto_id) {
                $repuesto = \App\Models\Repuesto::find($request->repuesto_id);
                if ($repuesto) {
                    if ($repuesto->stock_actual < $request->cantidad) {
                        return response()->json(['success' => false, 'message' => "Stock físico insuficiente en el taller. Solo te quedan {$repuesto->stock_actual} unidades de {$repuesto->nombre}."], 400);
                    }

                    // Descontar inmediatamente para reservar
                    $repuesto->stock_actual -= $request->cantidad;
                    $repuesto->save();

                    // Crear movimiento de salida
                    \Illuminate\Support\Facades\DB::table('movimientos_inventario')->insert([
                        'repuesto_id' => $repuesto->id,
                        'user_id' => \Illuminate\Support\Facades\Auth::id() ?? 1,
                        'cantidad' => $request->cantidad,
                        'tipo' => 'salida',
                        'motivo' => "Despacho a Orden de Trabajo: {$orden->codigo_orden}",
                        'created_at' => now(),
                        'updated_at' => now()
                    ]);
                }
            }

            $detalle = $orden->detalles()->create([
                'repuesto_id' => $request->repuesto_id,
                'descripcion_manual' => $request->descripcion_manual,
                'cantidad' => $request->cantidad,
                'precio_unitario' => $request->precio_unitario,
                'suministrado_por' => $request->suministrado_por ?? 'taller',
                'estado' => $request->estado ?? 'pendiente', // Por defecto nacen pendientes de aprobación
                'notas' => $request->notas
            ]);

            // Si se agrega un detalle extra mientras ya se estaba reparando, 
            // el flujo manda a pausar la orden a espera de confirmación cliente
            if ($orden->estado === 'en_proceso') {
                $orden->estado = 'espera_aprobacion_adicional';
                $orden->save();
            }

            // Notificar Administradores
            $admins = User::whereHas('roles', fn($q) => $q->where('slug', 'admin'))->get();
            $itemNombre = $detalle->repuesto ? $detalle->repuesto->nombre : $detalle->descripcion_manual;
            foreach ($admins as $admin) {
                /** @var \App\Models\User $admin */
                $admin->notify(new ActividadTaller(
                    "Repuesto/Servicio agregado a OT #{$orden->codigo_orden}: {$itemNombre}",
                    route('panel.operaciones.ordenes_trabajo.show', $orden->id),
                    'detalle_agregado'
                ));
            }

            return response()->json(['success' => true, 'message' => 'Detalle agregado con éxito', 'data' => $detalle]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function updateDetailStatus(Request $request, $id, $detail_id)
    {
        try {
            $orden = OrdenTrabajo::findOrFail($id);
            $detalle = $orden->detalles()->findOrFail($detail_id);

            // Logica de Devolución o Reasignación de Stock
            if ($request->estado === 'rechazado' && $detalle->estado !== 'rechazado') {
                // Return stock
                if ($detalle->suministrado_por === 'taller' && $detalle->repuesto_id) {
                    $repuesto = \App\Models\Repuesto::find($detalle->repuesto_id);
                    if ($repuesto) {
                        $repuesto->stock_actual += $detalle->cantidad;
                        $repuesto->save();
                        \Illuminate\Support\Facades\DB::table('movimientos_inventario')->insert([
                            'repuesto_id' => $repuesto->id,
                            'user_id' => \Illuminate\Support\Facades\Auth::id() ?? 1,
                            'cantidad' => $detalle->cantidad,
                            'tipo' => 'entrada',
                            'motivo' => "Devolución por repuesto rechazado en OT: {$orden->codigo_orden}",
                            'created_at' => now(),
                            'updated_at' => now()
                        ]);
                    }
                }
            } elseif ($detalle->estado === 'rechazado' && $request->estado !== 'rechazado') {
                // Re-deduct stock
                if ($detalle->suministrado_por === 'taller' && $detalle->repuesto_id) {
                    $repuesto = \App\Models\Repuesto::find($detalle->repuesto_id);
                    if ($repuesto && $repuesto->stock_actual < $detalle->cantidad) {
                        return response()->json(['success' => false, 'message' => "Stock insuficiente para reactivar el ítem. Quedan: {$repuesto->stock_actual}"], 400);
                    }
                    if ($repuesto) {
                        $repuesto->stock_actual -= $detalle->cantidad;
                        $repuesto->save();
                        \Illuminate\Support\Facades\DB::table('movimientos_inventario')->insert([
                            'repuesto_id' => $repuesto->id,
                            'user_id' => \Illuminate\Support\Facades\Auth::id() ?? 1,
                            'cantidad' => $detalle->cantidad,
                            'tipo' => 'salida',
                            'motivo' => "Reasignación de estado final en OT: {$orden->codigo_orden}",
                            'created_at' => now(),
                            'updated_at' => now()
                        ]);
                    }
                }
            }

            $detalle->estado = $request->estado; // 'aprobado', 'rechazado', 'pendiente'
            $detalle->save();

            // Lógica opcional: si todos fueron aprobados/rechazados y estaba en espera de aprobación adicional, devolver a en_proceso
            if ($orden->estado === 'espera_aprobacion_adicional') {
                $pendientes = $orden->detalles()->where('estado', 'pendiente')->count();
                if ($pendientes === 0) {
                    $orden->estado = 'en_proceso'; // Ya respondió a todos los adicionales
                    $orden->save();
                }
            }

            return response()->json(['success' => true, 'message' => 'Estado del ítem actualizado', 'data' => $detalle]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function updateDetail(Request $request, $id, $detail_id)
    {
        try {
            $orden = OrdenTrabajo::findOrFail($id);
            $detalle = $orden->detalles()->findOrFail($detail_id);

            // Ajuste de stock si cambia la cantidad y está como taller y no rechazado
            if ($request->has('cantidad') && $detalle->suministrado_por === 'taller' && $detalle->repuesto_id && $detalle->estado !== 'rechazado') {
                $diferencia = $request->cantidad - $detalle->cantidad;
                if ($diferencia != 0) {
                    $repuesto = \App\Models\Repuesto::find($detalle->repuesto_id);
                    if ($diferencia > 0) {
                        // Necesita más stock
                        if ($repuesto && $repuesto->stock_actual < $diferencia) {
                            return response()->json(['success' => false, 'message' => "Stock insuficiente para aumentar la cantidad. Quedan: {$repuesto->stock_actual}"], 400);
                        }
                        if ($repuesto) {
                            $repuesto->stock_actual -= $diferencia;
                            $repuesto->save();
                            \Illuminate\Support\Facades\DB::table('movimientos_inventario')->insert([
                                'repuesto_id' => $repuesto->id,
                                'user_id' => \Illuminate\Support\Facades\Auth::id() ?? 1,
                                'cantidad' => $diferencia,
                                'tipo' => 'salida',
                                'motivo' => "Suma de cantidad al editar en OT: {$orden->codigo_orden}",
                                'created_at' => now(),
                                'updated_at' => now()
                            ]);
                        }
                    } elseif ($diferencia < 0) {
                        // Devuelve stock
                        $absDif = abs($diferencia);
                        if ($repuesto) {
                            $repuesto->stock_actual += $absDif;
                            $repuesto->save();
                            \Illuminate\Support\Facades\DB::table('movimientos_inventario')->insert([
                                'repuesto_id' => $repuesto->id,
                                'user_id' => \Illuminate\Support\Facades\Auth::id() ?? 1,
                                'cantidad' => $absDif,
                                'tipo' => 'entrada',
                                'motivo' => "Resta de cantidad al editar en OT: {$orden->codigo_orden}",
                                'created_at' => now(),
                                'updated_at' => now()
                            ]);
                        }
                    }
                }
            }

            // Permitimos solo ciertos campos si se necesita
            $detalle->update([
                'descripcion_manual' => $request->descripcion_manual,
                'cantidad' => $request->cantidad,
                'precio_unitario' => $request->precio_unitario,
                'suministrado_por' => $request->suministrado_por ?? $detalle->suministrado_por,
            ]);

            return response()->json(['success' => true, 'message' => 'Repuesto actualizado con éxito', 'data' => $detalle]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function deleteDetail($id, $detail_id)
    {
        try {
            $orden = OrdenTrabajo::findOrFail($id);
            $detalle = $orden->detalles()->findOrFail($detail_id);

            if ($detalle->suministrado_por === 'taller' && $detalle->repuesto_id && $detalle->estado !== 'rechazado') {
                $repuesto = \App\Models\Repuesto::find($detalle->repuesto_id);
                if ($repuesto) {
                    $repuesto->stock_actual += $detalle->cantidad;
                    $repuesto->save();
                    \Illuminate\Support\Facades\DB::table('movimientos_inventario')->insert([
                        'repuesto_id' => $repuesto->id,
                        'user_id' => \Illuminate\Support\Facades\Auth::id() ?? 1,
                        'cantidad' => $detalle->cantidad,
                        'tipo' => 'entrada',
                        'motivo' => "Devolución por repuesto eliminado de OT: {$orden->codigo_orden}",
                        'created_at' => now(),
                        'updated_at' => now()
                    ]);
                }
            }

            $detalle->delete();

            // Notificar Administradores
            $admins = User::whereHas('roles', fn($q) => $q->where('slug', 'admin'))->get();
            $itemNombre = $detalle->repuesto ? $detalle->repuesto->nombre : $detalle->descripcion_manual;
            foreach ($admins as $admin) {
                /** @var \App\Models\User $admin */
                $admin->notify(new ActividadTaller(
                    "Repuesto/Servicio eliminado de OT #{$orden->codigo_orden}: {$itemNombre}",
                    route('panel.operaciones.ordenes_trabajo.show', $orden->id),
                    'detalle_eliminado'
                ));
            }

            return response()->json(['success' => true, 'message' => 'Repuesto eliminado con éxito']);
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
                'estado' => 'en_pausa', // Default planned
                'precio_cliente' => $request->precio_cliente ?? 0,
                'tipo_pago_mecanico' => $request->tipo_pago_mecanico ?? 'porcentaje',
                'valor_pago_mecanico' => $request->valor_pago_mecanico ?? 0,
                'descuento_cliente' => $request->descuento_cliente ?? 0,
                'motivo_descuento' => $request->motivo_descuento ?? null
            ]);

            // Al asignar la primera tarea, podríamos pasar a "en_proceso" automáticamente
            if ($orden->estado == 'abierta') {
                $orden->estado = 'en_proceso';
                $orden->save();
            }

            // Notificar Administradores
            $admins = User::whereHas('roles', fn($q) => $q->where('slug', 'admin'))->get();
            $mecanicoNombre = $tarea->mecanico ? $tarea->mecanico->name : 'un mecánico';
            foreach ($admins as $admin) {
                /** @var \App\Models\User $admin */
                $admin->notify(new ActividadTaller(
                    "Nueva tarea asignada en la Orden #{$orden->codigo_orden}: '{$request->descripcion}' a {$mecanicoNombre}",
                    route('panel.operaciones.ordenes_trabajo.show', $orden->id),
                    'tarea_asignada'
                ));
            }

            return response()->json(['success' => true, 'message' => 'Tarea asignada', 'data' => $tarea]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function updateTask(Request $request, $id, $task_id)
    {
        try {
            $orden = OrdenTrabajo::findOrFail($id);
            $tarea = $orden->bitacoras()->findOrFail($task_id);

            $tarea->update([
                'user_id' => $request->user_id,
                'descripcion' => $request->descripcion,
                'meta_minutos' => $request->meta_minutos,
                'precio_cliente' => $request->precio_cliente ?? 0,
                'tipo_pago_mecanico' => $request->tipo_pago_mecanico ?? 'porcentaje',
                'valor_pago_mecanico' => $request->valor_pago_mecanico ?? 0,
                'descuento_cliente' => $request->descuento_cliente ?? 0,
                'motivo_descuento' => $request->motivo_descuento ?? null
            ]);

            return response()->json(['success' => true, 'message' => 'Tarea actualizada']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function deleteTask($id, $task_id)
    {
        try {
            $orden = OrdenTrabajo::findOrFail($id);
            $tarea = $orden->bitacoras()->findOrFail($task_id);
            $tarea->delete();

            return response()->json(['success' => true, 'message' => 'Tarea eliminada']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function updateDiagnostico(Request $request, $id)
    {
        try {
            $orden = OrdenTrabajo::findOrFail($id);
            $orden->diagnostico = $request->diagnostico;
            $orden->diagnostico_final = $request->diagnostico_final;
            $orden->save();

            return response()->json(['success' => true, 'message' => 'Diagnóstico actualizado correctamente']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function updateStatus(Request $request, $id)
    {
        try {
            $orden = OrdenTrabajo::findOrFail($id);
            $orden->estado = $request->estado;

            if ($request->has('motivo_estado')) {
                $orden->motivo_estado = $request->motivo_estado;
            }

            if ($request->estado == 'finalizada') {
                $orden->fecha_finalizacion = now();
            }

            if ($request->estado == 'entregada') {
                $orden->fecha_entrega = now();
            }

            // Si reanuda, limpiar el motivo
            if ($request->estado == 'en_proceso') {
                $orden->motivo_estado = null;
            }

            $orden->save();

            // Notificar Administradores
            $motivoTxt = $orden->motivo_estado ? " (Motivo: {$orden->motivo_estado})" : "";
            $admins = User::whereHas('roles', fn($q) => $q->where('slug', 'admin'))->get();
            foreach ($admins as $admin) {
                /** @var \App\Models\User $admin */
                $admin->notify(new ActividadTaller(
                    "Orden #{$orden->codigo_orden} cambió a estado: " . strtoupper($request->estado) . $motivoTxt,
                    route('panel.operaciones.ordenes_trabajo.show', $orden->id),
                    'orden_estado'
                ));
            }

            return response()->json(['success' => true, 'message' => 'Estado actualizado a ' . $request->estado]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function sendNotification(Request $request, $id)
    {
        try {
            $orden = OrdenTrabajo::with(['cliente', 'vehiculo.marca', 'vehiculo.modelo', 'sucursal'])->findOrFail($id);
            $tipo = $request->input('tipo', 'fase'); // 'fase', 'listo' o 'cotizacion'
            $channel = $request->input('channel', 'whatsapp');

            // Buscar la plantilla correspondiente
            $slug = "orden_{$tipo}";
            $plantilla = \App\Models\PlantillaMensaje::where('slug', $slug)->where('activo', true)->first();

            $data = [
                'cliente' => $orden->cliente->nombre_completo,
                'vehiculo' => ($orden->vehiculo->marca->nombre ?? '') . ' ' . ($orden->vehiculo->modelo->nombre ?? ''),
                'placa' => $orden->vehiculo->placa,
                'codigo_orden' => $orden->codigo_orden,
                'sucursal' => $orden->sucursal->nombre ?? 'General',
                'fase' => str_replace('_', ' ', $orden->estado),
                'link' => route('panel.operaciones.ordenes_trabajo.print', $orden->id) . ($tipo === 'fase' ? '?mode=avances' : '?mode=full')
            ];

            $cuerpo = $plantilla ? $plantilla->parse($data) : null;
            $asunto = $plantilla ? $plantilla->parseAsunto($data) : null;

            if ($channel === 'email') {
                if (!$orden->cliente->email) {
                    return response()->json(['success' => false, 'message' => 'El cliente no tiene correo electrónico registrado.'], 400);
                }

                try {
                    Mail::to($orden->cliente->email)->send(new OrdenTrabajoStatusNotification($orden, $tipo, $cuerpo, $asunto));
                    return response()->json(['success' => true, 'message' => 'Correo enviado correctamente a ' . $orden->cliente->email]);
                } catch (\Exception $e) {
                    return response()->json(['success' => false, 'message' => 'Error al conectar con el servidor de correo: ' . $e->getMessage()], 500);
                }
            }

            // Para WhatsApp, retornamos el cuerpo procesado para que el frontend lo use
            return response()->json([
                'success' => true,
                'message' => 'Notificación lista para enviar',
                'whatsapp_text' => $cuerpo
            ]);

        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Error al enviar notificación: ' . $e->getMessage()], 500);
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
                /** @var \App\Models\Cita $cita */
                $cita = Cita::find($request->cita_id);
                if ($cita) {
                    $cita->estado = 'concretada'; // Estado válido
                    $cita->save();
                }
            }

            DB::commit();

            // Notificar Administradores
            $admins = User::whereHas('roles', fn($q) => $q->where('slug', 'admin'))->get();
            foreach ($admins as $admin) {
                /** @var \App\Models\User $admin */
                $admin->notify(new ActividadTaller(
                    "Nueva Orden de Trabajo creada: {$orden->codigo_orden} para {$orden->cliente->nombre_completo}",
                    route('panel.operaciones.ordenes_trabajo.show', $orden->id),
                    'orden_creada'
                ));
            }

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

    public function print(Request $request, $id)
    {
        $orden = OrdenTrabajo::with([
            'cliente',
            'vehiculo.marca',
            'vehiculo.modelo',
            'vehiculo.version',
            'sucursal',
            'archivos',
            'receptor',
            'bitacoras',
            'detalles.repuesto'
        ])->findOrFail($id);

        $mode = $request->get('mode', 'full'); // 'full' or 'avances'
        $inventoryItems = InventarioRecepcionItem::all();

        return view('panel.operaciones.ordenes_trabajo.print', compact('orden', 'mode', 'inventoryItems'));
    }
    public function searchRepuestos(Request $request)
    {
        $term = $request->term;
        $repuestos = \App\Models\Repuesto::with('categoria')
            ->where(function ($query) use ($term) {
                $query->where('nombre', 'LIKE', "%$term%")
                    ->orWhere('codigo_interno', 'LIKE', "%$term%");
            })
            ->take(20) // un limite para que sea ágil
            ->get();

        $data = $repuestos->map(function ($r) {
            return [
                'id' => $r->id,
                'nombre' => $r->nombre,
                'codigo' => $r->codigo_interno,
                'precio' => $r->precio_venta,
                'categoria' => $r->categoria ? $r->categoria->nombre : 'Sin Categoría',
                'texto' => $r->codigo_interno . ' - ' . $r->nombre . ' (Q.' . number_format($r->precio_venta, 2) . ')'
            ];
        });

        return response()->json($data);
    }

    public function storeFastRepuesto(Request $request)
    {
        try {
            $request->validate([
                'nombre' => 'required|string',
                'precio_venta' => 'required|numeric',
                'stock_actual' => 'required|numeric',
            ]);

            $codigo = 'RP-' . strtoupper(substr(uniqid(), -5));
            $sucursal_id = Auth::user()->sucursales->first()?->id ?? 1;

            $repuesto = \App\Models\Repuesto::create([
                'sucursal_id' => $sucursal_id,
                'codigo_interno' => $codigo,
                'nombre' => strtoupper($request->nombre),
                'precio_venta' => $request->precio_venta,
                'precio_costo' => $request->precio_venta * 0.7, // Costo dummy por defecto
                'stock_actual' => $request->stock_actual,
                'stock_minimo' => 1,
                'categoria_id' => null, // Permite nulo si no se especifica
                'unidad_medida' => 'unidad'
            ]);

            // Crear movimiento de entrada para el inventario inicial
            \Illuminate\Support\Facades\DB::table('movimientos_inventario')->insert([
                'repuesto_id' => $repuesto->id,
                'user_id' => Auth::id() ?? 1,
                'cantidad' => $request->stock_actual,
                'tipo' => 'entrada',
                'motivo' => "Ingreso rápido desde ventana de Orden de Trabajo",
                'created_at' => now(),
                'updated_at' => now()
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Pieza agregada con éxito al inventario maestro.',
                'repuesto' => [
                    'id' => $repuesto->id,
                    'nombre' => $repuesto->nombre,
                    'codigo' => $repuesto->codigo_interno,
                    'precio' => $repuesto->precio_venta
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function storePago(Request $request, $id)
    {
        try {
            $request->validate([
                'monto' => 'required|numeric|min:0.01',
                'metodo_pago' => 'required|string',
            ]);

            $orden = OrdenTrabajo::findOrFail($id);

            // Register movement/payment
            $pago = new \App\Models\Pago([
                'monto' => $request->monto,
                'metodo_pago' => $request->metodo_pago,
                'orden_trabajo_id' => $orden->id
            ]);
            $pago->save();

            // Notificar Administradores
            $admins = User::whereHas('roles', fn($q) => $q->where('slug', 'admin'))->get();
            foreach ($admins as $admin) {
                /** @var \App\Models\User $admin */
                $admin->notify(new ActividadTaller(
                    "Pago de Q." . number_format($request->monto, 2) . " recibido para la Orden #{$orden->codigo_orden}",
                    route('panel.operaciones.ordenes_trabajo.show', $orden->id),
                    'pago_recibido'
                ));
            }

            return response()->json([
                'success' => true,
                'message' => 'Cobro registrado con éxito.',
                'pago' => $pago
            ]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function updatePago(Request $request, $id, $pagoId)
    {
        try {
            $request->validate([
                'monto' => 'required|numeric|min:0.01',
                'metodo_pago' => 'required|string',
            ]);

            $pago = \App\Models\Pago::where('orden_trabajo_id', $id)->findOrFail($pagoId);
            $pago->monto = $request->monto;
            $pago->metodo_pago = $request->metodo_pago;
            $pago->save();

            return response()->json([
                'success' => true,
                'message' => 'Pago actualizado con éxito.',
                'pago' => $pago
            ]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function deletePago($id, $pagoId)
    {
        try {
            $pago = \App\Models\Pago::where('orden_trabajo_id', $id)->findOrFail($pagoId);
            $pago->delete();

            return response()->json([
                'success' => true,
                'message' => 'Pago eliminado con éxito.'
            ]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }
}
