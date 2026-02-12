<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class StaffSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $password = \Illuminate\Support\Facades\Hash::make('password');

        // 1. Recepcionista
        $recepcionista = \App\Models\User::firstOrCreate(
            ['email' => 'recepcion@taller.com'],
            ['name' => 'Ana Recepcionista', 'password' => $password]
        );
        $rolSecretario = \App\Models\Role::where('slug', 'secretario')->first();
        if ($rolSecretario) {
            $recepcionista->roles()->syncWithoutDetaching([$rolSecretario->id]);
            // Assign to Sucursal 1
            $recepcionista->sucursales()->syncWithoutDetaching([1]);
        }

        // 2. Mecánicos
        $mecanicos = [
            ['email' => 'mecanico1@taller.com', 'name' => 'Juan Mecánico'],
            ['email' => 'mecanico2@taller.com', 'name' => 'Pedro Técnico'],
            ['email' => 'mecanico3@taller.com', 'name' => 'Carlos Electricista'],
        ];

        $rolMecanico = \App\Models\Role::where('slug', 'mecanico')->first();

        foreach ($mecanicos as $m) {
            $user = \App\Models\User::firstOrCreate(
                ['email' => $m['email']],
                ['name' => $m['name'], 'password' => $password]
            );
            if ($rolMecanico) {
                $user->roles()->syncWithoutDetaching([$rolMecanico->id]);
                // Assign to Sucursal 1
                $user->sucursales()->syncWithoutDetaching([1]);
            }
        }
    }
}
