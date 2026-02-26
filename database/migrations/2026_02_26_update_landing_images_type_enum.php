<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        DB::statement("ALTER TABLE landing_images MODIFY COLUMN type ENUM('logo','service','gallery','video','about') NOT NULL");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE landing_images MODIFY COLUMN type ENUM('logo','service','gallery') NOT NULL");
    }
};
