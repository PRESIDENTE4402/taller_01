@extends('layouts.panel')

@section('title', 'Mantenimiento de Inventario de Recepción')
@section('subtitle', 'Configure los ítems que aparecen en el checklist de recepción')

@section('content')
    <div class="space-y-6">
        <div class="flex justify-end">
            <button onclick="openCreateModal()" class="btn btn-primary bg-blue-900 border-none text-white shadow-lg">
                <i class="fas fa-plus mr-2"></i> Nuevo Ítem
            </button>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <!-- Sección 1 -->
            <div class="card bg-white shadow-xl shadow-blue-900/5 border border-blue-100">
                <div class="card-body p-6">
                    <h3 class="card-title text-blue-900 flex items-center gap-2 mb-4">
                        <i class="fas fa-file-invoice"></i> DOCUMENTOS Y ACCESORIOS
                    </h3>
                    <div class="overflow-x-auto">
                        <table class="table table-compact w-full" id="table-documentos_accesorios">
                            <thead>
                                <tr class="text-gray-400 text-[10px] uppercase tracking-wider">
                                    <th>Nombre</th>
                                    <th>Tipo</th>
                                    <th class="text-center">Orden</th>
                                    <th class="text-center">Estado</th>
                                    <th class="text-right">Acciones</th>
                                </tr>
                            </thead>
                            <tbody class="text-sm">
                                <!-- Items via JS -->
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Sección 2 -->
            <div class="card bg-white shadow-xl shadow-blue-900/5 border border-blue-100">
                <div class="card-body p-6">
                    <h3 class="card-title text-amber-600 flex items-center gap-2 mb-4">
                        <i class="fas fa-tools"></i> HERRAMIENTAS Y EXTERIOR
                    </h3>
                    <div class="overflow-x-auto">
                        <table class="table table-compact w-full" id="table-herramientas_exterior">
                            <thead>
                                <tr class="text-gray-400 text-[10px] uppercase tracking-wider">
                                    <th>Nombre</th>
                                    <th>Tipo</th>
                                    <th class="text-center">Orden</th>
                                    <th class="text-center">Estado</th>
                                    <th class="text-right">Acciones</th>
                                </tr>
                            </thead>
                            <tbody class="text-sm">
                                <!-- Items via JS -->
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal -->
    <dialog id="itemModal" class="modal">
        <div class="modal-box bg-white max-w-md">
            <h3 class="font-bold text-lg text-blue-900 mb-4" id="modalTitle">Nuevo Ítem de Inventario</h3>
            <form id="itemForm" class="space-y-4">
                <input type="hidden" id="itemId">
                <div class="form-control">
                    <label class="label"><span class="label-text font-bold text-blue-900">Nombre del Ítem</span></label>
                    <input type="text" id="nombre" class="input input-bordered focus:border-blue-900" required
                        placeholder="Ej: Encendedor">
                </div>
                <div class="form-control">
                    <label class="label"><span class="label-text font-bold text-blue-900">Sección</span></label>
                    <select id="seccion" class="select select-bordered" required>
                        <option value="documentos_accesorios">Documentos y Accesorios</option>
                        <option value="herramientas_exterior">Herramientas y Exterior</option>
                    </select>
                </div>
                <div class="form-control">
                    <label class="label"><span class="label-text font-bold text-blue-900">Tipo de Control</span></label>
                    <select id="tipo" class="select select-bordered" required>
                        <option value="si_no">Sí / No</option>
                        <option value="cantidad">Cantidad (Numérico)</option>
                        <option value="tapiceria">Estado Tapicería (B/R/M)</option>
                        <option value="documentos">Documentos (Original/Copia)</option>
                    </select>
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div class="form-control">
                        <label class="label"><span class="label-text font-bold text-blue-900">Orden</span></label>
                        <input type="number" id="orden" class="input input-bordered" value="0">
                    </div>
                    <div class="form-control">
                        <label class="label"><span class="label-text font-bold text-blue-900">Estado</span></label>
                        <label class="label cursor-pointer justify-start gap-3">
                            <input type="checkbox" id="activo" class="checkbox checkbox-primary" checked>
                            <span class="label-text">Activo</span>
                        </label>
                    </div>
                </div>
                <div class="modal-action">
                    <button type="button" onclick="itemModal.close()" class="btn btn-ghost">Cancelar</button>
                    <button type="submit" class="btn btn-primary bg-blue-900 border-none text-white">Guardar</button>
                </div>
            </form>
        </div>
        <form method="dialog" class="modal-backdrop">
            <button>close</button>
        </form>
    </dialog>
@endsection

@push('scripts')
    @vite('resources/js/mantenimientos/inventario_recepcion.js')
@endpush