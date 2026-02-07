@extends('layouts.panel')

@section('title', 'Dashboard Operativo')
@section('subtitle', 'Resumen general de actividades del taller.')

@section('content')

{{-- Grid de Estadísticas Principales --}}
<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
    {{-- Card 1: Citas Pendientes --}}
    <div class="stats shadow bg-white border border-gray-100">
        <div class="stat">
            <div class="stat-figure text-blue-500">
                <i class="fas fa-calendar-alt text-3xl"></i>
            </div>
            <div class="stat-title text-gray-500">Citas Hoy</div>
            <div class="stat-value text-blue-600">--</div>
            <div class="stat-desc text-gray-400">Pendientes de confirmar</div>
        </div>
    </div>

    {{-- Card 2: En Taller --}}
    <div class="stats shadow bg-white border border-gray-100">
        <div class="stat">
            <div class="stat-figure text-indigo-500">
                <i class="fas fa-tools text-3xl"></i>
            </div>
            <div class="stat-title text-gray-500">En Reparación</div>
            <div class="stat-value text-indigo-600">--</div>
            <div class="stat-desc text-gray-400">Vehículos en bahía</div>
        </div>
    </div>

    {{-- Card 3: Por Entregar --}}
    <div class="stats shadow bg-white border border-gray-100">
        <div class="stat">
            <div class="stat-figure text-emerald-500">
                <i class="fas fa-check-circle text-3xl"></i>
            </div>
            <div class="stat-title text-gray-500">Listos</div>
            <div class="stat-value text-emerald-600">--</div>
            <div class="stat-desc text-gray-400">Esperando al cliente</div>
        </div>
    </div>

    {{-- Card 4: Ingresos --}}
    <div class="stats shadow bg-white border border-gray-100">
        <div class="stat">
            <div class="stat-figure text-amber-500">
                <i class="fas fa-dollar-sign text-3xl"></i>
            </div>
            <div class="stat-title text-gray-500">Facturación</div>
            <div class="stat-value text-amber-500">$0.00</div>
            <div class="stat-desc text-gray-400">Del día de hoy</div>
        </div>
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-8 h-full">
    {{-- Columna Izquierda: Acciones Rápidas --}}
    <div class="col-span-1 lg:col-span-2 space-y-8">
        
        {{-- Accesos Directos --}}
        <div class="card bg-white shadow-lg border border-gray-100">
            <div class="card-body">
                <h3 class="card-title text-sm font-bold uppercase text-gray-400 mb-4 tracking-wider">Acciones Frecuentes</h3>
                <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
                    <a href="{{ route('panel.operaciones.citas.index', ['action' => 'create']) }}" class="btn btn-outline btn-primary h-auto py-4 flex flex-col gap-2 hover:scale-105 transition-transform border-gray-200 hover:border-blue-500 hover:bg-blue-50 text-gray-600 hover:text-blue-600">
                        <i class="fas fa-plus-circle text-2xl text-blue-500"></i>
                        <span>Nueva Cita</span>
                    </a>
                    <button class="btn btn-outline btn-secondary h-auto py-4 flex flex-col gap-2 hover:scale-105 transition-transform border-gray-200 hover:border-indigo-500 hover:bg-indigo-50 text-gray-600 hover:text-indigo-600">
                        <i class="fas fa-car-side text-2xl text-indigo-500"></i>
                        <span>Ingreso Taller</span>
                    </button>
                    <button class="btn btn-outline h-auto py-4 flex flex-col gap-2 hover:scale-105 transition-transform border-gray-200 hover:border-gray-500 hover:bg-gray-50 text-gray-600 hover:text-gray-800">
                        <i class="fas fa-search text-2xl text-gray-500"></i>
                        <span>Buscar Orden</span>
                    </button>
                    <button class="btn btn-outline btn-success h-auto py-4 flex flex-col gap-2 hover:scale-105 transition-transform border-gray-200 hover:border-emerald-500 hover:bg-emerald-50 text-gray-600 hover:text-emerald-600">
                        <i class="fas fa-cash-register text-2xl text-emerald-500"></i>
                        <span>Caja / Cobro</span>
                    </button>
                </div>
            </div>
        </div>

        {{-- Tabla de Recepciones Recientes --}}
        <div class="card bg-white shadow-lg border border-gray-100">
            <div class="card-body p-0">
                <div class="p-5 border-b border-gray-100 flex justify-between items-center bg-gray-50 rounded-t-2xl">
                    <h3 class="font-bold text-gray-700">Últimos Vehículos Recibidos</h3>
                    <button class="btn btn-xs btn-ghost text-blue-600 hover:bg-blue-50">Ver Todos</button>
                </div>
                <div class="overflow-x-auto">
                    <table class="table w-full">
                        <thead class="bg-gray-50 text-gray-500 font-semibold border-b border-gray-200">
                            <tr>
                                <th>Orden</th>
                                <th>Cliente</th>
                                <th>Vehículo</th>
                                <th>Estado</th>
                                <th>Ingreso</th>
                            </tr>
                        </thead>
                        <tbody class="text-gray-600">
                            {{-- Placeholder row --}}
                            <tr class="hover:bg-gray-50 transition-colors border-b border-gray-100">
                                <td class="font-mono text-xs font-bold text-gray-500">OT-2024-001</td>
                                <td>
                                    <div class="font-bold text-gray-800">Juan Pérez</div>
                                    <div class="text-xs text-gray-400">5555-1234</div>
                                </td>
                                <td>
                                    <span class="badge bg-gray-100 border-none text-gray-600 font-medium">P001ABC</span>
                                    <div class="text-xs mt-1 text-gray-500">Toyota Corolla 2018</div>
                                </td>
                                <td><span class="badge badge-warning gap-2 text-white">En Diagnóstico</span></td>
                                <td class="text-xs text-gray-400">Hace 2 horas</td>
                            </tr>
                            <tr>
                                <td colspan="5" class="text-center text-gray-400 py-8 italic bg-gray-50/30">
                                    No hay más datos recientes para mostrar
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

    </div>

    {{-- Columna Derecha: Avisos y Novedades --}}
    <div class="col-span-1 space-y-6">
        <div class="card bg-white shadow-lg border border-gray-100">
            <div class="card-body">
                <h3 class="card-title text-gray-700 mb-4 text-lg">Avisos del Sistema</h3>
                
                <div class="alert alert-warning shadow-sm text-sm mb-3 bg-amber-50 text-amber-900 border-amber-200">
                    <i class="fas fa-exclamation-triangle"></i>
                    <span>Stock bajo: Aceite 10W-30 (Quedan 2 lts)</span>
                </div>

                <div class="alert alert-info shadow-sm text-sm bg-blue-50 border-blue-200 text-blue-900">
                    <i class="fas fa-info-circle text-blue-500"></i>
                    <span>Reunión de personal mañana a las 8:00 AM.</span>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection
