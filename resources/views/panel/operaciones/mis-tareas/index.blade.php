@extends('layouts.panel')

@section('title', $isAdmin ? 'Tareas de Personal' : 'Mi Tablero de Tareas')
@section('subtitle', 'Gestión de actividades operativas ' . ($isAdmin ? 'de los colaboradores' : 'diarias'))

@section('content')
    <div class="h-full flex flex-col gap-6">

        <!-- Fila Superior: Título y Selector de Administrador -->
        @if($isAdmin || session('success') || session('error'))
            <div
                class="flex flex-col md:flex-row justify-between items-center gap-4 bg-white p-4 rounded-xl shadow-sm border border-gray-100">
                <div class="flex-1 w-full">
                    @if(session('success'))
                        <div role="alert" class="alert alert-success shadow-sm rounded-lg py-2">
                            <i class="fas fa-check-circle"></i>
                            <span class="text-sm font-medium">{{ session('success') }}</span>
                        </div>
                    @endif
                    @if(session('error'))
                        <div role="alert" class="alert alert-error shadow-sm rounded-lg py-2">
                            <i class="fas fa-exclamation-triangle"></i>
                            <span class="text-sm font-medium">{{ session('error') }}</span>
                        </div>
                    @endif
                </div>

                @if($isAdmin && $mecanicos->count() > 0)
                    <div class="flex items-center gap-3 w-full md:w-auto bg-slate-50 p-2 rounded-lg border border-slate-200">
                        <i class="fas fa-user-cog text-slate-400 pl-2"></i>
                        <span class="text-sm font-bold text-slate-700 whitespace-nowrap">Ver tareas de:</span>
                        <form action="{{ route('panel.mis_tareas.index') }}" method="GET" class="m-0">
                            <select name="worker_id" class="select select-sm select-bordered bg-white w-full max-w-xs font-medium"
                                onchange="this.form.submit()">
                                @foreach($mecanicos as $mecanico)
                                    <option value="{{ $mecanico->id }}" {{ ($selectedUser->id ?? 0) == $mecanico->id ? 'selected' : '' }}>
                                        {{ $mecanico->persona ? $mecanico->persona->nombres . ' ' . $mecanico->persona->apellidos : $mecanico->name }}
                                    </option>
                                @endforeach
                            </select>
                        </form>
                    </div>
                @endif
            </div>
        @endif

        <!-- Content Grid -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

            <!-- Izquierda: Tarea en progreso/actual -->
            <div class="col-span-1 flex flex-col gap-4">
                <h3 class="font-bold text-gray-700 flex items-center gap-2">
                    <span class="w-2 h-6 bg-blue-500 rounded-full"></span>
                    Tarea Actual Activa
                </h3>

                @if($tareaActual)
                    <div class="bg-white rounded-xl shadow-md border-t-4 border-blue-500 p-5 relative overflow-hidden">
                        <div class="absolute top-0 right-0 p-4">
                            <span
                                class="badge {{ $tareaActual->estado == 'en_progreso' ? 'badge-success' : 'badge-warning' }} font-bold text-xs uppercase shadow-sm">
                                {{ str_replace('_', ' ', $tareaActual->estado) }}
                            </span>
                        </div>

                        <h4 class="text-lg font-black text-gray-800 uppercase pr-20">{{ $tareaActual->tipo_actividad }}</h4>
                        <p class="text-gray-600 mt-2 text-sm">{{ $tareaActual->descripcion }}</p>

                        <div class="mt-4 p-4 bg-gray-50 rounded-lg space-y-3">
                            @if($tareaActual->orden)
                                <div class="flex items-start gap-3">
                                    <i class="fas fa-car text-gray-400 mt-1"></i>
                                    <div>
                                        <p class="text-xs text-gray-500 font-bold uppercase tracking-wider">Vehículo</p>
                                        <p class="text-sm font-medium text-gray-800">
                                            {{ $tareaActual->orden->vehiculo->marca->nombre ?? '' }}
                                            {{ $tareaActual->orden->vehiculo->modelo->nombre ?? '' }}
                                            <span
                                                class="badge badge-sm badge-ghost ml-1">{{ $tareaActual->orden->vehiculo->placa ?? 'S/P' }}</span>
                                        </p>
                                    </div>
                                </div>
                                <div class="flex items-start gap-3">
                                    <i class="fas fa-clipboard-list text-gray-400 mt-1"></i>
                                    <div>
                                        <p class="text-xs text-gray-500 font-bold uppercase tracking-wider">Orden Vinculada</p>
                                        <a href="{{ route('panel.operaciones.ordenes_trabajo.show', $tareaActual->orden_trabajo_id) }}"
                                            class="text-sm font-bold text-blue-600 hover:underline">
                                            OT #{{ str_pad($tareaActual->orden_trabajo_id, 5, '0', STR_PAD_LEFT) }}
                                        </a>
                                    </div>
                                </div>
                            @endif

                            <div class="flex items-start gap-3">
                                <i class="fas fa-clock text-gray-400 mt-1"></i>
                                <div>
                                    <p class="text-xs text-gray-500 font-bold uppercase tracking-wider">Control de Tiempos</p>
                                    <div class="text-sm font-medium text-gray-800 space-y-1 mt-1">
                                        <div class="flex items-center gap-2">
                                            <i class="fas fa-calendar-plus text-gray-400 text-xs w-4"></i>
                                            <span><strong>Asignada:</strong>
                                                {{ $tareaActual->created_at->format('d M, Y - h:i A') }}</span>
                                        </div>
                                        <div class="flex items-center gap-2">
                                            <i class="fas fa-play-circle text-gray-400 text-xs w-4"></i>
                                            <span><strong>Iniciada:</strong>
                                                @if($tareaActual->inicio)
                                                    <span
                                                        class="text-green-600">{{ $tareaActual->inicio->format('d M, Y - h:i A') }}</span>
                                                @else
                                                    <span class="text-gray-400 italic">No iniciada</span>
                                                @endif
                                            </span>
                                        </div>
                                        <div class="flex items-center gap-2">
                                            <i class="fas fa-stopwatch text-gray-400 text-xs w-4"></i>
                                            <span><strong>Meta:</strong>
                                                {{ $tareaActual->meta_minutos ? $tareaActual->meta_minutos . ' minutos' : 'N/A' }}</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="mt-6 flex flex-col gap-2">
                            <form action="{{ route('panel.mis_tareas.status', $tareaActual->id) }}" method="POST"
                                class="flex gap-2">
                                @csrf
                                @if($tareaActual->estado == 'en_pausa')
                                    <button type="submit" name="estado" value="en_progreso"
                                        class="btn btn-success flex-1 shadow-sm text-white">
                                        <i class="fas fa-play"></i> Iniciar
                                    </button>
                                @elseif($tareaActual->estado == 'en_progreso')
                                    <button type="submit" name="estado" value="en_pausa" class="btn btn-warning flex-1 shadow-sm">
                                        <i class="fas fa-pause"></i> Pausar
                                    </button>
                                @endif
                                <button type="button" class="btn btn-primary flex-1 shadow-sm"
                                    onclick="confirmarFinalizacion(this)">
                                    <i class="fas fa-check-double"></i> Finalizar
                                </button>
                            </form>
                        </div>

                        <hr class="my-5 border-gray-100">

                        <form action="{{ route('panel.mis_tareas.notes', $tareaActual->id) }}" method="POST">
                            @csrf
                            <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-2">Añadir
                                Observación o Requisito</label>
                            <textarea name="notas_adicionales" class="textarea textarea-bordered w-full h-24 mb-2 text-sm"
                                placeholder="Ej: Faltan pastillas de freno, detecté una fuga..."></textarea>
                            <button type="submit" class="btn btn-sm btn-outline btn-block"><i class="fas fa-save"></i> Guardar
                                Nota</button>
                        </form>

                        @if($tareaActual->notas_adicionales)
                            <div class="mt-4 bg-yellow-50 p-3 rounded-lg border border-yellow-100">
                                <label class="block text-xs font-bold text-yellow-700 uppercase tracking-wider mb-1"><i
                                        class="fas fa-clipboard text-yellow-500 mr-1"></i> Notas Previas</label>
                                <p class="text-xs text-yellow-800 whitespace-pre-wrap leading-relaxed">
                                    {{ $tareaActual->notas_adicionales }}
                                </p>
                            </div>
                        @endif
                    </div>
                @else
                    <div
                        class="bg-white rounded-xl shadow-sm p-8 border border-dashed border-gray-300 text-center flex flex-col items-center justify-center h-64">
                        <div class="w-16 h-16 rounded-full bg-gray-50 flex items-center justify-center mb-4">
                            <i class="fas fa-mug-hot text-2xl text-gray-300"></i>
                        </div>
                        <h4 class="text-gray-500 font-bold">Sin tareas en progreso</h4>
                        <p class="text-xs text-gray-400 mt-1">Actualmente no tienes tareas asignadas corriendo.</p>
                    </div>
                @endif
            </div>

            <!-- Derecha: Historial -->
            <div class="col-span-1 lg:col-span-2 flex flex-col gap-4">
                <h3 class="font-bold text-gray-700 flex items-center gap-2">
                    <span class="w-2 h-6 bg-green-500 rounded-full"></span>
                    Historial de Tareas Finalizadas
                </h3>

                <div class="bg-white rounded-xl shadow-sm overflow-hidden border border-gray-100">
                    <div class="overflow-x-auto">
                        <table class="table w-full">
                            <thead class="bg-slate-50 text-slate-500">
                                <tr>
                                    <th class="font-bold text-xs uppercase tracking-wider">Fecha / Hora</th>
                                    <th class="font-bold text-xs uppercase tracking-wider">Descripción</th>
                                    <th class="font-bold text-xs uppercase tracking-wider">Referencia</th>
                                    <th class="font-bold text-xs uppercase tracking-wider">Tiempo Invertido</th>
                                </tr>
                            </thead>
                            <tbody class="text-sm">
                                @forelse($historico as $tarea)
                                    <tr class="hover:bg-slate-50 transition-colors">
                                        <td class="whitespace-nowrap">
                                            <div class="font-medium text-gray-700">{{ $tarea->updated_at->format('d M, Y') }}
                                            </div>
                                            <div class="text-xs text-gray-400">{{ $tarea->updated_at->format('H:i') }}</div>
                                        </td>
                                        <td>
                                            <div class="font-medium text-gray-800">{{ $tarea->descripcion }}</div>
                                            @if($tarea->notas_adicionales)
                                                <div class="mt-1">
                                                    <button type="button"
                                                        class="btn-view-notes badge badge-xs bg-yellow-100 hover:bg-yellow-200 text-yellow-700 border-none px-2 cursor-pointer transition-colors shadow-sm"
                                                        data-notas="{{ $tarea->notas_adicionales }}">
                                                        <i class="fas fa-comment-dots mr-1"></i> Ver Notas
                                                    </button>
                                                </div>
                                            @endif
                                        </td>
                                        <td>
                                            @if($tarea->orden)
                                                <a href="{{ route('panel.operaciones.ordenes_trabajo.show', $tarea->orden_trabajo_id) }}"
                                                    class="text-blue-600 font-bold hover:underline group">
                                                    OT #{{ str_pad($tarea->orden_trabajo_id, 5, '0', STR_PAD_LEFT) }}
                                                    <i
                                                        class="fas fa-external-link-alt text-[10px] ml-1 opacity-0 group-hover:opacity-100 transition-opacity"></i>
                                                </a>
                                                <div class="text-xs text-gray-500 mt-0.5">
                                                    {{ $tarea->orden->vehiculo->marca->nombre ?? '' }}
                                                    {{ $tarea->orden->vehiculo->modelo->nombre ?? '' }} -
                                                    {{ $tarea->orden->vehiculo->placa ?? '' }}
                                                </div>
                                            @else
                                                <span class="text-gray-400 italic">Sin OT</span>
                                            @endif
                                        </td>
                                        <td>
                                            <div class="flex flex-col gap-1">
                                                <div class="flex items-center gap-2">
                                                    <span
                                                        class="font-bold {{ $tarea->meta_minutos && $tarea->minutos_totales > $tarea->meta_minutos ? 'text-red-500' : 'text-green-600' }}">
                                                        Total: {{ $tarea->minutos_totales }} min
                                                    </span>
                                                    @if($tarea->meta_minutos)
                                                        <span class="text-xs text-gray-400">/ Meta: {{ $tarea->meta_minutos }} min</span>
                                                    @endif
                                                </div>
                                                <div class="text-[10px] text-gray-500 mt-1 space-y-0.5 leading-tight">
                                                    <div><strong>Asignada:</strong> {{ $tarea->created_at->format('d M, H:i') }}</div>
                                                    <div><strong>Iniciada:</strong> {{ $tarea->inicio ? $tarea->inicio->format('d M, H:i') : 'N/A' }}</div>
                                                    <div><strong>Terminada:</strong> {{ $tarea->fin ? $tarea->fin->format('d M, H:i') : $tarea->updated_at->format('d M, H:i') }}</div>
                                                </div>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="text-center py-10">
                                            <i class="fas fa-clipboard-check text-4xl text-gray-200 mb-3 block"></i>
                                            <span class="text-gray-400 font-medium">Aún no hay tareas finalizadas
                                                registradas.</span>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

        </div>
    </div>
@endsection

@push('scripts')
    <script>
        function confirmarFinalizacion(btn) {
            Swal.fire({
                title: '¿Finalizar Tarea?',
                text: '¿Confirmas que terminaste la tarea de forma definitiva?',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Sí, finalizar',
                cancelButtonText: 'No, cancelar',
                customClass: {
                    confirmButton: 'btn btn-primary',
                    cancelButton: 'btn btn-ghost hover:bg-gray-100'
                },
                buttonsStyling: false
            }).then((result) => {
                if (result.isConfirmed) {
                    const form = btn.closest('form');
                    const hiddenInput = document.createElement('input');
                    hiddenInput.type = 'hidden';
                    hiddenInput.name = 'estado';
                    hiddenInput.value = 'completado';
                    form.appendChild(hiddenInput);
                    form.submit();
                }
            });
        }

        document.addEventListener('DOMContentLoaded', () => {
            const buttons = document.querySelectorAll('.btn-view-notes');
            buttons.forEach(btn => {
                btn.addEventListener('click', () => {
                    const rawNotas = btn.getAttribute('data-notas');
                    // Escapar y reemplazar saltos de línea por tags HTML
                    const htmlNotas = rawNotas.replace(/\n/g, '<br>');

                    Swal.fire({
                        title: '<i class="fas fa-clipboard-list text-yellow-500 mb-2 text-4xl"></i><br><span class="text-xl font-black text-gray-800 uppercase">Notas del Mecánico</span>',
                        html: `
                                <div class="bg-yellow-50 text-left p-5 rounded-xl border border-yellow-200 mt-4 shadow-inner">
                                    <div class="text-gray-700 text-sm font-medium leading-relaxed max-h-64 overflow-y-auto custom-scrollbar">
                                        ${htmlNotas}
                                    </div>
                                </div>
                            `,
                        showConfirmButton: true,
                        confirmButtonText: '<i class="fas fa-check"></i> Entendido',
                        customClass: {
                            htmlContainer: 'm-0',
                            confirmButton: 'btn bg-gray-800 hover:bg-gray-900 border-none text-white rounded-xl w-full max-w-xs mt-4 font-bold shadow-lg shadow-gray-200',
                            popup: 'rounded-3xl border border-gray-100 shadow-2xl p-6 bg-white'
                        },
                        buttonsStyling: false
                    });
                });
            });
        });
    </script>
@endpush