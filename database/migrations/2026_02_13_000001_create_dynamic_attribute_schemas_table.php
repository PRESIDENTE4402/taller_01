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
        Schema::create('dynamic_attribute_schemas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sucursal_id')->constrained('sucursales')->onDelete('cascade');
            $table->foreignId('categoria_id')->constrained('categorias')->onDelete('cascade');
            $table->string('attribute_name');      // "número_parte", "rin", "ancho"
            $table->enum('data_type', ['text', 'number', 'date', 'select', 'boolean'])->default('text');
            $table->boolean('is_required')->default(false);
            $table->json('options')->nullable();   // Para select: ["opcion1", "opcion2"]
            $table->integer('display_order')->default(0);
            $table->timestamps();

            // Índice para búsquedas rápidas (con nombre acortado para evitar límite MySQL 64 chars)
            $table->unique(['sucursal_id', 'categoria_id', 'attribute_name'], 'unq_schema_attr');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('dynamic_attribute_schemas');
    }
};
