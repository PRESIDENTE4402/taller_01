<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Vehiculo>
 */
class VehiculoFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        // Randomly pick a Marca and its corresponding Modelos if any exist, otherwise generic
        // For factory simplicity we might need to rely on existing seeded data or create new one on fly if empty.
        // But better to assume Seeders ran first.

        return [
            'placa' => strtoupper($this->faker->bothify('P-###???')),
            // 'color' => $this->faker->safeColorName(), // Not in migration
            'anio' => $this->faker->year(),
            'vin' => strtoupper($this->faker->bothify('1HG#############')),
            // 'tipo_combustible' => $this->faker->randomElement(['gasolina', 'diesel', 'hibrido']), // Not in migration
            // 'tipo_transmision' => $this->faker->randomElement(['automatica', 'mecanica']), // Not in migration
            // Relationships usually overridden by Seeder or created if not provided
            'cliente_id' => \App\Models\Cliente::factory(),
            'marca_id' => \App\Models\MarcaVehiculo::inRandomOrder()->first()->id ?? \App\Models\MarcaVehiculo::factory(),
            'modelo_id' => function (array $attributes) {
                // If marca_id is newly created or fetched, ensure model belongs to it or create one
                // But simplified: fetch a model belonging to the marca or create new
                $marcaId = $attributes['marca_id'];
                $modelo = \App\Models\ModeloVehiculo::where('marca_id', $marcaId)->inRandomOrder()->first();
                return $modelo ? $modelo->id : \App\Models\ModeloVehiculo::factory()->create(['marca_id' => $marcaId])->id;
            },
            'version_id' => null,
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }
}
