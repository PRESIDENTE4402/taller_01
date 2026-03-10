@extends('layouts.panel')

@section('title', 'Plantillas de Mensajes')
@section('subtitle', 'Configure los mensajes automáticos para WhatsApp y Correo Electrónico')

@section('content')
    <div class="space-y-6">
        <div class="flex justify-between items-center">
            <div class="bg-blue-50 px-6 py-3 rounded-2xl border border-blue-100 flex gap-6">
                <div>
                    <p class="text-[10px] font-black text-blue-800 uppercase tracking-widest leading-tight mb-1">Citas:</p>
                    <p class="text-[9px] text-blue-500 font-bold uppercase">{cliente}, {vehiculo}, {placa}, {fecha}, {hora},
                        {sucursal}</p>
                </div>
                <div class="border-l border-blue-200 pl-6">
                    <p class="text-[10px] font-black text-indigo-800 uppercase tracking-widest leading-tight mb-1">Órdenes:
                    </p>
                    <p class="text-[9px] text-indigo-500 font-bold uppercase">{cliente}, {vehiculo}, {placa},
                        {codigo_orden}, {link}, {fase}, {sucursal}</p>
                </div>
            </div>
            <button onclick="openCreateModal()" class="btn btn-primary bg-blue-900 border-none text-white shadow-lg">
                <i class="fas fa-plus mr-2"></i> Nueva Plantilla
            </button>
        </div>

        <div class="grid grid-cols-1 gap-6">
            <!-- Categoria: Citas -->
            <div class="card bg-white shadow-xl shadow-blue-900/5 border border-blue-100 overflow-hidden">
                <div class="card-body p-0">
                    <div class="bg-blue-900 px-6 py-4 flex justify-between items-center">
                        <h3
                            class="card-title text-white flex items-center gap-2 text-sm font-black italic uppercase tracking-tighter">
                            <i class="fas fa-calendar-check"></i> Plantillas para Citas
                        </h3>
                    </div>
                    <div class="p-6 overflow-x-auto">
                        <table class="table w-full" id="table-Citas">
                            <thead>
                                <tr class="text-gray-400 text-[10px] uppercase tracking-wider font-black">
                                    <th>Nombre del Mensaje</th>
                                    <th>Canal</th>
                                    <th>Vista Previa</th>
                                    <th class="text-center">Estado</th>
                                    <th class="text-right">Acciones</th>
                                </tr>
                            </thead>
                            <tbody class="text-sm" id="tbody-Citas">
                                <!-- Data via JS -->
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Categoria: Órdenes -->
            <div class="card bg-white shadow-xl shadow-blue-900/5 border border-blue-100 overflow-hidden">
                <div class="card-body p-0">
                    <div class="bg-indigo-700 px-6 py-4 flex justify-between items-center">
                        <h3
                            class="card-title text-white flex items-center gap-2 text-sm font-black italic uppercase tracking-tighter">
                            <i class="fas fa-file-invoice"></i> Plantillas para Órdenes
                        </h3>
                    </div>
                    <div class="p-6 overflow-x-auto">
                        <table class="table w-full" id="table-Órdenes">
                            <thead>
                                <tr class="text-gray-400 text-[10px] uppercase tracking-wider font-black">
                                    <th>Nombre del Mensaje</th>
                                    <th>Canal</th>
                                    <th>Vista Previa</th>
                                    <th class="text-center">Estado</th>
                                    <th class="text-right">Acciones</th>
                                </tr>
                            </thead>
                            <tbody class="text-sm" id="tbody-Órdenes">
                                <!-- Data via JS -->
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal -->
    <dialog id="templateModal" class="modal">
        <div
            class="modal-box bg-white max-w-3xl rounded-3xl p-0 overflow-hidden border-none shadow-2xl flex flex-col max-h-[85vh]">
            <div class="bg-blue-900 p-6 text-white text-center flex-shrink-0">
                <h3 class="font-black italic text-2xl uppercase tracking-tighter" id="modalTitle">Nueva Plantilla</h3>
                <p class="text-blue-200 text-xs font-medium uppercase tracking-widest mt-1">Configure el contenido del
                    mensaje</p>
            </div>

            <form id="templateForm" class="p-8 space-y-5 overflow-y-auto custom-scrollbar flex-1">
                <input type="hidden" id="templateId">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                    <div class="form-control w-full">
                        <label class="label"><span
                                class="label-text font-black text-[10px] uppercase text-gray-400 tracking-widest">Nombre
                                Descriptivo</span></label>
                        <input type="text" id="nombre"
                            class="input input-bordered focus:border-blue-900 font-bold bg-gray-50" required
                            placeholder="Ej: Confirmación de Cita">
                    </div>
                    <div class="form-control w-full">
                        <label class="label"><span
                                class="label-text font-black text-[10px] uppercase text-gray-400 tracking-widest">Categoría</span></label>
                        <select id="categoria" class="select select-bordered font-bold bg-gray-50" required>
                            <option value="Citas">Citas</option>
                            <option value="Órdenes">Órdenes</option>
                        </select>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                    <div class="form-control w-full">
                        <label class="label"><span
                                class="label-text font-black text-[10px] uppercase text-gray-400 tracking-widest">Canal de
                                Envío</span></label>
                        <select id="tipo_canal" class="select select-bordered font-bold bg-gray-50" required>
                            <option value="whatsapp">Solo WhatsApp</option>
                            <option value="email">Solo Email</option>
                            <option value="ambos">Ambos (WhatsApp y Email)</option>
                        </select>
                    </div>
                    <div class="form-control w-full">
                        <label class="label"><span
                                class="label-text font-black text-[10px] uppercase text-gray-400 tracking-widest">Estado</span></label>
                        <label
                            class="label cursor-pointer justify-start gap-4 bg-gray-50 p-3 rounded-xl border border-gray-100">
                            <input type="checkbox" id="activo" class="checkbox checkbox-primary" checked>
                            <span class="label-text font-bold text-gray-700">Canal Habilitado</span>
                        </label>
                    </div>
                </div>

                <div class="form-control" id="asunto-container">
                    <label class="label"><span
                            class="label-text font-black text-[10px] uppercase text-gray-400 tracking-widest">Asunto (Para
                            Email)</span></label>
                    <input type="text" id="asunto" class="input input-bordered font-bold bg-gray-50"
                        placeholder="Título que aparecerá en el correo">
                </div>

                <!-- Guía de Variables -->
                <div class="bg-blue-50 p-5 rounded-2xl border border-blue-100">
                    <div class="flex items-center gap-2 mb-3">
                        <div
                            class="w-6 h-6 rounded-full bg-blue-600 text-white flex items-center justify-center text-[10px]">
                            <i class="fas fa-magic"></i>
                        </div>
                        <span class="text-[10px] font-black text-blue-800 uppercase tracking-widest leading-none">Guía de
                            Variables Dinámicas</span>
                    </div>

                    <p class="text-[11px] text-blue-600/80 leading-relaxed mb-4 font-medium italic">
                        Inserta estas etiquetas en el texto. El sistema las reemplazará automáticamente con la información
                        real del cliente o servicio.
                    </p>

                    <div class="flex flex-wrap gap-2">
                        <div class="group relative">
                            <span
                                class="badge bg-white border-blue-200 text-blue-700 font-bold py-2.5 px-3 rounded-lg text-[10px] cursor-help shadow-sm hover:border-blue-400 transition-colors uppercaseTracking-wider">{cliente}</span>
                            <div
                                class="absolute bottom-full left-1/2 -translate-x-1/2 mb-2 bg-gray-900 text-white text-[9px] px-2 py-1 rounded opacity-0 group-hover:opacity-100 transition-opacity whitespace-nowrap z-10 pointer-events-none">
                                Nombre del Cliente</div>
                        </div>
                        <div class="group relative">
                            <span
                                class="badge bg-white border-blue-200 text-blue-700 font-bold py-2.5 px-3 rounded-lg text-[10px] cursor-help shadow-sm hover:border-blue-400 transition-colors uppercaseTracking-wider">{vehiculo}</span>
                            <div
                                class="absolute bottom-full left-1/2 -translate-x-1/2 mb-2 bg-gray-900 text-white text-[9px] px-2 py-1 rounded opacity-0 group-hover:opacity-100 transition-opacity whitespace-nowrap z-10 pointer-events-none">
                                Marca y Modelo</div>
                        </div>
                        <div class="group relative">
                            <span
                                class="badge bg-white border-blue-200 text-blue-700 font-bold py-2.5 px-3 rounded-lg text-[10px] cursor-help shadow-sm hover:border-blue-400 transition-colors uppercaseTracking-wider">{placa}</span>
                            <div
                                class="absolute bottom-full left-1/2 -translate-x-1/2 mb-2 bg-gray-900 text-white text-[9px] px-2 py-1 rounded opacity-0 group-hover:opacity-100 transition-opacity whitespace-nowrap z-10 pointer-events-none">
                                Placa del Vehículo</div>
                        </div>
                        <div class="group relative">
                            <span
                                class="badge bg-white border-blue-200 text-blue-700 font-bold py-2.5 px-3 rounded-lg text-[10px] cursor-help shadow-sm hover:border-blue-400 transition-colors uppercaseTracking-wider">{sucursal}</span>
                            <div
                                class="absolute bottom-full left-1/2 -translate-x-1/2 mb-2 bg-gray-900 text-white text-[9px] px-2 py-1 rounded opacity-0 group-hover:opacity-100 transition-opacity whitespace-nowrap z-10 pointer-events-none">
                                Nombre de Sucursal</div>
                        </div>

                        <div class="w-full h-px bg-blue-100 my-1"></div>

                        <div class="group relative">
                            <span
                                class="badge bg-white border-orange-200 text-orange-700 font-bold py-2.5 px-3 rounded-lg text-[10px] cursor-help shadow-sm hover:border-orange-400 transition-colors uppercaseTracking-wider">{fecha}</span>
                            <div
                                class="absolute bottom-full left-1/2 -translate-x-1/2 mb-2 bg-gray-900 text-white text-[9px] px-2 py-1 rounded opacity-0 group-hover:opacity-100 transition-opacity whitespace-nowrap z-10 pointer-events-none">
                                Fecha (Citas)</div>
                        </div>
                        <div class="group relative">
                            <span
                                class="badge bg-white border-orange-200 text-orange-700 font-bold py-2.5 px-3 rounded-lg text-[10px] cursor-help shadow-sm hover:border-orange-400 transition-colors uppercaseTracking-wider">{hora}</span>
                            <div
                                class="absolute bottom-full left-1/2 -translate-x-1/2 mb-2 bg-gray-900 text-white text-[9px] px-2 py-1 rounded opacity-0 group-hover:opacity-100 transition-opacity whitespace-nowrap z-10 pointer-events-none">
                                Hora (Citas)</div>
                        </div>

                        <div class="group relative">
                            <span
                                class="badge bg-white border-indigo-200 text-indigo-700 font-bold py-2.5 px-3 rounded-lg text-[10px] cursor-help shadow-sm hover:border-indigo-400 transition-colors uppercaseTracking-wider">{codigo_orden}</span>
                            <div
                                class="absolute bottom-full left-1/2 -translate-x-1/2 mb-2 bg-gray-900 text-white text-[9px] px-2 py-1 rounded opacity-0 group-hover:opacity-100 transition-opacity whitespace-nowrap z-10 pointer-events-none">
                                # de Orden</div>
                        </div>
                        <div class="group relative">
                            <span
                                class="badge bg-white border-indigo-200 text-indigo-700 font-bold py-2.5 px-3 rounded-lg text-[10px] cursor-help shadow-sm hover:border-indigo-400 transition-colors uppercaseTracking-wider">{link}</span>
                            <div
                                class="absolute bottom-full left-1/2 -translate-x-1/2 mb-2 bg-gray-900 text-white text-[9px] px-2 py-1 rounded opacity-0 group-hover:opacity-100 transition-opacity whitespace-nowrap z-10 pointer-events-none">
                                Link al PDF/Avance</div>
                        </div>
                        <div class="group relative">
                            <span
                                class="badge bg-white border-indigo-200 text-indigo-700 font-bold py-2.5 px-3 rounded-lg text-[10px] cursor-help shadow-sm hover:border-indigo-400 transition-colors uppercaseTracking-wider">{fase}</span>
                            <div
                                class="absolute bottom-full left-1/2 -translate-x-1/2 mb-2 bg-gray-900 text-white text-[9px] px-2 py-1 rounded opacity-0 group-hover:opacity-100 transition-opacity whitespace-nowrap z-10 pointer-events-none">
                                Estado de la Orden</div>
                        </div>
                    </div>
                </div>

                <div class="form-control">
                    <label class="label flex justify-between">
                        <span class="label-text font-black text-[10px] uppercase text-gray-400 tracking-widest">Contenido
                            del Mensaje</span>
                    </label>
                    <textarea id="cuerpo"
                        class="textarea textarea-bordered h-40 font-medium leading-relaxed bg-gray-50 focus:bg-white transition-colors"
                        required placeholder="Escriba aquí el mensaje..."></textarea>
                </div>

                <div class="modal-action border-t border-gray-100 pt-6 flex justify-between items-center">
                    <button type="button" onclick="templateModal.close()"
                        class="btn btn-ghost font-bold text-gray-500 uppercase tracking-widest text-xs">Cancelar</button>
                    <button type="submit"
                        class="btn btn-primary bg-blue-900 hover:bg-black border-none text-white px-10 font-black italic shadow-lg shadow-blue-500/30">Guardar
                        Cambios</button>
                </div>
            </form>
        </div>
        <form method="dialog" class="modal-backdrop bg-blue-900/10 backdrop-blur-sm">
            <button>close</button>
        </form>
    </dialog>
@endsection

@push('scripts')
    @vite('resources/js/mantenimientos/plantillas_mensajes.js')
@endpush