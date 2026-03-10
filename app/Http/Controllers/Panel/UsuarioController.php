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
        $user = auth()->user();
        $isAdmin = $user->hasRole('admin');

        // Traemos usuarios con sus roles, perfil y sucursales
        $query = User::with('roles', 'persona', 'sucursales')->orderBy('id', 'desc');

        if (!$isAdmin) {
            $userSids = $user->sucursales->pluck('id');
            $query->whereHas('sucursales', function ($q) use ($userSids) {
                $q->whereIn('sucursales.id', $userSids);
            });
        }

        $users = $query->get();
        return response()->json($users);
    }

    public function listRoles()
    {
        // No permitir ver roles a no-admins por seguridad si no es necesario para la vista
        if (!auth()->user()->hasRole('admin')) {
            return response()->json([]);
        }
        $roles = Role::all();
        return response()->json($roles);
    }

    public function listSucursales()
    {
        $user = auth()->user();
        if (!$user->hasRole('admin')) {
            return response()->json($user->sucursales);
        }
        $sucursales = \App\Models\Sucursal::all();
        return response()->json($sucursales);
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:4',
            'sucursal_id' => 'required|exists:sucursales,id',
            // Persona validation
            'nombres' => 'required|string|max:255',
            'apellidos' => 'required|string|max:255',
            'telefono' => 'nullable|string|max:20',
            'edad' => 'nullable|integer|min:18',
        ]);

        try {
            \DB::beginTransaction();

            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => \Hash::make($request->password),
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

            // Assign Sucursal
            if ($request->sucursal_id) {
                $user->sucursales()->sync([$request->sucursal_id]);
            }

            // Send Notification (Only if an email is real, but to prevent crashes with fake ones, we catch any error)
            try {
                $user->notify(new \App\Notifications\NewUserWelcomeNotification($request->password));
            } catch (\Exception $e) {
                // Silently bypass fake email errors
            }

            \DB::commit();

            return response()->json([
                'message' => 'Usuario creado exitosamente con la contraseña especificada.',
                'user' => $user->load('persona', 'roles', 'sucursales')
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
            'sucursal_id' => 'required|exists:sucursales,id',
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

            // Update Sucursal
            if ($request->sucursal_id) {
                $user->sucursales()->sync([$request->sucursal_id]);
            }

            \DB::commit();

            return response()->json([
                'message' => 'Usuario actualizado correctamente',
                'user' => $user->load('persona', 'roles', 'sucursales')
            ]);

        } catch (\Exception $e) {
            \DB::rollback();
            return response()->json(['message' => 'Error al actualizar usuario: ' . $e->getMessage()], 500);
        }
    }
}
