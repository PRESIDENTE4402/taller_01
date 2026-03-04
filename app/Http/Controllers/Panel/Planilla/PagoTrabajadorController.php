<?php

namespace App\Http\Controllers\Panel\Planilla;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class PagoTrabajadorController extends Controller
{
    public function index()
    {
        // Traemos a los trabajadores (Mecánicos, ayudantes, etc. O simplemente a todos los colaboradores de la sucursal actual)
        // Por ahora listaremos todos los usuarios activos de la sucursal para poder seleccionarlos en el modal de pago
        $sucursalId = session('sucursal_id') ?? Auth::user()->sucursales->first()->id;

        $trabajadores = \App\Models\User::whereHas('sucursales', function ($q) use ($sucursalId) {
            $q->where('sucursales.id', $sucursalId);
        })->get();

        return view('panel.planilla.pagos.index', compact('trabajadores'));
    }

    public function list()
    {
        $sucursalId = session('sucursal_id') ?? Auth::user()->sucursales->first()->id;

        $pagos = \App\Models\PagoTrabajador::with('trabajador')
            ->whereHas('trabajador.sucursales', function ($q) use ($sucursalId) {
                $q->where('sucursales.id', $sucursalId);
            })
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json($pagos);
    }

    public function getTrabajadorData(Request $request, $userId)
    {
        $sucursalId = session('sucursal_id');
        $inicio = $request->get('inicio');
        $fin = $request->get('fin');

        // 1. Buscar Asistencias (resumen)
        $asistenciasQuery = \App\Models\Asistencia::where('user_id', $userId)
            ->where('sucursal_id', $sucursalId);

        if ($inicio && $fin) {
            $asistenciasQuery->whereBetween('fecha', [$inicio, $fin]);
        }

        $asistencias = $asistenciasQuery->get();
        $resumenAsistencia = [
            'total' => $asistencias->count(),
            'presente' => $asistencias->where('tipo', 'presente')->count(),
            'ausente' => $asistencias->where('tipo', 'ausente')->count(),
            'permiso' => $asistencias->where('tipo', 'permiso')->count(),
            'tardanzas' => $asistencias->where('estado', 'tardanza')->count()
        ];

        // 2. Buscar Trabajos PENDIENTES (Sin importar la fecha, porque el pago es por trabajo)
        $trabajos = \App\Models\BitacoraTrabajo::with(['orden.vehiculo', 'orden.cliente'])
            ->where('user_id', $userId)
            ->where('sucursal_id', $sucursalId)
            ->where('estado_pago', 'pendiente')
            ->orderBy('created_at', 'asc')
            ->get();

        return response()->json([
            'asistencia' => $resumenAsistencia,
            'trabajos' => $trabajos
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'monto_total' => 'required|numeric|min:0',
            'fecha_pago' => 'required|date',
            'metodo_pago' => 'required|string',
            'trabajos' => 'required|array', // IDs de BitacorasTrabajo
            'trabajos.*' => 'exists:bitacoras_trabajo,id'
        ]);

        DB::beginTransaction();
        try {
            $pago = \App\Models\PagoTrabajador::create([
                'user_id' => $request->user_id,
                'monto_total' => $request->monto_total,
                'fecha_pago' => $request->fecha_pago,
                'fecha_inicio_periodo' => $request->fecha_inicio_periodo,
                'fecha_fin_periodo' => $request->fecha_fin_periodo,
                'metodo_pago' => $request->metodo_pago,
                'observaciones' => $request->observaciones
            ]);

            // Marcar los trabajos como pagados y enlazarlos
            \App\Models\BitacoraTrabajo::whereIn('id', $request->trabajos)
                ->update([
                    'estado_pago' => 'pagado',
                    'pago_trabajador_id' => $pago->id
                ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Pago procesado exitosamente.'
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Error al procesar el pago: ' . $e->getMessage()
            ], 500);
        }
    }
}
