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
        Schema::table('movimientos_inventario', function (Blueprint $table) {
            $table->foreignId('sucursal_id')->after('user_id')->nullable()->constrained('sucursales');
            $table->decimal('stock_anterior', 10, 2)->after('cantidad')->default(0);
            $table->decimal('stock_nuevo', 10, 2)->after('stock_anterior')->default(0);
            $table->unsignedBigInteger('referencia_id')->after('motivo')->nullable();
            $table->text('notas')->after('referencia_id')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('movimientos_inventario', function (Blueprint $table) {
            $table->dropForeign(['sucursal_id']);
            $table->dropColumn(['sucursal_id', 'stock_anterior', 'stock_nuevo', 'referencia_id', 'notas']);
        });
    }
};
