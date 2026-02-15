<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "Citas: " . \App\Models\Cita::count() . PHP_EOL;
echo "Ordenes: " . \App\Models\OrdenTrabajo::count() . PHP_EOL;
echo "Vehiculos: " . \App\Models\Vehiculo::count() . PHP_EOL;
echo "Clientes: " . \App\Models\Cliente::count() . PHP_EOL;
