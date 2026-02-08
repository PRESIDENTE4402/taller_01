<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Role;
use App\Models\Permission;
use Illuminate\Support\Str;

class FixPermissionsSeeder extends Seeder
{
    public function run()
    {
        // 1. Asegurar rol admin con slug 'admin'
        $adminRole = Role::firstOrCreate(['slug' => 'admin'], [
            'nombre' => 'Admin',
            'descripcion' => 'Super usuario con todos los permisos'
        ]);

        // Corregir nombre si existe pero con otro nombre
        if ($adminRole->nombre !== 'Admin') {
            $adminRole->update(['nombre' => 'Admin']);
        }

        // 2. Asegurar primer usuario sea admin
        $user = User::first();
        if ($user) {
            $user->roles()->sync([$adminRole->id]);
        }

        // 3. Asegurar permisos básicos
        $perms = [
            ['nombre' => 'Ver Asistencias', 'slug' => 'ver_asistencias'],
            ['nombre' => 'Registrar Asistencia', 'slug' => 'registrar_asistencia'],
            ['nombre' => 'Gestionar Roles', 'slug' => 'gestionar_roles'],
            ['nombre' => 'Gestionar Permisos', 'slug' => 'gestionar_permisos'],
            ['nombre' => 'Gestionar Usuarios', 'slug' => 'gestionar_usuarios'],
        ];

        foreach ($perms as $p) {
            Permission::firstOrCreate(['slug' => $p['slug']], $p);
        }

        // 4. Asignar TODO al admin
        $adminRole->permissions()->sync(Permission::all());
    }
}
