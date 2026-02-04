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
        Schema::create('versiones_vehiculos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('modelo_id')->constrained('modelos_vehiculos')->onDelete('cascade');
            $table->string('nombre'); // Ej: "Híbrido Full", "Sport", "Diesel 2.4"
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
