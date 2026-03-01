@extends('layouts.panel')

@section('title', 'Directorio de Vehículos')
@section('subtitle', 'Gestión integral de todos los vehículos registrados')

@section('content')
<div class="container mx-auto px-4">
    <!-- Header Controls -->
    <div class="flex flex-col md:flex-row justify-between items-center gap-4 mb-8">
        <form action="{{ route('panel.vehiculos.index') }}" method="GET" class="w-full md:w-1/2 lg:w-1/3">
            <div class="relative">
                <input type="text" name="buscar" value="{{ request('buscar') }}" placeholder="Buscar por placa, cliente, NIT o marca..." class="input input-bordered w-full pl-10 h-10 shadow-sm focus:border-blue-500 rounded-xl" />
                <i class="fas fa-search absolute left-4 top-1/2 transform -translate-y-1/2 text-gray-400"></i>
                @if(request('buscar'))
                <a href="{{ route('panel.vehiculos.index') }}" class="absolute right-3 top-1/2 transform -translate-y-1/2 text-red-500 hover:text-red-700">
                    <i class="fas fa-times"></i>
                </a>
                @endif
            </div>
        </form>
    </div>

    <!-- Vehículos Grid/Table -->
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="table w-full">
                <thead>
                    <tr class="bg-gray-50/50 text-gray-500 uppercase text-[10px] tracking-widest font-black">
                        <th class="py-4 px-6 border-b border-gray-100">Vehículo</th>
                        <th class="py-4 px-6 border-b border-gray-100">Propietario</th>
                        <th class="py-4 px-6 border-b border-gray-100 text-center">Placa</th>
                        <th class="py-4 px-6 border-b border-gray-100">Detalles</th>
                        <th class="py-4 px-6 border-b border-gray-100 text-right">Acciones</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    @forelse($vehiculos as $vehiculo)
                    <tr class="hover:bg-gray-50/50 transition-colors">
                        <td class="py-4 px-6">
                            <div class="flex items-center gap-4">
                                <div class="w-10 h-10 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center font-black">
                                    <i class="fas fa-car text-lg"></i>
                                </div>
                                <div>
                                    <p class="font-bold text-gray-800">{{ $vehiculo->marca->nombre ?? 'N/A' }} {{ $vehiculo->modelo->nombre ?? '' }}</p>
                                    <p class="text-[10px] text-gray-400 font-bold uppercase tracking-wider">{{ $vehiculo->categoria ?? 'Vehículo' }}</p>
                                </div>
                            </div>
                        </td>
                        <td class="py-4 px-6">
                            <a href="{{ route('panel.clientes.show', $vehiculo->cliente_id) }}" class="flex items-center gap-2 hover:bg-gray-50 p-1 -ml-1 rounded transition-colors group">
                                <i class="fas {{ $vehiculo->cliente->es_empresa ? 'fa-building text-purple-500' : 'fa-user-circle text-gray-400' }} group-hover:text-blue-500 transition-colors"></i>
                                <div>
                                    <p class="text-sm font-bold text-gray-600 group-hover:text-blue-600 transition-colors">{{ $vehiculo->cliente->nombre_completo ?? $vehiculo->cliente->empresa }}</p>
                                    <p class="text-[9px] text-gray-400 font-bold uppercase tracking-widest mt-0.5">
                                        NIT/CI: {{ $vehiculo->cliente->nit ?? 'N/A' }}
                                    </p>
                                </div>
                            </a>
                        </td>
                        <td class="py-4 px-6 text-center">
                            <span class="badge badge-lg badge-outline border-gray-200 text-gray-700 font-mono font-bold tracking-widest uppercase h-8 px-4">
                                {{ $vehiculo->placa }}
                            </span>
                        </td>
                        <td class="py-4 px-6">
                            <div class="flex flex-wrap gap-2 text-[10px] uppercase font-bold tracking-widest">
                                <span class="text-orange-500 bg-orange-50 px-2 py-1 rounded border border-orange-100 hidden lg:inline-block"><i class="fas fa-calendar-alt mr-1"></i> {{ $vehiculo->anio }}</span>
                                <span class="text-pink-500 bg-pink-50 px-2 py-1 rounded border border-pink-100 hidden lg:inline-block"><i class="fas fa-palette mr-1"></i> {{ $vehiculo->color ?? 'N/A' }}</span>
                                @if($vehiculo->version)
                                <span class="text-slate-500 bg-slate-50 px-2 py-1 rounded border border-slate-100 truncate max-w-[120px]" title="{{ $vehiculo->version->nombre }}"><i class="fas fa-tag mr-1"></i> {{ $vehiculo->version->nombre }}</span>
                                @endif
                            </div>
                        </td>
                        <td class="py-4 px-6 text-right">
                            <a href="{{ route('panel.vehiculos.show', $vehiculo->id) }}" class="btn btn-primary bg-blue-600 border-none hover:bg-blue-700 btn-sm text-xs font-bold uppercase tracking-wider text-white shadow-sm shadow-blue-500/30">
                                <i class="fas fa-eye"></i> Historial
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="px-6 py-12 text-center">
                            <div class="inline-flex items-center justify-center w-16 h-16 rounded-full bg-gray-50 mb-4 border border-gray-100">
                                <i class="fas fa-car-side text-2xl text-gray-300"></i>
                            </div>
                            <h3 class="text-lg font-black text-gray-800 mb-1">No se encontraron vehículos</h3>
                            <p class="text-xs text-gray-500 font-medium max-w-sm mx-auto">
                                Aún no hay vehículos registrados, o tu búsqueda no arrojó resultados. <br>
                                (Los vehículos se registran desde el perfil o creación de cada cliente).
                            </p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($vehiculos->hasPages())
        <div class="px-6 py-4 border-t border-gray-100 bg-white">
            {{ $vehiculos->links() }}
        </div>
        @endif
    </div>
</div>
@endsection