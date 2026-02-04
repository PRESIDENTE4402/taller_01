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
        Schema::create('detalles_orden', function (Blueprint $table) {
            $table->id();
            $table->foreignId('orden_trabajo_id')->constrained('ordenes_trabajo')->onDelete('cascade');
            $table->foreignId('repuesto_id')->nullable()->constrained('repuestos'); // Nulo si lo trajo el cliente

            // Si el repuesto no está en nuestro inventario (lo trajo el cliente)
            $table->string('descripcion_manual')->nullable();

            $table->decimal('cantidad', 8, 2); // En lugar de integer
            $table->decimal('precio_unitario', 12, 2)->default(0); // Precio al que se le cobró al cliente

            // CLAVE: ¿Quién suministró el repuesto?
            $table->enum('suministrado_por', [
                'taller',      // Sale de nuestro stock y se cobra
                'cliente',     // El cliente lo trajo, no se cobra el repuesto, solo la mano de obra
                'externo'      // Lo compramos a un tercero específicamente para esta orden
            ])->default('taller');

            $table->text('notas')->nullable(); // Ej: "Cliente trajo pastillas marca Patito"
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('detalles_orden');
    }
};
