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
            $table->foreignId('sucursal_id')->constrained('sucursales');
            $table->string('codigo_orden')->unique(); // Ej: OT-2026-001
            $table->enum('tipo_orden', ['normal', 'garantia', 'cortesia'])->default('normal');

            // Relaciones
            $table->foreignId('vehiculo_id')->constrained('vehiculos');
            $table->foreignId('cliente_id')->constrained('clientes'); // Cliente tabla nueva
            $table->foreignId('cita_id')->nullable()->constrained('citas'); // Relación con Cita

            $table->foreignId('receptor_id')->constrained('users'); // Usuario que recibe (Secretario)
            
            // Fechas Clave
            $table->dateTime('fecha_recepcion'); // Cuándo se recibió el auto
            $table->dateTime('fecha_finalizacion')->nullable(); // Cuándo terminaron los mecánicos
            $table->dateTime('fecha_entrega')->nullable(); // Cuándo se entregó al cliente

            // Datos variables de esta visita
            $table->string('color');
            $table->integer('kilometraje_entrada');
            $table->string('nivel_combustible'); // E, 1/4, 1/2, 3/4, F

            // JSON: Checklist y daños
            $table->json('inventario_recepcion')->nullable();
            $table->json('danos_reportados')->nullable();

            $table->text('falla_cliente'); // Lo que reporta el cliente
            $table->text('diagnostico')->nullable();
            $table->text('diagnostico_final')->nullable();
            $table->integer('conteo_reprogramaciones')->default(0);

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
