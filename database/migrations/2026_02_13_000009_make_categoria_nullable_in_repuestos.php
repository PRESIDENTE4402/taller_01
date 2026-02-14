<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('repuestos', function (Blueprint $table) {
            // Hacer categoria nullable ya que usaremos categoria_id
            if (Schema::hasColumn('repuestos', 'categoria')) {
                $table->string('categoria')->nullable()->change();
            }
        });
    }

    public function down(): void
    {
        Schema::table('repuestos', function (Blueprint $table) {
            if (Schema::hasColumn('repuestos', 'categoria')) {
                $table->string('categoria')->nullable(false)->change();
            }
        });
    }
};
