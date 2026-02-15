<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\MarcaVehiculo>
 */
class MarcaVehiculoFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'nombre' => $this->faker->unique()->randomElement(['Toyota', 'Honda', 'Ford', 'Nissan', 'Hyundai', 'Kia', 'Mazda']),
            'activo' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }
}
