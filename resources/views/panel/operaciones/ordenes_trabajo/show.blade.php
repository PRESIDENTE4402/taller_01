@extends('layouts.panel')

@section('title', 'Planificación de Orden')
@section('subtitle', 'Gestionar tareas, mecánicos y repuestos para #' . $orden->codigo_orden)

@section('content')
<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

    <!-- Column 1 & 2: Main Planning -->
    <div class="lg:col-span-2 space-y-6">

        <!-- Order Header Summary & Progress Stepper -->
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
            <div class="p-6 flex flex-col md:flex-row justify-between items-center gap-4">
                <div class="flex items-center gap-4">
                    <div class="w-14 h-14 bg-blue-50 text-blue-600 rounded-xl flex items-center justify-center text-2xl shadow-inner">
                        <i class="fas fa-file-invoice"></i>
                    </div>
                    <div>
                        <h2 class="text-2xl font-black text-gray-800 tracking-tighter">{{ $orden->codigo_orden }}</h2>
                        <p class="text-gray-500 font-medium text-xs uppercase tracking-tight">{{ $orden->vehiculo->marca->nombre }} {{ $orden->vehiculo->modelo->nombre }} • <span class="text-blue-600 font-bold">{{ $orden->vehiculo->placa }}</span></p>
                    </div>
                </div>
                <div class="flex flex-col items-center md:items-end">
                    <span class="badge badge-lg {{ $orden->estado == 'abierta' ? 'badge-info' : ($orden->estado == 'finalizada' ? 'badge-success' : 'badge-primary') }} uppercase font-black italic px-4 shadow-sm border-none text-white h-8">
                        {{ str_replace('_', ' ', $orden->estado) }}
                    </span>
                    <p class="text-[9px] text-gray-400 mt-1 uppercase font-black tracking-widest">Estado de la Reparación</p>
                </div>
            </div>

            <!-- Visual Stepper -->
            <div class="px-6 pb-6 pt-2">
                <div class="flex items-center w-full">
                    <!-- Step 1: Received -->
                    <div class="flex flex-col items-center flex-1 relative">
                        <div class="w-8 h-8 rounded-full bg-blue-600 text-white flex items-center justify-center font-bold text-xs z-10 shadow-lg ring-4 ring-blue-50">
                            <i class="fas fa-check"></i>
                        </div>
                        <span class="text-[9px] font-bold mt-2 uppercase text-blue-600">Recibido</span>
                        <div class="absolute h-1 w-full bg-blue-600 top-4 left-1/2 -z-0"></div>
                    </div>

                    <!-- Step 2: Planning / Assigned -->
                    <div class="flex flex-col items-center flex-1 relative">
                        @php $isAssigned = $orden->bitacoras->count() > 0; @endphp
                        <div class="w-8 h-8 rounded-full {{ $isAssigned ? 'bg-blue-600 text-white shadow-lg ring-4 ring-blue-50' : 'bg-gray-200 text-gray-400' }} flex items-center justify-center font-bold text-xs z-10 transition-all duration-500">
                            @if($isAssigned) <i class="fas fa-check"></i> @else 2 @endif
                        </div>
                        <span class="text-[9px] font-bold mt-2 uppercase {{ $isAssigned ? 'text-blue-600' : 'text-gray-400' }}">Planificado</span>
                        <div class="absolute h-1 w-full {{ $orden->estado == 'en_proceso' || $orden->estado == 'finalizada' ? 'bg-blue-600' : 'bg-gray-200' }} top-4 left-1/2 -z-0"></div>
                    </div>

                    <!-- Step 3: Execution -->
                    <div class="flex flex-col items-center flex-1 relative">
                        @php $inProgress = $orden->estado == 'en_proceso' || $orden->estado == 'finalizada'; @endphp
                        <div class="w-8 h-8 rounded-full {{ $inProgress ? 'bg-blue-600 text-white shadow-lg ring-4 ring-blue-50' : 'bg-gray-200 text-gray-400' }} flex items-center justify-center font-bold text-xs z-10 transition-all duration-500">
                            @if($orden->estado == 'finalizada') <i class="fas fa-check"></i> @else 3 @endif
                        </div>
                        <span class="text-[9px] font-bold mt-2 uppercase {{ $inProgress ? 'text-blue-600' : 'text-gray-400' }}">Reparación</span>
                        <div class="absolute h-1 w-full {{ $orden->estado == 'finalizada' ? 'bg-blue-600' : 'bg-gray-200' }} top-4 left-1/2 -z-0"></div>
                    </div>

                    <!-- Step 4: Finished -->
                    <div class="flex flex-col items-center flex-1 relative">
                        @php $isFinished = $orden->estado == 'finalizada'; @endphp
                        <div class="w-8 h-8 rounded-full {{ $isFinished ? 'bg-green-500 text-white shadow-lg ring-4 ring-green-50' : 'bg-gray-200 text-gray-400' }} flex items-center justify-center font-bold text-xs z-10 transition-all duration-500">
                            @if($isFinished) <i class="fas fa-flag-checkered"></i> @else 4 @endif
                        </div>
                        <span class="text-[9px] font-bold mt-2 uppercase {{ $isFinished ? 'text-green-600' : 'text-gray-400' }}">Entregado</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Section: Tasks / Labor -->
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
            <div class="p-5 border-b border-gray-50 flex justify-between items-center bg-gray-50/50">
                <h3 class="font-black text-gray-700 flex items-center gap-2 uppercase text-xs tracking-widest">
                    <i class="fas fa-tools text-blue-500"></i> Planificación Técnica
                </h3>
                <button onclick="document.getElementById('modal-task').showModal()" class="btn btn-sm btn-primary gap-2 shadow-md shadow-blue-500/20 px-4">
                    <i class="fas fa-plus"></i> <span class="hidden sm:inline">Asignar Tarea</span><span class="sm:hidden">Tarea</span>
                </button>
            </div>
            <div class="overflow-x-auto custom-scrollbar">
                <table class="table w-full border-separate border-spacing-0">
                    <thead>
                        <tr class="text-gray-400 text-[10px] uppercase bg-gray-50/30">
                            <th class="rounded-tl-xl border-b border-gray-100 py-4">Descripción / Actividad</th>
                            <th class="border-b border-gray-100">Mecánico Asignado</th>
                            <th class="border-b border-gray-100">Estimado</th>
                            <th class="border-b border-gray-100">Estado</th>
                            <th class="rounded-tr-xl border-b border-gray-100"></th>
                        </tr>
                    </thead>
                    <tbody id="tasks-table-body">
                        @forelse($orden->bitacoras as $task)
                        <tr class="hover:bg-blue-50/30 transition-colors group">
                            <td class="border-b border-gray-50 py-4">
                                <div class="font-black text-gray-800 text-sm italic">{{ $task->descripcion }}</div>
                                <div class="flex items-center gap-2 mt-0.5">
                                    <div class="text-[9px] text-blue-500 font-black uppercase tracking-widest">{{ $task->tipo_actividad }}</div>
                                    @if($task->notas_adicionales)
                                        <button type="button" class="btn-view-notes badge badge-xs bg-yellow-100 hover:bg-yellow-200 text-yellow-700 border-none px-2 cursor-pointer transition-colors shadow-sm" data-notas="{{ $task->notas_adicionales }}">
                                            <i class="fas fa-comment-dots mr-1"></i> Ver Notas
                                        </button>
                                    @endif
                                </div>
                            </td>
                            <td class="border-b border-gray-50">
                                <div class="flex items-center gap-2">
                                    <div class="avatar placeholder">
                                        <div class="bg-blue-600 text-white rounded-lg w-7 h-7 flex items-center justify-center font-black text-[10px]">
                                            {{ substr($task->mecanico->name ?? 'M', 0, 1) }}
                                        </div>
                                    </div>
                                    <div>
                                        <span class="text-xs font-bold text-gray-700 block leading-none">{{ $task->mecanico->name ?? 'Sin asignar' }}</span>
                                        <span class="text-[9px] text-gray-400 uppercase font-bold">Técnico Especialista</span>
                                    </div>
                                </div>
                            </td>
                            <td class="border-b border-gray-50">
                                <span class="badge badge-ghost font-mono text-[10px] font-bold bg-gray-100 text-gray-600 border-none">{{ $task->meta_minutos }} MIN</span>
                            </td>
                            <td class="border-b border-gray-50">
                                <span class="badge badge-sm uppercase font-black text-[9px] italic {{ $task->estado == 'completado' ? 'badge-success' : 'badge-warning' }} border-none px-3">
                                    {{ $task->estado }}
                                </span>
                            </td>
                            <td class="text-right border-b border-gray-50 rounded-r-xl">
                                <button class="btn btn-ghost btn-xs text-gray-300 hover:text-red-500 transition-colors">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="5" class="text-center py-16">
                                <div class="flex flex-col items-center gap-2 opacity-30">
                                    <i class="fas fa-clipboard-list text-4xl"></i>
                                    <p class="text-xs font-black uppercase tracking-widest">No hay tareas planificadas aún</p>
                                    <button onclick="document.getElementById('modal-task').showModal()" class="btn btn-xs btn-outline btn-primary mt-2">Crear Primera Tarea</button>
                                </div>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Section: Diagnostics (New) -->
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
            <div class="p-5 border-b border-gray-50 flex justify-between items-center bg-gray-50/50">
                <h3 class="font-black text-gray-700 flex items-center gap-2 uppercase text-xs tracking-widest">
                    <i class="fas fa-microscope text-purple-500"></i> Diagnóstico Técnico
                </h3>
            </div>
            <div class="p-5 space-y-4 bg-white">
                <form id="form-diagnostico" class="space-y-4">
                    @csrf
                    <div>
                        <label class="block text-[10px] font-black text-gray-400 mb-2 uppercase tracking-widest">Diagnóstico Inicial</label>
                        <textarea id="val-diagnostico" name="diagnostico" class="textarea textarea-bordered w-full h-24 bg-gray-50 focus:bg-white text-gray-700 font-medium leading-relaxed border-gray-200" placeholder="Escriba el resultado de la revisión inicial antes de la reparación...">{{ $orden->diagnostico }}</textarea>
                    </div>
                    <div>
                        <label class="block text-[10px] font-black text-gray-400 mb-2 uppercase tracking-widest">Diagnóstico Final (Conclusión)</label>
                        <textarea id="val-diagnostico-final" name="diagnostico_final" class="textarea textarea-bordered w-full h-24 bg-gray-50 focus:bg-white text-gray-700 font-medium leading-relaxed border-gray-200" placeholder="Notas una vez concluido el trabajo...">{{ $orden->diagnostico_final }}</textarea>
                    </div>
                    <div class="flex justify-end mt-2">
                        <button type="button" onclick="guardarDiagnostico()" class="btn btn-sm btn-outline border-purple-200 text-purple-600 hover:bg-purple-600 hover:text-white hover:border-purple-600 gap-2 font-black uppercase tracking-tighter shadow-sm">
                            <i class="fas fa-save"></i> Guardar Diagnóstico
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Section: Parts / Inventory -->
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
            <div class="p-5 border-b border-gray-50 flex justify-between items-center bg-gray-50/50">
                <h3 class="font-bold text-gray-700 flex items-center gap-2">
                    <i class="fas fa-box-open text-orange-500"></i> Repuestos y Materiales
                </h3>
                <button onclick="document.getElementById('modal-part').showModal()" class="btn btn-sm btn-outline btn-warning gap-2">
                    <i class="fas fa-plus"></i> Agregar Repuesto
                </button>
            </div>
            <div class="overflow-x-auto">
                <table class="table w-full">
                    <thead>
                        <tr class="text-gray-400 text-xs uppercase">
                            <th>Repuesto / Descripción</th>
                            <th>Origen</th>
                            <th>Cant.</th>
                            <th>P. Unit.</th>
                            <th>Estado</th>
                            <th class="text-right">Total</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody id="parts-table-body">
                        @php $totalParts = 0; @endphp
                        @forelse($orden->detalles as $detalle)
                        @php 
                            $itemTotal = $detalle->cantidad * $detalle->precio_unitario; 
                            if ($detalle->estado !== 'rechazado') {
                                $totalParts += $itemTotal; 
                            }
                        @endphp
                        <tr class="hover:bg-gray-50 transition-colors" data-detail-id="{{ $detalle->id }}">
                            <td>
                                <div class="font-bold text-gray-700 text-sm {{ $detalle->estado === 'rechazado' ? 'line-through text-gray-400' : '' }}">
                                    {{ $detalle->repuesto ? $detalle->repuesto->nombre : $detalle->descripcion_manual }}
                                </div>
                                <div class="text-[10px] text-gray-400 uppercase font-bold">{{ $detalle->repuesto ? $detalle->repuesto->codigo : 'MANUAL' }}</div>
                            </td>
                            <td>
                                <span class="badge badge-sm uppercase font-bold text-[9px] {{ $detalle->suministrado_por == 'taller' ? 'bg-blue-100 text-blue-700' : 'bg-green-100 text-green-700' }} border-none">
                                    {{ $detalle->suministrado_por }}
                                </span>
                            </td>
                            <td class="font-bold text-sm text-gray-600">{{ number_format($detalle->cantidad, 2) }}</td>
                            <td class="text-sm text-gray-500 font-mono">Q.{{ number_format($detalle->precio_unitario, 2) }}</td>
                            <td>
                                <select class="select select-xs select-bordered w-full max-w-[100px] status-detail-select font-bold text-[10px] uppercase {{ $detalle->estado == 'pendiente' ? 'text-orange-500' : ($detalle->estado == 'aprobado' ? 'text-green-600' : 'text-red-500') }}">
                                    <option value="pendiente" {{ $detalle->estado == 'pendiente' ? 'selected' : '' }}>Pdte</option>
                                    <option value="aprobado" {{ $detalle->estado == 'aprobado' ? 'selected' : '' }}>Aprob</option>
                                    <option value="rechazado" {{ $detalle->estado == 'rechazado' ? 'selected' : '' }}>Rechaz</option>
                                </select>
                            </td>
                            <td class="text-right font-black text-gray-800 font-mono italic {{ $detalle->estado === 'rechazado' ? 'line-through text-gray-400 opacity-50' : '' }}">
                                Q.{{ number_format($itemTotal, 2) }}
                            </td>
                            <td class="text-right">
                                <button class="btn btn-ghost btn-xs text-gray-300 hover:text-red-500">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="text-center py-10 text-gray-400 italic">No se han registrado repuestos.</td>
                        </tr>
                        @endforelse
                    </tbody>
                    @if($totalParts > 0)
                    <tfoot>
                        <tr>
                            <th colspan="4" class="text-right text-gray-400 uppercase">Subtotal Repuestos</th>
                            <th class="text-right text-xl font-black text-blue-600">Q.{{ number_format($totalParts, 2) }}</th>
                            <th></th>
                        </tr>
                    </tfoot>
                    @endif
                </table>
            </div>
        </div>

    </div>

    <!-- Column 3: Sidebar Details & Actions -->
    <div class="space-y-6">

        <!-- Order Control Card -->
        <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100">
            <h3 class="font-black text-gray-800 text-lg mb-6 flex items-center gap-2">
                <i class="fas fa-cog text-blue-600"></i> Acciones Globales
            </h3>
            <div class="space-y-3">
                <a href="{{ route('panel.operaciones.ordenes_trabajo.print', $orden->id) }}" target="_blank" class="btn btn-outline w-full gap-2 border-gray-200 text-gray-600 hover:bg-gray-50 hover:border-gray-300">
                    <i class="fas fa-print"></i> Imprimir Recepción
                </a>
                <a href="{{ route('panel.operaciones.ordenes_trabajo.edit', $orden->id) }}" class="btn btn-outline w-full gap-2 border-gray-200 text-gray-600 hover:bg-gray-50 hover:border-gray-300">
                    <i class="fas fa-edit"></i> Editar Recepción
                </a>
                <div class="divider"></div>
                <button class="btn btn-primary w-full gap-2 shadow-lg shadow-blue-500/30">
                    <i class="fas fa-save"></i> Guardar Cambios
                </button>
                <button onclick="enviarCotizacion('{{ $orden->cliente->telefono ?? '' }}', '{{ route('panel.operaciones.ordenes_trabajo.print', $orden->id) }}', '{{ $orden->codigo_orden }}')" class="btn btn-warning w-full text-white gap-2 shadow-lg shadow-orange-500/30 font-black italic {{ $orden->detalles->where('estado', 'pendiente')->count() === 0 ? 'opacity-50' : '' }}">
                    <i class="fas fa-paper-plane mr-1 text-xl"></i> Enviar Cotización 
                    @if($orden->detalles->where('estado', 'pendiente')->count() > 0)
                        <span class="badge badge-sm bg-white text-orange-600 border-none ml-1">{{ $orden->detalles->where('estado', 'pendiente')->count() }}</span>
                    @endif
                </button>
                <button
                    class="btn btn-success w-full text-white gap-2 shadow-lg shadow-green-500/30 font-black italic btn-finalizar"
                    {{ $orden->estado == 'finalizada' ? 'disabled' : '' }}>
                    <i class="fas fa-check-double"></i> Finalizar Orden
                </button>
            </div>
        </div>

        <!-- Summary Statistics Card -->
        <div class="bg-gradient-to-br from-blue-900 to-blue-700 p-6 rounded-2xl shadow-xl text-white relative overflow-hidden">
            <div class="relative z-10">
                <h3 class="font-bold text-sm uppercase tracking-widest text-blue-200 mb-4 italic">Resumen de Costos</h3>
                <div class="space-y-4">
                    <div class="flex justify-between items-center">
                        <span class="text-blue-100 text-sm">Repuestos:</span>
                        <span class="font-bold font-mono">Q.{{ number_format($totalParts ?? 0, 2) }}</span>
                    </div>
                    <div class="flex justify-between items-center text-sm">
                        <span class="text-blue-100">Mano de Obra (Est):</span>
                        <span class="font-bold font-mono">Q.0.00</span>
                    </div>
                    <div class="divider border-blue-400 opacity-20 my-2"></div>
                    <div class="flex justify-between items-center pt-2">
                        <span class="text-lg font-black italic uppercase tracking-tighter">Total</span>
                        <span class="text-3xl font-black italic font-mono tracking-tighter">Q.{{ number_format($totalParts ?? 0, 2) }}</span>
                    </div>
                </div>
            </div>
            <i class="fas fa-dollar-sign absolute -bottom-4 -right-4 text-8xl text-white opacity-5 rotate-12"></i>
        </div>

        <!-- Client Info Card -->
        <div class="bg-white p-5 rounded-xl border border-gray-100 shadow-sm">
            <h4 class="font-bold text-gray-700 mb-4 text-xs uppercase tracking-wider">Cliente</h4>
            <div class="flex items-center gap-3">
                <div class="avatar placeholder">
                    <div class="bg-blue-100 text-blue-600 rounded-lg w-10">
                        <span class="text-xs font-bold">{{ substr($orden->cliente->nombre_completo, 0, 1) }}</span>
                    </div>
                </div>
                <div>
                    <p class="font-bold text-sm text-gray-800">{{ $orden->cliente->nombre_completo }}</p>
                    <p class="text-[10px] text-gray-400 font-bold uppercase">{{ $orden->cliente->telefono }}</p>
                </div>
            </div>
        </div>

    </div>

</div>

<!-- MODAL: AGREGAR TAREA -->
<dialog id="modal-task" class="modal modal-bottom sm:modal-middle">
    <div class="modal-box rounded-2xl p-0 overflow-hidden border-none max-w-lg">
        <div class="bg-blue-600 p-6 text-white text-center">
            <h3 class="font-black italic text-2xl uppercase tracking-tighter">Asignar Nueva Tarea</h3>
            <p class="text-blue-100 text-sm font-medium">Define el trabajo y el mecánico responsable</p>
        </div>
        <form id="form-add-task" class="p-6 space-y-4">
            @csrf
            <div>
                <label class="block text-xs font-bold text-gray-500 mb-1 uppercase tracking-widest">Mecánico Responsable</label>
                <select name="user_id" class="select select-bordered w-full bg-gray-50 border-gray-200 focus:border-blue-500 font-bold text-gray-700" required>
                    <option value="" disabled selected>Seleccione un mecánico...</option>
                    @foreach($mecanicos as $mecanico)
                    <option value="{{ $mecanico->id }}">{{ $mecanico->name }} (@foreach($mecanico->roles as $role){{ $role->name }}@endforeach)</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-xs font-bold text-gray-500 mb-1 uppercase tracking-widest">Descripción del Trabajo</label>
                <textarea name="descripcion" class="textarea textarea-bordered w-full bg-gray-50 border-gray-200 focus:border-blue-500 font-medium h-24" placeholder="Ej: Cambio de aceite, revisión de frenos..." required></textarea>
            </div>
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-bold text-gray-500 mb-1 uppercase tracking-widest">Tiempo Estimado (Min)</label>
                    <input type="number" name="meta_minutos" class="input input-bordered w-full bg-gray-50 border-gray-200 font-mono font-bold" value="30" required>
                </div>
                <div>
                    <label class="block text-xs font-bold text-gray-500 mb-1 uppercase tracking-widest">Prioridad</label>
                    <select class="select select-bordered w-full bg-gray-50 border-gray-200 font-bold">
                        <option value="normal">Normal</option>
                        <option value="urgente">Urgente</option>
                        <option value="programado">Programado</option>
                    </select>
                </div>
            </div>
            <div class="modal-action mt-8">
                <button type="button" onclick="document.getElementById('modal-task').close()" class="btn btn-ghost font-bold text-gray-400">Cancelar</button>
                <button type="submit" class="btn btn-primary px-10 font-black italic shadow-lg shadow-blue-500/30">Asignar Tarea</button>
            </div>
        </form>
    </div>
</dialog>

<!-- MODAL: AGREGAR REPUESTO -->
<dialog id="modal-part" class="modal modal-bottom sm:modal-middle">
    <div class="modal-box rounded-2xl p-0 overflow-hidden border-none max-w-lg">
        <div class="bg-orange-600 p-6 text-white text-center">
            <h3 class="font-black italic text-2xl uppercase tracking-tighter">Agregar Repuesto</h3>
            <p class="text-orange-100 text-sm font-medium">Insumos requeridos para la reparación</p>
        </div>
        <form id="form-add-part" class="p-6 space-y-4">
            @csrf
            <div>
                <label class="block text-xs font-bold text-gray-500 mb-1 uppercase tracking-widest">Origen del Repuesto</label>
                <div class="flex gap-2">
                    <button type="button" class="btn-origin flex-1 btn btn-sm btn-outline border-orange-200 text-orange-600 active" data-value="taller">Taller</button>
                    <button type="button" class="btn-origin flex-1 btn btn-sm btn-outline border-green-200 text-green-600" data-value="cliente">Cliente</button>
                    <button type="button" class="btn-origin flex-1 btn btn-sm btn-outline border-blue-200 text-blue-600" data-value="externo">Externo</button>
                    <input type="hidden" name="suministrado_por" value="taller">
                </div>
            </div>
            <div>
                <label class="block text-xs font-bold text-gray-500 mb-1 uppercase tracking-widest">Buscar en Inventario</label>
                <div class="relative">
                    <i class="fas fa-search absolute left-3 top-1/2 -translate-y-1/2 text-gray-400"></i>
                    <input type="text" placeholder="Código o nombre del repuesto..." class="input input-bordered w-full pl-10 bg-gray-50">
                </div>
            </div>
            <div class="divider text-[10px] text-gray-300 uppercase tracking-widest font-bold">O ingresar manualmente</div>
            <div>
                <label class="block text-xs font-bold text-gray-500 mb-1 uppercase tracking-widest">Descripción Manual</label>
                <input type="text" name="descripcion_manual" class="input input-bordered w-full bg-gray-50 border-gray-200 font-bold" placeholder="Especifique el repuesto si no está en stock">
            </div>
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-bold text-gray-500 mb-1 uppercase tracking-widest">Cantidad</label>
                    <input type="number" step="0.01" name="cantidad" class="input input-bordered w-full bg-gray-50 font-mono font-bold" value="1" required>
                </div>
                <div>
                    <label class="block text-xs font-bold text-gray-500 mb-1 uppercase tracking-widest">Precio Unitario</label>
                    <input type="number" step="0.01" name="precio_unitario" class="input input-bordered w-full bg-gray-50 font-mono font-bold" placeholder="0.00" required>
                </div>
            </div>
            <div class="modal-action mt-8">
                <button type="button" onclick="document.getElementById('modal-part').close()" class="btn btn-ghost font-bold text-gray-400">Cancelar</button>
                <button type="submit" class="btn btn-warning px-10 font-black italic shadow-lg shadow-orange-500/30">Agregar</button>
            </div>
        </form>
    </div>
</dialog>

@push('scripts')
@vite(['resources/js/operaciones/ordenes/show.js'])
@endpush

@endsection