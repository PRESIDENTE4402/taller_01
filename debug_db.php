<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

echo "Checking personas table...\n";
if (Schema::hasTable('personas')) {
    echo "Table 'personas' EXISTS.\n";
    try {
        Schema::drop('personas');
        echo "Table 'personas' DROPPED.\n";
    } catch (\Exception $e) {
        echo "Error dropping table: " . $e->getMessage() . "\n";
    }
} else {
    echo "Table 'personas' DOES NOT EXIST.\n";
}

echo "Attempting to create table manually...\n";
try {
    Schema::create('personas', function ($table) {
        $table->id();
        $table->foreignId('user_id')->unique()->constrained('users')->onDelete('cascade');
        $table->string('nombres');
        $table->string('apellidos');
        $table->integer('edad')->nullable();
        $table->string('sexo')->nullable();
        $table->string('telefono')->nullable();
        $table->string('correo')->nullable();
        $table->text('direccion')->nullable();
        $table->text('cursos')->nullable();
        $table->text('otros_datos')->nullable();
        $table->timestamps();
    });
    echo "Table 'personas' CREATED manually.\n";
} catch (\Exception $e) {
    echo "Error creating table: " . $e->getMessage() . "\n";
}
