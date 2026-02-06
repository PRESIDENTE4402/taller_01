<?php

namespace App\Http\Controllers\Panel;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Role;
use Illuminate\Http\Request;

class UsuarioController extends Controller
{
    public function index()
    {
        return view('panel.seguridad.usuarios.index');
    }

    public function list()
    {
        // Traemos usuarios con sus roles
        $users = User::with('roles')->orderBy('id', 'desc')->get();
        return response()->json($users);
    }

    public function listRoles()
    {
        $roles = Role::all();
        return response()->json($roles);
    }

    public function assignRole(Request $request, $id)
    {
        $user = User::findOrFail($id);

        $request->validate([
            'role_id' => 'required|exists:roles,id'
        ]);

        // Sincronizar roles (asumimos un usuario puede tener varios, o uno solo, 
        // pero sync() reemplaza los anteriores si pasamos un array o un solo id)
        // Si queremos permitir multiples roles acumulativos, usar attach.
        // Por simplicidad de gestión, usaremos sync para "establecer" los roles seleccionados.
        // Aquí asumimos que el frontend envía un solo role_id para "Cambiar rol principal" o similar.
        // Si quisieramos multiples, recibiríamos un array de IDs.

        $user->roles()->sync([$request->role_id]);

        return response()->json([
            'message' => 'Rol asignado correctamente',
            'user' => $user->load('roles')
        ]);
    }
}
