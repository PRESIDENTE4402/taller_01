<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Cita;
use App\Models\Vehiculo;
use App\Models\MarcaVehiculo;
use App\Models\ModeloVehiculo;

$cita = Cita::with('vehiculo.marca', 'vehiculo.modelo')->latest()->first();

if (!$cita) {
    echo "No cita found\n";
    exit;
}

echo "Resetting Cita ID: " . $cita->id . "\n";

// 1. Restore Motif with the manual vehicle marker
$originalMarca = $cita->vehiculo->marca->nombre;
$originalModelo = $cita->vehiculo->modelo->nombre;
$originalVersion = $cita->vehiculo->version->nombre ?? '';

$cita->motivo_cita = $cita->motivo_cita . " [Vehículo Ingresado: $originalMarca - $originalModelo - $originalVersion]";
$cita->estado = 'pendiente';
$cita->save();

// 2. Put Vehicle back to Generic
$marcaGen = MarcaVehiculo::firstOrCreate(['nombre' => 'GENERICA']);
$modeloGen = ModeloVehiculo::firstOrCreate(['marca_id' => $marcaGen->id, 'nombre' => 'GENERICO']);

$veh = $cita->vehiculo;
$veh->marca_id = $marcaGen->id;
$veh->modelo_id = $modeloGen->id;
$veh->version_id = null;
$veh->save();

// 3. (Optional) Cleanup the typo brand if it was 'TOYORA'
if (strtoupper($originalMarca) === 'TOYORA') {
    $marcaTypo = MarcaVehiculo::where('nombre', 'TOYORA')->first();
    if ($marcaTypo) {
        // Delete models and versions too if needed, but simple brand delete for now
        ModeloVehiculo::where('marca_id', $marcaTypo->id)->delete();
        $marcaTypo->delete();
        echo "Deleted typo brand: TOYORA\n";
    }
}

echo "Reset complete. Cita is now pending and vehicle is generic.\n";
