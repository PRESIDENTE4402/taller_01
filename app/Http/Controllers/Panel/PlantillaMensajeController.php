<?php

namespace App\Http\Controllers\Panel;

use App\Http\Controllers\Controller;
use App\Models\PlantillaMensaje;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class PlantillaMensajeController extends Controller
{
    public function index()
    {
        return view('panel.mantenimientos.plantillas_mensajes.index');
    }

    public function list()
    {
        $plantillas = PlantillaMensaje::orderBy('categoria')->orderBy('nombre')->get();
        return response()->json($plantillas);
    }

    public function store(Request $request)
    {
        $request->validate([
            'nombre' => 'required|string|max:255',
            'categoria' => 'required|string|max:100',
            'tipo_canal' => 'required|in:whatsapp,email,ambos',
            'cuerpo' => 'required|string',
            'asunto' => 'nullable|string|max:255',
        ]);

        $plantilla = PlantillaMensaje::create([
            'nombre' => $request->nombre,
            'slug' => Str::slug($request->nombre, '_'),
            'categoria' => $request->categoria,
            'tipo_canal' => $request->tipo_canal,
            'cuerpo' => $request->cuerpo,
            'asunto' => $request->asunto,
            'activo' => true,
        ]);

        return response()->json(['success' => true, 'message' => 'Plantilla creada correctamente', 'plantilla' => $plantilla]);
    }

    public function update(Request $request, $id)
    {
        $plantilla = PlantillaMensaje::findOrFail($id);

        $request->validate([
            'nombre' => 'required|string|max:255',
            'categoria' => 'required|string|max:100',
            'tipo_canal' => 'required|in:whatsapp,email,ambos',
            'cuerpo' => 'required|string',
            'asunto' => 'nullable|string|max:255',
            'activo' => 'required|boolean',
        ]);

        $plantilla->update([
            'nombre' => $request->nombre,
            'categoria' => $request->categoria,
            'tipo_canal' => $request->tipo_canal,
            'cuerpo' => $request->cuerpo,
            'asunto' => $request->asunto,
            'activo' => $request->activo,
        ]);

        return response()->json(['success' => true, 'message' => 'Plantilla actualizada correctamente']);
    }

    public function destroy($id)
    {
        $plantilla = PlantillaMensaje::findOrFail($id);

        // No permitir borrar las plantillas base por slug si son vitales
        $vitales = ['cita_confirmacion', 'cita_recordatorio', 'orden_cotizacion', 'orden_listo', 'orden_fase'];
        if (in_array($plantilla->slug, $vitales)) {
            return response()->json(['success' => false, 'message' => 'Esta plantilla es vital para el sistema y no puede eliminarse.'], 403);
        }

        $plantilla->delete();
        return response()->json(['success' => true, 'message' => 'Plantilla eliminada correctamente']);
    }
}
