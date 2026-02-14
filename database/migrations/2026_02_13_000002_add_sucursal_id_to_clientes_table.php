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
        Schema::table('clientes', function (Blueprint $table) {
            // Agregar sucursal_id para multitenant
            $table->foreignId('sucursal_id')->nullable()->after('id')->constrained('sucursales')->onDelete('cascade');
            
            // Hacer email único por sucursal, no globalmente
            $table->dropUnique(['email']);
            $table->unique(['sucursal_id', 'email']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('clientes', function (Blueprint $table) {
            // Revertir cambios
            $table->dropUnique(['sucursal_id', 'email']);
            $table->unique(['email']);
            $table->dropForeign(['sucursal_id']);
            $table->dropColumn('sucursal_id');
        });
    }
};
