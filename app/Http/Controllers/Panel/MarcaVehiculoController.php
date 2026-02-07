<?php

namespace App\Http\Controllers\Panel;

use App\Http\Controllers\Controller;
use App\Models\MarcaVehiculo; // Assuming Model exists, I will create/verify it too.
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class MarcaVehiculoController extends Controller
{
    public function index()
    {
        // Return view with initial data or empty if fetching via AJAX
        // For PWA dynamic feel, we can just load view and fetch data via JS, 
        // or pass initial data. Let's pass initial data for SSR speed + JS for updates.
        return view('panel.mantenimientos.marcas.index');
    }

    // API-like methods for AJAX handling
    public function list()
    {
        $marcas = DB::table('marcas_vehiculos')->orderBy('nombre', 'asc')->get();
        return response()->json($marcas);
    }

    public function store(Request $request)
    {
        $request->validate([
            'nombre' => 'required|unique:marcas_vehiculos,nombre|max:255',
        ], [
            'nombre.unique' => 'Esta marca ya está registrada en el sistema.',
            'nombre.required' => 'El nombre es obligatorio.',
        ]);

        $id = DB::table('marcas_vehiculos')->insertGetId([
            'nombre' => $request->nombre,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return response()->json(['success' => true, 'message' => 'Marca creada correctamente.', 'id' => $id]);
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'nombre' => 'required|max:255|unique:marcas_vehiculos,nombre,' . $id,
        ], [
            'nombre.unique' => 'Esta marca ya está registrada en el sistema.',
            'nombre.required' => 'El nombre es obligatorio.',
        ]);

        DB::table('marcas_vehiculos')->where('id', $id)->update([
            'nombre' => $request->nombre,
            'updated_at' => now(),
        ]);

        return response()->json(['success' => true, 'message' => 'Marca actualizada correctamente.']);
    }

    public function destroy($id)
    {
        DB::table('marcas_vehiculos')->where('id', $id)->delete();
        return response()->json(['success' => true, 'message' => 'Marca eliminada correctamente.']);
    }
}
