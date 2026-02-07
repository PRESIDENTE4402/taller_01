<?php

namespace App\Http\Controllers\Panel;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Sucursal;
use Illuminate\Support\Facades\Validator;

class SucursalController extends Controller
{
    /**
     * Display the main view for module.
     */
    public function index()
    {
        return view('panel.mantenimientos.sucursales.index');
    }

    /**
     * Return list of resources for AJAX.
     */
    public function list()
    {
        $sucursales = Sucursal::orderBy('id', 'desc')->get();
        return response()->json($sucursales);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'nombre' => 'required|string|max:255',
            'direccion' => 'required|string|max:255',
            'telefono' => 'required|string|max:20',
            'capacidad_bahias' => 'required|integer|min:1',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $sucursal = Sucursal::create($request->all());

        return response()->json(['success' => true, 'message' => 'Sucursal creada correctamente.', 'id' => $sucursal->id]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $sucursal = Sucursal::find($id);

        if (!$sucursal) {
            return response()->json(['message' => 'Sucursal no encontrada.'], 404);
        }

        $validator = Validator::make($request->all(), [
            'nombre' => 'required|string|max:255',
            'direccion' => 'required|string|max:255',
            'telefono' => 'required|string|max:20',
            'capacidad_bahias' => 'required|integer|min:1',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $sucursal->update($request->all());

        return response()->json(['success' => true, 'message' => 'Sucursal actualizada correctamente.']);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $sucursal = Sucursal::find($id);

        if (!$sucursal) {
            return response()->json(['message' => 'Sucursal no encontrada.'], 404);
        }

        $sucursal->delete();

        return response()->json(['success' => true, 'message' => 'Sucursal eliminada correctamente.']);
    }
}
