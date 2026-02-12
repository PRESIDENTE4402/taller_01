<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Cliente>
 */
class ClienteFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'nombre_completo' => $this->faker->name(),
            'telefono' => $this->faker->phoneNumber(),
            'email' => $this->faker->unique()->safeEmail(),
            'direccion' => $this->faker->address(),
            'nit' => $this->faker->numerify('#######-#'),
            'es_empresa' => $isCompany = $this->faker->boolean(20),
            'empresa' => $isCompany ? $this->faker->company() : null,
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }
}
