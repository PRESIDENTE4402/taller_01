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
            // Validación avanzada - Solo agregar si no existen las columnas
            if (!Schema::hasColumn('dynamic_attribute_schemas', 'validation_regex')) {
                $table->string('validation_regex')->nullable()->after('options');
            }
            if (!Schema::hasColumn('dynamic_attribute_schemas', 'min_value')) {
                $table->integer('min_value')->nullable()->after('validation_regex');
            }
            if (!Schema::hasColumn('dynamic_attribute_schemas', 'max_value')) {
                $table->integer('max_value')->nullable()->after('min_value');
            }
            if (!Schema::hasColumn('dynamic_attribute_schemas', 'min_length')) {
                $table->integer('min_length')->nullable()->after('max_value');
            }
            if (!Schema::hasColumn('dynamic_attribute_schemas', 'max_length')) {
                $table->integer('max_length')->nullable()->after('min_length');
            }
            if (!Schema::hasColumn('dynamic_attribute_schemas', 'help_text')) {
                $table->text('help_text')->nullable()->after('max_length');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('dynamic_attribute_schemas', function (Blueprint $table) {
            $table->dropColumn([
                'validation_regex',
                'min_value',
                'max_value',
                'min_length',
                'max_length',
                'help_text'
            ]);
        });
    }
};
