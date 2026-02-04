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
        // crear_tabla_modelos_vehiculos
        Schema::create('modelos_vehiculos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('marca_id')->constrained('marcas_vehiculos')->onDelete('cascade');
            $table->string('nombre');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('modelos_vehiculos');
    }
};
