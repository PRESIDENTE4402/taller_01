<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Cita>
 */
class CitaFactory extends Factory
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
            'sucursal_id' => 1,
            'fecha_programada' => $this->faker->dateTimeBetween('now', '+1 month'),
            'motivo_cita' => $this->faker->sentence(3),
            'origen' => $this->faker->randomElement(['web', 'llamada', 'presencial']),
            'estado' => $this->faker->randomElement(['pendiente', 'confirmada', 'cancelada', 'atendida']),
            'notas_secretario' => $this->faker->optional()->sentence(),
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }
}
