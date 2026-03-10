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
        Schema::table('bitacoras_trabajo', function (Illuminate\Database\Schema\Blueprint $table) {
            $table->text('motivo_pausa')->nullable()->after('estado');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('bitacoras_trabajo', function (Illuminate\Database\Schema\Blueprint $table) {
            $table->dropColumn('motivo_pausa');
        });
    }
};
