<?php

namespace App\Http\Controllers\Panel;

use App\Http\Controllers\Controller;
use App\Models\BitacoraTrabajo;
use App\Models\User;
use App\Notifications\TareaFinalizada;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class MisTareasController extends Controller
{
    public function index(Request $request)
    {
        /** @var \App\Models\User $currentUser */
        $currentUser = Auth::user();
        $isAdmin = $currentUser->hasRole('admin') || $currentUser->hasRole('recepcionista');

        $mecanicos = collect();
        $selectedUser = $currentUser;

        if ($isAdmin) {
            $mecanicos = User::whereHas('roles', function ($q) {
                $q->whereIn('slug', ['mecanico', 'tecnico', 'ayudante']);
            })->with('persona')->get();

            if ($request->has('worker_id')) {
                $selectedUser = User::findOrFail($request->worker_id);
            } elseif ($mecanicos->isNotEmpty()) {
                // By default select the first one if viewing as admin directly without param
                $selectedUser = $mecanicos->first();
            }
        }

        $tareas = BitacoraTrabajo::with(['orden.vehiculo', 'sucursal'])
            ->where('user_id', $selectedUser->id ?? $currentUser->id)
            ->orderBy('created_at', 'desc')
            ->get();

        $tareaActual = $tareas->whereIn('estado', ['en_progreso', 'en_pausa'])->first();
        $historico = $tareas->where('estado', 'completado');

        return view('panel.operaciones.mis-tareas.index', compact('tareaActual', 'historico', 'isAdmin', 'mecanicos', 'selectedUser'));
    }

    public function updateStatus(Request $request, $id)
    {
        $request->validate([
            'estado' => 'required|in:en_progreso,en_pausa,completado'
        ]);

        try {
            /** @var \App\Models\User $currentUser */
            $currentUser = Auth::user();
            $isAdmin = $currentUser->hasRole('admin') || $currentUser->hasRole('recepcionista');

            if ($isAdmin) {
                $tarea = BitacoraTrabajo::findOrFail($id);
            } else {
                $tarea = BitacoraTrabajo::where('user_id', $currentUser->id)->findOrFail($id);
            }

            // Manejo de tiempos
            if ($request->estado == 'en_progreso' && $tarea->estado == 'en_pausa' && !$tarea->inicio) {
                $tarea->inicio = Carbon::now();
            }

            if ($request->estado == 'completado') {
                $tarea->fin = Carbon::now();
                if ($tarea->inicio) {
                    $tarea->minutos_totales = $tarea->inicio->diffInMinutes($tarea->fin);
                } else {
                    $tarea->minutos_totales = 0;
                }

                // Enviar notificación a administradores o secretarios
                $admins = User::whereHas('roles', function ($q) {
                    $q->whereIn('slug', ['admin', 'recepcionista']);
                })->get();

                foreach ($admins as $admin) {
                    /** @var \App\Models\User $admin */
                    $admin->notify(new TareaFinalizada($tarea));
                }
            }

            $tarea->estado = $request->estado;
            $tarea->save();

            return redirect()->back()->with('success', 'Estado de la tarea actualizado exitosamente.');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Error al actualizar: ' . $e->getMessage());
        }
    }

    public function addNotes(Request $request, $id)
    {
        $request->validate([
            'notas_adicionales' => 'required|string|max:1000'
        ]);

        try {
            /** @var \App\Models\User $currentUser */
            $currentUser = Auth::user();
            $isAdmin = $currentUser->hasRole('admin') || $currentUser->hasRole('recepcionista');

            if ($isAdmin) {
                $tarea = BitacoraTrabajo::findOrFail($id);
            } else {
                $tarea = BitacoraTrabajo::where('user_id', $currentUser->id)->findOrFail($id);
            }

            // Append notes
            $newNotes = Carbon::now()->format('d/m/Y H:i') . ': ' . $request->notas_adicionales;
            $tarea->notas_adicionales = $tarea->notas_adicionales ? $tarea->notas_adicionales . "\n" . $newNotes : $newNotes;
            $tarea->save();

            return redirect()->back()->with('success', 'Observación agregada.');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Error al agregar observación: ' . $e->getMessage());
        }
    }

    public function destroy($id)
    {
        try {
            /** @var \App\Models\User $currentUser */
            $currentUser = Auth::user();
            $isAdmin = $currentUser->hasRole('admin') || $currentUser->hasRole('recepcionista');

            if ($isAdmin) {
                $tarea = BitacoraTrabajo::findOrFail($id);
            } else {
                $tarea = BitacoraTrabajo::where('user_id', $currentUser->id)->findOrFail($id);
            }

            // Eliminar la tarea (cancelar porque no se va a llevar a cabo)
            $tarea->delete();

            return redirect()->back()->with('success', 'Tarea cancelada y eliminada del tablero correctamente.');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Error al cancelar la tarea: ' . $e->getMessage());
        }
    }
}
