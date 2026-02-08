<?php

namespace App\Http\Controllers\Panel;

use App\Http\Controllers\Controller;
use App\Models\Asistencia;
use App\Models\User;
use App\Models\Sucursal;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

class AsistenciaController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        // Retornamos la vista principal
        return view('panel.asistencias.index');
    }

    /**
     * Obtener listado de asistencias para JS (DataTables o Render condicional)
     */
    public function list(Request $request)
    {
        // Filtros básicos
        $query = Asistencia::with(['user:id,name', 'sucursal:id,nombre'])
            ->orderBy('fecha', 'desc')
            ->orderBy('created_at', 'desc');

        if ($request->has('fecha_inicio') && $request->has('fecha_fin')) {
            $query->whereBetween('fecha', [$request->fecha_inicio, $request->fecha_fin]);
        } else {
            // Por defecto mostrar el mes actual
            $query->whereMonth('fecha', Carbon::now()->month);
        }

        if ($request->has('user_id')) {
            $query->where('user_id', $request->user_id);
        }

        return response()->json($query->get());
    }

    /**
     * Verificar estado del usuario para el Modal de Confirmación
     */
    public function verifyUser($userId)
    {
        $user = User::with(['sucursales', 'persona'])->find($userId);

        if (!$user) {
            return response()->json(['error' => 'Usuario no encontrado'], 404);
        }

        $today = Carbon::today();
        $asistencia = Asistencia::where('user_id', $userId)->where('fecha', $today)->first();

        // Determinar acción sugerida
        $accion = 'entrada';
        $estadoSugerido = $this->determinarEstadoEntrada(Carbon::now());

        if ($asistencia) {
            if (!$asistencia->hora_salida) {
                $accion = 'salida';
                $estadoSugerido = $asistencia->estado;
            } else {
                // Already has exit, maybe re-opening or just viewing
                $estadoSugerido = $asistencia->estado;
            }
        }

        // Parse Name using Persona if available
        $displayName = $user->persona ? ($user->persona->nombres . ' ' . $user->persona->apellidos) : $user->name;
        // Inject display name into user object for frontend convenience (or handle in JS)
        $user->display_name = $displayName;

        return response()->json([
            'user' => $user,
            'asistencia' => $asistencia,
            'accion_sugerida' => $accion, // 'entrada' or 'salida'
            'estado_sugerido' => $estadoSugerido
        ]);
    }

    /**
     * Registrar Asistencia (Confirmada desde Modal)
     */
    public function registerAttendance(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'accion' => 'required|in:entrada,salida',
            'tipo_asistencia' => 'required',
            'estado' => 'required'
        ]);

        $userId = $request->user_id;
        $user = User::find($userId);
        $today = Carbon::today();

        $asistencia = Asistencia::where('user_id', $userId)->where('fecha', $today)->first();

        // Preparar Horas
        // Si vienen en formato H:i le agregamos :00 para BD (Time type)
        $hEntrada = $request->hora_entrada ? Carbon::createFromFormat('H:i', $request->hora_entrada)->toTimeString() : Carbon::now()->toTimeString();
        $hSalida = $request->hora_salida ? Carbon::createFromFormat('H:i', $request->hora_salida)->toTimeString() : null;

        if ($asistencia) {
            // ACTUALIZACIÓN (Registrar Salida o Editar Entrada/Salida)
            $data = [
                'tipo' => $request->tipo_asistencia,
                'estado' => $request->estado,
                'observaciones' => $request->observaciones
            ];

            // Si se envió hora de entrada explícita (edición), actualizarla
            if ($request->hora_entrada) {
                $data['hora_entrada'] = $hEntrada;
            }

            // Si se envió hora de salida explícita, actualizarla
            if ($request->hora_salida) {
                $data['hora_salida'] = $hSalida;
            } elseif ($request->accion === 'salida' && !$asistencia->hora_salida) {
                // Si es "Marcar Salida" por primera vez sin hora manual -> NOW
                $data['hora_salida'] = Carbon::now()->toTimeString();
            }

            $asistencia->update($data);

            return response()->json([
                'type' => 'update',
                'message' => "Registro actualizado: {$user->name}",
                'data' => $asistencia
            ]);
        } else {
            // NUEVO REGISTRO (Solo Entrada)
            if ($request->accion === 'salida') {
                // Si intenta marcar salida sin entrada previa, error (o permitir crear incompleto?) 
                // Por consistencia, error.
                return response()->json(['type' => 'error', 'message' => 'No existe entrada previa para marcar salida.'], 400);
            }

            // Sucursal Fallback
            $sucursalId = $user->sucursal_id ?? ($user->sucursales->first()->id ?? (Sucursal::first()->id ?? 1));

            $asistencia = Asistencia::create([
                'user_id' => $userId,
                'sucursal_id' => $sucursalId,
                'fecha' => $today,
                'hora_entrada' => $hEntrada, // Usa manual o NOW
                'hora_salida' => $hSalida,   // Será null normalmente
                'tipo' => $request->tipo_asistencia,
                'estado' => $request->estado,
                'observaciones' => $request->observaciones
            ]);

            return response()->json([
                'type' => 'entrada',
                'message' => "Entrada registrada: {$user->name}",
                'data' => $asistencia
            ]);
        }
    }

    public function searchUsers(Request $request)
    {
        $term = $request->query('term');
        if (!$term || strlen($term) < 2)
            return response()->json([]);

        // Search in Personas table linked to User (Name, Surname, or Full Name)
        $users = User::whereHas('persona', function ($q) use ($term) {
            $q->where('nombres', 'LIKE', "%{$term}%")
                ->orWhere('apellidos', 'LIKE', "%{$term}%")
                ->orWhereRaw("CONCAT(nombres, ' ', apellidos) LIKE ?", ["%{$term}%"]);
        })->with('persona')->limit(10)->get();

        // Map to expected structure, using Persona names
        $results = $users->map(function ($user) {
            return [
                'id' => $user->id,
                'name' => $user->persona ? ($user->persona->nombres . ' ' . $user->persona->apellidos) : $user->name,
                'email' => $user->email
            ];
        });

        return response()->json($results);
    }

    public function destroy($id)
    {
        $asistencia = Asistencia::find($id);
        if (!$asistencia)
            return response()->json(['error' => 'Registro no encontrado'], 404);

        // Optional: Check if can delete? Admin only?
        $asistencia->delete();

        return response()->json(['message' => 'Registro eliminado correctamente']);
    }

    private function determinarEstadoEntrada($hora)
    {
        $horaLimite = Carbon::today()->setTime(9, 15, 0); // Configurable
        return $hora->greaterThan($horaLimite) ? 'tardanza' : 'a_tiempo';
    }
}
