<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\OrdenTrabajo>
 */
class OrdenTrabajoFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'cliente_id' => \App\Models\Cliente::factory(),
            'vehiculo_id' => \App\Models\Vehiculo::factory(),
            'cita_id' => null, // Optional
            'sucursal_id' => 1,
            'codigo_orden' => $this->faker->unique()->bothify('OT-' . date('Y') . '-####'),
            'tipo_orden' => $this->faker->randomElement(['normal', 'garantia', 'cortesia']),
            'estado' => $this->faker->randomElement(['abierta', 'en_proceso', 'espera_repuesto', 'finalizada', 'entregada']),
            // 'prioridad' => ... removed
            'kilometraje_entrada' => $this->faker->numberBetween(10000, 200000),
            'nivel_combustible' => $this->faker->randomElement(['E', '1/4', '1/2', '3/4', 'F']),
            'color' => $this->faker->safeColorName(),
            'falla_cliente' => $this->faker->paragraph(1),
            // 'observaciones_recepcion' => ... removed
            'fecha_recepcion' => $this->faker->dateTimeBetween('-1 month', 'now'),
            // 'fecha_promesa' => ... removed
            'receptor_id' => \App\Models\User::inRandomOrder()->first()->id ?? \App\Models\User::factory(), // User ID (Secretario)
            'total_estimado' => $this->faker->randomFloat(2, 100, 5000),
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }
}
