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
        Schema::table('bitacoras_trabajo', function (Blueprint $table) {
            $table->enum('estado_pago', ['pendiente', 'pagado'])->default('pendiente');
            $table->foreignId('pago_trabajador_id')->nullable()->constrained('pago_trabajadors')->nullOnDelete();
            $table->decimal('monto_pago', 10, 2)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('bitacoras_trabajo', function (Blueprint $table) {
            $table->dropForeign(['pago_trabajador_id']);
            $table->dropColumn(['estado_pago', 'pago_trabajador_id', 'monto_pago']);
        });
    }
};
