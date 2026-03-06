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
                    <!-- Quick anchor to pagos -->
                    <a href="#seccion-pagos" class="btn btn-sm btn-outline border-emerald-200 text-emerald-600 hover:bg-emerald-50 ml-4 shadow-sm hidden md:flex" title="Ir a Pagos y Anticipos">
                        <i class="fas fa-money-bill-wave"></i> Cobros / Saldo
                    </a>
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
                <div class="flex items-center w-full" id="visual-stepper">
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
                        <div class="absolute h-1 w-full {{ in_array($orden->estado, ['en_proceso', 'finalizada', 'entregada']) ? 'bg-blue-600' : 'bg-gray-200' }} top-4 left-1/2 -z-0"></div>
                    </div>

                    <!-- Step 3: Execution -->
                    <div class="flex flex-col items-center flex-1 relative">
                        @php $inProgress = in_array($orden->estado, ['en_proceso', 'finalizada', 'entregada']); @endphp
                        <div class="w-8 h-8 rounded-full {{ $inProgress ? 'bg-blue-600 text-white shadow-lg ring-4 ring-blue-50' : 'bg-gray-200 text-gray-400' }} flex items-center justify-center font-bold text-xs z-10 transition-all duration-500">
                            @if(in_array($orden->estado, ['finalizada', 'entregada'])) <i class="fas fa-check"></i> @else 3 @endif
                        </div>
                        <span class="text-[9px] font-bold mt-2 uppercase {{ $inProgress ? 'text-blue-600' : 'text-gray-400' }}">Reparación</span>
                        <div class="absolute h-1 w-full {{ in_array($orden->estado, ['finalizada', 'entregada']) ? 'bg-blue-600' : 'bg-gray-200' }} top-4 left-1/2 -z-0"></div>
                    </div>

                    <!-- Step 4: Finished -->
                    <div class="flex flex-col items-center flex-1 relative">
                        @php $isFinished = in_array($orden->estado, ['finalizada', 'entregada']); @endphp
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
                                    @if($task->precio_cliente > 0)
                                    <span class="badge badge-xs bg-emerald-100 text-emerald-700 border-none font-bold">Cobro: Q.{{ number_format($task->precio_cliente, 2) }}</span>
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
                            <td class="text-right border-b border-gray-50 rounded-r-xl flex justify-end space-x-1 py-4">
                                <button type="button" class="btn btn-ghost btn-xs text-gray-400 hover:text-blue-500 transition-colors btn-edit-task" onclick="window.openEditTaskModal({{ $task->id }}, {{ $task->mecanico->id }}, `{{ addslashes($task->descripcion) }}`, {{ $task->meta_minutos }}, `{{ $task->precio_cliente }}`, `{{ $task->descuento_cliente }}`, `{{ addslashes($task->motivo_descuento ?? '') }}`, `{{ $task->tipo_pago_mecanico }}`, `{{ $task->valor_pago_mecanico }}`)">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <button type="button" class="btn btn-ghost btn-xs text-gray-300 hover:text-red-500 transition-colors btn-delete-task" data-task-id="{{ $task->id }}">
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
                            <td class="text-right font-black text-gray-800 font-mono italic {{ $detalle->estado === 'rechazado' ? 'line-through text-gray-400 opacity-50' : '' }}" id="item-total-{{ $detalle->id }}">
                                Q.{{ number_format($itemTotal, 2) }}
                            </td>
                            <td class="text-right flex space-x-1 justify-end">
                                <button type="button" class="btn btn-ghost btn-xs text-gray-400 hover:text-blue-500 btn-edit-part" onclick="window.openEditPartModal({{ $detalle->id }}, '{{ $detalle->suministrado_por }}', '{{ addslashes($detalle->repuesto ? $detalle->repuesto->nombre : ($detalle->descripcion_manual ?? '')) }}', {{ $detalle->cantidad }}, {{ $detalle->precio_unitario }})">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <button type="button" class="btn btn-ghost btn-xs text-gray-300 hover:text-red-500 btn-delete-part">
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
                            <th class="text-right text-xl font-black text-blue-600" id="tfoot-total">Q.{{ number_format($totalParts, 2) }}</th>
                            <th></th>
                        </tr>
                    </tfoot>
                    @endif
                </table>
            </div>
        </div>

        @php 
            $totalManoObra = $orden->bitacoras->sum('precio_cliente') - $orden->bitacoras->sum('descuento_cliente');
            $granTotal = ($totalParts ?? 0) + $totalManoObra;
            $totalPagos = $orden->pagos->sum('monto');
            $saldoFaltante = $granTotal - $totalPagos;
        @endphp
        <!-- Section: Payments (Pagos) -->
        <div id="seccion-pagos" class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden mt-6">
            <div class="p-5 border-b border-gray-50 flex flex-col sm:flex-row justify-between items-center bg-gray-50/50 gap-4">
                <div>
                    <h3 class="font-bold text-gray-700 flex items-center gap-2">
                        <i class="fas fa-money-bill-wave text-emerald-500"></i> Pagos y Anticipos
                    </h3>
                    <p class="text-xs text-gray-400 font-bold uppercase tracking-widest mt-1">Saldo pendiente: 
                        <span class="text-{{ $saldoFaltante <= 0 ? 'green' : 'red' }}-500 font-black font-mono">Q.{{ number_format(max(0, $saldoFaltante), 2) }}</span>
                    </p>
                </div>
                <button onclick="document.getElementById('modal-pago').showModal()" class="btn btn-sm btn-outline btn-success gap-2">
                    <i class="fas fa-plus"></i> Registrar Pago
                </button>
            </div>
            <div class="overflow-x-auto p-4 md:p-0">
                <table class="table w-full">
                    <thead>
                        <tr class="text-gray-400 text-xs uppercase">
                            <th>Fecha</th>
                            <th>Método de Pago</th>
                            <th class="text-right">Monto</th>
                            <th class="text-right w-20">Acciones</th>
                        </tr>
                    </thead>
                    <tbody id="pagos-table-body">
                        @forelse($orden->pagos as $pago)
                        <tr class="hover:bg-gray-50 transition-colors">
                            <td class="font-bold text-sm text-gray-700">{{ $pago->created_at->format('d/m/Y H:i') }}</td>
                            <td>
                                <span class="badge badge-sm font-bold text-[10px] uppercase bg-emerald-100 text-emerald-700 border-none">{{ $pago->metodo_pago }}</span>
                            </td>
                            <td class="text-right font-black text-emerald-600 font-mono italic">
                                Q.{{ number_format($pago->monto, 2) }}
                            </td>
                            <td class="text-right border-b border-gray-50 rounded-r-xl flex justify-end space-x-1">
                                <button type="button" class="btn btn-ghost btn-xs text-gray-400 hover:text-emerald-500 transition-colors btn-edit-pago" onclick="window.openEditPagoModal({{ $pago->id }}, {{ $pago->monto }}, '{{ $pago->metodo_pago }}')">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <button type="button" class="btn btn-ghost btn-xs text-gray-300 hover:text-red-500 transition-colors btn-delete-pago" data-pago-id="{{ $pago->id }}">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="4" class="text-center py-10 text-gray-400 italic">No se han registrado pagos.</td>
                        </tr>
                        @endforelse
                    </tbody>
                    @if($totalPagos > 0)
                    <tfoot>
                        <tr>
                            <th colspan="2" class="text-right text-gray-400 uppercase">Subtotal Pagos</th>
                            <th class="text-right text-xl font-black text-emerald-600">Q.{{ number_format($totalPagos, 2) }}</th>
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
                <button type="button" onclick="document.getElementById('modal-pdf').showModal()" class="btn btn-outline w-full gap-2 border-gray-200 text-gray-600 hover:bg-gray-50 hover:border-gray-300">
                    <i class="fas fa-print"></i> Imprimir Recepción
                </button>
                <a href="{{ route('panel.operaciones.ordenes_trabajo.edit', $orden->id) }}" class="btn btn-outline w-full gap-2 border-gray-200 text-gray-600 hover:bg-gray-50 hover:border-gray-300">
                    <i class="fas fa-edit"></i> Editar Recepción
                </a>
                <div class="divider"></div>
                <button onclick="guardarDiagnostico()" class="btn btn-primary w-full gap-2 shadow-lg shadow-blue-500/30">
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
                    {{ $orden->estado == 'finalizada' || $orden->estado == 'entregada' ? 'style=display:none;' : '' }}>
                    <i class="fas fa-check-double"></i> Finalizar Reparación
                </button>
                <button
                    class="btn bg-indigo-600 hover:bg-indigo-700 w-full text-white gap-2 shadow-lg shadow-indigo-500/30 font-black italic btn-entregar"
                    {{ $orden->estado != 'finalizada' ? 'style=display:none;' : '' }}>
                    <i class="fas fa-key"></i> Entregar Vehículo al Cliente
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
                        <span class="font-bold font-mono" id="sidebar-total-parts">Q.{{ number_format($totalParts ?? 0, 2) }}</span>
                    </div>
                    @php 
                        // totalManoObra and granTotal already calculated above
                    @endphp
                    <div class="flex justify-between items-center text-sm">
                        <span class="text-blue-100">Mano de Obra (Est):</span>
                        <span class="font-bold font-mono">Q.{{ number_format($totalManoObra, 2) }}</span>
                    </div>
                    <div class="divider border-blue-400 opacity-20 my-2"></div>
                    <div class="flex justify-between items-center pt-2">
                        <span class="text-lg font-black italic uppercase tracking-tighter">Total Final</span>
                        <span class="text-3xl font-black italic font-mono tracking-tighter" id="sidebar-total-main">Q.{{ number_format($granTotal, 2) }}</span>
                    </div>
                    @if(isset($totalPagos) && $totalPagos > 0)
                    <div class="flex justify-between items-center text-sm pt-2">
                        <span class="text-emerald-300">Abonado:</span>
                        <span class="font-bold font-mono text-emerald-300" id="sidebar-total-pagos">Q.{{ number_format($totalPagos, 2) }}</span>
                    </div>
                    <div class="flex justify-between items-center pt-2">
                        <span class="text-lg font-black italic uppercase tracking-tighter text-yellow-300">Saldo</span>
                        <span class="text-2xl font-black italic font-mono tracking-tighter text-yellow-300" id="sidebar-saldo">Q.{{ number_format($granTotal - $totalPagos, 2) }}</span>
                    </div>
                    @else
                    <div class="flex justify-between items-center pt-2" style="display: none;" id="saldo-box">
                        <span class="text-lg font-black italic uppercase tracking-tighter text-yellow-300">Saldo</span>
                        <span class="text-2xl font-black italic font-mono tracking-tighter text-yellow-300" id="sidebar-saldo">Q.{{ number_format($granTotal, 2) }}</span>
                    </div>
                    @endif
                </div>
            </div>
            <i class="fas fa-dollar-sign absolute -bottom-4 -right-4 text-8xl text-white opacity-5 rotate-12"></i>
        </div>

        <!-- Client Info Card -->
        <div class="bg-white p-5 rounded-2xl border border-gray-100 shadow-sm flex flex-col gap-4">
            <h4 class="font-bold text-gray-700 text-xs uppercase tracking-wider border-b pb-2"><i class="fas fa-user-circle mr-1 text-blue-500"></i> Información del Cliente</h4>
            <div class="flex items-center gap-3">
                <div class="avatar placeholder">
                    <div class="bg-blue-100 text-blue-600 rounded-lg w-12 h-12 shadow-inner">
                        <span class="text-xl font-bold">{{ substr($orden->cliente->nombre_completo, 0, 1) }}</span>
                    </div>
                </div>
                <div>
                    <p class="font-black text-sm text-gray-800 tracking-tight">{{ $orden->cliente->nombre_completo }}</p>
                    <p class="text-[10px] text-gray-400 font-bold uppercase"><i class="fas fa-phone mr-1"></i> {{ $orden->cliente->telefono }}</p>
                </div>
            </div>
            <div class="bg-gray-50 rounded-xl p-3 space-y-2 mt-1 border border-gray-100">
                @if($orden->cliente->email)
                <div class="flex justify-between items-center text-xs">
                    <span class="text-gray-400 font-bold uppercase tracking-widest text-[9px]">Correo:</span>
                    <span class="font-medium text-gray-700 truncate max-w-[150px]" title="{{ $orden->cliente->email }}">{{ $orden->cliente->email }}</span>
                </div>
                @endif
                @if($orden->cliente->nit)
                <div class="flex justify-between items-center text-xs border-t border-gray-200/50 pt-2">
                    <span class="text-gray-400 font-bold uppercase tracking-widest text-[9px]">NIT:</span>
                    <span class="font-medium text-gray-700">{{ $orden->cliente->nit }}</span>
                </div>
                @endif
                @if($orden->cliente->direccion)
                <div class="flex justify-between items-start text-xs border-t border-gray-200/50 pt-2">
                    <span class="text-gray-400 font-bold uppercase tracking-widest text-[9px] mt-0.5">Dirección:</span>
                    <span class="font-medium text-gray-700 text-right max-w-[150px] leading-tight">{{ $orden->cliente->direccion }}</span>
                </div>
                @endif
            </div>

            <h4 class="font-bold text-gray-700 text-xs uppercase tracking-wider border-b pb-2 mt-2"><i class="fas fa-car mr-1 text-orange-500"></i> Sobre el Vehículo</h4>
            @php 
                $fotoVehiculo = $orden->archivos->where('tipo', 'recepcion')->first();
            @endphp
            @if($fotoVehiculo)
            <div class="rounded-xl overflow-hidden mb-2 shadow-sm border border-gray-100 relative group">
                <img src="{{ asset($fotoVehiculo->url) }}" alt="Foto Vehículo" class="w-full h-32 object-cover transition-transform duration-500 group-hover:scale-110 cursor-pointer" onclick="window.open('{{ asset($fotoVehiculo->url) }}', '_blank')">
                <div class="absolute inset-x-0 bottom-0 bg-gradient-to-t from-gray-900/80 p-2 text-white text-[9px] font-bold uppercase tracking-widest opacity-0 group-hover:opacity-100 transition-opacity">Ver Foto Completa</div>
            </div>
            @else
            <div class="bg-gray-50 rounded-xl p-6 text-center border border-gray-100 mb-2 border-dashed">
                <i class="fas fa-camera-retro text-gray-300 text-3xl mb-1 mt-2 block"></i>
                <p class="text-[9px] uppercase font-bold text-gray-400 tracking-widest">Sin fotografía de Recepción</p>
            </div>
            @endif
            <div class="grid grid-cols-2 gap-2 text-xs">
                <div class="bg-gray-50 p-2 rounded-lg border border-gray-100">
                    <span class="block text-[9px] font-bold text-gray-400 uppercase tracking-widest">Placa</span>
                    <span class="font-black text-gray-800">{{ $orden->vehiculo->placa }}</span>
                </div>
                <div class="bg-gray-50 p-2 rounded-lg border border-gray-100">
                    <span class="block text-[9px] font-bold text-gray-400 uppercase tracking-widest">Año/Color</span>
                    <span class="font-bold text-gray-700">{{ $orden->vehiculo->anio }} • {{ $orden->vehiculo->color }}</span>
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
        <form id="form-add-task" class="p-6 space-y-4 overflow-y-auto max-h-[60vh] custom-scrollbar">
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
            <div class="divider text-[10px] text-gray-300 uppercase tracking-widest font-bold">Costos y Cobros</div>
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-bold text-emerald-600 mb-1 uppercase tracking-widest">Cobro al Cliente (Q) *</label>
                    <input type="number" step="0.01" min="0" name="precio_cliente" class="input input-bordered w-full font-mono text-emerald-600 font-bold bg-white" placeholder="0.00" value="0.00" required>
                </div>
                <div>
                    <label class="block text-xs font-bold text-gray-500 mb-1 uppercase tracking-widest">Desc. al Cliente (Q)</label>
                    <input type="number" step="0.01" min="0" name="descuento_cliente" class="input input-bordered w-full font-mono font-bold bg-gray-50 focus:bg-white" placeholder="0.00" value="0.00">
                </div>
            </div>
            <div>
                <label class="block text-xs font-bold text-gray-500 mb-1 uppercase tracking-widest">Motivo de Descuento (Opcional)</label>
                <input type="text" name="motivo_descuento" class="input input-bordered input-sm w-full bg-gray-50" placeholder="Ej: Cliente especial, Paquete, etc.">
            </div>
            
            <div class="bg-blue-50/50 p-4 rounded-xl border border-blue-100">
                <label class="block text-xs font-bold text-blue-700 mb-3 uppercase tracking-widest border-b border-blue-200 pb-2"><i class="fas fa-hand-holding-usd mr-1"></i> Remuneración a Mecánico</label>
                <div class="flex gap-4">
                    <div class="flex-1">
                        <label class="block text-[10px] font-bold text-gray-500 mb-1 uppercase tracking-widest">Tipo Pago</label>
                        <select name="tipo_pago_mecanico" class="select select-bordered select-sm w-full bg-white font-bold text-gray-700">
                            <option value="porcentaje">% Porcentaje</option>
                            <option value="fijo">Monto Fijo (Q)</option>
                        </select>
                    </div>
                    <div class="flex-1">
                        <label class="block text-[10px] font-bold text-gray-500 mb-1 uppercase tracking-widest">Valor/Monto</label>
                        <input type="number" step="0.01" min="0" name="valor_pago_mecanico" class="input input-bordered input-sm w-full font-mono font-bold bg-white" placeholder="Ej: 30" value="0" required>
                    </div>
                </div>
            </div>
            <div class="modal-action mt-8">
                <button type="button" onclick="document.getElementById('modal-task').close()" class="btn btn-ghost font-bold text-gray-400">Cancelar</button>
                <button type="submit" class="btn btn-primary px-10 font-black italic shadow-lg shadow-blue-500/30">Asignar Tarea</button>
            </div>
        </form>
    </div>
</dialog>

<!-- MODAL: EDITAR TAREA -->
<dialog id="modal-edit-task" class="modal modal-bottom sm:modal-middle">
    <div class="modal-box rounded-2xl p-0 overflow-hidden border-none max-w-lg">
        <div class="bg-blue-600 p-6 text-white text-center">
            <h3 class="font-black italic text-2xl uppercase tracking-tighter">Editar Tarea</h3>
            <p class="text-blue-100 text-sm font-medium">Modifique los detalles de la labor</p>
        </div>
        <form id="form-edit-task" class="p-6 space-y-4 overflow-y-auto max-h-[60vh] custom-scrollbar">
            @csrf
            @method('PUT')
            <input type="hidden" id="edit-task-id" name="task_id">
            <div>
                <label class="block text-xs font-bold text-gray-500 mb-1 uppercase tracking-widest">Mecánico Responsable</label>
                <select id="edit-task-user_id" name="user_id" class="select select-bordered w-full bg-gray-50 border-gray-200 focus:border-blue-500 font-bold text-gray-700" required>
                    <option value="" disabled>Seleccione un mecánico...</option>
                    @foreach($mecanicos as $mecanico)
                    <option value="{{ $mecanico->id }}">{{ $mecanico->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-xs font-bold text-gray-500 mb-1 uppercase tracking-widest">Descripción del Trabajo</label>
                <textarea id="edit-task-descripcion" name="descripcion" class="textarea textarea-bordered w-full bg-gray-50 border-gray-200 focus:border-blue-500 font-medium h-24" required></textarea>
            </div>
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-bold text-gray-500 mb-1 uppercase tracking-widest">Tiempo (Min)</label>
                    <input type="number" id="edit-task-meta_minutos" name="meta_minutos" class="input input-bordered w-full bg-gray-50 border-gray-200 font-mono font-bold" required>
                </div>
            </div>
            <div class="divider text-[10px] text-gray-300 uppercase tracking-widest font-bold">Costos y Cobros</div>
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-bold text-emerald-600 mb-1 uppercase tracking-widest">Cobro al Cliente (Q) *</label>
                    <input type="number" step="0.01" min="0" id="edit-task-precio" name="precio_cliente" class="input input-bordered w-full font-mono text-emerald-600 font-bold bg-white" required>
                </div>
                <div>
                    <label class="block text-xs font-bold text-gray-500 mb-1 uppercase tracking-widest">Desc. Cliente (Q)</label>
                    <input type="number" step="0.01" min="0" id="edit-task-descuento" name="descuento_cliente" class="input input-bordered w-full font-mono font-bold bg-gray-50 focus:bg-white">
                </div>
            </div>
            <div>
                <label class="block text-xs font-bold text-gray-500 mb-1 uppercase tracking-widest">Motivo Descuento</label>
                <input type="text" id="edit-task-motivo_descuento" name="motivo_descuento" class="input input-bordered input-sm w-full bg-gray-50">
            </div>
            
            <div class="bg-blue-50/50 p-4 rounded-xl border border-blue-100">
                <label class="block text-xs font-bold text-blue-700 mb-3 uppercase tracking-widest border-b border-blue-200 pb-2"><i class="fas fa-hand-holding-usd mr-1"></i> Remuneración a Mecánico</label>
                <div class="flex gap-4">
                    <div class="flex-1">
                        <label class="block text-[10px] font-bold text-gray-500 mb-1 uppercase tracking-widest">Tipo Pago</label>
                        <select id="edit-task-tipo_pago" name="tipo_pago_mecanico" class="select select-bordered select-sm w-full bg-white font-bold text-gray-700">
                            <option value="porcentaje">% Porcentaje</option>
                            <option value="fijo">Monto Fijo (Q)</option>
                        </select>
                    </div>
                    <div class="flex-1">
                        <label class="block text-[10px] font-bold text-gray-500 mb-1 uppercase tracking-widest">Valor/Monto</label>
                        <input type="number" step="0.01" min="0" id="edit-task-valor_pago" name="valor_pago_mecanico" class="input input-bordered input-sm w-full font-mono font-bold bg-white" required>
                    </div>
                </div>
            </div>
            <div class="modal-action mt-8">
                <button type="button" onclick="document.getElementById('modal-edit-task').close()" class="btn btn-ghost font-bold text-gray-400">Cancelar</button>
                <button type="submit" class="btn btn-primary px-10 font-black italic shadow-lg shadow-blue-500/30">Guardar Cambios</button>
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
                <div class="flex gap-2 w-full">
                    <div class="relative w-full">
                        <i class="fas fa-search absolute left-3 top-1/2 -translate-y-1/2 text-gray-400"></i>
                        <input type="text" id="busqueda-repuesto" placeholder="Código o nombre del repuesto..." class="input input-bordered w-full pl-10 bg-gray-50 search-repuesto" autocomplete="off">
                        <input type="hidden" name="repuesto_id" id="repuesto_id_hidden">
                        <ul id="lista-sugerencias-repuestos" class="absolute z-50 w-full bg-white border border-gray-200 shadow-2xl rounded-xl mt-1 max-h-60 overflow-y-auto hidden">
                            <!-- SUGERENCIAS DINAMICAS UI -->
                        </ul>
                    </div>
                    <button type="button" class="btn bg-blue-50 text-blue-600 hover:bg-blue-600 hover:text-white border-blue-100 shadow-sm flex-shrink-0" onclick="document.getElementById('modal-fast-repuesto').showModal()" title="Crear en inventario rápido">
                        <i class="fas fa-plus"></i> <span class="hidden sm:inline">Nuevo</span>
                    </button>
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

<!-- MODAL: EDITAR REPUESTO -->
<dialog id="modal-edit-part" class="modal modal-bottom sm:modal-middle">
    <div class="modal-box rounded-2xl p-0 overflow-hidden border-none max-w-lg">
        <div class="bg-blue-600 p-6 text-white text-center">
            <h3 class="font-black italic text-2xl uppercase tracking-tighter">Editar Repuesto</h3>
            <p class="text-blue-100 text-sm font-medium">Modifique los detalles del repuesto</p>
        </div>
        <form id="form-edit-part" class="p-6 space-y-4">
            @csrf
            @method('PUT')
            <input type="hidden" name="detail_id" id="edit_part_id">
            <div>
                <label class="block text-xs font-bold text-gray-500 mb-1 uppercase tracking-widest">Origen del Repuesto</label>
                <div class="flex gap-2">
                    <button type="button" class="btn-origin-edit flex-1 btn btn-sm btn-outline border-orange-200 text-orange-600 active" data-value="taller">Taller</button>
                    <button type="button" class="btn-origin-edit flex-1 btn btn-sm btn-outline border-green-200 text-green-600" data-value="cliente">Cliente</button>
                    <button type="button" class="btn-origin-edit flex-1 btn btn-sm btn-outline border-blue-200 text-blue-600" data-value="externo">Externo</button>
                    <input type="hidden" name="suministrado_por" id="edit_suministrado_por" value="taller">
                </div>
            </div>
            <div class="divider text-[10px] text-gray-300 uppercase tracking-widest font-bold">Descripción</div>
            <div>
                <label class="block text-xs font-bold text-gray-500 mb-1 uppercase tracking-widest">Descripción Manual</label>
                <input type="text" name="descripcion_manual" id="edit_descripcion_manual" class="input input-bordered w-full bg-gray-50 border-gray-200 font-bold" required>
            </div>
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-bold text-gray-500 mb-1 uppercase tracking-widest">Cantidad</label>
                    <input type="number" step="0.01" name="cantidad" id="edit_cantidad" class="input input-bordered w-full bg-gray-50 font-mono font-bold" required>
                </div>
                <div>
                    <label class="block text-xs font-bold text-gray-500 mb-1 uppercase tracking-widest">Precio Unitario</label>
                    <input type="number" step="0.01" name="precio_unitario" id="edit_precio_unitario" class="input input-bordered w-full bg-gray-50 font-mono font-bold" required>
                </div>
            </div>
            <div class="modal-action mt-8">
                <button type="button" onclick="document.getElementById('modal-edit-part').close()" class="btn btn-ghost font-bold text-gray-400">Cancelar</button>
                <button type="submit" class="btn btn-primary px-10 font-black italic shadow-lg shadow-blue-500/30">Guardar Cambios</button>
            </div>
        </form>
    </div>
</dialog>

<!-- MODAL: CREAR REPUESTO RAPIDO -->
<dialog id="modal-fast-repuesto" class="modal modal-bottom sm:modal-middle">
    <div class="modal-box p-0 overflow-hidden bg-white rounded-3xl max-w-lg">
        <div class="bg-gradient-to-r from-blue-600 to-blue-800 p-6 flex justify-between items-center text-white">
            <h3 class="font-black text-xl flex items-center gap-3">
                <div class="w-10 h-10 rounded-full bg-white/20 flex items-center justify-center backdrop-blur-sm shadow-inner">
                    <i class="fas fa-box-open text-blue-100"></i>
                </div>
                Crear Repuesto
            </h3>
            <button class="text-white/60 hover:text-white transition-colors" onclick="document.getElementById('modal-fast-repuesto').close()">
                <i class="fas fa-times text-xl"></i>
            </button>
        </div>
        <div class="p-6 bg-gray-50">
            <p class="text-xs text-blue-700 mb-5 bg-blue-50 p-3 rounded-xl border border-blue-100/50 font-medium">
                <i class="fas fa-bolt text-yellow-500 mr-2 drop-shadow-sm"></i>Esta pieza se registrará directamente en el almacén del taller con su cantidad inicial.
            </p>
            <form id="form-fast-repuesto">
                <div class="space-y-4">
                    <div>
                        <label class="block text-xs font-bold text-gray-500 mb-1 uppercase tracking-widest">Nombre de Repuesto *</label>
                        <input type="text" id="fast_nombre" class="input input-bordered w-full font-bold text-gray-700 bg-white" placeholder="Ej: Pastillas de Freno Premium" required>
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-bold text-gray-500 mb-1 uppercase tracking-widest text-emerald-600">P. Venta Público (Q) *</label>
                            <input type="number" step="0.01" id="fast_precio" min="0" class="input input-bordered w-full font-mono text-emerald-600 font-bold bg-white" placeholder="0.00" required>
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-gray-500 mb-1 uppercase tracking-widest text-blue-600">Unidades Físicas *</label>
                            <input type="number" id="fast_stock" min="1" class="input input-bordered w-full font-mono font-bold bg-white" value="1" required>
                        </div>
                    </div>
                </div>
                <div class="modal-action mt-6 border-t border-gray-100 pt-4 flex justify-between items-center">
                    <button type="button" class="btn btn-ghost text-gray-500 px-6 font-bold hover:bg-gray-200" onclick="document.getElementById('modal-fast-repuesto').close()">Cancelar</button>
                    <button type="submit" class="btn bg-blue-600 hover:bg-blue-700 border-none text-white px-8 font-black shadow-lg shadow-blue-500/30">Guardar Pieza</button>
                </div>
            </form>
        </div>
    </div>
    <form method="dialog" class="modal-backdrop">
        <button>close</button>
    </form>
</dialog>

<!-- MODAL: REGISTRAR PAGO -->
<dialog id="modal-pago" class="modal modal-bottom sm:modal-middle">
    <div class="modal-box p-0 overflow-hidden bg-white rounded-3xl max-w-sm">
        <div class="bg-gradient-to-r from-emerald-500 to-emerald-700 p-6 flex justify-between items-center text-white">
            <h3 class="font-black text-xl flex items-center gap-3">
                <div class="w-10 h-10 rounded-full bg-white/20 flex items-center justify-center backdrop-blur-sm shadow-inner">
                    <i class="fas fa-money-bill-wave text-emerald-100"></i>
                </div>
                Registrar Cobro
            </h3>
            <button class="text-white/60 hover:text-white transition-colors" onclick="document.getElementById('modal-pago').close()">
                <i class="fas fa-times text-xl"></i>
            </button>
        </div>
        <div class="p-6 bg-gray-50">
            <form id="form-pago">
                @csrf
                <div class="space-y-4">
                    <div>
                        <label class="block text-xs font-bold text-gray-500 mb-1 uppercase tracking-widest text-emerald-600">Monto del Pago (Q) *</label>
                        <input type="number" step="0.01" id="pago_monto" name="monto" min="0" class="input input-bordered w-full font-mono text-emerald-600 font-bold bg-white text-xl" placeholder="0.00" value="{{ isset($granTotal) && isset($totalPagos) ? number_format($granTotal - $totalPagos, 2, '.', '') : '0.00' }}" required>
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-gray-500 mb-1 uppercase tracking-widest">Método de Pago *</label>
                        <select name="metodo_pago" id="pago_metodo" class="select select-bordered w-full font-bold text-gray-700 bg-white" required>
                            <option value="Efectivo">Efectivo</option>
                            <option value="Transferencia">Transferencia</option>
                            <option value="Tarjeta">Tarjeta (Crédito/Débito)</option>
                            <option value="Cheque">Cheque</option>
                            <option value="Otro">Otro</option>
                        </select>
                    </div>
                </div>
                <div class="modal-action mt-6 border-t border-gray-100 pt-4 flex justify-between items-center">
                    <button type="button" class="btn btn-ghost text-gray-500 px-6 font-bold hover:bg-gray-200" onclick="document.getElementById('modal-pago').close()">Cancelar</button>
                    <button type="submit" class="btn bg-emerald-600 hover:bg-emerald-700 border-none text-white px-8 font-black shadow-lg shadow-emerald-500/30">Guardar</button>
                </div>
            </form>
        </div>
    </div>
    <form method="dialog" class="modal-backdrop">
        <button>close</button>
    </form>
</dialog>

<!-- MODAL: EDITAR PAGO -->
<dialog id="modal-edit-pago" class="modal modal-bottom sm:modal-middle">
    <div class="modal-box p-0 overflow-hidden bg-white rounded-3xl max-w-sm">
        <div class="bg-gradient-to-r from-emerald-600 to-teal-700 p-6 flex justify-between items-center text-white">
            <h3 class="font-black text-xl flex items-center gap-3">
                <i class="fas fa-edit text-emerald-100"></i> Modificar Pago
            </h3>
            <button class="text-white/60 hover:text-white transition-colors" onclick="document.getElementById('modal-edit-pago').close()">
                <i class="fas fa-times text-xl"></i>
            </button>
        </div>
        <div class="p-6 bg-gray-50">
            <form id="form-edit-pago">
                @csrf
                @method('PUT')
                <input type="hidden" name="pago_id" id="edit_pago_id">
                <div class="space-y-4">
                    <div>
                        <label class="block text-xs font-bold text-gray-500 mb-1 uppercase tracking-widest text-emerald-600">Monto del Pago (Q) *</label>
                        <input type="number" step="0.01" id="edit_pago_monto" name="monto" min="0" class="input input-bordered w-full font-mono text-emerald-600 font-bold bg-white text-xl" required>
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-gray-500 mb-1 uppercase tracking-widest">Método de Pago *</label>
                        <select name="metodo_pago" id="edit_pago_metodo" class="select select-bordered w-full font-bold text-gray-700 bg-white" required>
                            <option value="Efectivo">Efectivo</option>
                            <option value="Transferencia">Transferencia</option>
                            <option value="Tarjeta">Tarjeta (Crédito/Débito)</option>
                            <option value="Cheque">Cheque</option>
                            <option value="Otro">Otro</option>
                        </select>
                    </div>
                </div>
                <div class="modal-action mt-6 border-t border-gray-100 pt-4 flex justify-between items-center">
                    <button type="button" class="btn btn-ghost text-gray-500 px-6 font-bold hover:bg-gray-200" onclick="document.getElementById('modal-edit-pago').close()">Cancelar</button>
                    <button type="submit" class="btn bg-emerald-600 hover:bg-emerald-700 border-none text-white px-8 font-black shadow-lg shadow-emerald-500/30">Actualizar</button>
                </div>
            </form>
        </div>
    </div>
    <form method="dialog" class="modal-backdrop">
        <button>close</button>
    </form>
</dialog>

<!-- MODAL: VISOR PDF -->
<dialog id="modal-pdf" class="modal">
    <div class="modal-box w-11/12 max-w-5xl h-[90vh] p-0 flex flex-col rounded-2xl overflow-hidden bg-gray-100">
        <!-- Encabezado del Modal -->
        <div class="bg-gray-800 p-4 flex justify-between items-center">
            <div class="flex items-center gap-3">
                <i class="fas fa-file-pdf text-red-500 text-2xl"></i>
                <h3 class="font-bold text-white text-lg">Recepción de Orden #{{ $orden->codigo_orden }}</h3>
            </div>
            <div class="flex items-center gap-2">
                <a href="{{ route('panel.operaciones.ordenes_trabajo.print', $orden->id) }}" target="_blank" class="btn btn-sm btn-ghost text-white border border-gray-600 hover:bg-gray-700">
                    <i class="fas fa-external-link-alt"></i> Pantalla Completa
                </a>
                <button class="btn btn-sm btn-circle btn-ghost text-white/50 hover:bg-gray-700 hover:text-white" onclick="document.getElementById('modal-pdf').close()">
                    <i class="fas fa-times text-xl"></i>
                </button>
            </div>
        </div>
        
        <!-- Contenedor del Iframe -->
        <div class="flex-grow bg-gray-300 w-full relative">
            <iframe 
                src="{{ route('panel.operaciones.ordenes_trabajo.print', $orden->id) }}?preview=true" 
                class="absolute inset-0 w-full h-full border-none shadow-inner bg-white"
                title="Visor PDF">
            </iframe>
        </div>
    </div>
    <form method="dialog" class="modal-backdrop bg-gray-900/80">
        <button>close</button>
    </form>
</dialog>

@push('scripts')
@vite(['resources/js/operaciones/ordenes/show.js'])
@endpush

@endsection