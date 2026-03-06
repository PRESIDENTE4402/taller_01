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
            'ciudad' => 'nullable|string|max:100',
            'latitud' => 'nullable|numeric|between:-90,90',
            'longitud' => 'nullable|numeric|between:-180,180',
            'activa' => 'nullable|boolean',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $data = $request->only(['nombre', 'direccion', 'telefono', 'capacidad_bahias', 'ciudad', 'latitud', 'longitud']);
        $data['activa'] = $request->boolean('activa', true);
        $sucursal = Sucursal::create($data);

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
            'nombre' => 'sometimes|required|string|max:255',
            'direccion' => 'sometimes|required|string|max:255',
            'telefono' => 'sometimes|required|string|max:20',
            'capacidad_bahias' => 'sometimes|required|integer|min:1',
            'ciudad' => 'nullable|string|max:100',
            'latitud' => 'nullable|numeric|between:-90,90',
            'longitud' => 'nullable|numeric|between:-180,180',
            'activa' => 'nullable|boolean',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $data = $request->only(['nombre', 'direccion', 'telefono', 'capacidad_bahias', 'ciudad', 'latitud', 'longitud']);
        $data['activa'] = $request->boolean('activa', true);
        $sucursal->update($data);

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
