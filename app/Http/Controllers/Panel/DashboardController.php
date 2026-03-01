<?php

namespace App\Http\Controllers\Panel;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Cita;
use App\Models\OrdenTrabajo;
use App\Models\Vehiculo;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function index()
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        // Determinar si es admin y la sucursal activa
        $isAdmin = $user->hasRole('admin');
        $sucursalId = session('sucursal_id') ?? $user->sucursales->first()?->id;

        // Base Queries con filtrado de sucursal si aplica
        $baseCitas = Cita::query();
        $baseOrdenes = OrdenTrabajo::query();
        $baseVehiculos = Vehiculo::query();
        $baseUsers = User::query();

        if (!$isAdmin && $sucursalId) {
            $baseCitas->where('sucursal_id', $sucursalId);
            $baseOrdenes->where('sucursal_id', $sucursalId);

            // Vehículos que tengan órdenes en esta sucursal o pertenezcan a clientes de esta sucursal (simplificado: basados en historial de órdenes)
            $baseVehiculos->whereHas('ordenes', function ($q) use ($sucursalId) {
                $q->where('sucursal_id', $sucursalId);
            });

            // Trabajadores de la sucursal
            $baseUsers->whereHas('sucursales', function ($q) use ($sucursalId) {
                $q->where('sucursales.id', $sucursalId);
            });
        }

        // 1. Estadísticas Generales (Cards)

        // Citas de hoy (pendientes o confirmadas)
        $citasHoy = (clone $baseCitas)
            ->whereDate('fecha_programada', Carbon::today())
            ->whereIn('estado', ['pendiente', 'confirmada'])
            ->count();

        // En taller (En proceso o en espera de repuesto)
        $enTaller = (clone $baseOrdenes)
            ->whereIn('estado', ['en_proceso', 'espera_repuesto', 'abierta'])
            ->count();

        // Listos por entregar (Finalizada)
        $listosEntregar = (clone $baseOrdenes)
            ->where('estado', 'finalizada')
            ->count();

        // Totales de control (Vehículos, Trabajadores)
        $totalVehiculos = (clone $baseVehiculos)->count();
        $totalTrabajadores = (clone $baseUsers)->count(); // Aquí se asume que los listados ya filtran por sucursal, podemos afinarlo luego

        // Total Órdenes (histórico)
        $totalOrdenes = (clone $baseOrdenes)->count();

        // 2. Tablas y Listados

        // Órdenes separadas por estado (con su última bitácora para observaciones)
        $ordenesEnProceso = (clone $baseOrdenes)
            ->with(['cliente', 'vehiculo.marca', 'vehiculo.modelo', 'bitacoras' => function ($q) {
                $q->latest()->limit(1);
            }])
            ->where('estado', 'en_proceso')
            ->orderBy('updated_at', 'desc')
            ->take(8)
            ->get();

        $ordenesAbiertas = (clone $baseOrdenes)
            ->with(['cliente', 'vehiculo.marca', 'vehiculo.modelo', 'bitacoras' => function ($q) {
                $q->latest()->limit(1);
            }])
            ->where('estado', 'abierta')
            ->orderBy('updated_at', 'desc')
            ->take(8)
            ->get();

        $ordenesEsperaRepuesto = (clone $baseOrdenes)
            ->with(['cliente', 'vehiculo.marca', 'vehiculo.modelo', 'bitacoras' => function ($q) {
                $q->latest()->limit(1);
            }])
            ->where('estado', 'espera_repuesto')
            ->orderBy('updated_at', 'desc')
            ->take(8)
            ->get();

        // Citas próximas de hoy o mañana
        $citasProximas = (clone $baseCitas)
            ->with(['cliente', 'vehiculo.marca'])
            ->whereDate('fecha_programada', '>=', Carbon::today())
            ->whereIn('estado', ['pendiente', 'confirmada'])
            ->orderBy('fecha_programada', 'asc')
            ->take(5)
            ->get();

        return view('dashboard', compact(
            'citasHoy',
            'enTaller',
            'listosEntregar',
            'totalVehiculos',
            'totalTrabajadores',
            'totalOrdenes',
            'ordenesEnProceso',
            'ordenesAbiertas',
            'ordenesEsperaRepuesto',
            'citasProximas',
            'isAdmin',
            'sucursalId'
        ));
    }
}
