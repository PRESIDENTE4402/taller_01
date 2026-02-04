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
        Schema::create('bitacoras_trabajo', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users'); // El mecánico o trabajador
            $table->foreignId('sucursal_id')->after('id')->constrained('sucursales');
            // Relación opcional con vehículo/orden
            $table->foreignId('orden_trabajo_id')->nullable()->constrained('ordenes_trabajo');

            // Clasificación del trabajo
            $table->enum('tipo_actividad', [
                'mecanica',      // Reparación directa de vehículo
                'diagnostico',   // Revisión inicial
                'limpieza',      // Limpieza de área o taller
                'mantenimiento', // Mantenimiento de herramientas propias
                'administrativo',// Llenado de papeles o reportes
                'otro'
            ]);

            $table->string('descripcion'); // Ej: "Cambio de pastillas de freno" o "Limpieza de fosa 2"

            // Control de tiempo (Para metas y alertas del punto 5)
            $table->dateTime('inicio')->nullable();
            $table->dateTime('fin')->nullable();
            $table->integer('minutos_totales')->default(0); // Calculado automáticamente al finalizar

            // Metas de tiempo (Punto 5 del alcance)
            $table->integer('meta_minutos')->nullable(); // Tiempo estimado para la tarea

            $table->enum('estado', ['en_pausa', 'en_progreso', 'completado']);
            $table->text('notas_adicionales')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bitacoras_trabajo');
    }
};
