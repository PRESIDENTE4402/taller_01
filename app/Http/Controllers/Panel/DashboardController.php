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
    public function index(Request $request)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        // Determinar si es admin y la sucursal activa
        $isAdmin = $user->hasRole('admin');

        // Permitir cambiar sucursal vía query string
        if ($request->has('sucursal_id')) {
            $sid = $request->input('sucursal_id');
            if ($sid === 'all' && $isAdmin) {
                session(['sucursal_id' => 'all']);
            } elseif ($sid && $sid !== 'all') {
                $canAccess = $isAdmin || $user->sucursales->contains($sid);
                if ($canAccess) {
                    session(['sucursal_id' => $sid]);
                }
            }
        }

        $sucursalId = session('sucursal_id') ?? $user->sucursales->first()?->id;

        // Lista de sucursales para el selector
        $sucursales = $isAdmin ? \App\Models\Sucursal::where('activa', true)->get() : $user->sucursales;

        // Base Queries con filtrado de sucursal si aplica
        $baseCitas = Cita::query();
        $baseOrdenes = OrdenTrabajo::query();
        $baseVehiculos = Vehiculo::query();
        $baseUsers = User::query();

        // Aplicar filtros de sucursal
        if ($sucursalId && $sucursalId !== 'all') {
            $baseCitas->where('sucursal_id', $sucursalId);
            $baseOrdenes->where('sucursal_id', $sucursalId);

            $baseVehiculos->whereHas('ordenes', function ($q) use ($sucursalId) {
                $q->where('sucursal_id', $sucursalId);
            });

            $baseUsers->whereHas('sucursales', function ($q) use ($sucursalId) {
                $q->where('sucursales.id', $sucursalId);
            });
        } elseif (!$isAdmin) {
            // Si no es admin y no tiene nada en sesión (raro), forzar sus propias sucursales
            $userSids = $user->sucursales->pluck('id');
            $baseCitas->whereIn('sucursal_id', $userSids);
            $baseOrdenes->whereIn('sucursal_id', $userSids);
            $baseVehiculos->whereHas('ordenes', function ($q) use ($userSids) {
                $q->whereIn('sucursal_id', $userSids);
            });
            $baseUsers->whereHas('sucursales', function ($q) use ($userSids) {
                $q->whereIn('sucursales.id', $userSids);
            });
        }

        // Filtro de Fechas
        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');

        if ($startDate) {
            $baseCitas->whereDate('created_at', '>=', $startDate);
            $baseOrdenes->whereDate('created_at', '>=', $startDate);
            $baseVehiculos->whereDate('created_at', '>=', $startDate);
            // Users are static usually, but we could filter it too or omit it. 
            // Better not filter users by date unless they were created in that range?
            // Yes, let's just filter Citas, Ordenes y Vehiculos (nuevos ingresos).
        }
        if ($endDate) {
            $baseCitas->whereDate('created_at', '<=', $endDate);
            $baseOrdenes->whereDate('created_at', '<=', $endDate);
            $baseVehiculos->whereDate('created_at', '<=', $endDate);
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
        // Órdenes separadas por estado (con su última bitácora para observaciones)
        $ordenesEnProceso = (clone $baseOrdenes)
            ->with([
                'cliente',
                'vehiculo.marca',
                'vehiculo.modelo',
                'sucursal',
                'bitacoras' => function ($q) {
                    $q->latest()->limit(1);
                }
            ])
            ->where('estado', 'en_proceso')
            ->orderBy('updated_at', 'desc')
            ->take(8)
            ->get();

        $ordenesAbiertas = (clone $baseOrdenes)
            ->with([
                'cliente',
                'vehiculo.marca',
                'vehiculo.modelo',
                'sucursal',
                'bitacoras' => function ($q) {
                    $q->latest()->limit(1);
                }
            ])
            ->where('estado', 'abierta')
            ->orderBy('updated_at', 'desc')
            ->take(8)
            ->get();

        $ordenesEsperaRepuesto = (clone $baseOrdenes)
            ->with([
                'cliente',
                'vehiculo.marca',
                'vehiculo.modelo',
                'sucursal',
                'bitacoras' => function ($q) {
                    $q->latest()->limit(1);
                }
            ])
            ->where('estado', 'espera_repuesto')
            ->orderBy('updated_at', 'desc')
            ->take(8)
            ->get();

        $ordenesFinalizadas = (clone $baseOrdenes)
            ->with([
                'cliente',
                'vehiculo.marca',
                'vehiculo.modelo',
                'sucursal',
                'bitacoras' => function ($q) {
                    $q->latest()->limit(1);
                }
            ])
            ->where('estado', 'finalizada')
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

        // Mecánicos de la sucursal y su disponibilidad actual
        $mecanicos = (clone $baseUsers)
            ->whereHas('roles', function ($q) {
                $q->whereIn('slug', ['mecanico', 'tecnico', 'ayudante']);
            })
            ->with([
                'bitacoras' => function ($q) {
                    $q->latest()->limit(1);
                }
            ])
            ->get()
            ->map(function ($mecanico) {
                $ultimaBitacora = $mecanico->bitacoras->first();
                $mecanico->is_available = !$ultimaBitacora || $ultimaBitacora->estado !== 'en_progreso';
                $mecanico->tarea_actual = $ultimaBitacora && $ultimaBitacora->estado === 'en_progreso' ? $ultimaBitacora : null;
                return $mecanico;
            });

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
            'ordenesFinalizadas',
            'citasProximas',
            'mecanicos',
            'isAdmin',
            'sucursalId',
            'sucursales',
            'startDate',
            'endDate'
        ));
    }
}
