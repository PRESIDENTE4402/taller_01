<?php

namespace App\Http\Controllers\Panel;

use App\Http\Controllers\Controller;
use App\Models\Permission;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class PermissionController extends Controller
{
    public function index()
    {
        return view('panel.seguridad.permisos.index');
    }

    public function list()
    {
        $permissions = Permission::orderBy('id', 'desc')->get();
        return response()->json($permissions);
    }

    public function store(Request $request)
    {
        $request->validate([
            'nombre' => 'required|string|max:255|unique:permisos,nombre',
            'descripcion' => 'nullable|string|max:500'
        ]);

        $permission = Permission::create([
            'nombre' => $request->nombre,
            'slug' => Str::slug($request->nombre, '_'),
            'descripcion' => $request->descripcion
        ]);

        return response()->json([
            'message' => 'Permiso creado correctamente',
            'permission' => $permission
        ], 201);
    }

    public function update(Request $request, $id)
    {
        $permission = Permission::findOrFail($id);

        $request->validate([
            'nombre' => 'required|string|max:255|unique:permisos,nombre,' . $id,
            'descripcion' => 'nullable|string|max:500'
        ]);

        $permission->update([
            'nombre' => $request->nombre,
            'slug' => Str::slug($request->nombre, '_'),
            'descripcion' => $request->descripcion
        ]);

        return response()->json([
            'message' => 'Permiso actualizado correctamente',
            'permission' => $permission
        ]);
    }

    public function destroy($id)
    {
        $permission = Permission::findOrFail($id);
        $permission->delete();

        return response()->json([
            'message' => 'Permiso eliminado correctamente'
        ]);
    }
}
