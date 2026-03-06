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

    public function list(Request $request)
    {
        $sucursalId = session('sucursal_id') ?? Auth::user()->sucursales->first()->id;

        $mes = $request->get('mes');
        $userId = $request->get('user_id');

        $query = \App\Models\PagoTrabajador::with('trabajador')
            ->whereHas('trabajador.sucursales', function ($q) use ($sucursalId) {
                $q->where('sucursales.id', $sucursalId);
            });

        if ($userId) {
            $query->where('user_id', $userId);
        }

        if ($mes) {
            // $mes tiene formato "YYYY-MM"
            $parts = explode('-', $mes);
            if (count($parts) === 2) {
                $query->whereYear('fecha_pago', $parts[0])
                    ->whereMonth('fecha_pago', $parts[1]);
            }
        } else {
            // Por defecto, traer los del mes actual y el mes pasado para no saturar
            $currentDate = now();
            $lastMonth = now()->subMonth();
            $query->where(function ($q) use ($currentDate, $lastMonth) {
                $q->where(function ($subQ) use ($currentDate) {
                    $subQ->whereYear('fecha_pago', $currentDate->year)
                        ->whereMonth('fecha_pago', $currentDate->month);
                })->orWhere(function ($subQ) use ($lastMonth) {
                    $subQ->whereYear('fecha_pago', $lastMonth->year)
                        ->whereMonth('fecha_pago', $lastMonth->month);
                });
            });
        }

        $pagos = $query->orderBy('fecha_pago', 'desc')->get();

        return response()->json($pagos);
    }

    public function detalles($id)
    {
        $pago = \App\Models\PagoTrabajador::with(['trabajador', 'bitacoras.orden.vehiculo.modelo', 'bitacoras.orden.cliente'])->findOrFail($id);

        // Ensure user is from the same sucursal 
        return response()->json([
            'pago' => $pago,
            'trabajos' => $pago->bitacoras->map(function ($t) {
                $vehiculo = $t->orden->vehiculo->placa ?? 'N/A';
                if ($t->orden && $t->orden->vehiculo && $t->orden->vehiculo->modelo) {
                    $vehiculo .= ' (' . $t->orden->vehiculo->modelo->nombre . ')';
                }

                $precio_calc = $t->monto_pago ?? 0;
                if ($precio_calc == 0) {
                    $base = ($t->precio_cliente ?? 0) - ($t->descuento_cliente ?? 0);
                    if ($t->tipo_pago_mecanico === 'porcentaje') {
                        $precio_calc = $base * (($t->valor_pago_mecanico ?? 0) / 100);
                    } else {
                        $precio_calc = $t->valor_pago_mecanico ?? 0;
                    }
                }

                return [
                    'id' => $t->id,
                    'fecha' => $t->created_at->format('Y-m-d'),
                    'orden_codigo' => $t->orden->codigo_orden ?? '-',
                    'vehiculo' => $vehiculo,
                    'actividad' => $t->tipo_actividad,
                    'descripcion' => $t->descripcion,
                    'monto' => $precio_calc
                ];
            })
        ]);
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

    public function getTrabajadorDataEdit(Request $request, $pagoId)
    {
        $sucursalId = session('sucursal_id');
        $pago = \App\Models\PagoTrabajador::findOrFail($pagoId);
        $userId = $pago->user_id;

        $trabajos = \App\Models\BitacoraTrabajo::with(['orden.vehiculo', 'orden.cliente'])
            ->where('user_id', $userId)
            ->where('sucursal_id', $sucursalId)
            ->where(function ($q) use ($pagoId) {
                $q->where('estado_pago', 'pendiente')
                    ->orWhere('pago_trabajador_id', $pagoId);
            })
            ->orderBy('created_at', 'asc')
            ->get();

        return response()->json([
            'trabajos' => $trabajos,
            'pago_id' => $pagoId
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
                'sueldo_base' => $request->sueldo_base ?? 0,
                'descuentos' => $request->descuentos ?? 0,
                'metodo_pago' => $request->metodo_pago,
                'observaciones' => $request->observaciones
            ]);

            // Marcar los trabajos como pagados y enlazarlos
            foreach ($request->trabajos as $trabajoId) {
                \App\Models\BitacoraTrabajo::where('id', $trabajoId)
                    ->update([
                        'estado_pago' => 'pagado',
                        'pago_trabajador_id' => $pago->id,
                        'monto_pago' => $request->montos[$trabajoId] ?? 0
                    ]);
            }

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

    public function update(Request $request, $id)
    {
        $request->validate([
            'sueldo_base' => 'nullable|numeric|min:0',
            'descuentos' => 'nullable|numeric|min:0',
            'metodo_pago' => 'required|string',
            'trabajos' => 'required|array',
            'montos' => 'required|array'
        ]);

        DB::beginTransaction();
        try {
            $pago = \App\Models\PagoTrabajador::with('bitacoras')->findOrFail($id);

            // Liberar trabajos anteriores (por si desmarcaron algunos)
            \App\Models\BitacoraTrabajo::where('pago_trabajador_id', $id)
                ->whereNotIn('id', $request->trabajos)
                ->update([
                    'estado_pago' => 'pendiente',
                    'pago_trabajador_id' => null,
                    'monto_pago' => null
                ]);

            // Asignar los nuevos y actualizar los que se mantienen
            $totalTrabajos = 0;
            foreach ($request->trabajos as $trabajoId) {
                $montoManual = floatval($request->montos[$trabajoId] ?? 0);
                \App\Models\BitacoraTrabajo::where('id', $trabajoId)
                    ->update([
                        'estado_pago' => 'pagado',
                        'pago_trabajador_id' => $pago->id,
                        'monto_pago' => $montoManual
                    ]);
                $totalTrabajos += $montoManual;
            }

            $sueldoBase = floatval($request->sueldo_base ?? 0);
            $descuentos = floatval($request->descuentos ?? 0);

            $montoTotal = $totalTrabajos + $sueldoBase - $descuentos;
            if ($montoTotal < 0)
                $montoTotal = 0;

            $pago->update([
                'sueldo_base' => $sueldoBase,
                'descuentos' => $descuentos,
                'monto_total' => $montoTotal,
                'metodo_pago' => $request->metodo_pago,
                'observaciones' => $request->observaciones,
                'modificado' => true
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Pago actualizado exitosamente.',
                'pago' => $pago
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Error al actualizar el pago: ' . $e->getMessage()
            ], 500);
        }
    }

    public function destroy($id)
    {
        DB::beginTransaction();
        try {
            $pago = \App\Models\PagoTrabajador::findOrFail($id);

            // Liberar trabajos anteriores
            \App\Models\BitacoraTrabajo::where('pago_trabajador_id', $id)
                ->update([
                    'estado_pago' => 'pendiente',
                    'pago_trabajador_id' => null,
                    'monto_pago' => null
                ]);

            $pago->delete();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Pago eliminado exitosamente. Los trabajos han retornado a la lista de pendientes.'
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Error al eliminar el pago: ' . $e->getMessage()
            ], 500);
        }
    }
}
