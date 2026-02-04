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
        Schema::create('reprogramaciones_orden', function (Blueprint $table) {
            $table->id();
            $table->foreignId('orden_trabajo_id')->constrained('ordenes_trabajo')->onDelete('cascade');
            $table->foreignId('autorizado_por')->constrained('users'); // Quién registró el cambio (Gerente/Secretario)

            // Trazabilidad de fechas
            $table->dateTime('fecha_anterior');
            $table->dateTime('fecha_nueva');

            // Justificación técnica
            $table->text('motivo_hallazgo'); // "Se detectó fuga en radiador al desarmar el motor"
            $table->string('foto_evidencia')->nullable(); // Para que el cliente vea que es verdad

            // Control de comunicación (Vital para evitar reclamos)
            $table->boolean('notificado_al_cliente')->default(false);
            $table->enum('medio_notificacion', ['whatsapp', 'llamada', 'presencial', 'correo'])->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('reprogramaciones_orden');
    }
};
