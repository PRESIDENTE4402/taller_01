<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\ModeloVehiculo>
 */
class ModeloVehiculoFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'marca_id' => \App\Models\MarcaVehiculo::factory(),
            'nombre' => $this->faker->word(),
            'activo' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }
}
