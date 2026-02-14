<?php

// Script para probar creación de repuesto
// Ejecutar con: php artisan tinker < test_repuesto.php

// Verificar datos existentes
echo "=== VERIFICANDO DATOS EXISTENTES ===\n\n";

// 1. Sucursales
$sucursales = DB::select('SELECT id, nombre FROM sucursales LIMIT 3');
echo "Sucursales:\n";
foreach ($sucursales as $s) {
    echo "  - ID: {$s->id}, Nombre: {$s->nombre}\n";
}

// 2. Usuarios
$usuarios = DB::select('SELECT id, email, sucursal_por_defecto_id FROM users LIMIT 3');
echo "\nUsuarios:\n";
foreach ($usuarios as $u) {
    echo "  - ID: {$u->id}, Email: {$u->email}, Sucursal Default: {$u->sucursal_por_defecto_id}\n";
}

// 3. Categorías
$categorias = DB::select('SELECT id, nombre FROM categorias LIMIT 5');
echo "\nCategorías:\n";
if (count($categorias) === 0) {
    echo "  ⚠️ No hay categorías! Creando una...\n";
    DB::insert('INSERT INTO categorias (nombre, descripcion, sucursal_id, created_at, updated_at) VALUES (?, ?, ?, NOW(), NOW())', 
        ['Llantas', 'Llantas y ruedas', 1]
    );
    $categorias = DB::select('SELECT id, nombre FROM categorias WHERE nombre = "Llantas"');
} else {
    foreach ($categorias as $c) {
        echo "  - ID: {$c->id}, Nombre: {$c->nombre}\n";
    }
}

// 4. Atributos para primera categoría
if (count($categorias) > 0) {
    $cat_id = $categorias[0]->id;
    $schemas = DB::select('SELECT id, attribute_name, data_type, options FROM dynamic_attribute_schemas WHERE categoria_id = ?', [$cat_id]);
    echo "\nAtributos para Categoría {$cat_id}:\n";
    if (count($schemas) === 0) {
        echo "  ⚠️ No hay atributos! Creando uno...\n";
        DB::insert(
            'INSERT INTO dynamic_attribute_schemas (categoria_id, sucursal_id, attribute_name, data_type, is_required, options, created_at, updated_at) VALUES (?, ?, ?, ?, ?, ?, NOW(), NOW())',
            [$cat_id, 1, 'rin', 'select', true, json_encode(['13','14','15','16','17','18'])]
        );
        $schemas = DB::select('SELECT id, attribute_name, data_type FROM dynamic_attribute_schemas WHERE categoria_id = ? AND attribute_name = "rin"', [$cat_id]);
    } else {
        foreach ($schemas as $s) {
            $opts = json_decode($s->options, true);
            $opts_str = implode(', ', (array)$opts);
            echo "  - {$s->attribute_name} ({$s->data_type}): [{$opts_str}]\n";
        }
    }
}

// 5. Intentar crear Repuesto
echo "\n\n=== INTENTANDO CREAR REPUESTO ===\n\n";

try {
    $repuesto = \App\Models\Repuesto::create([
        'codigo' => 'LLANTA-TEST-001',
        'nombre' => 'Pirelli P7 225/45/R17',
        'categoria_id' => $categorias[0]->id,
        'precio_costo' => 310.00,
        'precio_venta' => 450.00,
        'stock' => 5,
        'stock_minimo' => 2,
        'atributos' => [
            'rin' => '17'
        ],
        'sucursal_id' => 1,
    ]);
    
    echo "✅ ÉXITO! Repuesto creado:\n";
    echo "  - ID: {$repuesto->id}\n";
    echo "  - Nombre: {$repuesto->nombre}\n";
    echo "  - Código: {$repuesto->codigo}\n";
    echo "  - Atributos: " . json_encode($repuesto->atributos) . "\n";
    
} catch (\Illuminate\Database\QueryException $e) {
    echo "❌ ERROR DE BD:\n";
    echo "  Mensaje: " . $e->getMessage() . "\n";
    echo "  SQL: " . $e->getSql() . "\n";
    
} catch (\Illuminate\Validation\ValidationException $e) {
    echo "❌ ERROR DE VALIDACIÓN:\n";
    var_dump($e->errors());
    
} catch (\Exception $e) {
    echo "❌ ERROR GENERAL:\n";
    echo "  Clase: " . get_class($e) . "\n";
    echo "  Mensaje: " . $e->getMessage() . "\n";
    echo "  Archivo: " . $e->getFile() . ":" . $e->getLine() . "\n";
}

echo "\n";
