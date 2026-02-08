<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Permission;
use App\Models\Role;

class PermissionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $perms = [
            ['nombre' => 'Gestionar Roles', 'slug' => 'gestionar_roles', 'descripcion' => 'Permite crear, editar y eliminar roles'],
            ['nombre' => 'Gestionar Permisos', 'slug' => 'gestionar_permisos', 'descripcion' => 'Permite crear, editar y eliminar permisos individuales'],
            ['nombre' => 'Gestionar Usuarios', 'slug' => 'gestionar_usuarios', 'descripcion' => 'Permite administrar cuentas de usuarios'],
            ['nombre' => 'Registrar Asistencia', 'slug' => 'registrar_asistencia', 'descripcion' => 'Permite marcar entrada y salida de personal'],
            ['nombre' => 'Ver Asistencias', 'slug' => 'ver_asistencias', 'descripcion' => 'Permite ver el reporte de asistencias'],
        ];

        foreach ($perms as $p) {
            Permission::firstOrCreate(['slug' => $p['slug']], $p);
        }

        // Assign all to admin
        $admin = Role::where('slug', 'admin')->first();
        if ($admin) {
            $admin->permissions()->sync(Permission::all());
        }
    }
}
