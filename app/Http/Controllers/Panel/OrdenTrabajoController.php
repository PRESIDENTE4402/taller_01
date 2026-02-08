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

            // Validación (simplificada por ahora)
            $request->validate([
                'cliente_id' => 'required|exists:clientes,id',
                'vehiculo_id' => 'required|exists:vehiculos,id',
                'kilometraje' => 'required|integer',
                'nivel_combustible' => 'required|string',
                'falla_cliente' => 'required|string',
                'color' => 'required|string',
            ]);

            // Generar Código (Ej: OT-2026-0001)
            $lastId = OrdenTrabajo::max('id') ?? 0;
            $codigo = 'OT-' . date('Y') . '-' . str_pad($lastId + 1, 4, '0', STR_PAD_LEFT);

            $orden = OrdenTrabajo::create([
                'sucursal_id' => 1, // Hardcoded por ahora o Auth::user()->sucursal_id
                'codigo_orden' => $codigo,
                'tipo_orden' => $request->tipo_orden ?? 'normal',
                'vehiculo_id' => $request->vehiculo_id,
                'cliente_id' => $request->cliente_id,
                'cita_id' => $request->cita_id, // Nullable
                'receptor_id' => Auth::id() ?? 1,
                'fecha_recepcion' => now(),
                'color' => $request->color,
                'kilometraje_entrada' => $request->kilometraje,
                'nivel_combustible' => $request->nivel_combustible,
                'inventario_recepcion' => json_encode($request->inventario ?? []), // Checklist
                'danos_reportados' => json_encode($request->danos ?? []), // Fotos o coordenadas
                'falla_cliente' => $request->falla_cliente,
                'estado' => 'abierta'
            ]);

            DB::commit();

            return response()->json(['success' => true, 'redirect' => route('panel.operaciones.ordenes_trabajo.index'), 'message' => 'Orden creada con éxito']);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => 'Error al crear orden: ' . $e->getMessage()], 500);
        }
    }
}
