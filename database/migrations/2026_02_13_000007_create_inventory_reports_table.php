<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('inventory_reports', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sucursal_id')->constrained('sucursales')->onDelete('cascade');
            $table->foreignId('created_by')->constrained('users')->onDelete('cascade');
            $table->string('nombre_reporte');
            $table->text('descripcion')->nullable();
            $table->enum('tipo_reporte', [
                'inventario_general',      // Inventario completo
                'bajo_stock',              // Productos con stock bajo
                'sin_movimiento',          // Productos sin venta en X días
                'valor_inventario',        // Valor total del inventario
                'por_categoria',           // Inventario por categoría
                'comparativa_periodos'     // Comparativa mes a mes
            ])->default('inventario_general');
            
            // Filtros aplicados en reporte
            $table->foreignId('categoria_id')->nullable()->constrained('categorias')->onDelete('set null');
            $table->date('fecha_inicio')->nullable();
            $table->date('fecha_fin')->nullable();
            $table->json('filtros_adicionales')->nullable(); // JSON con filtros personalizados
            
            // Datos del reporte
            $table->integer('total_productos');
            $table->decimal('valor_total_costo', 15, 2);
            $table->decimal('valor_total_venta', 15, 2);
            $table->integer('stock_total');
            $table->json('datos_reporte')->nullable(); // Datos completos del reporte
            
            $table->timestamp('generated_at');
            $table->timestamps();

            // Índices
            $table->index('sucursal_id');
            $table->index('created_by');
            $table->index('generated_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('inventory_reports');
    }
};
