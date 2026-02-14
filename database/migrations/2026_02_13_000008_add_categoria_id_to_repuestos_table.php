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
        Schema::table('repuestos', function (Blueprint $table) {
            // Agregar la columna categoria_id como foreign key
            if (!Schema::hasColumn('repuestos', 'categoria_id')) {
                $table->foreignId('categoria_id')
                    ->nullable()
                    ->constrained('categorias')
                    ->onDelete('restrict')
                    ->after('sucursal_id');
            }
            
            // Agregar columna de atributos JSON si no existe
            if (!Schema::hasColumn('repuestos', 'atributos')) {
                $table->json('atributos')->nullable()->after('categoria_id');
            }
            
            // Agregar stock y stock_minimo con mejores nombres si falta
            if (!Schema::hasColumn('repuestos', 'stock')) {
                $table->integer('stock')
                    ->default(0)
                    ->after('stock_actual');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('repuestos', function (Blueprint $table) {
            if (Schema::hasColumn('repuestos', 'categoria_id')) {
                $table->dropForeignKey(['categoria_id']);
                $table->dropColumn('categoria_id');
            }
            
            if (Schema::hasColumn('repuestos', 'atributos')) {
                $table->dropColumn('atributos');
            }
            
            if (Schema::hasColumn('repuestos', 'stock')) {
                $table->dropColumn('stock');
            }
        });
    }
};
