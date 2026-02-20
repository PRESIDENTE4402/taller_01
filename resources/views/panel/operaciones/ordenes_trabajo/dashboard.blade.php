@extends('layouts.panel')

@section('title', 'Recepción de Vehículos')
@section('subtitle', 'Gestionar ingresos del día')

@section('content')
<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

    <!-- Column 1: Today's Appointments -->
    <div class="lg:col-span-2 space-y-4">
        <!-- Search & Filter Form -->
        <div class="bg-white p-4 rounded-xl shadow-sm border border-gray-100">
            <form action="{{ route('panel.operaciones.ordenes_trabajo.dashboard') }}" method="GET" class="flex flex-col sm:flex-row gap-4 items-end">
                <div class="w-full sm:w-auto">
                    <label class="block text-xs font-bold text-gray-500 mb-1">Fecha</label>
                    <input type="date" name="fecha" value="{{ request('fecha', now()->format('Y-m-d')) }}" class="input input-sm input-bordered w-full">
                </div>
                <div class="flex-1 w-full">
                    <label class="block text-xs font-bold text-gray-500 mb-1">Buscar (Cliente, Placa, Vehículo)</label>
                    <div class="relative">
                        <i class="fas fa-search absolute left-3 top-1/2 -translate-y-1/2 text-gray-400"></i>
                        <input type="text" name="search" value="{{ request('search') }}" placeholder="Ej: Juan Perez, P-123ABC..." class="input input-sm input-bordered w-full pl-9">
                    </div>
                </div>
                <div class="w-full sm:w-auto">
                    <button type="submit" class="btn btn-sm btn-primary w-full">
                        <i class="fas fa-filter"></i> Filtrar
                    </button>
                </div>
                @if(request('search') || request('fecha') != now()->format('Y-m-d'))
                <div class="w-full sm:w-auto">
                    <a href="{{ route('panel.operaciones.ordenes_trabajo.dashboard') }}" class="btn btn-sm btn-ghost w-full text-gray-500" title="Limpiar Filtros">
                        <i class="fas fa-times"></i>
                    </a>
                </div>
                @endif
            </form>
        </div>

        <div class="flex items-center justify-between">
            <h2 class="font-bold text-gray-800 text-lg">
                @if(request('fecha') == now()->format('Y-m-d'))
                Citas para Hoy
                @else
                Citas del {{ \Carbon\Carbon::parse(request('fecha'))->format('d/m/Y') }}
                @endif
            </h2>
            <span id="results-count" class="badge badge-primary">{{ $citasHoy->count() }} Resultados</span>
        </div>

        <div id="no-citas-msg" class="{{ $citasHoy->isEmpty() ? '' : 'hidden' }} bg-white p-8 rounded-xl shadow-sm border border-gray-100 text-center">
            <div class="w-16 h-16 bg-blue-50 text-blue-500 rounded-full flex items-center justify-center mx-auto mb-4">
                <i class="fas fa-calendar-check text-2xl"></i>
            </div>
            <h3 class="font-bold text-gray-700">No hay citas para hoy</h3>
            <p class="text-sm text-gray-500 mt-1">Todos los vehículos agendados han sido recibidos o no hay programación.</p>
        </div>

        <div id="citas-container" class="grid grid-cols-1 md:grid-cols-2 gap-4 {{ $citasHoy->isEmpty() ? 'hidden' : '' }}">
            @foreach($citasHoy as $cita)
            <div id="cita-card-{{ $cita->id }}" class="appointment-card bg-white p-4 rounded-xl shadow-sm border border-gray-100 hover:border-blue-300 transition-colors group">
                <div class="flex justify-between items-start mb-2">
                    <span class="font-bold text-blue-600 text-lg">{{ \Carbon\Carbon::parse($cita->fecha_programada)->format('H:i') }}</span>
                    <span class="badge badge-ghost badge-sm">{{ $cita->codigo_cita ?? 'CITA-'.$cita->id }}</span>
                </div>

                <div class="flex items-center gap-3 mb-3">
                    <div class="avatar placeholder">
                        <div class="bg-neutral-focus text-neutral-content rounded-full w-10">
                            <span class="text-xs">{{ substr($cita->cliente->nombre_completo ?? 'C', 0, 1) }}</span>
                        </div>
                    </div>
                    <div>
                        <p class="font-bold text-sm text-gray-800">{{ $cita->cliente->nombre_completo }}</p>
                        <p class="text-xs text-gray-500">{{ $cita->vehiculo->marca->nombre }} {{ $cita->vehiculo->modelo->nombre }}</p>
                        <p class="text-xs text-gray-400 font-mono">{{ $cita->vehiculo->placa }}</p>
                    </div>
                </div>

                <div class="text-xs text-gray-600 bg-gray-50 p-2 rounded mb-3 truncate">
                    <i class="fas fa-info-circle mr-1"></i> {{ $cita->motivo_cita }}
                </div>

                <div class="flex gap-2">
                    <a href="{{ route('panel.operaciones.ordenes_trabajo.create', ['cita_id' => $cita->id]) }}" class="btn btn-sm btn-primary flex-1 gap-2">
                        <i class="fas fa-check-circle"></i> Recibir
                    </a>
                    <button type="button"
                        data-id="{{ $cita->id }}"
                        data-cliente="{{ addslashes($cita->cliente->nombre_completo) }}"
                        data-url="{{ route('panel.operaciones.ordenes_trabajo.citas.cancel', $cita->id) }}"
                        class="btn btn-sm btn-outline btn-error px-3 btn-cancelar"
                        title="Marcar como: No asistió">
                        <i class="fas fa-user-times"></i>
                    </button>
                </div>
            </div>
            @endforeach
        </div>
    </div>

    <!-- Column 2: Quick Actions -->
    <div class="space-y-6">
        <!-- Card: Walk-in -->
        <div class="bg-gradient-to-br from-blue-600 to-blue-800 text-white p-6 rounded-2xl shadow-lg relative overflow-hidden">
            <div class="relative z-10">
                <h3 class="font-bold text-xl mb-2">Recepción Sin Cita</h3>
                <p class="text-blue-100 text-sm mb-6">Registre un vehículo que llega de imprevisto (Walk-in).</p>
                <a href="{{ route('panel.operaciones.ordenes_trabajo.create') }}" class="btn bg-white text-blue-700 hover:bg-blue-50 border-none w-full shadow-lg font-bold">
                    <i class="fas fa-plus"></i> Iniciar Recepción
                </a>
            </div>
            <!-- Decor -->
            <i class="fas fa-car absolute -bottom-4 -right-4 text-9xl text-white opacity-10 rotate-12"></i>
        </div>

        <!-- Card: Stats or Info -->
        <div class="bg-white p-5 rounded-xl shadow-sm border border-gray-100">
            <h4 class="font-bold text-gray-700 mb-4 text-sm uppercase tracking-wider">Resumen del Día</h4>
            <div class="space-y-3">
                <div class="flex justify-between items-center text-sm">
                    <span class="text-gray-500">Recibidos</span>
                    <span class="font-bold text-gray-800">{{ $totalRecibidosHoy ?? 0 }}</span>
                </div>
                <div class="flex justify-between items-center text-sm">
                    <span class="text-gray-500">Pendientes</span>
                    <span id="stats-pendientes-count" class="font-bold text-orange-500">{{ $citasHoy->count() }}</span>
                </div>
            </div>
        </div>
    </div>

</div>

@push('scripts')
@vite(['resources/js/operaciones/ordenes/dashboard.js'])
@endpush

@endsection