<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Add estado to detalles_orden
        Schema::table('detalles_orden', function (Blueprint $table) {
            $table->enum('estado', ['pendiente', 'aprobado', 'rechazado'])->default('aprobado')->after('notas');
        });

        // Add new states to ordenes_trabajo enum safely via raw SQL to avoid doctrine/dbal requirement in older apps
        // Actually, if it's MySQL:
        DB::statement("ALTER TABLE ordenes_trabajo MODIFY COLUMN estado ENUM('abierta', 'en_proceso', 'espera_repuesto', 'finalizada', 'entregada', 'espera_aprobacion', 'espera_aprobacion_adicional') NOT NULL DEFAULT 'abierta'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('detalles_orden', function (Blueprint $table) {
            $table->dropColumn('estado');
        });

        // Revert enum in ordenes_trabajo
        DB::statement("ALTER TABLE ordenes_trabajo MODIFY COLUMN estado ENUM('abierta', 'en_proceso', 'espera_repuesto', 'finalizada', 'entregada') NOT NULL DEFAULT 'abierta'");
    }
};
