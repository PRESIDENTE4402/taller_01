<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('sucursales', function (Blueprint $table) {
            $table->decimal('latitud', 10, 7)->nullable()->after('telefono')->comment('Latitud GPS');
            $table->decimal('longitud', 10, 7)->nullable()->after('latitud')->comment('Longitud GPS');
            $table->string('ciudad', 100)->nullable()->after('longitud');
            $table->boolean('activa')->default(true)->after('ciudad');
        });
    }

    public function down(): void
    {
        Schema::table('sucursales', function (Blueprint $table) {
            $table->dropColumn(['latitud', 'longitud', 'ciudad', 'activa']);
        });
    }
};
