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
        Schema::table('users', function (Blueprint $table) {
            // Agregar sucursal por defecto (cuando el usuario inicia sesión sin especificar sucursal)
            $table->foreignId('sucursal_por_defecto_id')->nullable()->after('id')->constrained('sucursales')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['sucursal_por_defecto_id']);
            $table->dropColumn('sucursal_por_defecto_id');
        });
    }
};
