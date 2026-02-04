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
        // Repuestos
        Schema::create('repuestos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sucursal_id')->constrained('sucursales'); // Cada taller tiene su propio stock
            $table->string('codigo_interno')->unique(); // SKU o Código de barras
            $table->string('nombre');
            $table->string('marca_repuesto')->nullable(); // Ej: Bosch, Brembo, Genérico
            $table->string('categoria'); // Ej: Frenos, Motor, Suspensión
            $table->enum('unidad_medida', ['unidad', 'litro', 'galon', 'cuarto', 'onza'])->default('unidad');
            $table->decimal('contenido_por_unidad', 8, 2)->default(1); // Ej: 1 galón = 3.785 litros

            // Costos y Precios
            $table->decimal('precio_costo', 12, 2);
            $table->decimal('precio_venta', 12, 2);

            // Inventario
            $table->integer('stock_actual');
            $table->integer('stock_minimo'); // Para que la IA te avise qué comprar
            $table->string('ubicacion_estante')->nullable(); // Ej: Pasillo A - Estante 3

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('repuestos');
    }
};
