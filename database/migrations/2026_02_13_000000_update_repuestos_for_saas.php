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
        // 1. Create 'categorias' table
        Schema::create('categorias', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sucursal_id')->constrained('sucursales')->onDelete('cascade');
            $table->string('nombre');
            $table->foreignId('parent_id')->nullable()->constrained('categorias')->onDelete('cascade');
            $table->text('descripcion')->nullable();
            $table->timestamps();
        });

        // 2. Modify 'repuestos' table
        Schema::table('repuestos', function (Blueprint $table) {
            // Drop the old string column
            $table->dropColumn('categoria');

            // Add the foreign key to the new table
            $table->foreignId('categoria_id')->nullable()->after('sucursal_id')->constrained('categorias')->onDelete('set null');

            // Add JSON attributes for flexibility (SaaS)
            // Using 'text' as a fail-safe fallback if JSON type has strict version issues, 
            // but normally 'json' is preferred. Given the user context, 'json' is likely fine 
            // but let's stick to standard 'json' and if it fails we can adjust.
            $table->json('atributos')->nullable()->after('contenido_por_unidad');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('repuestos', function (Blueprint $table) {
            $table->dropForeign(['categoria_id']);
            $table->dropColumn('categoria_id');
            $table->dropColumn('atributos');
            $table->string('categoria')->after('marca_repuesto'); // Restore old column
        });

        Schema::dropIfExists('categorias');
    }
};
