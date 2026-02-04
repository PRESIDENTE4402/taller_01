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

        // 2. Crear Roles
        $roles = [
            'admin',
            'gerente',
            'secretario',
            'mecanico',
            'contador',
            'supervisor',
            'dueño',
            'cliente'
        ];

        foreach ($roles as $rol) {
            DB::table('roles')->insert([
                'nombre' => $rol,
                'created_at' => now(),
            ]);
        }

        // 3. Crear Usuario Administrador (Dueño)
        $admin = User::create([
            'name' => 'Administrador General',
            'email' => 'admin@taller.com',
            'password' => Hash::make('admin123'),
            'phone' => '4444-5555',
        ]);

        // Asignar Rol de Dueño y Admin al usuario
        $rolAdmin = DB::table('roles')->where('nombre', 'admin')->first();
        $rolDueño = DB::table('roles')->where('nombre', 'dueño')->first();

        DB::table('rol_usuario')->insert([
            ['user_id' => $admin->id, 'role_id' => $rolAdmin->id],
            ['user_id' => $admin->id, 'role_id' => $rolDueño->id],
        ]);

        // Asignar al admin a AMBAS sucursales
        DB::table('sucursal_usuario')->insert([
            ['user_id' => $admin->id, 'sucursal_id' => $sucursal1],
            ['user_id' => $admin->id, 'sucursal_id' => $sucursal2],
        ]);

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