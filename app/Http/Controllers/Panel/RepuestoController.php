<?php

namespace App\Http\Controllers\Panel;

use App\Http\Controllers\Controller;
use App\Models\Repuesto;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RepuestoController extends Controller
{
    public function index()
    {
        return view('panel.mantenimientos.repuestos.index');
    }

    public function list(Request $request)
    {
        $repuestos = Repuesto::forCurrentUser()
            ->with('categoria', 'categoria.attributeSchemas')
            ->orderBy('nombre', 'asc')
            ->get();

        return response()->json($repuestos);
    }

    public function store(Request $request)
    {
        $request->validate([
            'sucursal_id' => 'required|exists:sucursales,id',
            'categoria_id' => 'nullable|exists:categorias,id',
            'codigo_interno' => 'required|unique:repuestos,codigo_interno',
            'nombre' => 'required',
            'precio_costo' => 'required|numeric',
            'precio_venta' => 'required|numeric',
            'stock_actual' => 'required|integer',
            'atributos' => 'nullable|array'
        ]);

        try {
            // Validar atributos dinámicos según el schema de la categoría
            if ($request->categoria_id && $request->atributos) {
                Repuesto::validateAttributes($request->categoria_id, $request->atributos);
            }

            $repuesto = Repuesto::create($request->all());

            return response()->json([
                'success' => true,
                'message' => 'Repuesto creado exitosamente.',
                'data' => $repuesto->load('categoria')
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 422);
        }
    }

    public function update(Request $request, $id)
    {
        $repuesto = Repuesto::findOrFail($id);

        // Ownership check
        if (!Auth::user()->sucursales->contains($repuesto->sucursal_id)) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $request->validate([
            'codigo_interno' => 'required|unique:repuestos,codigo_interno,' . $id,
            // Add other validations as needed
        ]);

        $repuesto->update($request->all());

        return response()->json(['success' => true, 'message' => 'Repuesto actualizado.']);
    }

    public function destroy($id)
    {
        $repuesto = Repuesto::findOrFail($id);
        if (!Auth::user()->sucursales->contains($repuesto->sucursal_id)) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }
        $repuesto->delete();
        return response()->json(['success' => true, 'message' => 'Repuesto eliminado.']);
    }
}
