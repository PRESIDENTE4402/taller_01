<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Repuesto>
 */
class RepuestoFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'sucursal_id' => 1,
            'codigo_interno' => strtoupper($this->faker->unique()->bothify('???-#####')),
            'nombre' => $this->faker->words(3, true),
            'marca_repuesto' => $this->faker->word(),
            'categoria_id' => \App\Models\Categoria::inRandomOrder()->first()->id ?? \App\Models\Categoria::factory(),
            'unidad_medida' => 'unidad',
            'contenido_por_unidad' => 1,
            'precio_costo' => $this->faker->randomFloat(2, 10, 500),
            'precio_venta' => $this->faker->randomFloat(2, 20, 1000),
            'stock_actual' => $this->faker->numberBetween(0, 100),
            'stock_minimo' => 5,
            'ubicacion_estante' => 'A-01',
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }
}
