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
        Schema::create('movimientos_inventario', function (Blueprint $table) {
            $table->id();
            $table->foreignId('repuesto_id')->constrained('repuestos');
            $table->foreignId('user_id')->constrained('users'); // Quién hizo el movimiento
            $table->decimal('cantidad', 10, 2);
            $table->enum('tipo', ['entrada', 'salida', 'ajuste']);
            $table->string('motivo'); // Ej: "Compra a proveedor", "Repuesto defectuoso"
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
