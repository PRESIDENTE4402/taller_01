@extends('layouts.panel')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="flex flex-col md:flex-row justify-between items-center gap-4 mb-10">
        <div>
            <h1 class="text-4xl font-black text-gray-800 tracking-tighter italic uppercase">Tablero de Actividades</h1>
            <p class="text-gray-500 font-bold uppercase text-xs tracking-widest mt-1">Control de personal y asignación de tareas</p>
        </div>
        <div class="flex gap-2">
            <select id="sucursalFilter" class="select select-bordered select-sm rounded-xl font-bold bg-white text-gray-700">
                <option value="">Todas las sucursales</option>
                @foreach($sucursales as $sucursal)
                <option value="{{ $sucursal->id }}">{{ $sucursal->nombre }}</option>
                @endforeach
            </select>
            <button onclick="loadBoard()" class="btn btn-sm btn-ghost bg-white border-gray-200 text-gray-600 rounded-xl">
                <i class="fas fa-sync-alt"></i>
            </button>
        </div>
    </div>

    <!-- Tablero de Actividades -->
    <div class="space-y-10">
        <!-- Tabla 1: Actividades en Curso -->
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
            <div class="p-6 border-b border-gray-50 flex justify-between items-center bg-gray-50/50">
                <h2 class="text-xl font-black text-gray-800 tracking-tighter uppercase italic"><i class="fas fa-tools text-blue-500 mr-2"></i> Actividades en Curso</h2>
                <span class="badge badge-info font-bold text-[10px] uppercase p-3" id="activeTasksCount">0 ACTIVAS</span>
            </div>
            <div class="overflow-x-auto">
                <table class="table w-full">
                    <thead>
                        <tr class="bg-gray-50 text-gray-400 text-[10px] uppercase font-black tracking-widest border-b border-gray-100">
                            <th class="py-4 px-6">Trabajador</th>
                            <th class="py-4 px-6">Actividad</th>
                            <th class="py-4 px-6 text-center">Referencia</th>
                            <th class="py-4 px-6 text-center">Estado</th>
                            <th class="py-4 px-6 text-right">Inicio</th>
                        </tr>
                    </thead>
                    <tbody id="activeTasksTable">
                        <!-- Render JS -->
                        <tr>
                            <td colspan="5" class="text-center py-10 text-gray-400 font-bold uppercase text-xs tracking-widest">Cargando actividades...</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Tabla 2: Listado de Todos los Trabajadores -->
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
            <div class="p-6 border-b border-gray-50 flex justify-between items-center bg-gray-50/50">
                <h2 class="text-xl font-black text-gray-800 tracking-tighter uppercase italic"><i class="fas fa-users text-purple-500 mr-2"></i> Listado de Personal</h2>
                <div class="flex gap-2">
                    <button class="btn btn-xs btn-outline rounded-lg font-black uppercase text-[9px]" onclick="loadBoard()">Refrescar</button>
                </div>
            </div>
            <div class="overflow-x-auto">
                <table class="table w-full">
                    <thead>
                        <tr class="bg-gray-50 text-gray-400 text-[10px] uppercase font-black tracking-widest border-b border-gray-100">
                            <th class="py-4 px-6">Colaborador</th>
                            <th class="py-4 px-6 text-center">Sucursal</th>
                            <th class="py-4 px-6 text-center">Tareas Hoy</th>
                            <th class="py-4 px-6 text-center">Disponibilidad</th>
                            <th class="py-4 px-6 text-right">Acciones</th>
                        </tr>
                    </thead>
                    <tbody id="workersTable">
                        <!-- Render JS -->
                        <tr>
                            <td colspan="5" class="text-center py-10 text-gray-400 font-bold uppercase text-xs tracking-widest">Cargando colaboradores...</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Modal: Asignar Tarea -->
<dialog id="modalAssignTask" class="modal modal-bottom sm:modal-middle">
    <div class="modal-box bg-white p-0 rounded-2xl overflow-hidden shadow-2xl">
        <div class="bg-gray-900 p-6 text-white flex justify-between items-center">
            <div>
                <h3 class="text-xl font-black italic tracking-tighter">ASIGNAR NUEVA TAREA</h3>
                <p id="targetWorkerName" class="text-blue-400 text-[10px] font-bold uppercase tracking-widest">NOMBRE DEL TRABAJADOR</p>
            </div>
            <form method="dialog">
                <button class="btn btn-sm btn-circle btn-ghost text-white">✕</button>
            </form>
        </div>

        <form id="formAssignTask" class="p-6 space-y-4">
            @csrf
            <input type="hidden" name="user_id" id="inputUserId">

            <div class="form-control w-full">
                <label class="label"><span class="label-text font-black text-gray-700 text-xs uppercase tracking-widest">Descripción de la Tarea</span></label>
                <textarea name="descripcion" required class="textarea textarea-bordered h-24 rounded-xl font-bold text-sm bg-gray-50 border-gray-200 focus:border-blue-500 transition-all" placeholder="Ej: Limpieza de herramientas, Organizar estantería de aceites..."></textarea>
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div class="form-control">
                    <label class="label"><span class="label-text font-black text-gray-700 text-xs uppercase tracking-widest">Tipo de actividad</span></label>
                    <select name="tipo_actividad" class="select select-bordered rounded-xl font-bold bg-gray-50 text-sm">
                        <option value="mecanica">Mecánica</option>
                        <option value="diagnostico">Diagnóstico</option>
                        <option value="limpieza">Limpieza</option>
                        <option value="mantenimiento">Mantenimiento Interno</option>
                        <option value="administrativo">Administrativo</option>
                        <option value="otro">Otro</option>
                    </select>
                </div>
                <div class="form-control">
                    <label class="label"><span class="label-text font-black text-gray-700 text-xs uppercase tracking-widest">Minutos estimados</span></label>
                    <input type="number" name="meta_minutos" value="30" class="input input-bordered rounded-xl font-bold bg-gray-50" />
                </div>
            </div>

            <div class="form-control w-full">
                <label class="label"><span class="label-text font-black text-gray-700 text-xs uppercase tracking-widest">Orden de Trabajo (Opcional)</span></label>
                <select name="orden_trabajo_id" class="select select-bordered rounded-xl font-bold bg-gray-50 text-sm">
                    <option value="">Sin orden específica</option>
                    @foreach($ordenesActivas as $orden)
                    <option value="{{ $orden->id }}">#{{ $orden->codigo_orden }} - {{ $orden->vehiculo->placa }} ({{ $orden->cliente->nombre_completo }})</option>
                    @endforeach
                </select>
            </div>

            <div class="pt-4 flex gap-2">
                <button type="submit" class="btn btn-primary flex-1 gap-2 rounded-xl shadow-lg shadow-blue-500/30 font-black italic">
                    <i class="fas fa-paper-plane"></i> ASIGNAR TAREA
                </button>
            </div>
        </form>
    </div>
</dialog>

@push('scripts')
<script>
    window.routes = {
        list: "{{ route('panel.colaboradores.list') }}",
        assign: "{{ route('panel.colaboradores.assign') }}"
    };
</script>
@vite('resources/js/panel/colaboradores.js')
@endpush
@endsection