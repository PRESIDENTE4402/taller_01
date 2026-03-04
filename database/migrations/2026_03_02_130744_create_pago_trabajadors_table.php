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
        Schema::create('pago_trabajadors', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users');
            $table->decimal('monto_total', 12, 2)->default(0);
            $table->date('fecha_pago');
            $table->date('fecha_inicio_periodo')->nullable();
            $table->date('fecha_fin_periodo')->nullable();
            $table->string('metodo_pago');
            $table->text('observaciones')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pago_trabajadors');
    }
};
