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
        // Traemos usuarios con sus roles y perfil
        $users = User::with('roles', 'persona')->orderBy('id', 'desc')->get();
        return response()->json($users);
    }

    public function listRoles()
    {
        $roles = Role::all();
        return response()->json($roles);
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            // 'password' => 'required|string|min:8', // Generated automatically
            // Persona validation
            'nombres' => 'required|string|max:255',
            'apellidos' => 'required|string|max:255',
            'telefono' => 'nullable|string|max:20',
            'edad' => 'nullable|integer|min:18',
        ]);

        try {
            \DB::beginTransaction();

            // Generar una contraseña genérica/aleatoria de 8 caracteres
            $generatedPassword = \Illuminate\Support\Str::random(8);

            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => \Hash::make($generatedPassword),
            ]);

            $user->persona()->create([
                'nombres' => $request->nombres,
                'apellidos' => $request->apellidos,
                'edad' => $request->edad,
                'sexo' => $request->sexo,
                'telefono' => $request->telefono,
                'correo' => $request->correo, // Optional different email
                'direccion' => $request->direccion,
                'cursos' => $request->cursos,
                'otros_datos' => $request->otros_datos,
            ]);

            // Assign default role if provided or handle later
            if ($request->role_id) {
                $user->roles()->sync([$request->role_id]);
            }

            // Send Notification with the generated password
            $user->notify(new \App\Notifications\NewUserWelcomeNotification($generatedPassword));

            \DB::commit();

            return response()->json([
                'message' => 'Usuario creado. Se ha enviado un correo con la contraseña temporal.',
                'user' => $user->load('persona', 'roles')
            ], 201);

        } catch (\Exception $e) {
            \DB::rollback();
            return response()->json(['message' => 'Error al crear usuario: ' . $e->getMessage()], 500);
        }
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
    public function update(Request $request, $id)
    {
        $user = User::findOrFail($id);

        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email,' . $user->id,
            'password' => 'nullable|string|min:8',
            // Persona validation
            'nombres' => 'required|string|max:255',
            'apellidos' => 'required|string|max:255',
            'telefono' => 'nullable|string|max:20',
            'edad' => 'nullable|integer|min:18',
        ]);

        try {
            \DB::beginTransaction();

            $updateData = [
                'name' => $request->name,
                'email' => $request->email,
            ];

            if ($request->filled('password')) {
                $updateData['password'] = \Hash::make($request->password);
            }

            $user->update($updateData);

            $user->persona()->updateOrCreate(
                ['user_id' => $user->id],
                [
                    'nombres' => $request->nombres,
                    'apellidos' => $request->apellidos,
                    'edad' => $request->edad,
                    'sexo' => $request->sexo,
                    'telefono' => $request->telefono,
                    'correo' => $request->correo,
                    'direccion' => $request->direccion,
                    'cursos' => $request->cursos,
                    'otros_datos' => $request->otros_datos,
                ]
            );

            // Update role if provided
            if ($request->role_id) {
                $user->roles()->sync([$request->role_id]);
            }

            \DB::commit();

            return response()->json([
                'message' => 'Usuario actualizado correctamente',
                'user' => $user->load('persona', 'roles')
            ]);

        } catch (\Exception $e) {
            \DB::rollback();
            return response()->json(['message' => 'Error al actualizar usuario: ' . $e->getMessage()], 500);
        }
    }
}
