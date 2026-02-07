<?php

namespace App\Http\Controllers\Panel;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class VersionVehiculoController extends Controller
{
    public function index()
    {
        return view('panel.mantenimientos.versiones.index');
    }

    public function list()
    {
        $modelos = DB::table('modelos_vehiculos')
            ->join('marcas_vehiculos', 'modelos_vehiculos.marca_id', '=', 'marcas_vehiculos.id')
            ->leftJoin('versiones_vehiculos', 'modelos_vehiculos.id', '=', 'versiones_vehiculos.modelo_id')
            ->select(
                'modelos_vehiculos.id',
                'modelos_vehiculos.nombre',
                'marcas_vehiculos.nombre as marca_nombre',
                'marcas_vehiculos.id as marca_id',
                DB::raw('count(versiones_vehiculos.id) as versiones_count')
            )
            ->groupBy('modelos_vehiculos.id', 'modelos_vehiculos.nombre', 'marcas_vehiculos.nombre', 'marcas_vehiculos.id')
            ->orderBy('marcas_vehiculos.nombre', 'asc')
            ->orderBy('modelos_vehiculos.nombre', 'asc')
            ->get();

        return response()->json($modelos);
    }

    public function listByModelo($modeloId)
    {
        $versiones = DB::table('versiones_vehiculos')
            ->where('modelo_id', $modeloId)
            ->orderBy('nombre', 'asc')
            ->get();
            
        return response()->json($versiones);
    }

    // Endpoint to populate the select dropdown
    public function listModelos()
    {
        $modelos = DB::table('modelos_vehiculos')
            ->join('marcas_vehiculos', 'modelos_vehiculos.marca_id', '=', 'marcas_vehiculos.id')
            ->select('modelos_vehiculos.id', 'modelos_vehiculos.nombre', 'marcas_vehiculos.nombre as marca_nombre')
            ->orderBy('marcas_vehiculos.nombre', 'asc')
            ->orderBy('modelos_vehiculos.nombre', 'asc')
            ->get();

        return response()->json($modelos);
    }

    public function store(Request $request)
    {
        $request->validate([
            'modelo_id' => 'required|exists:modelos_vehiculos,id',
            'nombre' => 'required|max:255',
        ], [
            'modelo_id.required' => 'Debe seleccionar un modelo.',
            'modelo_id.exists' => 'El modelo seleccionado no es válido.',
            'nombre.required' => 'El nombre de la versión es obligatorio.',
        ]);

        // Optional: Check custom unique constraint (model_id + name)
        $exists = DB::table('versiones_vehiculos')
            ->where('modelo_id', $request->modelo_id)
            ->where('nombre', $request->nombre)
            ->exists();

        if ($exists) {
            return response()->json(['errors' => ['nombre' => ['Esta versión ya existe para el modelo seleccionado.']]], 422);
        }

        $id = DB::table('versiones_vehiculos')->insertGetId([
            'modelo_id' => $request->modelo_id,
            'nombre' => $request->nombre,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return response()->json(['success' => true, 'message' => 'Versión creada correctamente.', 'id' => $id]);
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'modelo_id' => 'required|exists:modelos_vehiculos,id',
            'nombre' => 'required|max:255',
        ], [
            'modelo_id.required' => 'Debe seleccionar un modelo.',
            'nombre.required' => 'El nombre de la versión es obligatorio.',
        ]);

         // Optional: Check custom unique constraint (model_id + name) excluding current
         $exists = DB::table('versiones_vehiculos')
            ->where('modelo_id', $request->modelo_id)
            ->where('nombre', $request->nombre)
            ->where('id', '!=', $id)
            ->exists();

        if ($exists) {
             return response()->json(['errors' => ['nombre' => ['Esta versión ya existe para el modelo seleccionado.']]], 422);
        }

        DB::table('versiones_vehiculos')->where('id', $id)->update([
            'modelo_id' => $request->modelo_id,
            'nombre' => $request->nombre,
            'updated_at' => now(),
        ]);

        return response()->json(['success' => true, 'message' => 'Versión actualizada correctamente.']);
    }

    public function destroy($id)
    {
        DB::table('versiones_vehiculos')->where('id', $id)->delete();
        return response()->json(['success' => true, 'message' => 'Versión eliminada correctamente.']);
    }
}
