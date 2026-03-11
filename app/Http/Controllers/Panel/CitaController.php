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
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Carbon\Carbon;

class CitaController extends Controller
{
    public function index()
    {
        $sucursales = \App\Models\Sucursal::all();
        return view('panel.operaciones.citas.index', compact('sucursales'));
    }

    public function list(Request $request)
    {
        // Filtros
        $start = $request->get('start');
        $end = $request->get('end');
        $estado = $request->get('estado');
        $sucursalId = $request->get('sucursal_id');

        $query = Cita::with(['cliente', 'vehiculo.marca', 'vehiculo.modelo', 'sucursal'])
            ->orderBy('fecha_programada', 'asc');

        // Si hay filtro de fecha exacto o rango
        if ($start) {
            $endDate = $end ?? $start;
            if (strlen($endDate) <= 10)
                $endDate .= ' 23:59:59';
            if (strlen($start) <= 10)
                $start .= ' 00:00:00';

            $query->whereBetween('fecha_programada', [$start, $endDate]);
        }

        // Filtro por Sucursal (Seguridad para no-admins)
        if (!auth()->user()->hasRole('admin')) {
            $userSids = auth()->user()->sucursales->pluck('id');
            $query->whereIn('sucursal_id', $userSids);
        } elseif ($sucursalId && $sucursalId !== 'all') {
            $query->where('sucursal_id', $sucursalId);
        }

        if ($estado && $estado !== 'all') {
            $query->where('estado', $estado);
        } else if ($estado === 'all') {
            // Show all
        } else {
            $query->whereIn('estado', ['pendiente', 'confirmada']);
        }

        // 1. Calculate Counts (Respect Date & Sucursal, Ignore Status)
        $countsQuery = Cita::query();
        if ($start) {
            $endDateForCounts = $end ?? $start;
            if (strlen($endDateForCounts) <= 10)
                $endDateForCounts .= ' 23:59:59';
            if (strlen($start) <= 10)
                $startClone = $start; // Already formatted above actually, but careful with variable reuse
            else
                $startClone = $start;

            // Re-use logic from above for safety if variable was modified
            $s = $request->get('start');
            if (strlen($s) <= 10)
                $s .= ' 00:00:00';

            $countsQuery->whereBetween('fecha_programada', [$s, $endDateForCounts]);
        }
        if ($sucursalId && $sucursalId !== 'all') {
            $countsQuery->where('sucursal_id', $sucursalId);
        }

        $counts = $countsQuery->select('estado', DB::raw('count(*) as total'))
            ->groupBy('estado')
            ->pluck('total', 'estado');

        // 2. Count Today (Global or Filtered by Sucursal) - SOLO "Confirmadas" (Pendientes de recibir)
        $todayQuery = Cita::whereDate('fecha_programada', now()->toDateString())
            ->where('estado', 'confirmada');

        if ($sucursalId && $sucursalId !== 'all') {
            $todayQuery->where('sucursal_id', $sucursalId);
        }
        $countToday = $todayQuery->count();

        // 3. CAPACIDAD (Visualización para el Secretario)
        // Calculamos la capacidad para el día de INICIO del rango seleccionado (o Hoy si no hay filtro)
        // Esto ayuda a ver la disponibilidad del día que se está consultando.
        $capacityDate = $request->get('start') ? Carbon::parse($request->get('start'))->format('Y-m-d') : now()->format('Y-m-d');

        // Obtenemos sucursales (todas o la filtrada)
        $sucursalesQuery = \App\Models\Sucursal::query();
        if ($sucursalId && $sucursalId !== 'all') {
            $sucursalesQuery->where('id', $sucursalId);
        }
        $sucursalesData = $sucursalesQuery->get();

        $capacities = [];
        foreach ($sucursalesData as $sucursal) {
            // Contar ocupación para ese día (solo cuentan confirmadas, en_taller, etc., NO pendientes)
            $ocupados = Cita::where('sucursal_id', $sucursal->id)
                ->whereDate('fecha_programada', $capacityDate)
                ->whereNotIn('estado', ['cancelada', 'no_asistio', 'pendiente']) // Pendientes no restan espacio
                ->count();

            $capacities[] = [
                'id' => $sucursal->id,
                'nombre' => $sucursal->nombre,
                'capacidad' => $sucursal->capacidad_bahias,
                'ocupados' => $ocupados,
                'disponibles' => max(0, $sucursal->capacidad_bahias - $ocupados),
                'porcentaje' => $sucursal->capacidad_bahias > 0 ? round(($ocupados / $sucursal->capacidad_bahias) * 100) : 100
            ];
        }

        // 4. Get Citas
        $citas = $query->get()->map(function ($cita) {
            $clienteNombre = $cita->cliente?->nombre_completo ?? 'Cliente Desconocido';
            $vehiculoTexto = 'Vehículo Desconocido';
            $isManualVehicle = false;
            $manualInfo = null;

            // Intentar extraer info manual si la marca es GENERICA
            if ($cita->vehiculo && $cita->vehiculo->marca?->nombre === 'GENERICA') {
                if (preg_match('/\[Vehículo Ingresado: (.*?) - (.*?) - (.*?)\]/', $cita->motivo_cita, $matches)) {
                    $isManualVehicle = true;
                    $manualInfo = [
                        'marca' => $matches[1],
                        'modelo' => $matches[2],
                        'version' => $matches[3]
                    ];
                    $vehiculoTexto = trim("{$matches[1]} {$matches[2]} ({$cita->vehiculo->placa})");
                }
            }

            if (!$isManualVehicle && $cita->vehiculo) {
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
                'sucursal' => $cita->sucursal?->nombre ?? 'N/A',
                'cliente' => $clienteNombre,
                'telefono' => $cita->cliente?->telefono ?? 'N/A',
                'vehiculo' => $vehiculoTexto,
                'is_manual_vehicle' => $isManualVehicle,
                'manual_info' => $manualInfo,
                'email' => $cita->cliente?->email,
                'className' => 'fc-event-' . $cita->estado
            ];
        });

        return response()->json([
            'citas' => $citas,
            'counts' => $counts,
            'count_today' => $countToday,
            'capacities' => $capacities, // NEW DATA
            'capacity_date' => $capacityDate
        ]);
    }

    // API para el Calendario (Puntos Verdes/Rojos)
    public function getCalendarCounts(Request $request)
    {
        $month = $request->get('month'); // "2026-02"

        if (!$month)
            return response()->json([]);

        $startOfMonth = Carbon::parse($month . '-01')->startOfMonth();
        $endOfMonth = Carbon::parse($month . '-01')->endOfMonth();

        // Contar citas por día
        // "que las citas que ya estan aceptadas y que ya estan en taller ya no se muestren"
        // Excluir 'concretada' y 'cancelada'/'no_asistio'
        // Contar citas por día
        $countsQuery = Cita::select(DB::raw('DATE(fecha_programada) as date'), DB::raw('count(*) as count'))
            ->whereBetween('fecha_programada', [$startOfMonth, $endOfMonth])
            ->whereIn('estado', ['pendiente', 'confirmada']);

        if (!auth()->user()->hasRole('admin')) {
            $userSids = auth()->user()->sucursales->pluck('id');
            $countsQuery->whereIn('sucursal_id', $userSids);
        }

        $counts = $countsQuery->groupBy('date')
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

                // 2. Evitar crear clientes duplicados
                $telefono = trim($request->telefono_nuevo);
                $email = trim($request->email_nuevo);

                $clienteExistente = Cliente::where('telefono', $telefono)
                    ->when($email !== '', function ($query) use ($email) {
                        return $query->orWhere('email', $email);
                    })->first();

                if ($clienteExistente) {
                    DB::rollBack();
                    return response()->json([
                        'success' => false,
                        'message' => 'Ya existe un cliente registrado con este teléfono o correo (' . $clienteExistente->nombre_completo . '). Por favor cambia a la opción "Buscar Cliente".'
                    ], 422);
                }

                // Generamos cliente 100% nuevo
                $cliente = new Cliente();
                $cliente->telefono = $telefono;
                $cliente->nombre_completo = trim($request->nombre_nuevo);
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
                    $placa = 'S/P-' . time() . '-' . rand(100, 999);
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
                if ($vehiculo->cliente_id !== $cliente->id) {
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
                        $placa = 'S/P-' . time() . '-' . rand(100, 999);
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
                    if ($vehiculo->cliente_id != $clienteId) {
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
                'sucursal_id' => $request->sucursal_id ?? 1,
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

            // Si se confirma la cita, convertir prospectos en activos
            if ($cita->estado === 'confirmada') {
                if ($cita->cliente && $cita->cliente->situacion === 'prospecto') {
                    $cita->cliente->situacion = 'activo';
                    $cita->cliente->save();
                }

                if ($cita->vehiculo && $cita->vehiculo->situacion === 'prospecto') {
                    $cita->vehiculo->situacion = 'activo';
                    $cita->vehiculo->save();
                }

                // NUEVO: Normalizar vehículo si era manual/genérico
                if ($request->has('vehiculo_manual_data') && $cita->vehiculo) {
                    $dataV = $request->vehiculo_manual_data;
                    $nombreMarca = trim(strtoupper($dataV['marca']));

                    // --- VALIDACIÓN DE SIMILITUD (Evitar Honba vs Honda) ---
                    if (!$request->has('confirm_similarity')) {
                        $todasLasMarcas = MarcaVehiculo::pluck('nombre')->toArray();
                        foreach ($todasLasMarcas as $marcaExistente) {
                            $distancia = levenshtein(strtoupper($nombreMarca), strtoupper($marcaExistente));
                            
                            // Umbral dinámico: 
                            // - Para marcas cortas (<=4 letras): máximo 1 error
                            // - Para marcas largas (>4 letras): máximo 3 errores (ej: Yotora vs Toyota)
                            $umbral = (strlen($nombreMarca) <= 4) ? 1 : 3;

                            if ($distancia > 0 && $distancia <= $umbral) {
                                return response()->json([
                                    'success' => false,
                                    'needs_similarity_confirmation' => true,
                                    'message' => "¿Quisiste decir '{$marcaExistente}'?",
                                    'suggestion' => $marcaExistente
                                ], 200);
                            }
                        }
                    }
                    // --------------------------------------------------------
                    
                    // 1. Crear/Buscar registros oficiales
                    $marca = MarcaVehiculo::firstOrCreate(['nombre' => $nombreMarca]);
                    $modelo = ModeloVehiculo::firstOrCreate([
                        'marca_id' => $marca->id, 
                        'nombre' => trim(strtoupper($dataV['modelo']))
                    ]);
                    
                    $versionId = null;
                    if (!empty($dataV['version'])) {
                        $version = VersionVehiculo::firstOrCreate([
                            'modelo_id' => $modelo->id, 
                            'nombre' => trim(strtoupper($dataV['version']))
                        ]);
                        $versionId = $version->id;
                    }

                    // 2. Actualizar vehículo de la cita
                    $cita->vehiculo->update([
                        'marca_id' => $marca->id,
                        'modelo_id' => $modelo->id,
                        'version_id' => $versionId
                    ]);

                    // 3. Limpiar el motivo (quitar la etiqueta [Vehículo Ingresado: ...])
                    $cita->motivo_cita = preg_replace('/\s*\[Vehículo Ingresado:.*?\]/', '', $cita->motivo_cita);
                    $cita->save();
                }
            }

            return response()->json(['success' => true, 'message' => 'Estado actualizado y prospectos activados si corresponde']);
        }

        // Editar normal (Manejo de fecha y hora si vienen separados)
        $data = $request->all();
        if ($request->has('fecha') && $request->has('hora')) {
            $data['fecha_programada'] = Carbon::parse($request->fecha . ' ' . $request->hora);
        }

        $cita->update($data);
        return response()->json(['success' => true, 'message' => 'Cita actualizada']);
    }

    public function destroy($id)
    {
        $cita = Cita::findOrFail($id);
        $cita->delete();
        return response()->json(['success' => true, 'message' => 'Cita eliminada']);
    }

    // API Auxiliares para el Modal de Creación
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

    public function checkClientExists(Request $request)
    {
        $telefono = $request->query('telefono');
        $email = $request->query('email');

        if (!$telefono && !$email) {
            return response()->json(['exists' => false]);
        }

        $query = Cliente::query();

        if ($telefono) {
            $query->where('telefono', $telefono);
        }

        if ($email) {
            // We use orWhere inside a logical group to ensure it doesn't break other conditions if we add more
            $query->orWhere(function ($q) use ($email) {
                if ($email !== '') {
                    $q->where('email', $email);
                }
            });
        }

        $cliente = $query->first(['id', 'nombre_completo', 'email', 'telefono']);

        if ($cliente) {
            return response()->json(['exists' => true, 'cliente' => $cliente]);
        }

        return response()->json(['exists' => false]);
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
                'texto' => $v->placa . ' - ' . ($v->marca?->nombre ?? '') . ' ' . ($v->modelo?->nombre ?? '')
            ];
        });

        return response()->json($data);
    }

    public function getClientVehicles($clienteId)
    {
        $vehiculos = Vehiculo::where('cliente_id', $clienteId)
            ->with(['marca', 'modelo'])
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
                'texto' => ($v->marca?->nombre ?? '') . ' ' . ($v->modelo?->nombre ?? '') . ' - ' . $v->placa
            ];
        });

        return response()->json($data);
    }
    public function getBrands()
    {
        return response()->json(MarcaVehiculo::orderBy('nombre')->get(['id', 'nombre']));
    }
    public function sendNotification(Request $request, $id)
    {
        try {
            $cita = Cita::with(['cliente', 'vehiculo.marca', 'vehiculo.modelo', 'sucursal'])->findOrFail($id);
            $tipo = $request->input('tipo', 'confirmacion'); // 'confirmacion' o 'recordatorio'
            $channel = $request->input('channel', 'whatsapp');

            // Buscar la plantilla correspondiente
            $slug = "cita_{$tipo}";
            $plantilla = \App\Models\PlantillaMensaje::where('slug', $slug)->where('activo', true)->first();

            $fecha = Carbon::parse($cita->fecha_programada);

            $data = [
                'cliente' => $cita->cliente->nombre_completo,
                'vehiculo' => ($cita->vehiculo->marca->nombre ?? '') . ' ' . ($cita->vehiculo->modelo->nombre ?? ''),
                'placa' => $cita->vehiculo->placa ?? 'S/P',
                'fecha' => $fecha->format('d/m/Y'),
                'hora' => $fecha->format('H:i'),
                'sucursal' => $cita->sucursal->nombre ?? 'General',
                'link' => route('home') // Por ahora link a la web
            ];

            $cuerpo = $plantilla ? $plantilla->parse($data) : null;
            $asunto = $plantilla ? $plantilla->parseAsunto($data) : null;

            if ($channel === 'email') {
                if (!$cita->cliente->email) {
                    return response()->json(['success' => false, 'message' => 'El cliente no tiene correo electrónico registrado.'], 400);
                }

                try {
                    // Reusamos la notificación de orden con un ajuste menor o podríamos crear CitaNotification
                    // Por ahora para no crear mil clases si no es necesario:
                    Mail::to($cita->cliente->email)->send(new \App\Mail\OrdenTrabajoStatusNotification($cita->orden ?? new \App\Models\OrdenTrabajo(), 'cita', $cuerpo, $asunto));
                    return response()->json(['success' => true, 'message' => 'Correo enviado correctamente a ' . $cita->cliente->email]);
                } catch (\Exception $e) {
                    return response()->json(['success' => false, 'message' => 'Error al conectar con el servidor de correo: ' . $e->getMessage()], 500);
                }
            }

            return response()->json([
                'success' => true,
                'message' => 'Notificación lista para enviar',
                'whatsapp_text' => $cuerpo
            ]);

        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Error al enviar notificación: ' . $e->getMessage()], 500);
        }
    }
}
