<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class SistemaSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Crear Sucursales
        $sucursal1 = DB::table('sucursales')->insertGetId([
            'nombre' => 'Taller Central Norte',
            'direccion' => 'Calle Principal 123, Zona 1',
            'telefono' => '5555-0001',
            'capacidad_bahias' => 10,
            'created_at' => now(),
        ]);

        $sucursal2 = DB::table('sucursales')->insertGetId([
            'nombre' => 'Taller Express Sur',
            'direccion' => 'Avenida Sur 45-67, Zona 10',
            'telefono' => '5555-0002',
            'capacidad_bahias' => 5,
            'created_at' => now(),
        ]);

        // 2. Crear Roles con slugs
        $roles = [
            ['nombre' => 'Administrador', 'slug' => 'admin', 'descripcion' => 'Acceso total al sistema'],
            ['nombre' => 'Gerente', 'slug' => 'gerente', 'descripcion' => 'Gestión de sucursal'],
            ['nombre' => 'Secretario', 'slug' => 'secretario', 'descripcion' => 'Gestión administrativa'],
            ['nombre' => 'Mecánico', 'slug' => 'mecanico', 'descripcion' => 'Personal técnico'],
            ['nombre' => 'Contador', 'slug' => 'contador', 'descripcion' => 'Gestión financiera'],
            ['nombre' => 'Supervisor', 'slug' => 'supervisor', 'descripcion' => 'Supervisión de taller'],
            ['nombre' => 'Dueño', 'slug' => 'dueno', 'descripcion' => 'Propietario'],
            ['nombre' => 'Cliente', 'slug' => 'cliente', 'descripcion' => 'Usuario final'],
        ];

        foreach ($roles as $rol) {
            DB::table('roles')->updateOrInsert(
                ['slug' => $rol['slug']],
                array_merge($rol, ['created_at' => now()])
            );
        }

        // 3. Crear Usuario Administrador
        $admin = User::firstOrCreate(['email' => 'admin@taller.com'], [
            'name' => 'Administrador General',
            'password' => Hash::make('admin123'),
        ]);

        // Asignar Roles (Admin y Dueno)
        $rolAdmin = DB::table('roles')->where('slug', 'admin')->first();
        $rolDueno = DB::table('roles')->where('slug', 'dueno')->first();

        DB::table('rol_usuario')->updateOrInsert(
            ['user_id' => $admin->id, 'role_id' => $rolAdmin->id]
        );
        DB::table('rol_usuario')->updateOrInsert(
            ['user_id' => $admin->id, 'role_id' => $rolDueno->id]
        );

        // Asignar al admin a AMBAS sucursales
        DB::table('sucursal_usuario')->updateOrInsert(
            ['user_id' => $admin->id, 'sucursal_id' => $sucursal1]
        );
        DB::table('sucursal_usuario')->updateOrInsert(
            ['user_id' => $admin->id, 'sucursal_id' => $sucursal2]
        );

        // 4. Catálogo de Marcas y Modelos
        $marcas = [
            'Toyota' => ['Hilux', 'Corolla', 'Rav4'],
            'Honda' => ['Civic', 'CR-V', 'Fit'],
            'Nissan' => ['Sentra', 'Frontier', 'Versa'],
            'Hyundai' => ['Tucson', 'Elantra', 'Accent'],
            'Ford' => ['Ranger', 'F-150', 'Explorer']
        ];

        foreach ($marcas as $marca => $modelos) {
            $marcaId = DB::table('marcas_vehiculos')->insertGetId([
                'nombre' => $marca,
                'created_at' => now()
            ]);

            foreach ($modelos as $modelo) {
                DB::table('modelos_vehiculos')->insert([
                    'marca_id' => $marcaId,
                    'nombre' => $modelo,
                    'created_at' => now()
                ]);
            }
        }
    }
}