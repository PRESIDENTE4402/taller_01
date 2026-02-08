<?php

namespace App\Http\Controllers\Panel;

use App\Http\Controllers\Controller;
use App\Models\Role;
use Illuminate\Http\Request;

class RoleController extends Controller
{
    public function index()
    {
        return view('panel.seguridad.roles.index');
    }

    public function list()
    {
        $roles = Role::with('permissions')->orderBy('id', 'desc')->get();
        return response()->json($roles);
    }

    public function store(Request $request)
    {
        $request->validate([
            'nombre' => 'required|string|max:255|unique:roles,nombre',
            'descripcion' => 'nullable|string|max:500',
            'permisos' => 'nullable|array',
            'permisos.*' => 'exists:permisos,id'
        ]);

        try {
            $role = Role::create([
                'nombre' => $request->nombre,
                'slug' => \Illuminate\Support\Str::slug($request->nombre, '_'),
                'descripcion' => $request->descripcion
            ]);

            if ($request->has('permisos')) {
                $role->permissions()->sync($request->permisos);
            }

            return response()->json([
                'message' => 'Rol creado correctamente',
                'role' => $role->load('permissions')
            ], 201);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Error al guardar rol: ' . $e->getMessage()], 500);
        }
    }

    public function update(Request $request, $id)
    {
        $role = Role::findOrFail($id);

        $request->validate([
            'nombre' => 'required|string|max:255|unique:roles,nombre,' . $id,
            'descripcion' => 'nullable|string|max:500',
            'permisos' => 'nullable|array',
            'permisos.*' => 'exists:permisos,id'
        ]);

        try {
            $role->update([
                'nombre' => $request->nombre,
                'slug' => \Illuminate\Support\Str::slug($request->nombre, '_'),
                'descripcion' => $request->descripcion
            ]);

            if ($request->has('permisos')) {
                $role->permissions()->sync($request->permisos);
            }

            return response()->json([
                'message' => 'Rol actualizado correctamente',
                'role' => $role->load('permissions')
            ]);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Error al actualizar rol: ' . $e->getMessage()], 500);
        }
    }

    public function destroy($id)
    {
        $role = Role::findOrFail($id);
        $role->delete();

        return response()->json([
            'message' => 'Rol eliminado correctamente'
        ]);
    }
}
