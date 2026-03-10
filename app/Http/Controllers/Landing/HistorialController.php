<?php

namespace App\Http\Controllers\Landing;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Models\Vehiculo;
use App\Models\Cliente;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

class HistorialController extends Controller
{
    public function search(Request $request)
    {
        $placa = strtoupper($request->input('placa'));
        $password = $request->input('password');

        if (!$placa || !$password) {
            return response()->json(['success' => false, 'message' => 'Por favor, ingrese la placa y su contraseña de seguridad.'], 400);
        }

        // Buscamos el vehículo y cargamos sus relaciones críticas de historial
        $vehiculoBuscado = Vehiculo::with([
            'cliente',
            'marca',
            'modelo',
            'version',
            'ordenes' => function ($q) {
                $q->with(['detalles.repuesto', 'bitacoras.mecanico', 'pagos', 'sucursal'])
                    ->orderBy('fecha_recepcion', 'desc');
            }
        ])
            ->where('placa', 'LIKE', $placa)
            ->first();

        if (!$vehiculoBuscado) {
            return response()->json(['success' => false, 'message' => 'Vehículo no encontrado con esa placa.'], 404);
        }

        $cliente = $vehiculoBuscado->cliente;

        // Validar contraseña (ahora obligatoria para cualquier visualización)
        if ($cliente->password && Hash::check($password, $cliente->password)) {
            $todosVehiculos = Vehiculo::with([
                'marca',
                'modelo',
                'version',
                'ordenes' => function ($q) {
                    $q->with(['detalles.repuesto', 'bitacoras.mecanico', 'pagos', 'sucursal'])
                        ->orderBy('fecha_recepcion', 'desc');
                }
            ])
                ->where('cliente_id', $cliente->id)
                ->get();

            return response()->json([
                'success' => true,
                'mode' => 'full',
                'cliente' => $cliente->nombre_completo,
                'vehiculos' => $todosVehiculos
            ]);
        } else {
            return response()->json([
                'success' => false,
                'message' => 'Contraseña incorrecta. Verifique sus datos o solicite su contraseña en recepción.'
            ], 401);
        }
    }
}
