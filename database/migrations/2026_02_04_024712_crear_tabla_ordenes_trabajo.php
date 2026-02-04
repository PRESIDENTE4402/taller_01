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
        Schema::create('ordenes_trabajo', function (Blueprint $table) {
            $table->id();
            $table->string('codigo_orden')->unique(); // Ej: OT-2026-001
            $table->foreignId('vehiculo_id')->constrained('vehiculos');
            $table->foreignId('cliente_id')->constrained('users');
            $table->string('color');
            $table->foreignId('receptor_id')->constrained('users'); // Secretario que recibe
            $table->foreignId('sucursal_id')->after('id')->constrained('sucursales');
            // Datos variables de esta visita
            $table->integer('kilometraje_entrada');
            $table->string('nivel_combustible'); // E, 1/4, 1/2, 3/4, F

            // JSON: Aquí guardamos checklist (radio, herramientas, etc) y mapa de daños
            $table->json('inventario_recepcion')->nullable();
            $table->json('danos_reportados')->nullable();

            $table->text('falla_cliente'); // Lo que reporta el cliente
            $table->text('diagnostico')->nullable();

            $table->enum('estado', ['abierta', 'en_proceso', 'espera_repuesto', 'finalizada', 'entregada']);
            $table->decimal('total_estimado', 12, 2)->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ordenes_trabajo');
    }
};
