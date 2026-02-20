@extends('layouts.panel')

@section('title', 'Planificación de Orden')
@section('subtitle', 'Gestionar tareas, mecánicos y repuestos para #' . $orden->codigo_orden)

@section('content')
<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

    <!-- Column 1 & 2: Main Planning -->
    <div class="lg:col-span-2 space-y-6">

        <!-- Order Header Summary -->
        <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100 flex justify-between items-center">
            <div class="flex items-center gap-4">
                <div class="w-14 h-14 bg-blue-50 text-blue-600 rounded-xl flex items-center justify-center text-2xl">
                    <i class="fas fa-file-invoice"></i>
                </div>
                <div>
                    <h2 class="text-2xl font-black text-gray-800">{{ $orden->codigo_orden }}</h2>
                    <p class="text-gray-500 font-medium">{{ $orden->vehiculo->marca->nombre }} {{ $orden->vehiculo->modelo->nombre }} • {{ $orden->vehiculo->placa }}</p>
                </div>
            </div>
            <div class="text-right">
                <span class="badge badge-lg {{ $orden->estado == 'abierta' ? 'badge-info' : 'badge-primary' }} uppercase font-bold">{{ $orden->estado }}</span>
                <p class="text-xs text-gray-400 mt-1 uppercase font-bold tracking-widest">Estado Actual</p>
            </div>
        </div>

        <!-- Section: Tasks / Labor -->
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
            <div class="p-5 border-b border-gray-50 flex justify-between items-center bg-gray-50/50">
                <h3 class="font-bold text-gray-700 flex items-center gap-2">
                    <i class="fas fa-tools text-blue-500"></i> Mano de Obra y Tareas
                </h3>
                <button onclick="document.getElementById('modal-task').showModal()" class="btn btn-sm btn-primary gap-2">
                    <i class="fas fa-plus"></i> Asignar Tarea
                </button>
            </div>
            <div class="overflow-x-auto">
                <table class="table w-full">
                    <thead>
                        <tr class="text-gray-400 text-xs uppercase">
                            <th>Descripción</th>
                            <th>Mecánico</th>
                            <th>Tiempo Est.</th>
                            <th>Estado</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody id="tasks-table-body">
                        @forelse($orden->bitacoras as $task)
                        <tr class="hover:bg-gray-50 transition-colors">
                            <td>
                                <div class="font-bold text-gray-700 text-sm">{{ $task->descripcion }}</div>
                                <div class="text-[10px] text-gray-400 uppercase font-bold">{{ $task->tipo_actividad }}</div>
                            </td>
                            <td>
                                <div class="flex items-center gap-2">
                                    <div class="avatar placeholder">
                                        <div class="bg-neutral text-neutral-content rounded-full w-6">
                                            <span class="text-[10px]">{{ substr($task->mecanico->name ?? 'M', 0, 1) }}</span>
                                        </div>
                                    </div>
                                    <span class="text-sm font-medium">{{ $task->mecanico->name ?? 'Sin asignar' }}</span>
                                </div>
                            </td>
                            <td>
                                <span class="badge badge-ghost font-mono text-xs">{{ $task->meta_minutos }} min</span>
                            </td>
                            <td>
                                <span class="badge badge-sm uppercase font-bold text-[9px] {{ $task->estado == 'completado' ? 'badge-success' : 'badge-warning' }}">
                                    {{ $task->estado }}
                                </span>
                            </td>
                            <td class="text-right">
                                <button class="btn btn-ghost btn-xs text-gray-300 hover:text-red-500">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="5" class="text-center py-10 text-gray-400 italic">No hay tareas asignadas aún.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
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
                            <th class="text-right">Total</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody id="parts-table-body">
                        @php $totalParts = 0; @endphp
                        @forelse($orden->detalles as $detalle)
                        @php $itemTotal = $detalle->cantidad * $detalle->precio_unitario; $totalParts += $itemTotal; @endphp
                        <tr class="hover:bg-gray-50 transition-colors">
                            <td>
                                <div class="font-bold text-gray-700 text-sm">
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
                            <td class="text-right font-black text-gray-800 font-mono italic">Q.{{ number_format($itemTotal, 2) }}</td>
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
                <button class="btn btn-success w-full text-white gap-2 shadow-lg shadow-green-500/30 font-black italic">
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