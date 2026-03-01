@extends('layouts.panel')

@section('content')
<div class="container mx-auto px-4 py-8">
    <!-- Header: Perfil del Vehículo -->
    <div class="flex flex-col md:flex-row justify-between items-start gap-8 mb-10">
        <div class="flex items-center gap-6">
            @php
            $vehicleImageUrl = null;
            // 1. Priorizar fotos reales de recepcion (excluyendo canvast)
            foreach($vehiculo->ordenes as $o) {
            $img = $o->archivos->where('tipo', 'recepcion')->first();
            if($img) { $vehicleImageUrl = $img->url; break; }
            }

            // 2. Si no hay de recepcion, buscar en archivos "otro" (fotos de daños adicionales)
            if(!$vehicleImageUrl) {
            foreach($vehiculo->ordenes as $o) {
            $img = $o->archivos->where('tipo', 'otro')->first();
            if($img) { $vehicleImageUrl = $img->url; break; }
            }
            }
            @endphp

            <div class="w-32 h-32 rounded-3xl bg-blue-100 flex items-center justify-center text-4xl font-black text-blue-600 shadow-2xl shadow-blue-500/20 overflow-hidden relative border-4 border-white">
                @if($vehicleImageUrl)
                <img src="{{ asset($vehicleImageUrl) }}" alt="Imagen del Vehículo" class="w-full h-full object-cover">
                @else
                <i class="fas fa-car text-5xl"></i>
                @endif
            </div>
            <div>
                <div class="flex items-center gap-3 mb-2">
                    <h1 class="text-4xl font-black text-gray-800 tracking-tighter uppercase">{{ $vehiculo->marca->nombre }} {{ $vehiculo->modelo->nombre }}</h1>
                    <span class="badge badge-lg bg-blue-500/10 text-blue-600 border-blue-500/20 font-black italic uppercase text-[10px] tracking-widest px-4 py-3">{{ $vehiculo->placa }}</span>
                </div>
                <div class="flex flex-wrap gap-4 text-xs font-bold text-gray-400 uppercase tracking-widest mt-3">
                    <span class="bg-white px-3 py-1 rounded-lg border border-gray-100 shadow-sm"><i class="fas fa-calendar-alt text-orange-500 mr-2"></i> {{ $vehiculo->anio }}</span>
                    <span class="bg-white px-3 py-1 rounded-lg border border-gray-100 shadow-sm"><i class="fas fa-palette text-pink-500 mr-2"></i> {{ $vehiculo->color ?? 'N/A' }}</span>
                    <span class="bg-white px-3 py-1 rounded-lg border border-gray-100 shadow-sm"><i class="fas fa-barcode text-gray-500 mr-2"></i> {{ $vehiculo->vin ?? 'N/A' }}</span>
                    @if($vehiculo->version)
                    <span class="bg-white px-3 py-1 rounded-lg border border-gray-100 shadow-sm"><i class="fas fa-tag text-purple-500 mr-2"></i> {{ $vehiculo->version->nombre }}</span>
                    @endif
                </div>
                <div class="mt-4">
                    <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest mb-1">Propietario</p>
                    <a href="{{ route('panel.clientes.show', $vehiculo->cliente_id) }}" class="text-sm font-bold text-blue-600 hover:text-blue-800 hover:underline">
                        <i class="fas fa-user-circle mr-1"></i> {{ $vehiculo->cliente->nombre_completo ?? $vehiculo->cliente->empresa }}
                    </a>
                </div>
            </div>
        </div>
        <div class="flex flex-wrap items-center gap-3 mt-4 md:mt-0">
            <a href="javascript:history.back()" class="btn btn-outline border-gray-200 text-gray-500 hover:bg-gray-100 hover:text-gray-800 rounded-xl font-black italic uppercase text-xs">
                <i class="fas fa-chevron-left"></i> Volver
            </a>
        </div>
    </div>

    <!-- Quick Stats -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-10">
        <div class="bg-white p-6 rounded-2xl border border-gray-100 shadow-sm flex items-center gap-4">
            <div class="w-12 h-12 rounded-full bg-blue-50 flex items-center justify-center text-blue-500 text-xl">
                <i class="fas fa-tools"></i>
            </div>
            <div>
                <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest mb-1">Total Órdenes</p>
                <h3 class="text-3xl font-black text-gray-800 italic">{{ $vehiculo->ordenes->count() }}</h3>
            </div>
        </div>
        <div class="bg-white p-6 rounded-2xl border border-gray-100 shadow-sm flex items-center gap-4">
            <div class="w-12 h-12 rounded-full bg-green-50 flex items-center justify-center text-green-500 text-xl">
                <i class="fas fa-clipboard-check"></i>
            </div>
            <div>
                <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest mb-1">Órdenes Completadas</p>
                <h3 class="text-3xl font-black text-green-600 italic">{{ $vehiculo->ordenes->where('estado', 'completado')->count() }}</h3>
            </div>
        </div>
        <div class="bg-white p-6 rounded-2xl border border-gray-100 shadow-sm flex items-center gap-4">
            <div class="w-12 h-12 rounded-full bg-purple-50 flex items-center justify-center text-purple-500 text-xl">
                <i class="fas fa-clock"></i>
            </div>
            <div>
                <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest mb-1">Último Ingreso</p>
                @if($vehiculo->ordenes->isNotEmpty())
                <h3 class="text-xl font-black text-purple-600 italic mt-1">{{ $vehiculo->ordenes->first()->fecha_recepcion ? $vehiculo->ordenes->first()->fecha_recepcion->format('d M, Y') : 'N/A' }}</h3>
                @else
                <h3 class="text-xl font-black text-gray-400 italic mt-1">-</h3>
                @endif
            </div>
        </div>
    </div>

    <!-- Historial de Órdenes de Trabajo -->
    <div class="bg-white rounded-3xl shadow-sm border border-gray-100 mb-10">
        <div class="p-8 border-b border-gray-50 flex justify-between items-center bg-gray-50/50 rounded-t-3xl gap-4">
            <div>
                <h2 class="text-2xl font-black text-gray-800 tracking-tighter uppercase italic">Historial de Servicio</h2>
                <p class="text-gray-400 font-bold uppercase text-[10px] tracking-widest mt-1">Órdenes de trabajo asociadas a este vehículo</p>
            </div>
            <div><i class="fas fa-list-alt text-3xl text-gray-300"></i></div>
        </div>

        <div class="p-6">
            @forelse($vehiculo->ordenes as $orden)
            <div class="mb-8 border border-gray-100 rounded-2xl overflow-hidden shadow-sm hover:shadow-md transition-shadow">
                <!-- Cabecera de la orden -->
                <div class="bg-gray-50 p-4 border-b border-gray-100 flex flex-wrap items-center justify-between gap-4">
                    <div class="flex items-center gap-4">
                        <div class="w-12 h-12 rounded-xl bg-orange-100 text-orange-600 flex items-center justify-center font-bold">
                            <i class="fas fa-receipt text-xl"></i>
                        </div>
                        <div>
                            <a href="{{ route('panel.operaciones.ordenes_trabajo.show', $orden->id) }}" class="text-lg font-black text-blue-600 hover:underline uppercase">OT-{{ $orden->codigo_orden }}</a>
                            <p class="text-[10px] text-gray-500 font-bold uppercase tracking-widest mt-1">
                                <i class="fas fa-calendar-alt mr-1"></i> {{ $orden->fecha_recepcion ? $orden->fecha_recepcion->format('d/m/Y H:i') : 'N/A' }}
                            </p>
                        </div>
                    </div>
                    <div class="flex items-center gap-6">
                        <div class="text-right">
                            <p class="text-[9px] font-black text-gray-400 uppercase tracking-widest mb-1">Estado</p>
                            @php
                            $statusColors = [
                            'completado' => 'bg-green-100 text-green-700 border-green-200',
                            'en_progreso' => 'bg-blue-100 text-blue-700 border-blue-200',
                            'recepcion' => 'bg-orange-100 text-orange-700 border-orange-200',
                            'pausado' => 'bg-red-100 text-red-700 border-red-200',
                            ];
                            $color = $statusColors[$orden->estado] ?? 'bg-gray-100 text-gray-600 border-gray-200';
                            @endphp
                            <span class="px-3 py-1 rounded-full text-[9px] font-black uppercase tracking-widest border {{ $color }}">
                                {{ str_replace('_', ' ', $orden->estado) }}
                            </span>
                        </div>
                        <div class="text-right hidden sm:block">
                            <p class="text-[9px] font-black text-gray-400 uppercase tracking-widest mb-1">Kilometraje</p>
                            <span class="font-bold text-gray-700 text-sm italic">{{ number_format($orden->kilometraje_entrada) }} km</span>
                        </div>
                    </div>
                </div>

                <!-- Contenido de la orden -->
                <div class="p-6 grid grid-cols-1 lg:grid-cols-4 gap-6">
                    <!-- Detalles y Falla -->
                    <div class="lg:col-span-3">
                        <div class="mb-4">
                            <h4 class="text-xs font-black text-gray-800 uppercase tracking-widest mb-2"><i class="fas fa-exclamation-triangle text-orange-500 mr-1"></i> Falla Reportada</h4>
                            <p class="text-sm font-medium text-gray-600 bg-orange-50/50 p-3 rounded-xl">{{ $orden->falla_cliente ?? 'Sin especificar.' }}</p>
                        </div>

                        @if($orden->diagnostico)
                        <div class="mb-4">
                            <h4 class="text-xs font-black text-gray-800 uppercase tracking-widest mb-2"><i class="fas fa-stethoscope text-blue-500 mr-1"></i> Diagnóstico Inicial</h4>
                            <p class="text-sm font-medium text-gray-600 bg-blue-50/50 p-3 rounded-xl">{{ $orden->diagnostico }}</p>
                        </div>
                        @endif

                        <!-- Tabla de Detalles (Repuestos y Servicios) -->
                        @if($orden->detalles && $orden->detalles->count() > 0)
                        <h4 class="text-xs font-black text-gray-800 uppercase tracking-widest mt-6 mb-3"><i class="fas fa-clipboard-list text-gray-500 mr-1"></i> Trabajos y Repuestos</h4>
                        <div class="bg-white border border-gray-100 rounded-xl overflow-x-auto">
                            <table class="table w-full text-xs">
                                <thead>
                                    <tr class="bg-gray-50 text-gray-500 uppercase font-black text-[9px] tracking-widest">
                                        <th class="py-3 px-4 rounded-tl-xl border-b border-gray-100">Descripción</th>
                                        <th class="py-3 px-4 text-center border-b border-gray-100">Cantidad</th>
                                        <th class="py-3 px-4 text-center border-b border-gray-100">Precio Unit. (S/)</th>
                                        <th class="py-3 px-4 text-right rounded-tr-xl border-b border-gray-100">Subtotal (S/)</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-50">
                                    @php $totalOrden = 0; @endphp
                                    @foreach($orden->detalles as $detalle)
                                    @php
                                    $subtotal = $detalle->cantidad * $detalle->precio_unitario;
                                    $totalOrden += $subtotal;
                                    @endphp
                                    <tr class="hover:bg-gray-50/50">
                                        <td class="py-2 px-4 font-bold text-gray-700">
                                            {{ $detalle->repuesto ? $detalle->repuesto->nombre : $detalle->descripcion_manual }}
                                            @if($detalle->notas)
                                            <span class="block text-[9px] text-gray-400 font-normal italic mt-0.5">{{ $detalle->notas }}</span>
                                            @endif
                                        </td>
                                        <td class="py-2 px-4 text-center font-bold text-gray-600">{{ $detalle->cantidad }}</td>
                                        <td class="py-2 px-4 text-center text-gray-600">{{ number_format($detalle->precio_unitario, 2) }}</td>
                                        <td class="py-2 px-4 text-right font-black text-gray-800">{{ number_format($subtotal, 2) }}</td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        <div class="text-right mt-3 text-sm">
                            <span class="font-black text-gray-400 uppercase text-[10px] tracking-widest mr-2">Total Estimado Estimado:</span>
                            <span class="font-black text-lg text-blue-600">S/ {{ number_format($totalOrden, 2) }}</span>
                        </div>
                        @else
                        <div class="mt-4 p-4 border border-dashed border-gray-200 rounded-xl text-center">
                            <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest italic">Aún no hay trabajos ni repuestos agregados a esta orden</p>
                        </div>
                        @endif
                    </div>

                    <!-- Columna Derecha: Imágenes u otra info adicional -->
                    <div class="lg:col-span-1 border-l border-gray-100 lg:pl-6 pt-4 lg:pt-0">
                        <h4 class="text-xs font-black text-gray-800 uppercase tracking-widest mb-3"><i class="fas fa-camera text-purple-500 mr-1"></i> Fotos de Recepción</h4>

                        @php
                        // Traemos todos los archivos que sean imágenes y descartamos el campo directo del canvas
                        $realPhotos = $orden->archivos->whereIn('tipo', ['recepcion', 'otro']);

                        // Encontrar la foto principal priorizando recepción
                        $mainPhotoUrl = $realPhotos->where('tipo', 'recepcion')->first()->url ?? $realPhotos->first()->url ?? null;

                        // Lista de URLs únicas a procesar en la galería (ignorando el main para el grid)
                        $uniqueUrls = $realPhotos->pluck('url')->unique();
                        @endphp

                        @if($mainPhotoUrl)
                        <div class="rounded-xl overflow-hidden border border-gray-200 mb-2">
                            <a href="{{ asset($mainPhotoUrl) }}" target="_blank" class="block group relative bg-gray-100">
                                <img src="{{ asset($mainPhotoUrl) }}" class="w-full h-32 object-cover hover:scale-105 transition-transform duration-300" alt="Foto Principal">
                                <div class="absolute inset-0 bg-black/40 opacity-0 group-hover:opacity-100 flex items-center justify-center transition-opacity">
                                    <i class="fas fa-search-plus text-white text-2xl"></i>
                                </div>
                            </a>
                        </div>

                        @if($uniqueUrls->count() > 1)
                        <div class="flex flex-wrap gap-2 px-1">
                            @foreach($uniqueUrls->take(4) as $thumb)
                            @if($thumb != $mainPhotoUrl)
                            <a href="{{ asset($thumb) }}" target="_blank" class="w-10 h-10 rounded-lg overflow-hidden border border-gray-200 block hover:opacity-80 transition-opacity bg-gray-100">
                                <img src="{{ asset($thumb) }}" class="w-full h-full object-cover" alt="Miniatura">
                            </a>
                            @endif
                            @endforeach
                            @if($uniqueUrls->count() > 4)
                            <a href="{{ route('panel.operaciones.ordenes_trabajo.show', $orden->id) }}" class="w-10 h-10 rounded-lg bg-gray-100 text-gray-500 font-bold flex items-center justify-center text-[10px] hover:bg-gray-200 transition-colors">
                                +{{ $uniqueUrls->count() - 4 }}
                            </a>
                            @endif
                        </div>
                        @endif

                        @else
                        <div class="h-32 bg-gray-50 rounded-xl border border-dashed border-gray-200 flex flex-col items-center justify-center text-gray-400">
                            <i class="fas fa-image text-3xl mb-2 opacity-30"></i>
                            <span class="text-[9px] font-bold uppercase tracking-widest">Sin imagen</span>
                        </div>
                        @endif

                        <div class="mt-4 pt-4 border-t border-gray-100">
                            <a href="{{ route('panel.operaciones.ordenes_trabajo.show', $orden->id) }}" class="btn btn-sm w-full bg-blue-50 text-blue-600 border-none hover:bg-blue-100 font-bold uppercase text-[10px] tracking-wider rounded-lg">
                                Ver Detalle Completo
                            </a>
                        </div>
                    </div>
                </div>
            </div>
            @empty
            <div class="text-center py-16">
                <div class="w-20 h-20 mx-auto bg-gray-50 rounded-full flex items-center justify-center mb-4 border border-gray-100">
                    <i class="fas fa-folder-open text-3xl text-gray-300"></i>
                </div>
                <h3 class="text-xl font-black text-gray-800 tracking-tighter uppercase italic">Sin Historial</h3>
                <p class="text-gray-400 font-bold text-xs uppercase tracking-widest mt-2 max-w-sm mx-auto">Este vehículo no tiene órdenes de trabajo registradas aún.</p>
            </div>
            @endforelse
        </div>
    </div>
</div>
@endsection