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
            ['nombre' => 'Ver Dashboard', 'slug' => 'ver_dashboard'],
            ['nombre' => 'Gestionar Citas', 'slug' => 'gestionar_citas'],
            ['nombre' => 'Gestionar Órdenes de Trabajo', 'slug' => 'gestionar_ordenes_trabajo'],
            ['nombre' => 'Recepción de Vehículos', 'slug' => 'gestionar_recepcion'],
            ['nombre' => 'Gestionar Inventario', 'slug' => 'gestionar_inventario'],
            ['nombre' => 'Gestionar Marcas', 'slug' => 'gestionar_marcas'],
            ['nombre' => 'Gestionar Versiones', 'slug' => 'gestionar_versiones'],
            ['nombre' => 'Gestionar Sucursales', 'slug' => 'gestionar_sucursales'],
            ['nombre' => 'Ver Mi QR', 'slug' => 'ver_mi_qr'],
            ['nombre' => 'Ver Asistencias', 'slug' => 'ver_asistencias'],
            ['nombre' => 'Registrar Asistencia', 'slug' => 'registrar_asistencia'],
            ['nombre' => 'Gestionar Roles', 'slug' => 'gestionar_roles'],
            ['nombre' => 'Gestionar Permisos', 'slug' => 'gestionar_permisos'],
            ['nombre' => 'Gestionar Usuarios', 'slug' => 'gestionar_usuarios'],
            ['nombre' => 'Gestionar Clientes', 'slug' => 'gestionar_clientes'],
            ['nombre' => 'Gestionar Vehículos', 'slug' => 'gestionar_vehiculos'],
            ['nombre' => 'Gestionar Imágenes Landing', 'slug' => 'gestionar_imagenes_landing'],
            ['nombre' => 'Gestionar Ítems Recepción', 'slug' => 'gestionar_items_recepcion'],
            ['nombre' => 'Ver Reportes', 'slug' => 'ver_reportes'],
            ['nombre' => 'Gestionar Planilla', 'slug' => 'gestionar_planilla'],
        ];

        foreach ($perms as $p) {
            Permission::updateOrCreate(['slug' => $p['slug']], $p);
        }

        // 4. Asignar TODO al admin
        $adminRole->permissions()->sync(Permission::all());
    }
}
