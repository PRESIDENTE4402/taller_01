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
        Schema::create('asistencias', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('sucursal_id')->constrained('sucursales');
            $table->date('fecha'); // El día de la jornada

            // Horas reales
            $table->time('hora_entrada')->nullable();
            $table->time('hora_salida')->nullable();

            // Clasificación de la asistencia
            $table->enum('tipo', [
                'presente',
                'ausente',
                'permiso',
                'vacaciones',
                'suspension_medica'
            ])->default('presente');

            // Estados para alertas (Punto 12 de tu alcance)
            $table->enum('estado', [
                'a_tiempo',
                'tardanza',
                'falta_justificada',
                'falta_injustificada'
            ])->default('a_tiempo');

            $table->text('observaciones')->nullable(); // Para anotar por qué llegó tarde o el motivo del permiso
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('asistencias');
    }
};
