<?php

namespace App\Http\Controllers\Panel;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\BitacoraTrabajo;
use App\Models\OrdenTrabajo;
use App\Models\Sucursal;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use App\Notifications\TareaAsignada;

class ColaboradorController extends Controller
{
    public function index()
    {
        $sucursales = Sucursal::all();
        $ordenesActivas = OrdenTrabajo::whereNotIn('estado', ['finalizada', 'entregada'])->get();
        return view('panel.operaciones.colaboradores.index', compact('sucursales', 'ordenesActivas'));
    }

    public function list(Request $request)
    {
        $sucursalId = $request->get('sucursal_id');
        /** @var \App\Models\User $user */
        $user = Auth::user();

        // Si se pide una sucursal específica, validar acceso (excepto para administradores)
        if (!empty($sucursalId) && !$user->hasRole('admin')) {
            $userSucursals = $user->sucursales->pluck('id');
            if (!$userSucursals->contains($sucursalId)) {
                return response()->json(['success' => false, 'message' => 'Sin acceso a esta sucursal'], 403);
            }
        }

        // Obtenemos usuarios con rol 'mecanico' (o ajusta según desees mostrar)
        $usersQuery = User::whereHas('roles', function ($q) {
            $q->whereIn('slug', ['mecanico', 'tecnico', 'ayudante']);
        })->with(['sucursales', 'persona']);

        if (!empty($sucursalId)) {
            $usersQuery->whereHas('sucursales', function ($q) use ($sucursalId) {
                $q->where('sucursales.id', $sucursalId);
            });
        }

        $users = $usersQuery->get();

        $today = Carbon::today();

        // Mapeamos lo que está haciendo cada uno
        $reporte = $users->map(function ($user) use ($today) {
            // Última tarea no completada o la más reciente de hoy
            $tareaActual = BitacoraTrabajo::with(['orden.vehiculo'])
                ->where('user_id', $user->id)
                ->whereIn('estado', ['en_progreso', 'en_pausa'])
                ->orderBy('created_at', 'desc')
                ->first();

            // Historial de hoy
            $tareasHoy = BitacoraTrabajo::where('user_id', $user->id)
                ->whereDate('created_at', $today)
                ->count();

            return [
                'id' => $user->id,
                'nombre' => $user->persona ? ($user->persona->nombres . ' ' . $user->persona->apellidos) : $user->name,
                'sucursal' => $user->sucursales->first()->nombre ?? 'N/A',
                'tarea_actual' => $tareaActual,
                'estado_laboral' => $tareaActual ? ($tareaActual->estado == 'en_progreso' ? 'ocupado' : 'pausado') : 'disponible',
                'tareas_completadas_hoy' => BitacoraTrabajo::where('user_id', $user->id)->whereDate('created_at', $today)->where('estado', 'completado')->count(),
                'total_tareas_hoy' => $tareasHoy
            ];
        });

        return response()->json($reporte);
    }

    public function assignTask(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'descripcion' => 'required|string|max:255',
            'tipo_actividad' => 'required',
            'meta_minutos' => 'nullable|integer'
        ]);

        try {
            $user = User::find($request->user_id);
            $sucursalId = $user->sucursales->first()->id ?? 1;

            $tarea = BitacoraTrabajo::create([
                'user_id' => $request->user_id,
                'sucursal_id' => $sucursalId,
                'orden_trabajo_id' => $request->orden_trabajo_id, // Puede ser null
                'tipo_actividad' => $request->tipo_actividad,
                'descripcion' => $request->descripcion,
                'meta_minutos' => $request->meta_minutos ?? 30,
                'estado' => 'en_pausa'
            ]);

            $user->notify(new TareaAsignada($tarea));

            return response()->json(['success' => true, 'message' => 'Tarea asignada correctamente', 'data' => $tarea]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function show(Request $request, $id)
    {
        $worker = User::with(['persona', 'sucursales', 'roles'])->findOrFail($id);

        // Persistencia de filtros en Sesión
        if ($request->has('desde')) {
            session(['colab_filtro_desde' => $request->get('desde')]);
        }
        if ($request->has('hasta')) {
            session(['colab_filtro_hasta' => $request->get('hasta')]);
        }

        // Si se solicita limpiar explícitamente (vía parámetro o si prefieres botón dedicado)
        if ($request->has('clear')) {
            session()->forget(['colab_filtro_desde', 'colab_filtro_hasta']);
            return redirect()->route('panel.colaboradores.show', $id);
        }

        $desde = session('colab_filtro_desde');
        $hasta = session('colab_filtro_hasta');

        $query = BitacoraTrabajo::with(['orden.vehiculo', 'sucursal'])
            ->where('user_id', $id);

        if ($desde) {
            $query->whereDate('created_at', '>=', $desde);
        }
        if ($hasta) {
            $query->whereDate('created_at', '<=', $hasta);
        }

        $historico = $query->orderBy('created_at', 'desc')->paginate(15);

        // Estadísticas rápidas
        $stats = [
            'total_tareas' => BitacoraTrabajo::where('user_id', $id)->count(),
            'tareas_mes' => BitacoraTrabajo::where('user_id', $id)->whereMonth('created_at', Carbon::now()->month)->count(),
            'minutos_totales' => BitacoraTrabajo::where('user_id', $id)->sum('minutos_totales'),
            'promedio_minutos' => BitacoraTrabajo::where('user_id', $id)->avg('minutos_totales') ?? 0
        ];

        return view('panel.operaciones.colaboradores.show', compact('worker', 'historico', 'stats', 'desde', 'hasta'));
    }

    public function print(Request $request, $id)
    {
        $worker = User::with(['persona', 'sucursales'])->findOrFail($id);

        // Usar los filtros de la sesión para el reporte si no vienen en la URL
        $desde = $request->get('desde') ?: session('colab_filtro_desde');
        $hasta = $request->get('hasta') ?: session('colab_filtro_hasta');

        $query = BitacoraTrabajo::with(['orden.vehiculo', 'sucursal'])
            ->where('user_id', $id);

        if ($desde) {
            $query->whereDate('created_at', '>=', $desde);
        }
        if ($hasta) {
            $query->whereDate('created_at', '<=', $hasta);
        }

        $historico = $query->orderBy('created_at', 'desc')->get();

        return view('panel.operaciones.colaboradores.print', compact('worker', 'historico', 'desde', 'hasta'));
    }
}
