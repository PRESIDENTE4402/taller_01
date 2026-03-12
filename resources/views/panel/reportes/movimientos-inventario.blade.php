@extends('layouts.panel')

@section('title', 'Reporte de Movimientos de Inventario')
@section('subtitle', 'Historial detallado de entradas, salidas y ajustes de stock.')

@section('content')
    <div class="w-full max-w-[1920px] mx-auto px-4 lg:px-8 xl:px-12 2xl:px-16">
        
        {{-- Filtros Premium --}}
        <div class="bg-white/80 backdrop-blur-xl rounded-[2rem] shadow-[0_8px_30px_rgb(0,0,0,0.04)] border border-white p-8 mb-8">
            <form action="{{ route('panel.mantenimientos.reportes.movimientos-inventario') }}" method="GET" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-6 items-end">
                
                <div class="space-y-2">
                    <label class="block text-[10px] font-black text-slate-400 upper tracking-widest ml-1">FECHA INICIO</label>
                    <input type="date" name="fecha_inicio" value="{{ $fechaInicio }}" 
                        class="w-full rounded-2xl border-slate-100 bg-slate-50/50 focus:border-blue-500 focus:ring-blue-500 text-sm font-bold p-3">
                </div>

                <div class="space-y-2">
                    <label class="block text-[10px] font-black text-slate-400 upper tracking-widest ml-1">FECHA FIN</label>
                    <input type="date" name="fecha_fin" value="{{ $fechaFin }}" 
                        class="w-full rounded-2xl border-slate-100 bg-slate-50/50 focus:border-blue-500 focus:ring-blue-500 text-sm font-bold p-3">
                </div>

                <div class="space-y-2">
                    <label class="block text-[10px] font-black text-slate-400 upper tracking-widest ml-1">TIPO</label>
                    <select name="tipo" class="w-full rounded-2xl border-slate-100 bg-slate-50/50 focus:border-blue-500 focus:ring-blue-500 text-sm font-bold p-3">
                        <option value="todos" {{ ($filtros['tipo'] ?? '') == 'todos' ? 'selected' : '' }}>Todos los Tipos</option>
                        <option value="entrada" {{ ($filtros['tipo'] ?? '') == 'entrada' ? 'selected' : '' }}>Entradas (+)</option>
                        <option value="salida" {{ ($filtros['tipo'] ?? '') == 'salida' ? 'selected' : '' }}>Salidas (-)</option>
                    </select>
                </div>

                <div class="space-y-2">
                    <label class="block text-[10px] font-black text-slate-400 upper tracking-widest ml-1">SUCURSAL</label>
                    <select name="sucursal_id" class="w-full rounded-2xl border-slate-100 bg-slate-50/50 focus:border-blue-500 focus:ring-blue-500 text-sm font-bold p-3">
                        <option value="">Todas las Sucursales</option>
                        @foreach($sucursales as $suc)
                            <option value="{{ $suc->id }}" {{ ($filtros['sucursal_id'] ?? '') == $suc->id ? 'selected' : '' }}>{{ $suc->nombre }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="flex gap-2 lg:col-span-1">
                    <button type="submit" class="flex-1 bg-slate-900 text-white rounded-2xl font-bold p-3.5 hover:bg-black transition-all shadow-lg shadow-black/10 flex items-center justify-center gap-2">
                        <i class="fas fa-filter text-xs"></i> Filtrar
                    </button>
                    <a href="{{ route('panel.mantenimientos.reportes.movimientos-pdf', request()->all()) }}" class="flex-1 bg-red-500 text-white rounded-2xl font-bold p-3.5 hover:bg-red-600 transition-all shadow-lg shadow-red-500/20 flex items-center justify-center gap-2">
                        <i class="fas fa-file-pdf text-xs"></i> PDF
                    </a>
                    <a href="{{ route('panel.mantenimientos.reportes.movimientos-inventario') }}" class="w-12 bg-slate-100 text-slate-500 rounded-2xl font-bold flex items-center justify-center hover:bg-slate-200 transition-all border border-slate-200/50">
                        <i class="fas fa-undo-alt text-xs"></i>
                    </a>
                </div>
            </form>
        </div>

        {{-- Tabla de Movimientos --}}
        <div class="bg-white/80 backdrop-blur-xl rounded-[2.5rem] shadow-[0_20px_50px_rgba(0,0,0,0.05)] border border-white overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-slate-50/50 border-b border-slate-100">
                            <th class="px-8 py-6 text-[10px] font-black text-slate-400 uppercase tracking-widest">Fecha y Hora</th>
                            <th class="px-6 py-6 text-[10px] font-black text-slate-400 uppercase tracking-widest">Producto / SKU</th>
                            <th class="px-6 py-6 text-[10px] font-black text-slate-400 uppercase tracking-widest">Tipo</th>
                            <th class="px-6 py-6 text-[10px] font-black text-slate-400 uppercase tracking-widest">Cantidad</th>
                            <th class="px-6 py-6 text-[10px] font-black text-slate-400 uppercase tracking-widest text-center">Stock S. Ant / Nuevo</th>
                            <th class="px-6 py-6 text-[10px] font-black text-slate-400 uppercase tracking-widest">Motivo / Notas</th>
                            <th class="px-8 py-6 text-[10px] font-black text-slate-400 uppercase tracking-widest text-right">Usuario</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-50">
                        @forelse($movimientos as $m)
                            <tr class="hover:bg-blue-50/30 transition-colors group">
                                <td class="px-8 py-5">
                                    <div class="flex flex-col">
                                        <span class="text-sm font-bold text-slate-700 leading-none mb-1">{{ $m->created_at->format('d/m/Y') }}</span>
                                        <span class="text-[10px] font-black text-slate-400 uppercase tracking-widest leading-none">{{ $m->created_at->format('H:i:s') }}</span>
                                    </div>
                                </td>
                                <td class="px-6 py-5">
                                    <div class="flex flex-col">
                                        <span class="text-sm font-black text-slate-800 leading-none mb-1">{{ $m->repuesto->nombre }}</span>
                                        <span class="text-[10px] font-bold text-blue-500 uppercase tracking-widest leading-none">{{ $m->repuesto->codigo_interno }}</span>
                                    </div>
                                </td>
                                <td class="px-6 py-5">
                                    @if($m->tipo === 'entrada')
                                        <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-emerald-50 text-emerald-600 text-[10px] font-black uppercase tracking-widest border border-emerald-100">
                                            <i class="fas fa-arrow-up text-[8px]"></i> Entrada
                                        </span>
                                    @else
                                        <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-amber-50 text-amber-600 text-[10px] font-black uppercase tracking-widest border border-amber-100">
                                            <i class="fas fa-arrow-down text-[8px]"></i> Salida
                                        </span>
                                    @endif
                                </td>
                                <td class="px-6 py-5">
                                    <span class="text-base font-black {{ $m->tipo === 'entrada' ? 'text-emerald-500' : 'text-amber-500' }}">
                                        {{ $m->tipo === 'entrada' ? '+' : '-' }}{{ number_format($m->cantidad, 2) }}
                                    </span>
                                </td>
                                <td class="px-6 py-5 text-center">
                                    <div class="flex items-center justify-center gap-3">
                                        <span class="text-[11px] font-bold text-slate-400 italic">{{ number_format($m->stock_anterior, 2) }}</span>
                                        <i class="fas fa-chevron-right text-[8px] text-slate-300"></i>
                                        <span class="text-sm font-black text-slate-700">{{ number_format($m->stock_nuevo, 2) }}</span>
                                    </div>
                                </td>
                                <td class="px-6 py-5">
                                    <div class="flex flex-col max-w-xs">
                                        <span class="text-xs font-black text-slate-600 uppercase tracking-tight mb-1">{{ str_replace(['_', '-'], ' ', $m->motivo) }}</span>
                                        <span class="text-[10px] font-medium text-slate-400 line-clamp-1 italic">{{ $m->notas ?? 'Sin notas adicionales' }}</span>
                                    </div>
                                </td>
                                <td class="px-8 py-5 text-right">
                                    <div class="flex items-center justify-end gap-3">
                                        <div class="flex flex-col">
                                            <span class="text-sm font-black text-slate-700 leading-none mb-1">{{ $m->usuario->name }}</span>
                                            <span class="text-[9px] font-bold text-slate-400 uppercase tracking-widest leading-none">{{ $m->sucursal->nombre ?? 'Sede Central' }}</span>
                                        </div>
                                        <div class="w-10 h-10 rounded-2xl bg-slate-100 border border-slate-200/50 flex items-center justify-center text-slate-400 shadow-sm">
                                            <i class="fas fa-user text-xs"></i>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="py-20 text-center">
                                    <div class="flex flex-col items-center">
                                        <div class="w-20 h-20 rounded-full bg-slate-50 flex items-center justify-center text-slate-200 mb-6">
                                            <i class="fas fa-exchange-alt text-4xl"></i>
                                        </div>
                                        <h3 class="text-lg font-black text-slate-400 uppercase tracking-widest">No se encontraron movimientos</h3>
                                        <p class="text-slate-400 text-sm italic font-medium mt-1">Prueba ajustando los filtros de fecha o tipo.</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($movimientos->hasPages())
                <div class="px-8 py-6 bg-slate-50/50 border-t border-slate-100">
                    {{ $movimientos->links() }}
                </div>
            @endif
        </div>
    </div>
@endsection
