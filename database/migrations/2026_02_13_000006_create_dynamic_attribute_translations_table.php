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
        Schema::create('dynamic_attribute_translations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('dynamic_attribute_schema_id')
                  ->constrained('dynamic_attribute_schemas', 'id')
                  ->onDelete('cascade');
            $table->string('locale')->default('es'); // es, en, fr, pt, etc.
            $table->string('label'); // Etiqueta en idioma específico (ej: "Viscosidad" vs "Viscosity")
            $table->text('description')->nullable(); // Descripción en idioma específico
            $table->timestamps();

            // Índice único para evitar duplicados
            $table->unique(['dynamic_attribute_schema_id', 'locale'], 'unq_attr_translation');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('dynamic_attribute_translations');
    }
};
