<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;

class AdminBrianSeeder extends Seeder
{
    public function run(): void
    {
        // Verificar si el usuario ya existe para evitar duplicados
        $existingUser = User::where('email', 'brianmarin734@gmail.com')->first();
        
        if ($existingUser) {
            $this->command->info('El usuario brianmarin734@gmail.com ya existe.');
            return;
        }

        // Crear Usuario BRIAN
        $user = User::create([
            'name' => 'BRIAN',
            'email' => 'brianmarin734@gmail.com',
            'password' => Hash::make('admin1234'),
            'phone' => '0000-0000', // Teléfono placeholder
        ]);

        $this->command->info('Usuario BRIAN creado correctamente.');

        // Asignar Roles (Admin y Dueño)
        $rolAdmin = DB::table('roles')->where('nombre', 'admin')->first();
        $rolDueño = DB::table('roles')->where('nombre', 'dueño')->first();

        if ($rolAdmin) {
            DB::table('rol_usuario')->insert([
                'user_id' => $user->id,
                'role_id' => $rolAdmin->id
            ]);
        }

        if ($rolDueño) {
            DB::table('rol_usuario')->insert([
                'user_id' => $user->id,
                'role_id' => $rolDueño->id
            ]);
        }
        
        // Asignar a todas las sucursales existentes
        $sucursales = DB::table('sucursales')->pluck('id');
        foreach ($sucursales as $sucursalId) {
             DB::table('sucursal_usuario')->insert([
                'user_id' => $user->id,
                'sucursal_id' => $sucursalId
            ]);
        }

        $this->command->info('Roles y sucursales asignados a BRIAN.');
    }
}
