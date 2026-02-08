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
        if (Schema::hasTable('roles')) {
            Schema::table('roles', function (Blueprint $table) {
                if (Schema::hasColumn('roles', 'name') && !Schema::hasColumn('roles', 'nombre')) {
                    $table->renameColumn('name', 'nombre');
                }
                if (Schema::hasColumn('roles', 'description') && !Schema::hasColumn('roles', 'descripcion')) {
                    $table->renameColumn('description', 'descripcion');
                }
                // Ensure slug exists for hasRole logic
                if (!Schema::hasColumn('roles', 'slug')) {
                    $table->string('slug')->unique()->nullable();
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // No reverse needed or just reverse renames
    }
};
