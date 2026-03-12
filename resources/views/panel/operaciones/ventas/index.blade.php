@extends('layouts.panel')

@section('titulo', 'Historial de Ventas')

@section('content')
    <div class="h-full flex flex-col gap-6 p-4 md:p-8">
        {{-- Header --}}
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div>
                <h1 class="text-3xl font-black text-slate-800 tracking-tight">Historial de Ventas</h1>
                <p class="text-slate-500 font-medium">Control total de ingresos, devoluciones y clientes.</p>
            </div>
            <div class="flex items-center gap-3">
                <div class="bg-white rounded-2xl p-1 shadow-sm border border-slate-100 flex items-center">
                    <div class="px-4 py-2 text-right border-r border-slate-100">
                        <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Total Vendido</p>
                        <p id="stats-total" class="text-xl font-black text-blue-600">$0.00</p>
                    </div>
                    <div class="px-4 py-2 text-right">
                        <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Ventas Hoy</p>
                        <p id="stats-count" class="text-xl font-black text-slate-800">0</p>
                    </div>
                </div>
            </div>
        </div>

        {{-- Filtros --}}
        <div class="bg-white rounded-3xl p-6 shadow-sm border border-slate-100 flex flex-col gap-6 animate-fade-in">
            {{-- Filtros Rápidos --}}
            <div class="flex flex-wrap items-center gap-2">
                <span class="text-[10px] font-black text-slate-400 uppercase tracking-widest mr-2">Filtros Rápidos:</span>
                <button onclick="setQuickFilter('today')" class="px-4 py-2 bg-slate-50 text-slate-600 rounded-xl text-[10px] font-black uppercase tracking-widest hover:bg-blue-50 hover:text-blue-600 transition-all border border-slate-100/50 hover:border-blue-100">Hoy</button>
                <button onclick="setQuickFilter('yesterday')" class="px-4 py-2 bg-slate-50 text-slate-600 rounded-xl text-[10px] font-black uppercase tracking-widest hover:bg-blue-50 hover:text-blue-600 transition-all border border-slate-100/50 hover:border-blue-100">Ayer</button>
                <button onclick="setQuickFilter('month')" class="px-4 py-2 bg-slate-50 text-slate-600 rounded-xl text-[10px] font-black uppercase tracking-widest hover:bg-blue-50 hover:text-blue-600 transition-all border border-slate-100/50 hover:border-blue-100">Este Mes</button>
                <div class="h-4 w-[1px] bg-slate-100 mx-2 hidden md:block"></div>
                <button onclick="clearFilters()" class="px-4 py-2 text-slate-400 hover:text-red-500 text-[10px] font-black uppercase tracking-widest transition-all">Limpiar</button>
            </div>

            <div class="flex flex-col md:flex-row items-end gap-6">
            <div class="flex-1 w-full">
                <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2 ml-1">Fecha Inicio</label>
                <div class="relative group">
                    <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none text-slate-400 group-hover:text-blue-500 transition-colors">
                        <i class="fas fa-calendar-alt text-sm"></i>
                    </div>
                    <input type="date" id="filter-date-start" class="w-full pl-11 pr-4 py-3 bg-slate-50 border-none rounded-2xl text-sm font-bold text-slate-700 focus:ring-2 focus:ring-blue-500/20 transition-all">
                </div>
            </div>
            <div class="flex-1 w-full">
                <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2 ml-1">Fecha Fin</label>
                <div class="relative group">
                    <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none text-slate-400 group-hover:text-blue-500 transition-colors">
                        <i class="fas fa-calendar-check text-sm"></i>
                    </div>
                    <input type="date" id="filter-date-end" class="w-full pl-11 pr-4 py-3 bg-slate-50 border-none rounded-2xl text-sm font-bold text-slate-700 focus:ring-2 focus:ring-blue-500/20 transition-all">
                </div>
            </div>
            <div class="flex gap-2 w-full md:w-auto">
                <button onclick="window.loadVentas(1)" class="flex-1 md:flex-none h-[52px] px-8 bg-blue-600 text-white rounded-2xl font-black text-xs uppercase tracking-widest hover:bg-blue-700 hover:shadow-lg hover:shadow-blue-500/30 transition-all flex items-center justify-center gap-2 active:scale-95">
                    <i class="fas fa-filter"></i>
                    Filtrar
                </button>
                <button onclick="clearFilters()" class="w-[52px] h-[52px] bg-slate-100 text-slate-400 rounded-2xl hover:bg-slate-200 hover:text-slate-600 transition-all flex items-center justify-center active:scale-95" title="Limpiar Filtros">
                    <i class="fas fa-undo-alt"></i>
                </button>
            </div>
        </div>

        {{-- Tabla de Ventas --}}
        <div class="flex-1 bg-white rounded-[2.5rem] shadow-xl shadow-slate-200/50 border border-slate-100 overflow-hidden flex flex-col">
            <div class="overflow-x-auto h-full scrollbar-thin scrollbar-thumb-slate-200">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-slate-50/50 border-b border-slate-100">
                            <th class="px-6 py-5 text-[10px] font-black text-slate-400 uppercase tracking-widest">Folio</th>
                            <th class="px-6 py-5 text-[10px] font-black text-slate-400 uppercase tracking-widest">Cliente / Fecha</th>
                            <th class="px-6 py-5 text-[10px] font-black text-slate-400 uppercase tracking-widest">Estado</th>
                            <th class="px-6 py-5 text-[10px] font-black text-slate-400 uppercase tracking-widest text-right">Total</th>
                            <th class="px-6 py-5 text-[10px] font-black text-slate-400 uppercase tracking-widest text-center">Acciones</th>
                        </tr>
                    </thead>
                    <tbody id="ventas-table-body" class="divide-y divide-slate-50">
                        {{-- Cargando... --}}
                        <tr>
                            <td colspan="5" class="px-6 py-20 text-center">
                                <i class="fas fa-circle-notch fa-spin text-4xl text-blue-100 mb-4"></i>
                                <p class="text-slate-400 font-bold uppercase tracking-widest text-xs">Cargando historial...</p>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            {{-- Paginación --}}
            <div id="pagination-container" class="px-6 py-4 bg-slate-50 border-t border-slate-100 flex items-center justify-between">
                {{-- Dinámico --}}
            </div>
        </div>
    </div>

    {{-- Modal Detalle de Venta --}}
    <div id="ventaModal" class="fixed inset-0 z-50 hidden overflow-hidden">
        <div id="modalBackdrop" class="absolute inset-0 bg-slate-900/60 backdrop-blur-sm transition-opacity duration-300 opacity-0"></div>
        <div class="absolute inset-0 flex items-center justify-center p-4">
            <div id="modalPanel" class="bg-white w-full max-w-2xl rounded-[2.5rem] shadow-2xl transition-all duration-300 opacity-0 translate-y-4 flex flex-col max-h-[90dvh]">
                {{-- Header Modal --}}
                <div class="p-8 border-b border-slate-100 flex items-center justify-between bg-slate-50/50">
                    <div class="flex items-center gap-4">
                        <div class="w-12 h-12 rounded-2xl bg-blue-600 text-white flex items-center justify-center shadow-lg shadow-blue-500/30">
                            <i class="fas fa-receipt text-xl"></i>
                        </div>
                        <div>
                            <h3 id="modal-folio" class="text-2xl font-black text-slate-800 tracking-tight">VNT-000000</h3>
                            <p id="modal-fecha" class="text-xs font-bold text-slate-400 uppercase tracking-widest">00/00/0000 00:00</p>
                        </div>
                    </div>
                    <button onclick="window.closeVentaModal()" class="w-12 h-12 rounded-2xl bg-white border border-slate-200 text-slate-400 flex items-center justify-center hover:bg-slate-50 hover:text-slate-800 transition-all">
                        <i class="fas fa-times"></i>
                    </button>
                </div>

                {{-- Body Modal --}}
                <div id="modal-body" class="flex-1 overflow-y-auto p-8 space-y-8 custom-scrollbar">
                    {{-- Información General --}}
                    <div class="grid grid-cols-2 gap-6">
                        <div class="bg-slate-50 p-4 rounded-2xl border border-slate-100">
                            <p class="text-[9px] font-black text-slate-400 uppercase tracking-widest mb-1">Cliente</p>
                            <p id="modal-cliente" class="text-sm font-black text-slate-800">Venta Mostrador</p>
                        </div>
                        <div class="bg-slate-50 p-4 rounded-2xl border border-slate-100">
                            <p class="text-[9px] font-black text-slate-400 uppercase tracking-widest mb-1">Vendedor</p>
                            <p id="modal-vendedor" class="text-sm font-black text-slate-800">Admin</p>
                        </div>
                    </div>

                    {{-- Tabla de Productos --}}
                    <div>
                        <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-4 ml-1">Productos Vendidos</p>
                        <div class="border border-slate-100 rounded-3xl overflow-hidden">
                            <table class="w-full text-left border-collapse bg-white">
                                <thead>
                                    <tr class="bg-slate-50/50">
                                        <th class="px-4 py-3 text-[9px] font-black text-slate-400 uppercase tracking-widest">Ítem</th>
                                        <th class="px-4 py-3 text-[9px] font-black text-slate-400 uppercase tracking-widest text-center">Cant.</th>
                                        <th class="px-4 py-3 text-[9px] font-black text-slate-400 uppercase tracking-widest text-right">Subtotal</th>
                                    </tr>
                                </thead>
                                <tbody id="modal-items-body" class="divide-y divide-slate-50 text-sm">
                                    {{-- Dinámico --}}
                                </tbody>
                            </table>
                        </div>
                    </div>

                    {{-- Notas --}}
                    <div id="modal-notas-container" class="hidden">
                        <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2 ml-1">Notas de Venta</p>
                        <div class="bg-blue-50/30 p-4 rounded-2xl border border-blue-100/50">
                            <p id="modal-notas" class="text-xs text-slate-600 leading-relaxed"></p>
                        </div>
                    </div>
                </div>

                {{-- Footer Modal --}}
                <div class="p-8 bg-slate-50 border-t border-slate-100 flex flex-col gap-4">
                    <div class="flex items-center justify-between">
                        <span class="text-xs font-black text-slate-400 uppercase tracking-widest">Total de la Venta</span>
                        <span id="modal-total" class="text-3xl font-black text-slate-900">$0.00</span>
                    </div>

                    <div id="modal-actions" class="flex gap-3">
                        {{-- Botón Devolución Dinámico --}}
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        window.API_VENTAS_URL = "{{ route('panel.ventas.index') }}";
        window.CSRF_TOKEN = "{{ csrf_token() }}";
    </script>
@endsection

@push('scripts')
    @vite('resources/js/panel/ventas/ventas.js')
@endpush
