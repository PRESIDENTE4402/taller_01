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
        Schema::create('vehiculos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cliente_id')->constrained('clientes')->onDelete('cascade');
            $table->foreignId('marca_id')->constrained('marcas_vehiculos');
            $table->foreignId('modelo_id')->constrained('modelos_vehiculos');
            $table->foreignId('version_id')->nullable()->constrained('versiones_vehiculos');
            $table->string('placa')->unique();
            $table->integer('anio');
            $table->string('color')->nullable();
            $table->string('vin')->nullable(); // Chasis
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vehiculos');
    }
};
