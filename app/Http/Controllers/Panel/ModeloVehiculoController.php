<?php

namespace App\Http\Controllers\Panel;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ModeloVehiculoController extends Controller
{
    // Listar modelos por marca ID
    public function listByMarca($marcaId)
    {
        $modelos = DB::table('modelos_vehiculos')
            ->where('marca_id', $marcaId)
            ->orderBy('nombre', 'asc')
            ->get();
            
        return response()->json($modelos);
    }

    public function store(Request $request)
    {
        $request->validate([
            'marca_id' => 'required|exists:marcas_vehiculos,id',
            'nombre' => 'required|max:255',
        ], [
            'nombre.required' => 'El nombre del modelo es obligatorio.',
        ]);

        // Validar duplicados para esa marca específica
        $exists = DB::table('modelos_vehiculos')
            ->where('marca_id', $request->marca_id)
            ->where('nombre', $request->nombre)
            ->exists();

        if ($exists) {
            return response()->json(['errors' => ['nombre' => ['Este modelo ya existe para la marca seleccionada.']]], 422);
        }

        $id = DB::table('modelos_vehiculos')->insertGetId([
            'marca_id' => $request->marca_id,
            'nombre' => $request->nombre,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return response()->json(['success' => true, 'message' => 'Modelo creado correctamente.', 'id' => $id]);
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'nombre' => 'required|max:255',
        ]);
        
        // Obtener el modelo actual para saber su marca_id
        $currentModel = DB::table('modelos_vehiculos')->where('id', $id)->first();
        if (!$currentModel) return response()->json(['message' => 'Modelo no encontrado'], 404);

        // Validar duplicados excluyendo el actual
        $exists = DB::table('modelos_vehiculos')
            ->where('marca_id', $currentModel->marca_id)
            ->where('nombre', $request->nombre)
            ->where('id', '!=', $id)
            ->exists();

        if ($exists) {
            return response()->json(['errors' => ['nombre' => ['Este modelo ya existe para esta marca.']]], 422);
        }

        DB::table('modelos_vehiculos')->where('id', $id)->update([
            'nombre' => $request->nombre,
            'updated_at' => now(),
        ]);

        return response()->json(['success' => true, 'message' => 'Modelo actualizado correctamente.']);
    }

    public function destroy($id)
    {
        DB::table('modelos_vehiculos')->where('id', $id)->delete();
        return response()->json(['success' => true, 'message' => 'Modelo eliminado correctamente.']);
    }
}
