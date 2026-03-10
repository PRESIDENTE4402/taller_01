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
        Schema::table('ordenes_trabajo', function (Illuminate\Database\Schema\Blueprint $table) {
            $table->string('estado')->change(); // Cambiar a string para mayor flexibilidad o actualizar enum
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('ordenes_trabajo', function (Illuminate\Database\Schema\Blueprint $table) {
            //
        });
    }
};
