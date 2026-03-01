@extends('layouts.panel')

@section('title', 'Perfil de Cliente')
@section('subtitle', 'Gestión detallada de información y vehículos')

@section('content')
<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    {{-- Columna Izquierda: Información del Cliente --}}
    <div class="lg:col-span-1 space-y-6">
        {{-- Tarjeta de Perfil --}}
        <div class="card bg-white shadow-sm border border-slate-200">
            <div class="card-body items-center text-center">
                <div class="avatar placeholder mb-2">
                    <div class="bg-blue-600 text-white rounded-full w-24 text-3xl shadow-lg ring ring-blue-50 ring-offset-2">
                        <span>{{ substr($cliente->nombre_completo, 0, 1) }}</span>
                    </div>
                </div>
                <h2 id="clientName" class="card-title text-slate-800">{{ $cliente->nombre_completo }}</h2>
                <div id="clientBadge" class="badge badge-lg {{ $cliente->es_empresa ? 'badge-primary' : 'badge-ghost' }} gap-2">
                    <i id="clientBadgeIcon" class="fas {{ $cliente->es_empresa ? 'fa-building' : 'fa-user' }}"></i>
                    <span id="clientBadgeText">{{ $cliente->es_empresa ? 'Corporativo' : 'Particular' }}</span>
                </div>

                <div class="divider my-2"></div>

                <div class="w-full text-left space-y-3">
                    <div id="infoEmpresaBlock" class="flex items-start gap-3 {{ $cliente->es_empresa ? '' : 'hidden' }}">
                        <i class="fas fa-building mt-1 text-slate-400 w-5"></i>
                        <div>
                            <p class="text-xs text-slate-500 uppercase font-bold">Empresa</p>
                            <p id="clientEmpresa" class="text-sm font-medium">{{ $cliente->empresa }}</p>
                        </div>
                    </div>

                    <div class="flex items-start gap-3">
                        <i class="fas fa-id-card mt-1 text-slate-400 w-5"></i>
                        <div>
                            <p class="text-xs text-slate-500 uppercase font-bold">NIT / CI</p>
                            <p id="clientNit" class="text-sm font-medium">{{ $cliente->nit ?: 'N/A' }}</p>
                        </div>
                    </div>

                    <div class="flex items-start gap-3">
                        <i class="fas fa-phone mt-1 text-slate-400 w-5"></i>
                        <div>
                            <p class="text-xs text-slate-500 uppercase font-bold">Teléfono</p>
                            <p id="clientPhone" class="text-sm font-medium">{{ $cliente->telefono }}</p>
                        </div>
                    </div>

                    <div class="flex items-start gap-3">
                        <i class="fas fa-envelope mt-1 text-slate-400 w-5"></i>
                        <div>
                            <p class="text-xs text-slate-500 uppercase font-bold">Email</p>
                            <p id="clientEmail" class="text-sm font-medium text-blue-600 truncate">{{ $cliente->email ?: 'Sin email' }}</p>
                        </div>
                    </div>

                    <div class="flex items-start gap-3">
                        <i class="fas fa-map-marker-alt mt-1 text-slate-400 w-5"></i>
                        <div>
                            <p class="text-xs text-slate-500 uppercase font-bold">Dirección</p>
                            <p id="clientAddress" class="text-sm font-medium text-slate-600">{{ $cliente->direccion ?: 'Sin dirección registrada' }}</p>
                        </div>
                    </div>
                </div>

                <div class="card-actions w-full mt-6">
                    <button id="btnEditClient" class="btn btn-primary btn-block btn-sm">
                        <i class="fas fa-edit"></i> Editar Datos
                    </button>
                </div>
            </div>
        </div>

        {{-- Estadísticas Rápidas --}}
        <div class="stats w-full shadow-sm border border-slate-200 bg-white">
            <div class="stat place-items-center">
                <div class="stat-title text-xs uppercase tracking-wider">Visitas</div>
                <div class="stat-value text-blue-600 text-2xl">{{ $visitas }}</div>
                <div class="stat-desc text-blue-400 font-medium">Concretadas</div>
            </div>

            <div class="stat place-items-center">
                <div class="stat-title text-xs uppercase tracking-wider">Vehículos</div>
                <div class="stat-value text-slate-700 text-2xl">{{ $vehiculosCount }}</div>
                <div class="stat-desc">Registrados</div>
            </div>
        </div>

        <div class="card bg-gradient-to-br from-slate-800 to-slate-900 text-white shadow-lg">
            <div class="card-body p-5">
                <h3 class="font-bold text-lg mb-1">Acciones Rápidas</h3>
                <div class="grid grid-cols-2 gap-3">
                    <a href="{{ route('panel.operaciones.citas.index') }}" class="btn btn-sm btn-outline btn-info gap-2 h-auto py-2">
                        <i class="fas fa-plus"></i> Cita
                    </a>
                    <a href="{{ route('panel.operaciones.ordenes_trabajo.create', ['cliente_id' => $cliente->id]) }}" class="btn btn-sm btn-outline btn-warning gap-2 h-auto py-2">
                        <i class="fas fa-clipboard-check"></i> Orden
                    </a>
                </div>
            </div>
        </div>
    </div>

    {{-- Columna Derecha: Contenido Principal --}}
    <div class="lg:col-span-2 space-y-6">

        {{-- Sección de Vehículos --}}
        <div class="card bg-white shadow-sm border border-slate-200">
            <div class="card-header p-5 border-b border-slate-100 flex justify-between items-center bg-slate-50/50">
                <h3 class="font-bold text-lg text-slate-800 flex items-center gap-2">
                    <i class="fas fa-car text-blue-500"></i> Vehículos
                </h3>
                <button id="btnAddVehicle" class="btn btn-sm btn-ghost text-blue-600 gap-2 hover:bg-blue-50">
                    <i class="fas fa-plus"></i> Agregar Vehículo
                </button>
            </div>
            <div class="card-body p-0">
                <div id="vehiclesTableContainer" class="overflow-x-auto {{ $cliente->vehiculos->count() > 0 ? '' : 'hidden' }}">
                    <table class="table table-zebra w-full">
                        <thead>
                            <tr>
                                <th>Vehículo</th>
                                <th>Placa</th>
                                <th>Año</th>
                                <th>Última Visita</th>
                                <th class="text-right">Acciones</th>
                            </tr>
                        </thead>
                        <tbody id="vehiclesTableBody">
                            @foreach($cliente->vehiculos as $vehiculo)
                            <tr class="hover" id="vehicle-row-{{ $vehiculo->id }}">
                                <td>
                                    <div class="flex items-center gap-3">
                                        <div class="avatar placeholder">
                                            <div class="w-10 rounded bg-slate-100 text-slate-400">
                                                <i class="fas fa-car text-lg"></i>
                                            </div>
                                        </div>
                                        <div>
                                            <div class="font-bold">{{ $vehiculo->marca->nombre ?? 'N/A' }}</div>
                                            <div class="text-xs opacity-50">{{ $vehiculo->modelo->nombre ?? 'N/A' }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td><span class="badge badge-outline font-mono font-bold">{{ $vehiculo->placa }}</span></td>
                                <td>{{ $vehiculo->anio }}</td>
                                <td class="text-slate-500 text-sm">
                                    {{ $vehiculo->latestOrden ? $vehiculo->latestOrden->fecha_recepcion->format('d/m/Y') : '-' }}
                                </td>
                                <td class="text-right">
                                    <a href="{{ route('panel.vehiculos.show', $vehiculo->id) }}" class="btn btn-ghost btn-xs text-info" title="Ver Perfil del Vehículo"><i class="fas fa-eye"></i></a>
                                    <button class="btn btn-ghost btn-xs text-blue-600 btn-edit-vehicle" data-vehicle="{{ json_encode($vehiculo) }}"><i class="fas fa-edit"></i></button>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div id="vehiclesEmptyState" class="p-8 text-center text-slate-500 {{ $cliente->vehiculos->count() > 0 ? 'hidden' : '' }}">
                    <div class="w-16 h-16 bg-slate-100 rounded-full flex items-center justify-center mx-auto mb-3">
                        <i class="fas fa-car-side text-2xl text-slate-300"></i>
                    </div>
                    <p class="font-medium">No hay vehículos registrados</p>
                    <p class="text-sm mb-4">Agrega un vehículo para comenzar a crear citas.</p>
                    <button class="btn btn-sm btn-primary" id="btnRegisterFirstVehicle">Registrar Primer Vehículo</button>
                </div>
            </div>
        </div>

        {{-- Historial de Actividad (Tabs) --}}
        <div role="tablist" class="tabs tabs-lifted">
            <input type="radio" name="historial_tabs" role="tab" class="tab" aria-label="Órdenes de Trabajo" checked />
            <div role="tabpanel" class="tab-content bg-base-100 border-base-300 rounded-box p-6 shadow-sm border">

                @if($cliente->ordenes->count() > 0)
                <table class="table w-full">
                    <thead>
                        <tr>
                            <th># Orden</th>
                            <th>Fecha</th>
                            <th>Vehículo</th>
                            <th>Estado</th>
                            <th>Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($cliente->ordenes as $orden)
                        <tr>
                            <td class="font-mono font-bold text-blue-600">#{{ str_pad($orden->id, 5, '0', STR_PAD_LEFT) }}</td>
                            <td>{{ $orden->created_at->format('d/m/Y') }}</td>
                            <td>{{ $orden->vehiculo->placa ?? 'N/A' }}</td>
                            <td>
                                <div class="badge badge-sm badge-{{ $orden->estado_color ?? 'primary' }}">{{ ucfirst($orden->estado) }}</div>
                            </td>
                            <td class="font-bold text-slate-700">Bs. 0.00</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
                @else
                <div class="text-center py-8 text-slate-500">
                    <p>No hay historial de órdenes de trabajo.</p>
                </div>
                @endif

            </div>

            <input type="radio" name="historial_tabs" role="tab" class="tab" aria-label="Citas Pasadas" />
            <div role="tabpanel" class="tab-content bg-base-100 border-base-300 rounded-box p-6 shadow-sm border">
                @if($cliente->citas->count() > 0)
                <ul class="steps steps-vertical w-full">
                    @foreach($cliente->citas->sortByDesc('created_at')->take(5) as $cita)
                    <li class="step {{ $cita->estado == 'concretada' ? 'step-primary' : '' }}">
                        <div class="text-left w-full ml-4 mb-4">
                            <p class="font-bold">{{ $cita->fecha_programada->format('d/m/Y H:i') }} - <span class="badge badge-sm">{{ $cita->estado }}</span></p>
                            <p class="text-sm text-slate-500">{{ $cita->motivo_cita }}</p>
                            <p class="text-xs text-slate-400">Vehículo: {{ $cita->vehiculo->placa ?? 'N/A' }}</p>
                        </div>
                    </li>
                    @endforeach
                </ul>
                @else
                <div class="text-center py-8 text-slate-500">
                    <p>No hay historial de citas.</p>
                </div>
                @endif
            </div>
        </div>

    </div>
</div>

<!-- Modal Edit Client -->
<div id="clientModal" class="fixed inset-0 z-50 hidden overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
    <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
        <div id="clientModalBackdrop" class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" aria-hidden="true"></div>
        <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
        <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
            <form id="clientForm">
                @csrf
                @method('PUT')
                <input type="hidden" id="clientId" name="id" value="{{ $cliente->id }}">

                <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                    <h3 class="text-lg leading-6 font-medium text-gray-900 mb-4">Editar Cliente</h3>

                    <div class="grid grid-cols-1 gap-4">
                        <div class="flex items-center mb-2">
                            <input type="checkbox" id="es_empresa" name="es_empresa" class="rounded border-gray-300 text-blue-600 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50">
                            <label for="es_empresa" class="ml-2 block text-sm text-gray-900">¿Es Empresa?</label>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700">Nombre Completo *</label>
                            <input type="text" name="nombre_completo" id="nombre_completo" required value="{{ $cliente->nombre_completo }}"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                        </div>

                        <div id="empresaField" class="hidden">
                            <label class="block text-sm font-medium text-gray-700">Nombre Empresa *</label>
                            <input type="text" name="empresa" id="empresa" value="{{ $cliente->empresa }}"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700">Teléfono *</label>
                            <input type="text" name="telefono" id="telefono" required value="{{ $cliente->telefono }}"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700">Email</label>
                            <input type="email" name="email" id="email" value="{{ $cliente->email }}"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                        </div>

                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700">NIT</label>
                                <input type="text" name="nit" id="nit" value="{{ $cliente->nit }}"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                            </div>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700">Dirección</label>
                            <textarea name="direccion" id="direccion" rows="2"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">{{ $cliente->direccion }}</textarea>
                        </div>
                    </div>
                </div>

                <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                    <button type="submit" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-blue-600 text-base font-medium text-white hover:bg-blue-700 focus:outline-none sm:ml-3 sm:w-auto sm:text-sm">
                        Guardar Cambios
                    </button>
                    <button type="button" id="btnCancelClient" class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
                        Cancelar
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Create Vehicle -->
<div id="vehicleModal" class="fixed inset-0 z-50 hidden overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
    <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
        <div id="vehicleModalBackdrop" class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" aria-hidden="true"></div>
        <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
        <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
            <form id="vehicleForm">
                @csrf
                <input type="hidden" name="cliente_id" value="{{ $cliente->id }}">
                <input type="hidden" id="vehicleId" name="id"> <!-- For edit in future -->
                @method('POST')

                <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                    <h3 class="text-lg leading-6 font-medium text-gray-900 mb-4" id="vehicleModalTitle">Nuevo Vehículo</h3>

                    <div class="grid grid-cols-1 gap-4">
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Marca *</label>
                                <select name="marca_id" id="marca_id" required
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                    <option value="">Seleccione...</option>
                                    <!-- Loaded via JS -->
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Modelo *</label>
                                <select name="modelo_id" id="modelo_id" required
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                    {{-- <option value="">Seleccione Marca primero</option> --}}
                                </select>
                            </div>

                            <!-- Version Selection -->
                            <div class="col-span-2">
                                <label class="block text-sm font-medium text-gray-700">Versión <span class="text-xs text-gray-400">(Opcional)</span></label>
                                <select name="version_id" id="version_id"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500" disabled>
                                    <option value="">Seleccione Modelo primero</option>
                                </select>
                            </div>
                        </div>

                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Placa *</label>
                                <input type="text" name="placa" id="placa" required style="text-transform: uppercase;"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Año *</label>
                                <input type="number" name="anio" id="anio" required min="1900" max="{{ date('Y') + 1 }}" value="{{ date('Y') }}"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                            </div>
                        </div>

                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Color</label>
                                <input type="text" name="color" id="color"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700">VIN</label>
                                <input type="text" name="vin" id="vin"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                            </div>
                        </div>
                    </div>
                </div>

                <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                    <button type="submit" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-blue-600 text-base font-medium text-white hover:bg-blue-700 focus:outline-none sm:ml-3 sm:w-auto sm:text-sm">
                        Guardar Vehículo
                    </button>
                    <button type="button" id="btnCancelVehicle" class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
                        Cancelar
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>



<div id="client-data"
    data-es-empresa="{{ $cliente->es_empresa ? 'true' : 'false' }}"
    data-client-base="{{ url('panel/clientes') }}"
    data-vehicle-store="{{ route('panel.vehiculos.store') }}"
    data-vehicle-base="{{ url('panel/vehiculos') }}"
    data-get-brands="{{ route('panel.operaciones.citas.getBrands') }}"
    data-list-models-by-marca="{{ route('panel.mantenimientos.modelos.listByMarca', 'PLACEHOLDER') }}"
    data-list-versions-by-modelo="{{ route('panel.mantenimientos.versiones.listByModelo', 'PLACEHOLDER') }}"></div>

@push('scripts')
@vite('resources/js/clientes/show.js')
@endpush
@endsection