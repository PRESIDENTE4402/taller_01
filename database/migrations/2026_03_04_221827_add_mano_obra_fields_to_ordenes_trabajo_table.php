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
        Schema::table('ordenes_trabajo', function (Blueprint $table) {
            $table->decimal('total_mano_obra', 12, 2)->default(0)->after('total_estimado');
            $table->decimal('descuento_mano_obra', 12, 2)->default(0)->after('total_mano_obra');
            $table->string('motivo_descuento')->nullable()->after('descuento_mano_obra');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('ordenes_trabajo', function (Blueprint $table) {
            $table->dropColumn(['total_mano_obra', 'descuento_mano_obra', 'motivo_descuento']);
        });
    }
};
