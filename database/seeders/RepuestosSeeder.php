<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class RepuestosSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1. Common Parts (Hardcoded for realism)
        $commonParts = [
            ['codigo_interno' => 'ACE-001', 'nombre' => 'Aceite Sintético 5W-30', 'categoria' => 'Motor', 'precio_costo' => 45.00, 'precio_venta' => 85.00, 'stock_actual' => 50],
            ['codigo_interno' => 'FIL-001', 'nombre' => 'Filtro de Aceite Universal', 'categoria' => 'Filtros', 'precio_costo' => 15.00, 'precio_venta' => 35.00, 'stock_actual' => 100],
            ['codigo_interno' => 'FRE-001', 'nombre' => 'Pastillas de Freno Delanteras', 'categoria' => 'Frenos', 'precio_costo' => 120.00, 'precio_venta' => 250.00, 'stock_actual' => 20],
            ['codigo_interno' => 'BUJ-001', 'nombre' => 'Bujía Iridium', 'categoria' => 'Motor', 'precio_costo' => 25.00, 'precio_venta' => 60.00, 'stock_actual' => 200],
            ['codigo_interno' => 'BAT-001', 'nombre' => 'Batería 12V 60Ah', 'categoria' => 'Eléctrico', 'precio_costo' => 300.00, 'precio_venta' => 550.00, 'stock_actual' => 10],
        ];

        foreach ($commonParts as $part) {
            $catName = $part['categoria'];
            unset($part['categoria']);

            $categoria = \App\Models\Categoria::firstOrCreate(
                ['nombre' => $catName],
                ['sucursal_id' => 1, 'descripcion' => "Categoría para $catName"]
            );

            \App\Models\Repuesto::updateOrCreate(
                ['codigo_interno' => $part['codigo_interno']],
                array_merge($part, [
                    'categoria_id' => $categoria->id,
                    'stock_minimo' => 5,
                    'marca_repuesto' => 'Genérica',
                    'sucursal_id' => 1,
                    'unidad_medida' => 'unidad'
                ])
            );
        }

        // 2. Random Parts
        \App\Models\Repuesto::factory()->count(50)->create();
    }
}
