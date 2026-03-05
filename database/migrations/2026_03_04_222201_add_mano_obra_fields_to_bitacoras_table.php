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
        Schema::table('bitacoras_trabajo', function (Blueprint $table) {
            $table->decimal('precio_cliente', 12, 2)->default(0)->after('notas_adicionales');
            $table->enum('tipo_pago_mecanico', ['porcentaje', 'fijo'])->default('porcentaje')->after('precio_cliente');
            $table->decimal('valor_pago_mecanico', 8, 2)->default(0)->after('tipo_pago_mecanico'); // El porcentaje (ej 30) o el monto fijo (ej 150)
            $table->decimal('descuento_cliente', 12, 2)->default(0)->after('valor_pago_mecanico');
            $table->string('motivo_descuento')->nullable()->after('descuento_cliente');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('bitacoras_trabajo', function (Blueprint $table) {
            $table->dropColumn(['precio_cliente', 'tipo_pago_mecanico', 'valor_pago_mecanico', 'descuento_cliente', 'motivo_descuento']);
        });
    }
};
