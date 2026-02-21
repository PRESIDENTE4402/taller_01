@extends('layouts.panel')

@section('content')
<div class="container mx-auto px-4 py-8">
    <!-- Header: Perfil del Trabajador -->
    <div class="flex flex-col md:flex-row justify-between items-start gap-8 mb-10">
        <div class="flex items-center gap-6">
            <div class="w-24 h-24 rounded-3xl bg-blue-600 flex items-center justify-center text-4xl font-black text-white shadow-2xl shadow-blue-500/40 uppercase">
                {{ substr($worker->persona->nombres ?? $worker->name, 0, 1) }}
            </div>
            <div>
                <div class="flex items-center gap-3 mb-2">
                    <h1 class="text-4xl font-black text-gray-800 tracking-tighter italic uppercase">{{ $worker->persona ? $worker->persona->nombres . ' ' . $worker->persona->apellidos : $worker->name }}</h1>
                    <span class="badge badge-lg bg-green-500/10 text-green-600 border-green-500/20 font-black italic uppercase text-[10px] tracking-widest px-4 py-3">Activo</span>
                </div>
                <div class="flex flex-wrap gap-4 text-xs font-bold text-gray-400 uppercase tracking-widest">
                    <span><i class="fas fa-id-badge text-blue-500 mr-2"></i> {{ $worker->roles->first()->nombre ?? 'Colaborador' }}</span>
                    <span><i class="fas fa-map-marker-alt text-red-500 mr-2"></i> {{ $worker->sucursales->first()->nombre ?? 'N/A' }}</span>
                    <span><i class="fas fa-envelope text-purple-500 mr-2"></i> {{ $worker->email }}</span>
                </div>
            </div>
        </div>
        <div class="flex flex-wrap items-center gap-3">
            <form action="{{ route('panel.colaboradores.show', $worker->id) }}" method="GET" class="flex items-center gap-2 bg-white p-2 rounded-xl border border-gray-100 shadow-sm">
                <div class="flex items-center gap-2">
                    <label class="text-[9px] font-black text-gray-400 uppercase tracking-widest pl-2">Desde:</label>
                    <input type="date" name="desde" value="{{ $desde }}" class="input input-xs input-bordered rounded-lg font-bold text-gray-600">
                </div>
                <div class="flex items-center gap-2 border-l border-gray-100 pl-2">
                    <label class="text-[9px] font-black text-gray-400 uppercase tracking-widest">Hasta:</label>
                    <input type="date" name="hasta" value="{{ $hasta }}" class="input input-xs input-bordered rounded-lg font-bold text-gray-600">
                </div>
                <button type="submit" class="btn btn-xs btn-ghost text-blue-600 hover:bg-blue-50 rounded-lg" title="Aplicar Filtros">
                    <i class="fas fa-filter"></i>
                </button>
                @if($desde || $hasta)
                <a href="{{ route('panel.colaboradores.show', ['id' => $worker->id, 'clear' => 1]) }}" class="btn btn-xs btn-ghost text-red-500 hover:bg-red-50 rounded-lg" title="Limpiar Filtros">
                    <i class="fas fa-times"></i>
                </a>
                @endif
            </form>

            @php $printUrl = route('panel.colaboradores.print', ['id' => $worker->id, 'desde' => $desde, 'hasta' => $hasta]); @endphp
            <button onclick="openPDFPreview('{{ $printUrl }}')" class="btn btn-primary bg-blue-600 border-none text-white hover:bg-blue-700 rounded-xl font-black italic uppercase text-xs shadow-lg shadow-blue-500/30">
                <i class="fas fa-file-pdf"></i> Exportar Historial
            </button>
            <a href="{{ route('panel.colaboradores.index') }}" class="btn btn-outline border-gray-200 text-gray-500 hover:bg-gray-100 hover:text-gray-800 rounded-xl font-black italic uppercase text-xs">
                <i class="fas fa-chevron-left"></i> Volver al Tablero
            </a>
        </div>
    </div>

    <!-- Quick Stats -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-10">
        <div class="bg-white p-6 rounded-2xl border border-gray-100 shadow-sm">
            <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest mb-1">Total Tareas</p>
            <h3 class="text-3xl font-black text-gray-800 italic">{{ $stats['total_tareas'] }}</h3>
        </div>
        <div class="bg-white p-6 rounded-2xl border border-gray-100 shadow-sm">
            <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest mb-1">Este Mes</p>
            <h3 class="text-3xl font-black text-blue-600 italic">{{ $stats['tareas_mes'] }}</h3>
        </div>
        <div class="bg-white p-6 rounded-2xl border border-gray-100 shadow-sm">
            <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest mb-1">Minutos Totales</p>
            <h3 class="text-3xl font-black text-purple-600 italic">{{ number_format($stats['minutos_totales']) }}</h3>
        </div>
        <div class="bg-white p-6 rounded-2xl border border-gray-100 shadow-sm">
            <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest mb-1">Promedio Tarea</p>
            <h3 class="text-3xl font-black text-orange-600 italic">{{ number_format($stats['promedio_minutos'], 1) }} min</h3>
        </div>
    </div>

    <!-- Historial de Trabajos -->
    <div class="bg-white rounded-3xl shadow-sm border border-gray-100 overflow-hidden mb-10">
        <div class="p-8 border-b border-gray-50 flex justify-between items-center bg-gray-50/50">
            <div>
                <h2 class="text-2xl font-black text-gray-800 tracking-tighter uppercase italic">Historial de Trabajos Realizados</h2>
                <p class="text-gray-400 font-bold uppercase text-[10px] tracking-widest mt-1">Registro cronológico de actividades</p>
            </div>
        </div>
        <div class="overflow-x-auto">
            <table class="table w-full">
                <thead>
                    <tr class="bg-gray-50 text-gray-400 text-[10px] uppercase font-black tracking-widest border-b border-gray-100">
                        <th class="py-6 px-8">Fecha y Hora</th>
                        <th class="py-6 px-8">Actividad</th>
                        <th class="py-6 px-8 text-center">Orden Relacionada</th>
                        <th class="py-6 px-8 text-center">Sucursal</th>
                        <th class="py-6 px-8 text-center">Duración</th>
                        <th class="py-6 px-8 text-center">Estado</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50 border-b border-gray-50">
                    @forelse($historico as $tarea)
                    <tr class="hover:bg-gray-50 transition-colors">
                        <td class="py-6 px-8">
                            <span class="font-bold text-gray-800 text-sm block">{{ $tarea->created_at->format('d/m/Y') }}</span>
                            <span class="text-[10px] text-gray-400 font-black uppercase">{{ $tarea->created_at->format('H:i A') }}</span>
                        </td>
                        <td class="py-6 px-8">
                            <p class="font-bold text-gray-700 text-sm italic">"{{ $tarea->descripcion }}"</p>
                            <span class="text-[10px] font-black text-blue-500 uppercase tracking-tighter">{{ $tarea->tipo_actividad }}</span>
                        </td>
                        <td class="py-6 px-8 text-center">
                            @if($tarea->orden)
                            <a href="{{ route('panel.operaciones.ordenes_trabajo.show', $tarea->orden->id) }}" class="badge badge-outline border-blue-200 text-blue-600 font-black text-[10px] uppercase tracking-tighter h-8 px-4 hover:bg-blue-600 hover:text-white transition-all">
                                OT-{{ $tarea->orden->codigo_orden }}
                            </a>
                            @else
                            <span class="text-gray-300 text-[10px] font-black uppercase tracking-widest">---</span>
                            @endif
                        </td>
                        <td class="py-6 px-8 text-center">
                            <span class="text-[11px] font-black text-gray-500 uppercase tracking-widest">{{ $tarea->sucursal->nombre ?? 'N/A' }}</span>
                        </td>
                        <td class="py-6 px-8 text-center">
                            <span class="font-mono font-black text-gray-700 text-sm">{{ $tarea->minutos_totales ?? '--' }} min</span>
                        </td>
                        <td class="py-6 px-8 text-center">
                            @php
                            $statusColors = [
                            'completado' => 'bg-green-100 text-green-700 border-green-200',
                            'en_progreso' => 'bg-blue-100 text-blue-700 border-blue-200',
                            'en_pausa' => 'bg-orange-100 text-orange-700 border-orange-200',
                            ];
                            $color = $statusColors[$tarea->estado] ?? 'bg-gray-100 text-gray-600';
                            @endphp
                            <span class="px-3 py-1.5 rounded-full text-[9px] font-black uppercase tracking-widest border border-current {{ $color }}">
                                {{ $tarea->estado }}
                            </span>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="py-20 text-center">
                            <i class="fas fa-history text-4xl text-gray-200 mb-4"></i>
                            <p class="text-gray-400 font-black uppercase tracking-widest italic">No hay historial registrado aún</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($historico->hasPages())
        <div class="p-8 border-t border-gray-50 bg-gray-50/30">
            {{ $historico->links() }}
        </div>
        @endif
    </div>
</div>
@endsection

<!-- Modal: Vista Previa PDF -->
@push('modals')
<dialog id="modalPreviewPDF" class="modal">
    <div class="modal-box w-11/12 max-w-5xl h-[90vh] bg-gray-900 p-0 rounded-3xl overflow-hidden shadow-2xl flex flex-col">
        <div class="p-6 bg-gray-900 text-white flex justify-between items-center border-b border-gray-800">
            <div>
                <h3 class="text-xl font-black italic tracking-tighter uppercase">Vista Previa del Reporte</h3>
                <p class="text-gray-400 text-[10px] font-bold uppercase tracking-widest">Puedes descargar o imprimir el documento desde el visor</p>
            </div>
            <form method="dialog">
                <button class="btn btn-sm btn-circle btn-ghost text-white">✕</button>
            </form>
        </div>
        <div class="flex-1 bg-gray-100">
            <iframe id="pdfIframe" src="" class="w-full h-full border-none"></iframe>
        </div>
    </div>
</dialog>
@endpush

@push('scripts')
<script type="text/javascript">
    function openPDFPreview(url) {
        const modal = document.getElementById('modalPreviewPDF');
        const iframe = document.getElementById('pdfIframe');
        if (modal && iframe) {
            iframe.src = url;
            modal.showModal();
        }
    }
</script>
@endpush