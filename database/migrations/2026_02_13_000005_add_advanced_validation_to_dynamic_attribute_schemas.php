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
        Schema::table('dynamic_attribute_schemas', function (Blueprint $table) {
            // Validación avanzada
            $table->string('regex_pattern')->nullable()->after('options'); // Patrón regex
            $table->integer('min_length')->nullable()->after('regex_pattern'); // Longitud mínima
            $table->integer('max_length')->nullable()->after('min_length'); // Longitud máxima
            $table->decimal('min_value', 10, 2)->nullable()->after('max_length'); // Valor mínimo (números)
            $table->decimal('max_value', 10, 2)->nullable()->after('min_value'); // Valor máximo (números)
            $table->text('help_text')->nullable()->after('max_value'); // Texto de ayuda
            $table->boolean('is_active')->default(true)->after('help_text'); // Activar/desactivar sin borrar
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('dynamic_attribute_schemas', function (Blueprint $table) {
            $table->dropColumn([
                'regex_pattern',
                'min_length',
                'max_length',
                'min_value',
                'max_value',
                'help_text',
                'is_active'
            ]);
        });
    }
};
