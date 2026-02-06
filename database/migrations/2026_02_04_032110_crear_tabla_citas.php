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
        Schema::create('citas', function (Blueprint $table) {
            $table->id();
            // Relaciones
            $table->foreignId('cliente_id')->constrained('clientes')->onDelete('cascade');
            $table->foreignId('vehiculo_id')->constrained('vehiculos')->onDelete('cascade');

            // Datos de la cita
            $table->dateTime('fecha_programada'); // Para cuándo es la cita
            $table->dateTime('fecha_realizacion')->nullable(); // Cuándo se realizó/completó efectivamente
            
            $table->text('motivo_cita'); // "Cambio de aceite", "Ruidos en motor"
            $table->foreignId('sucursal_id')->constrained('sucursales');
            
            // Canal de origen
            $table->enum('origen', ['web', 'telefono', 'presencial', 'whatsapp'])->default('web');

            // Estados de la cita
            $table->enum('estado', [
                'pendiente',    // Recién creada
                'confirmada',   // Confirmada por secretario
                'concretada',   // Cliente llegó -> Se crea OT
                'cancelada',    // Cliente canceló
                'no_asistio'    // No show
            ])->default('pendiente');

            $table->text('notas_secretario')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('citas');
    }
};
