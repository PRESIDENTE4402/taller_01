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
            // Seguridad
            ['nombre' => 'Gestionar Roles', 'slug' => 'gestionar_roles', 'descripcion' => 'Permite crear, editar y eliminar roles'],
            ['nombre' => 'Gestionar Permisos', 'slug' => 'gestionar_permisos', 'descripcion' => 'Permite crear, editar y eliminar permisos individuales'],
            ['nombre' => 'Gestionar Usuarios', 'slug' => 'gestionar_usuarios', 'descripcion' => 'Permite administrar cuentas de usuarios'],

            // Recursos Humanos
            ['nombre' => 'Registrar Asistencia', 'slug' => 'registrar_asistencia', 'descripcion' => 'Permite marcar entrada y salida de personal'],
            ['nombre' => 'Ver Asistencias', 'slug' => 'ver_asistencias', 'descripcion' => 'Permite ver el reporte de asistencias'],
            ['nombre' => 'Ver Mi QR', 'slug' => 'ver_mi_qr', 'descripcion' => 'Permite ver la credencial QR personal'],

            // Principal
            ['nombre' => 'Ver Dashboard', 'slug' => 'ver_dashboard', 'descripcion' => 'Acceso al panel de estadísticas principal'],

            // Operaciones
            ['nombre' => 'Gestionar Citas', 'slug' => 'gestionar_citas', 'descripcion' => 'Administración de citas de taller'],
            ['nombre' => 'Gestionar Órdenes de Trabajo', 'slug' => 'gestionar_ordenes_trabajo', 'descripcion' => 'Gestión de procesos técnicos y órdenes'],
            ['nombre' => 'Recepción de Vehículos', 'slug' => 'gestionar_recepcion', 'descripcion' => 'Proceso de entrada de vehículos al taller'],

            // Logística
            ['nombre' => 'Gestionar Inventario', 'slug' => 'gestionar_inventario', 'descripcion' => 'Control de stock y repuestos'],

            // Mantenimientos
            ['nombre' => 'Gestionar Marcas', 'slug' => 'gestionar_marcas', 'descripcion' => 'Configuración de marcas de vehículos'],
            ['nombre' => 'Gestionar Versiones', 'slug' => 'gestionar_versiones', 'descripcion' => 'Configuración de modelos y versiones'],
            ['nombre' => 'Gestionar Sucursales', 'slug' => 'gestionar_sucursales', 'descripcion' => 'Administración de sedes físicas'],
        ];

        foreach ($perms as $p) {
            Permission::updateOrCreate(['slug' => $p['slug']], $p);
        }

        // Assign all to admin
        $admin = Role::where('slug', 'admin')->first();
        if ($admin) {
            $admin->permissions()->sync(Permission::all());
        }
    }
}
