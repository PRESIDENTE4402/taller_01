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
            $table->decimal('sueldo_base', 10, 2)->default(0)->after('fecha_fin_periodo');
            $table->decimal('descuentos', 10, 2)->default(0)->after('sueldo_base');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pago_trabajadors', function (Blueprint $table) {
            $table->dropColumn(['sueldo_base', 'descuentos']);
        });
    }
};
