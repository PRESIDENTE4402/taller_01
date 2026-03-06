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
        Schema::table('pago_trabajadors', function (Blueprint $table) {
            $table->boolean('modificado')->default(false)->after('observaciones');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pago_trabajadors', function (Blueprint $table) {
            $table->dropColumn('modificado');
        });
    }
};
