<?php

namespace App\Http\Controllers\Panel;

use App\Http\Controllers\Controller;
use App\Models\Categoria;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CategoriaController extends Controller
{
    public function index()
    {
        return view('panel.mantenimientos.categorias.index');
    }

    public function list(Request $request)
    {
        // SaaS: Filtrar automáticamente por las sucursales del usuario actual
        $categorias = Categoria::forCurrentUser()
            ->with('parent', 'attributeSchemas')
            ->orderBy('nombre', 'asc')
            ->get();

        return response()->json($categorias);
    }

    public function store(Request $request)
    {
        $request->validate([
            'nombre' => 'required|max:255',
            'sucursal_id' => 'required|exists:sucursales,id',
        ]);

        $categoria = Categoria::create([
            'sucursal_id' => $request->sucursal_id, // Should probably be validated against user's reliable sucursales
            'nombre' => $request->nombre,
            'parent_id' => $request->parent_id,
            'descripcion' => $request->descripcion,
        ]);

        return response()->json(['success' => true, 'message' => 'Categoría creada.', 'data' => $categoria]);
    }

    public function update(Request $request, $id)
    {
        $categoria = Categoria::findOrFail($id);

        // Ownership check
        if (!Auth::user()->sucursales->contains($categoria->sucursal_id)) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $categoria->update($request->only('nombre', 'parent_id', 'descripcion'));

        return response()->json(['success' => true, 'message' => 'Categoría actualizada.']);
    }

    public function destroy($id)
    {
        $categoria = Categoria::findOrFail($id);
        // Ownership check
        if (!Auth::user()->sucursales->contains($categoria->sucursal_id)) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $categoria->delete();
        return response()->json(['success' => true, 'message' => 'Categoría eliminada.']);
    }
}
