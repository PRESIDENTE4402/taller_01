<?php

use Illuminate\Http\Request;
use App\Http\Controllers\Panel\CitaController;

echo "--- START TEST ---\n";
$controller = new CitaController();

echo "Testing Search Vehicles with term 'P'\n";
$request = new Request();
$request->merge(['term' => 'P']);

try {
    $response = $controller->searchVehicles($request);
    $data = $response->getData();
    echo "Found: " . count($data) . " vehicles\n";
    if (count($data) > 0) {
        print_r($data[0]);
    } else {
        echo "No vehicles found for 'P'.\n";
    }
} catch (\Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
echo "--- END TEST ---\n";
