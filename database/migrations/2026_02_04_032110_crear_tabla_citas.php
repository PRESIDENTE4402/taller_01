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
            $table->foreignId('cliente_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('vehiculo_id')->constrained('vehiculos')->onDelete('cascade');

            // Datos de la cita
            $table->dateTime('fecha_programada'); // Cuándo reservó en la web
            $table->text('motivo_cita'); // "Cambio de aceite", "Ruidos en motor"
            $table->foreignId('sucursal_id')->after('id')->constrained('sucursales');
            // Canal de origen (Para saber si reservó por la web o por teléfono)
            $table->enum('origen', ['web', 'telefono', 'presencial', 'whatsapp'])->default('web');

            // Estados de la cita
            $table->enum('estado', [
                'pendiente',    // Recién creada en la web
                'confirmada',   // El secretario ya habló con el cliente y confirmó
                'concretada',   // El cliente LLEGÓ y se creó una Orden de Trabajo
                'cancelada',    // El cliente avisó que no venía
                'no_asistio'    // Pasó la hora y nunca llegó
            ])->default('pendiente');

            // Relación con la orden (Solo se llena si el estado es 'concretada')
            $table->foreignId('orden_trabajo_id')->nullable()->constrained('ordenes_trabajo');

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
