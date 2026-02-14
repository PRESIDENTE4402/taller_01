<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\DB;

Route::get('/test-repuesto', function () {
    $html = "<h1>🧪 PRUEBA DE CREACIÓN DE REPUESTO</h1>";
    
    try {
        // 1. Verificar conexión BD
        $html .= "<h2>✓ Paso 1: Conexión BD</h2>";
        $tables = DB::select("SELECT COUNT(*) as cnt FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_SCHEMA=DATABASE()");
        $html .= "<p>Se encontraron " . $tables[0]->cnt . " tablas</p>";
        
        // 2. Verificar sucursales
        $html .= "<h2>✓ Paso 2: Verificando sucursales</h2>";
        $sucursal = DB::table('sucursales')->first();
        if (!$sucursal) {
            $html .= "<p>❌ No hay sucursales en la BD</p>";
            return response($html, 400)->header('Content-Type', 'text/html');
        }
        $html .= "<p>Sucursal: ID={$sucursal->id}, Nombre={$sucursal->nombre}</p>";
        
        // 3. Verificar usuario
        $html .= "<h2>✓ Paso 3: Verificando usuario</h2>";
        $usuario = DB::table('users')->first();
        if (!$usuario) {
            $html .= "<p>❌ No hay usuarios en la BD</p>";
            return response($html, 400)->header('Content-Type', 'text/html');
        }
        $html .= "<p>Usuario: ID={$usuario->id}, Email={$usuario->email}</p>";
        
        // 4. Verificar/crear categoría
        $html .= "<h2>✓ Paso 4: Verificando categorería</h2>";
        $categoria = DB::table('categorias')->first();
        if (!$categoria) {
            DB::table('categorias')->insert([
                'nombre' => 'Llantas',
                'descripcion' => 'Llantas y ruedas',
                'sucursal_id' => $sucursal->id,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            $categoria = DB::table('categorias')->first();
            $html .= "<p>⚠️ Categoría creada</p>";
        }
        $html .= "<p>Categoría: ID={$categoria->id}, Nombre={$categoria->nombre}</p>";
        
        // 5. Verificar/crear atributo
        $html .= "<h2>✓ Paso 5: Verificando atributo dinámico</h2>";
        $schema = DB::table('dynamic_attribute_schemas')
            ->where('categoria_id', $categoria->id)
            ->where('attribute_name', 'rin')
            ->first();
        
        if (!$schema) {
            DB::table('dynamic_attribute_schemas')->insert([
                'categoria_id' => $categoria->id,
                'sucursal_id' => $sucursal->id,
                'attribute_name' => 'rin',
                'data_type' => 'select',
                'is_required' => true,
                'options' => json_encode(['13','14','15','16','17','18']),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            $html .= "<p>⚠️ Atributo 'rin' creado</p>";
        } else {
            $html .= "<p>Atributo ya existe: ID={$schema->id}</p>";
        }
        
        // 6. CREAR REPUESTO
        $html .= "<h2>✓ Paso 6: Creando repuesto...</h2>";
        $repuesto = \App\Models\Repuesto::create([
            'codigo_interno' => 'TEST-' . time(), // Campo requerido original
            'nombre' => 'Pirelli P7 225/45/R17',
            'categoria_id' => $categoria->id,
            'precio_costo' => 310.00,
            'precio_venta' => 450.00,
            'stock_minimo' => 2,
            'stock_actual' => 5, // Nombre correcto de la columna
            'atributos' => ['rin' => '17'],
            'sucursal_id' => $sucursal->id,
        ]);
        
        $html .= "<div style='background:lightgreen; padding:20px; margin:20px 0; border-radius:5px;'>";
        $html .= "<h2 style='color:green;'>✅ ¡ÉXITO! REPUESTO CREADO CORRECTAMENTE</h2>";
        $html .= "<ul>";
        $html .= "<li><strong>ID:</strong> {$repuesto->id}</li>";
        $html .= "<li><strong>Código:</strong> {$repuesto->codigo}</li>";
        $html .= "<li><strong>Nombre:</strong> {$repuesto->nombre}</li>";
        $html .= "<li><strong>Categoría:</strong> {$repuesto->categoria_id}</li>";
        $html .= "<li><strong>Sucursal:</strong> {$repuesto->sucursal_id}</li>";
        $html .= "<li><strong>Stock:</strong> {$repuesto->stock}</li>";
        $html .= "<li><strong>Precio Costo:</strong> ${$repuesto->precio_costo}</li>";
        $html .= "<li><strong>Precio Venta:</strong> ${$repuesto->precio_venta}</li>";
        $html .= "<li><strong>Atributos:</strong> " . json_encode($repuesto->atributos) . "</li>";
        $html .= "</ul>";
        $html .= "</div>";
        
    } catch (\Exception $e) {
        $html .= "<div style='background:lightcoral; padding:20px; margin:20p 0; border-radius:5px;'>";
        $html .= "<h2 style='color:darkred;'>❌ ERROR</h2>";
        $html .= "<p><strong>Tipo:</strong> " . get_class($e) . "</p>";
        $html .= "<p><strong>Mensaje:</strong> " . $e->getMessage() . "</p>";
        $html .= "<p><strong>Archivo:</strong> " . $e->getFile() . ":" . $e->getLine() . "</p>";
        
        if ($e instanceof \Illuminate\Database\QueryException) {
            $html .= "<p><strong>SQL:</strong> " . $e->getSql() . "</p>";
        }
        
        $html .= "<pre style='background:#f0f0f0; padding:10px; overflow:auto;'>";
        $html .= $e->getTraceAsString();
        $html .= "</pre>";
        $html .= "</div>";
    }
    
    return response($html, 200)->header('Content-Type', 'text/html; charset=utf-8');
});
