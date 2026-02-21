<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('clientes', function (Blueprint $table) {
            $table->string('situacion')->default('activo')->after('es_empresa'); // activo, prospecto, inactivo
        });

        Schema::table('vehiculos', function (Blueprint $table) {
            $table->string('situacion')->default('activo')->after('vin'); // activo, prospecto, inactivo
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('clientes', function (Blueprint $table) {
            $table->dropColumn('situacion');
        });

        Schema::table('vehiculos', function (Blueprint $table) {
            $table->dropColumn('situacion');
        });
    }
};
