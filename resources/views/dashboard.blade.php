@extends('layouts.panel')

@section('title', 'Dashboard Operativo')
@section('subtitle', 'Resumen general de actividades del taller.')

@section('content')

    {{-- Selector de Sucursal --}}
    <div
        class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-6 bg-white p-4 rounded-2xl shadow-sm border border-gray-100">
        <div>
            <h2 class="text-sm font-bold text-gray-400 uppercase tracking-widest flex items-center gap-2">
                <i class="fas fa-store text-blue-500"></i> Vista de Sucursal
            </h2>
            <p class="text-xs text-gray-500">Filtrando datos operativos según sucursal seleccionada.</p>
        </div>
        <div class="flex items-center gap-2">
            <form action="{{ route('panel.dashboard') }}" method="GET" id="form-sucursal" class="flex items-center gap-2">
                <select name="sucursal_id" class="select select-bordered select-sm w-full md:w-64 font-medium"
                    onchange="this.form.submit()">
                    @if($isAdmin)
                        <option value="all" {{ $sucursalId === 'all' ? 'selected' : '' }}>🌎 Todas las Sucursales</option>
                    @endif
                    @foreach($sucursales as $suc)
                        <option value="{{ $suc->id }}" {{ $sucursalId == $suc->id ? 'selected' : '' }}>
                            📍 {{ $suc->nombre }}
                        </option>
                    @endforeach
                </select>
            </form>
        </div>
    </div>

    {{-- Grid de Estadísticas Principales --}}
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
        {{-- Card 1: Citas Pendientes --}}
        <div class="stats shadow bg-white border border-gray-100">
            <div class="stat">
                <div class="stat-figure text-blue-500">
                    <i class="fas fa-calendar-alt text-3xl"></i>
                </div>
                <div class="stat-title text-gray-500">Citas Hoy/Mañana</div>
                <div class="stat-value text-blue-600">{{ $citasHoy }}</div>
                <div class="stat-desc text-gray-400">Pendientes/Confirmadas</div>
            </div>
        </div>

        {{-- Card 2: En Taller --}}
        <div class="stats shadow bg-white border border-gray-100">
            <div class="stat">
                <div class="stat-figure text-indigo-500">
                    <i class="fas fa-tools text-3xl"></i>
                </div>
                <div class="stat-title text-gray-500">En Reparación</div>
                <div class="stat-value text-indigo-600">{{ $enTaller }}</div>
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
                <div class="stat-value text-emerald-600">{{ $listosEntregar }}</div>
                <div class="stat-desc text-gray-400">Esperando al cliente</div>
            </div>
        </div>

        {{-- Card 4: Control Global --}}
        <div class="stats shadow bg-white border border-gray-100">
            <div class="stat flex items-center gap-4">
                <div class="flex-1">
                    <div class="stat-title text-[10px] font-bold uppercase tracking-widest text-gray-400">Vehículos</div>
                    <div class="text-xl font-black text-gray-700">{{ $totalVehiculos }}</div>
                </div>
                <div class="w-[1px] h-10 bg-gray-200"></div>
                <div class="flex-1">
                    <div class="stat-title text-[10px] font-bold uppercase tracking-widest text-gray-400">Personal</div>
                    <div class="text-xl font-black text-gray-700">{{ $totalTrabajadores }}</div>
                </div>
                <div class="w-[1px] h-10 bg-gray-200"></div>
                <div class="flex-1">
                    <div class="stat-title text-[10px] font-bold uppercase tracking-widest text-gray-400">Órdenes</div>
                    <div class="text-xl font-black text-gray-700">{{ $totalOrdenes }}</div>
                </div>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8 h-full">
        {{-- Columna Izquierda: Acciones Rápidas --}}
        <div class="col-span-1 lg:col-span-2 space-y-8">

            {{-- Accesos Directos --}}
            <div class="card bg-white shadow-lg border border-gray-100">
                <div class="card-body">
                    <h3 class="card-title text-sm font-bold uppercase text-gray-400 mb-4 tracking-wider">Acciones Frecuentes
                    </h3>
                    <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
                        <a href="{{ route('panel.operaciones.citas.index', ['action' => 'create']) }}"
                            class="btn btn-outline btn-primary h-auto py-4 flex flex-col gap-2 hover:scale-105 transition-transform border-gray-200 hover:border-blue-500 hover:bg-blue-50 text-gray-600 hover:text-blue-600">
                            <i class="fas fa-plus-circle text-2xl text-blue-500"></i>
                            <span>Nueva Cita</span>
                        </a>
                        <button
                            class="btn btn-outline btn-secondary h-auto py-4 flex flex-col gap-2 hover:scale-105 transition-transform border-gray-200 hover:border-indigo-500 hover:bg-indigo-50 text-gray-600 hover:text-indigo-600">
                            <i class="fas fa-car-side text-2xl text-indigo-500"></i>
                            <span>Ingreso Taller</span>
                        </button>
                        <a href="{{ route('panel.operaciones.ordenes_trabajo.index') }}"
                            class="btn btn-outline h-auto py-4 flex flex-col gap-2 hover:scale-105 transition-transform border-gray-200 hover:border-gray-500 hover:bg-gray-50 text-gray-600 hover:text-gray-800">
                            <i class="fas fa-search text-2xl text-gray-500"></i>
                            <span>Buscar Orden</span>
                        </a>
                        <a href="{{ route('panel.vehiculos.index') }}"
                            class="btn btn-outline btn-success h-auto py-4 flex flex-col gap-2 hover:scale-105 transition-transform border-gray-200 hover:border-emerald-500 hover:bg-emerald-50 text-gray-600 hover:text-emerald-600">
                            <i class="fas fa-car text-2xl text-emerald-500"></i>
                            <span>Directorio Vehículos</span>
                        </a>
                    </div>
                </div>
            </div>

            {{-- 1. Vehículos en Taller (Trabajando / En proceso) --}}
            <div class="card bg-white shadow-lg border border-gray-100 mb-6">
                <div class="card-body p-0">
                    <div class="p-5 border-b border-gray-100 flex justify-between items-center bg-blue-50/50 rounded-t-2xl">
                        <h3 class="font-bold text-blue-900 flex items-center gap-2"><i class="fas fa-tools"></i> En
                            Reparación / Taller</h3>
                    </div>
                    <div class="p-4 grid grid-cols-1 gap-4">
                        @forelse($ordenesEnProceso as $orden)
                            <div
                                class="flex flex-col sm:flex-row gap-4 p-4 border border-gray-100 hover:border-blue-300 hover:shadow-md transition-all rounded-xl bg-white">
                                <div class="flex-1">
                                    <div class="flex items-center gap-3 mb-2">
                                        <span
                                            class="badge badge-warning text-white text-[10px] font-black tracking-widest uppercase">En
                                            Proceso</span>
                                        <a href="{{ route('panel.operaciones.ordenes_trabajo.show', $orden->id) }}"
                                            class="font-bold text-blue-700 hover:underline text-lg">{{ $orden->vehiculo->placa }}</a>
                                        <span class="text-xs text-gray-400 font-medium">{{ $orden->vehiculo->marca->nombre }}
                                            {{ $orden->vehiculo->modelo->nombre }}</span>
                                    </div>
                                    <p class="text-sm text-gray-500 mb-2">
                                        <i class="fas fa-user-circle mr-1"></i>
                                        {{ $orden->cliente->nombre_completo ?? $orden->cliente->empresa }}
                                        <span class="text-gray-300 mx-2">|</span>
                                        <i class="fas fa-store text-[10px] mr-1"></i>
                                        {{ $orden->sucursal->nombre ?? 'Principal' }}
                                        <span class="text-gray-300 mx-2">|</span>
                                        Hace {{ $orden->updated_at->diffForHumans() }}
                                    </p>

                                    {{-- Última Observación --}}
                                    <div class="bg-gray-50 border-l-4 border-blue-400 p-3 rounded-r-lg mt-3">
                                        <p class="text-xs text-gray-500 uppercase font-bold tracking-widest mb-1">Última
                                            Actualización:</p>
                                        <p class="text-sm text-gray-700 italic">
                                            @if($orden->bitacoras->isNotEmpty())
                                                "{{ $orden->bitacoras->first()->descripcion }}"
                                                <span class="text-[10px] text-gray-400 block mt-1">-
                                                    {{ $orden->bitacoras->first()->user->name ?? 'Taller' }}
                                                    ({{ $orden->bitacoras->first()->tipo_actividad }})</span>
                                            @else
                                                No hay bitácoras documentadas aún.
                                            @endif
                                        </p>
                                    </div>
                                </div>
                                <div class="flex items-center sm:border-l sm:border-gray-100 sm:pl-4">
                                    <a href="{{ route('panel.operaciones.ordenes_trabajo.show', $orden->id) }}"
                                        class="btn btn-outline btn-primary btn-sm rounded-lg hover:scale-105 transition-transform"><i
                                            class="fas fa-arrow-right"></i> Ver Informe</a>
                                </div>
                            </div>
                        @empty
                            <div
                                class="text-center text-gray-400 py-6 italic bg-gray-50/50 rounded-xl border border-dashed border-gray-200">
                                No hay vehículos trabajándose activamente en este momento.
                            </div>
                        @endforelse
                    </div>
                </div>
            </div>

            {{-- 2. Vehículos Abiertos (Pendientes de Confirmación/Presupuesto) --}}
            <div class="card bg-white shadow-lg border border-gray-100 mb-6">
                <div class="card-body p-0">
                    <div class="p-5 border-b border-gray-100 flex justify-between items-center bg-gray-50 rounded-t-2xl">
                        <h3 class="font-bold text-gray-700 flex items-center gap-2"><i
                                class="fas fa-clipboard-list text-gray-500"></i> Pendientes de Revisión o Aprobación</h3>
                    </div>
                    <div class="p-4 grid grid-cols-1 gap-4">
                        @forelse($ordenesAbiertas as $orden)
                            <div
                                class="flex flex-col sm:flex-row gap-4 p-4 border border-gray-200 hover:border-gray-400 hover:shadow-md transition-all rounded-xl bg-gray-50/30">
                                <div class="flex-1">
                                    <div class="flex items-center gap-3 mb-2">
                                        <span
                                            class="badge badge-info text-white text-[10px] font-black tracking-widest uppercase">Abierta</span>
                                        <a href="{{ route('panel.operaciones.ordenes_trabajo.show', $orden->id) }}"
                                            class="font-bold text-gray-700 hover:text-blue-600 hover:underline text-lg">{{ $orden->vehiculo->placa }}</a>
                                        <span class="text-xs text-gray-400 font-medium">{{ $orden->vehiculo->marca->nombre }}
                                            {{ $orden->vehiculo->modelo->nombre }}</span>
                                    </div>
                                    <p class="text-sm text-gray-500 mb-2">
                                        <i class="fas fa-user-circle mr-1"></i>
                                        {{ $orden->cliente->nombre_completo ?? $orden->cliente->empresa }}
                                        <span class="text-gray-300 mx-2">|</span>
                                        <i class="fas fa-store text-[10px] mr-1"></i>
                                        {{ $orden->sucursal->nombre ?? 'Principal' }}
                                        <span class="text-gray-300 mx-2">|</span>
                                        Hace {{ $orden->updated_at->diffForHumans() }}
                                    </p>

                                    <div
                                        class="bg-white border-l-4 border-gray-300 p-3 rounded-r-lg mt-3 shadow-sm shadow-gray-100">
                                        <p class="text-xs text-gray-400 uppercase font-bold tracking-widest mb-1">Última
                                            Observación:</p>
                                        <p class="text-sm text-gray-600 italic">
                                            @if($orden->bitacoras->isNotEmpty())
                                                "{{ $orden->bitacoras->first()->descripcion }}"
                                            @else
                                                Ingresado recientemente. Pendiente de asignar mecánico o diagnosticar.
                                            @endif
                                        </p>
                                    </div>
                                </div>
                                <div class="flex items-center sm:border-l sm:border-gray-200 sm:pl-4">
                                    <a href="{{ route('panel.operaciones.ordenes_trabajo.show', $orden->id) }}"
                                        class="btn btn-outline btn-sm rounded-lg hover:scale-105 transition-transform">Ver
                                        Detalles</a>
                                </div>
                            </div>
                        @empty
                            <div class="text-center text-gray-400 py-6 italic border border-dashed border-gray-200 rounded-xl">
                                Todas las órdenes han sido procesadas de primera línea.
                            </div>
                        @endforelse
                    </div>
                </div>
            </div>

            {{-- 3. Vehículos en Espera de Repuestos --}}
            <div class="card bg-white shadow-lg border border-gray-100">
                <div class="card-body p-0">
                    <div class="p-5 border-b border-gray-100 flex justify-between items-center bg-red-50/50 rounded-t-2xl">
                        <h3 class="font-bold text-red-800 flex items-center gap-2"><i class="fas fa-box-open"></i> En Espera
                            de Repuestos</h3>
                    </div>
                    <div class="p-4 grid grid-cols-1 gap-4">
                        @forelse($ordenesEsperaRepuesto as $orden)
                            <div
                                class="flex flex-col sm:flex-row gap-4 p-4 border border-red-100 hover:border-red-300 hover:shadow-md transition-all rounded-xl bg-white">
                                <div class="flex-1">
                                    <div class="flex items-center gap-3 mb-2">
                                        <span
                                            class="badge badge-error text-white text-[10px] font-black tracking-widest uppercase">Pausado</span>
                                        <a href="{{ route('panel.operaciones.ordenes_trabajo.show', $orden->id) }}"
                                            class="font-bold text-red-700 hover:underline text-lg">{{ $orden->vehiculo->placa }}</a>
                                        <span class="text-xs text-gray-400 font-medium">{{ $orden->vehiculo->marca->nombre }}
                                            {{ $orden->vehiculo->modelo->nombre }}</span>
                                    </div>
                                    <p class="text-sm text-gray-500 mb-2">
                                        <i class="fas fa-user-circle mr-1"></i>
                                        {{ $orden->cliente->nombre_completo ?? $orden->cliente->empresa }}
                                        <span class="text-gray-300 mx-2">|</span>
                                        <i class="fas fa-store text-[10px] mr-1"></i>
                                        {{ $orden->sucursal->nombre ?? 'Principal' }}
                                        <span class="text-gray-300 mx-2">|</span>
                                        Hace {{ $orden->updated_at->diffForHumans() }}
                                    </p>

                                    <div class="bg-red-50 border-l-4 border-red-400 p-3 rounded-r-lg mt-3">
                                        <p class="text-xs text-red-500 uppercase font-bold tracking-widest mb-1">Motivo de la
                                            Pausa:</p>
                                        <p class="text-sm text-gray-800 font-medium italic">
                                            @if($orden->bitacoras->isNotEmpty())
                                                "{{ $orden->bitacoras->first()->descripcion }}"
                                            @else
                                                A la espera de confirmación de llegada de refacciones.
                                            @endif
                                        </p>
                                    </div>
                                </div>
                                <div class="flex items-center sm:border-l sm:border-red-100 sm:pl-4">
                                    <a href="{{ route('panel.operaciones.ordenes_trabajo.show', $orden->id) }}"
                                        class="btn btn-outline btn-error btn-sm rounded-lg hover:scale-105 transition-transform">Actualizar
                                        Estado</a>
                                </div>
                            </div>
                        @empty
                            <div class="text-center text-gray-400 py-6 italic border border-dashed border-gray-200 rounded-xl">
                                Ningún vehículo está frenado por falta de inventario en este momento.
                            </div>
                        @endforelse
                    </div>
                </div>
            </div>

            {{-- 4. Vehículos Finalizados (Listos para Entrega) --}}
            <div class="card bg-white shadow-lg border border-gray-100 mb-6">
                <div class="card-body p-0">
                    <div
                        class="p-5 border-b border-gray-100 flex justify-between items-center bg-emerald-50/50 rounded-t-2xl">
                        <h3 class="font-bold text-emerald-800 flex items-center gap-2"><i class="fas fa-check-circle"></i>
                            Listos para Entregar al Cliente</h3>
                    </div>
                    <div class="p-4 grid grid-cols-1 gap-4">
                        @forelse($ordenesFinalizadas as $orden)
                            <div
                                class="flex flex-col sm:flex-row gap-4 p-4 border border-emerald-100 hover:border-emerald-400 hover:shadow-md transition-all rounded-xl bg-white">
                                <div class="flex-1">
                                    <div class="flex items-center gap-3 mb-2">
                                        <span
                                            class="badge badge-success text-white text-[10px] font-black tracking-widest uppercase">¡Terminado!</span>
                                        <a href="{{ route('panel.operaciones.ordenes_trabajo.show', $orden->id) }}"
                                            class="font-bold text-emerald-700 hover:underline text-lg">{{ $orden->vehiculo->placa }}</a>
                                        <span class="text-xs text-gray-400 font-medium">{{ $orden->vehiculo->marca->nombre }}
                                            {{ $orden->vehiculo->modelo->nombre }}</span>
                                    </div>
                                    <p class="text-sm text-gray-500 mb-2">
                                        <i class="fas fa-user-circle mr-1"></i>
                                        {{ $orden->cliente->nombre_completo ?? $orden->cliente->empresa }}
                                        <span class="text-gray-300 mx-2">|</span>
                                        <i class="fas fa-phone text-[10px] mr-1"></i> {{ $orden->cliente->telefono ?? 'S/N' }}
                                        <span class="text-gray-300 mx-2">|</span>
                                        <i class="fas fa-store text-[10px] mr-1"></i>
                                        {{ $orden->sucursal->nombre ?? 'Principal' }}
                                        <span class="text-gray-300 mx-2">|</span>
                                        Hace {{ $orden->updated_at->diffForHumans() }}
                                    </p>

                                    <div class="bg-emerald-50 border-l-4 border-emerald-400 p-3 rounded-r-lg mt-3">
                                        <p class="text-xs text-emerald-600 uppercase font-bold tracking-widest mb-1">
                                            Verificación final:</p>
                                        <p class="text-sm text-gray-700 italic">
                                            Vehículo terminado y aparcado. Listo para lavarse y generar factura si aplica.
                                        </p>
                                    </div>
                                </div>
                                <div class="flex items-center sm:border-l sm:border-emerald-100 sm:pl-4">
                                    <a href="{{ route('panel.operaciones.ordenes_trabajo.show', $orden->id) }}"
                                        class="btn bg-emerald-500 hover:bg-emerald-600 border-none text-white btn-sm rounded-lg hover:scale-105 transition-transform shadow-lg shadow-emerald-200">
                                        <i class="fas fa-flag-checkered mr-1"></i> Entregar Auto
                                    </a>
                                </div>
                            </div>
                        @empty
                            <div class="text-center text-gray-400 py-6 italic border border-dashed border-gray-200 rounded-xl">
                                No hay vehículos listos esperando a ser recogidos.
                            </div>
                        @endforelse
                    </div>
                </div>
            </div>

        </div>

        {{-- Columna Derecha: Avisos y Novedades --}}
        <div class="col-span-1 space-y-6">

            {{-- Mecánicos Disponibles --}}
            <div class="card bg-white shadow-lg border border-gray-100">
                <div class="card-body p-5">
                    <h3 class="card-title text-gray-700 mb-2 text-sm uppercase tracking-widest"><i
                            class="fas fa-users-cog text-indigo-500"></i> ESTADO DEL PERSONAL</h3>
                    <p class="text-[10px] text-gray-400 font-bold uppercase tracking-widest mb-4">Mecánicos asignados a esta
                        sucursal</p>

                    <div class="space-y-3">
                        @forelse($mecanicos as $mecanico)
                            <div
                                class="flex items-center justify-between p-3 {{ $mecanico->is_available ? 'bg-emerald-50 border-emerald-100' : 'bg-orange-50 border-orange-100' }} border rounded-xl">
                                <div class="flex items-center gap-3 w-full sm:w-auto overflow-hidden">
                                    <div class="avatar placeholder flex-shrink-0">
                                        <div
                                            class="{{ $mecanico->is_available ? 'bg-emerald-500' : 'bg-orange-500' }} text-white rounded-full w-8 h-8 shadow-sm">
                                            <span class="text-xs font-bold">{{ substr($mecanico->name, 0, 1) }}</span>
                                        </div>
                                    </div>
                                    <div class="flex flex-col min-w-0">
                                        <span class="font-bold text-gray-700 text-xs truncate">{{ $mecanico->name }}</span>
                                        @if($mecanico->is_available)
                                            <span class="text-[9px] font-black text-emerald-600 uppercase tracking-wider">Libre /
                                                Disponible</span>
                                        @else
                                            <span class="text-[9px] font-black text-orange-600 uppercase tracking-wider truncate"
                                                title="{{ $mecanico->tarea_actual->descripcion ?? 'Ocupado en una orden' }}">
                                                Trabajando
                                            </span>
                                        @endif
                                    </div>
                                </div>
                                @if(!$mecanico->is_available && $mecanico->tarea_actual)
                                    <a href="{{ route('panel.operaciones.ordenes_trabajo.show', $mecanico->tarea_actual->orden_trabajo_id) }}"
                                        class="btn btn-xs btn-ghost btn-circle text-orange-500 hover:bg-orange-100 tooltip tooltip-left flex-shrink-0"
                                        data-tip="Ir a la orden">
                                        <i class="fas fa-external-link-alt"></i>
                                    </a>
                                @endif
                            </div>
                        @empty
                            <div class="text-center p-6 bg-gray-50 rounded-xl border border-dashed border-gray-200">
                                <i class="fas fa-user-slash text-2xl text-gray-300 mb-2"></i>
                                <p class="text-xs text-gray-400 font-bold uppercase tracking-widest">No hay técnicos asignados
                                </p>
                            </div>
                        @endforelse
                    </div>
                </div>
            </div>

            {{-- Próximas Citas --}}
            <div class="card bg-white shadow-lg border border-gray-100">
                <div class="card-body p-5">
                    <h3 class="card-title text-gray-700 mb-2 text-sm uppercase tracking-widest"><i
                            class="fas fa-calendar-check text-blue-500"></i> PRÓXIMAS CITAS</h3>
                    <p class="text-[10px] text-gray-400 font-bold uppercase tracking-widest mb-4">Agendadas para hoy y
                        mañana</p>

                    <div class="space-y-3">
                        @forelse($citasProximas as $citaRow)
                            <div class="flex flex-col gap-1 p-3 bg-gray-50 border border-gray-100 rounded-xl">
                                <div class="flex justify-between items-center">
                                    <span class="font-black text-gray-700 text-xs">{{ $citaRow->vehiculo->placa ?? 'N/A' }}
                                        <span class="text-gray-400 font-medium">|
                                            {{ $citaRow->vehiculo->marca->nombre ?? '' }}</span></span>
                                    <span
                                        class="text-[9px] font-bold px-2 py-0.5 rounded-full {{ $citaRow->fecha_programada->isToday() ? 'bg-orange-100 text-orange-600' : 'bg-blue-100 text-blue-600' }}">
                                        {{ $citaRow->fecha_programada->isToday() ? 'HOY' : 'MAÑANA' }}
                                        {{ $citaRow->fecha_programada->format('H:i') }}
                                    </span>
                                </div>
                                <div class="flex justify-between items-end mt-1">
                                    <span class="text-xs text-gray-500"><i
                                            class="fas fa-user text-[10px] mr-1"></i>{{ $citaRow->cliente->nombre_completo ?? '' }}</span>
                                    <span class="text-[9px] font-black text-gray-400 uppercase">{{ $citaRow->tipo_cita }}</span>
                                </div>
                            </div>
                        @empty
                            <div class="text-center p-6 bg-gray-50 rounded-xl border border-dashed border-gray-200">
                                <i class="fas fa-calendar-times text-2xl text-gray-300 mb-2"></i>
                                <p class="text-xs text-gray-400 font-bold uppercase tracking-widest">No hay citas a corto plazo
                                </p>
                            </div>
                        @endforelse
                    </div>
                </div>
            </div>

            <div class="card bg-white shadow-lg border border-gray-100">
                <div class="card-body">
                    <h3 class="card-title text-gray-700 mb-4 text-sm font-bold uppercase tracking-widest"><i
                            class="fas fa-bell text-yellow-500"></i> Avisos del Sistema</h3>

                    <div class="alert alert-warning shadow-sm text-sm mb-3 bg-amber-50 text-amber-900 border-amber-200 py-2">
                        <i class="fas fa-store"></i>
                        <span><strong>Sucursal Actual:</strong>
                            @if($sucursalId === 'all')
                                Todas las Sucursales
                            @else
                                {{ $sucursales->where('id', $sucursalId)->first()?->nombre ?? (session('sucursal_nombre') ?? 'Desconocida') }}
                            @endif
                        </span>
                    </div>

                    <div class="alert alert-info shadow-sm text-sm bg-blue-50 border-blue-200 text-blue-900 py-2">
                        <i class="fas fa-info-circle text-blue-500"></i>
                        <span>Tus reportes operativos del día se actualizarán automáticamente.</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

@endsection