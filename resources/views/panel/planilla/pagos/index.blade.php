@extends('layouts.panel')

@section('title', 'Pago a Trabajadores')
@section('subtitle', 'Gestiona los pagos por trabajos realizados')

@section('content')
    <div class="space-y-6">

        <div class="flex justify-between items-center">
            <div>
                <h2 class="text-xl font-bold text-gray-800">Historial de Pagos</h2>
            </div>
            <button onclick="document.getElementById('modal_nuevo_pago').showModal()" class="btn btn-primary gap-2">
                <i class="fas fa-plus"></i> Nuevo Pago
            </button>
        </div>

        <!-- Filtros y herramientas -->
        <div class="card bg-base-100 shadow-sm border border-gray-100 mb-6">
            <div class="card-body p-4">
                <form id="form-filtros" class="flex flex-col md:flex-row gap-4 items-end"
                    onsubmit="event.preventDefault(); cargarTablaPagos();">
                    <div class="form-control w-full md:w-1/3">
                        <label class="label"><span class="label-text font-bold text-gray-600">Trabajador</span></label>
                        <select id="filtro_user_id" class="select select-bordered w-full">
                            <option value="">Todos los trabajadores</option>
                            @foreach($trabajadores as $trabajador)
                                <option value="{{ $trabajador->id }}">{{ $trabajador->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-control w-full md:w-1/4">
                        <label class="label"><span class="label-text font-bold text-gray-600">Mes de Pago</span></label>
                        <input type="month" id="filtro_mes" class="input input-bordered w-full">
                    </div>
                    <div class="form-control w-full md:w-auto">
                        <button type="submit" class="btn btn-primary gap-2">
                            <i class="fas fa-search"></i> Filtrar
                        </button>
                    </div>
                    <div class="form-control w-full md:w-auto ml-auto">
                        <button type="button" class="btn btn-ghost text-gray-500 gap-2" onclick="limpiarFiltros()">
                            <i class="fas fa-eraser"></i> Limpiar
                        </button>
                    </div>
                </form>
            </div>
        </div>
        <div class="card bg-base-100 shadow-xl overflow-hidden">
            <div class="overflow-x-auto">
                <table class="table w-full">
                    <thead class="bg-gray-50 text-gray-700">
                        <tr>
                            <th class="py-4">Trabajador</th>
                            <th>Fecha de Pago</th>
                            <th>Período</th>
                            <th>Monto Total</th>
                            <th>Método</th>
                            <th class="text-right">Acciones</th>
                        </tr>
                    </thead>
                    <tbody id="tabla-pagos-body">
                        <!-- Se llenará dinámicamente -->
                    </tbody>
                </table>
            </div>
            <div id="loading-pagos" class="p-8 text-center text-gray-500 hidden">
                <span class="loading loading-spinner loading-lg text-primary"></span>
                <p class="mt-2">Cargando pagos...</p>
            </div>
        </div>
    </div>

    <!-- Modal Nuevo Pago -->
    <dialog id="modal_nuevo_pago" class="modal">
        <div class="modal-box w-11/12 max-w-7xl rounded-lg">
            <form method="dialog">
                <button class="btn btn-sm btn-circle btn-ghost absolute right-2 top-2">✕</button>
            </form>
            <h3 class="font-bold text-lg text-gray-800 border-b pb-3 mb-4"><i
                    class="fas fa-money-check-alt text-success mr-2"></i> Procesar Pago a Trabajador</h3>

            <form id="form-nuevo-pago" onsubmit="event.preventDefault(); procesarPago();">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">

                    <!-- Columna Izquierda: Datos del Pago -->
                    <div class="md:col-span-1 space-y-4">
                        <div class="form-control">
                            <label class="label"><span class="label-text font-semibold">Seleccionar
                                    Trabajador</span></label>
                            <select name="user_id" id="user_id" class="select select-bordered w-full" required
                                onchange="cargarDatosTrabajador()">
                                <option value="" disabled selected>Seleccione un trabajador...</option>
                                @foreach($trabajadores as $trabajador)
                                    <option value="{{ $trabajador->id }}">{{ $trabajador->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="form-control">
                            <label class="label"><span class="label-text font-semibold">Fecha de Pago</span></label>
                            <input type="date" name="fecha_pago" id="fecha_pago" class="input input-bordered w-full"
                                value="{{ date('Y-m-d') }}" required>
                        </div>

                        <div class="grid grid-cols-2 gap-2">
                            <div class="form-control">
                                <label class="label"><span class="label-text font-semibold text-xs text-gray-500">Inicio
                                        Período (Ref)</span></label>
                                <input type="date" name="fecha_inicio_periodo" id="fecha_inicio_periodo"
                                    class="input input-bordered input-sm w-full" onchange="cargarDatosTrabajador()">
                            </div>
                            <div class="form-control">
                                <label class="label"><span class="label-text font-semibold text-xs text-gray-500">Fin
                                        Período (Ref)</span></label>
                                <input type="date" name="fecha_fin_periodo" id="fecha_fin_periodo"
                                    class="input input-bordered input-sm w-full" onchange="cargarDatosTrabajador()">
                            </div>
                        </div>

                        <div class="grid grid-cols-2 gap-2 mt-2">
                            <div class="form-control">
                                <label class="label"><span class="label-text font-semibold">Sueldo Base ($)</span></label>
                                <input type="number" step="0.01" min="0" name="sueldo_base" id="sueldo_base" class="input input-bordered w-full font-bold text-success" value="0" onchange="recalcularTotal()" onkeyup="recalcularTotal()">
                            </div>
                            <div class="form-control">
                                <label class="label"><span class="label-text font-semibold">Descuentos ($)</span></label>
                                <input type="number" step="0.01" min="0" name="descuentos" id="descuentos" class="input input-bordered w-full font-bold text-error" value="0" onchange="recalcularTotal()" onkeyup="recalcularTotal()">
                            </div>
                        </div>

                        <div class="form-control mt-2">
                            <label class="label"><span class="label-text font-semibold">Método de Pago</span></label>
                            <select name="metodo_pago" id="metodo_pago" class="select select-bordered w-full" required>
                                <option value="EFECTIVO">Efectivo</option>
                                <option value="TRANSFERENCIA">Transferencia</option>
                                <option value="CHEQUE">Cheque</option>
                            </select>
                        </div>

                        <div class="form-control">
                            <label class="label"><span class="label-text font-semibold">Observaciones</span></label>
                            <textarea name="observaciones" id="observaciones" class="textarea textarea-bordered h-24"
                                placeholder="Alguna nota o concepto especial..."></textarea>
                        </div>

                        <div class="card bg-success/10 border border-success/20 mt-4 rounded-lg">
                            <div class="card-body p-4 text-center">
                                <div class="text-sm text-gray-600 font-semibold mb-1">Monto a Pagar</div>
                                <div class="text-3xl font-bold text-success" id="display-total">$0.00</div>
                                <input type="hidden" name="monto_total" id="input-monto_total" value="0">
                            </div>
                        </div>
                    </div>

                    <!-- Columna Derecha: Resumen y Selección de Trabajos -->
                    <div class="md:col-span-2 flex flex-col space-y-4">

                        <!-- Resumen de Asistencia -->
                        <div class="card bg-gray-50 border border-gray-200">
                            <div class="card-body p-4">
                                <h4 class="font-bold text-gray-700 text-sm mb-3 uppercase tracking-wide"><i
                                        class="fas fa-clipboard-user mr-1"></i> Resumen de Asistencia del Período</h4>

                                <div id="loading-asistencia" class="text-center py-4 hidden">
                                    <span class="loading loading-spinner text-primary"></span>
                                </div>

                                <div id="stats-asistencia" class="grid grid-cols-2 md:grid-cols-4 gap-4 text-center">
                                    <div
                                        class="bg-white p-2 rounded shadow-sm border border-gray-100 placeholder-box opacity-50">
                                        <div class="text-xs text-gray-400">Seleccione trabajador</div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Trabajos Pendientes -->
                        <div class="card bg-white border border-gray-200 flex-1 flex flex-col">
                            <div class="card-body p-4 flex flex-col h-full overflow-hidden">
                                <div class="flex justify-between items-center mb-3">
                                    <h4 class="font-bold text-gray-700 text-sm uppercase tracking-wide"><i
                                            class="fas fa-wrench mr-1"></i> Trabajos Pendientes de Pago</h4>
                                    <div class="text-xs text-gray-500 italic">No dependen de las fechas de arriba</div>
                                </div>

                                <div id="loading-trabajos" class="text-center py-8 hidden">
                                    <span class="loading loading-spinner text-primary"></span>
                                </div>

                                <div class="overflow-y-auto max-h-[500px] flex-1 border rounded-lg bg-gray-50 relative custom-scrollbar">
                                    <table class="table table-sm w-full">
                                        <thead class="bg-gray-100 sticky top-0 z-10 shadow-sm">
                                            <tr>
                                                <th>
                                                    <label>
                                                        <input type="checkbox" id="check-all" class="checkbox checkbox-xs"
                                                            onchange="toggleAllChecks(this)" disabled />
                                                    </label>
                                                </th>
                                                <th>Fecha</th>
                                                <th>Orden / OT</th>
                                                <th>Actividad</th>
                                                <th>Valor Pago ($)</th>
                                            </tr>
                                        </thead>
                                        <tbody id="lista-trabajos-body">
                                            <tr>
                                                <td colspan="5" class="text-center py-8 text-gray-400">
                                                    Seleccione un trabajador para ver sus trabajos pendientes
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="modal-action mt-6 border-t pt-4">
                    <button type="button" class="btn btn-ghost"
                        onclick="document.getElementById('modal_nuevo_pago').close()">Cancelar</button>
                    <button type="submit" class="btn btn-primary" id="btn-submit" disabled>
                        <i class="fas fa-save"></i> Procesar Pago
                    </button>
                </div>
            </form>
        </div>
    </dialog>

    <!-- Modal Detalles de Pago -->
    <dialog id="modal_detalle_pago" class="modal">
        <div class="modal-box w-11/12 max-w-4xl rounded-lg">
            <form method="dialog">
                <button class="btn btn-sm btn-circle btn-ghost absolute right-2 top-2">✕</button>
            </form>
            <h3 class="font-bold text-lg text-gray-800 border-b pb-3 mb-4 flex items-center gap-2">
                <i class="fas fa-file-invoice-dollar text-primary"></i>
                <span>Detalles del Pago</span>
            </h3>

            <div id="loading-detalles" class="text-center py-10 hidden">
                <span class="loading loading-spinner text-primary loading-lg"></span>
                <p class="text-gray-500 mt-2 font-medium">Obteniendo detalles...</p>
            </div>

            <div id="contenido-detalles" class="hidden">
                <div class="bg-gray-50 rounded-xl p-4 mb-6 border border-gray-100 grid grid-cols-2 md:grid-cols-4 gap-4">
                    <div>
                        <div class="text-xs text-gray-400 font-bold uppercase tracking-wider mb-1">Trabajador</div>
                        <div class="font-black text-gray-800" id="det-trabajador">--</div>
                    </div>
                    <div>
                        <div class="text-xs text-gray-400 font-bold uppercase tracking-wider mb-1">Fecha de Pago</div>
                        <div class="font-black text-gray-800" id="det-fecha">--</div>
                    </div>
                    <div>
                        <div class="text-xs text-gray-400 font-bold uppercase tracking-wider mb-1">Sueldo Base</div>
                        <div class="font-black text-gray-600" id="det-sueldo">--</div>
                    </div>
                    <div>
                        <div class="text-xs text-gray-400 font-bold uppercase tracking-wider mb-1">Descuentos</div>
                        <div class="font-black text-error" id="det-descuentos">--</div>
                    </div>
                    <div>
                        <div class="text-xs text-gray-400 font-bold uppercase tracking-wider mb-1">Método</div>
                        <div class="font-black text-gray-800" id="det-metodo">--</div>
                    </div>
                    <div>
                        <div class="text-xs text-gray-400 font-bold uppercase tracking-wider mb-1">Monto Líquido</div>
                        <div class="font-black text-success text-xl" id="det-monto">--</div>
                    </div>
                </div>

                <div class="mb-4 text-sm text-gray-600 italic bg-blue-50/50 p-3 rounded-lg border border-blue-50 hidden"
                    id="det-observaciones-container">
                    <span class="font-bold mr-1">Nota:</span> <span id="det-observaciones"></span>
                </div>

                <h4 class="font-bold text-gray-700 text-sm mb-3 uppercase tracking-wide border-b pb-2"><i
                        class="fas fa-list-check mr-1 text-gray-400"></i> Trabajos Pagados</h4>

                <div class="overflow-x-auto rounded-lg border border-gray-200">
                    <table class="table table-sm w-full">
                        <thead class="bg-gray-100 text-gray-600">
                            <tr>
                                <th>Fecha</th>
                                <th>Orden</th>
                                <th>Vehículo</th>
                                <th>Actividad</th>
                                <th>Monto</th>
                            </tr>
                        </thead>
                        <tbody id="lista-detalles-trabajos">
                            <!-- Inyectado dinámicamente -->
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="modal-action mt-6">
                <form method="dialog">
                    <button class="btn btn-ghost border border-gray-200">Cerrar</button>
                </form>
            </div>
        </div>
    </dialog>

    <!-- Modal Editar Pago -->
    <dialog id="modal_editar_pago" class="modal">
        <div class="modal-box w-11/12 max-w-7xl rounded-lg bg-gray-50 p-6">
            <form method="dialog">
                <button class="btn btn-sm btn-circle btn-ghost absolute right-2 top-2">✕</button>
            </form>
            <h3 class="font-bold text-lg text-gray-800 border-b pb-3 mb-4 flex items-center gap-2">
                <i class="fas fa-edit text-orange-500"></i> 
                <span>Modificar Pago y Trabajos</span>
            </h3>

            <form id="form-editar-pago" onsubmit="event.preventDefault(); guardarEdicionPago();">
                <input type="hidden" id="edit_pago_id">
                
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <div class="md:col-span-1 border-r pr-4">
                        <div class="grid grid-cols-2 gap-2 mt-2">
                            <div class="form-control">
                                <label class="label"><span class="label-text font-semibold">Sueldo Base ($)</span></label>
                                <input type="number" step="0.01" min="0" id="edit_sueldo_base" name="sueldo_base" class="input input-bordered w-full font-bold text-success" required onchange="recalcularTotalEdit()" onkeyup="recalcularTotalEdit()">
                            </div>
                            <div class="form-control">
                                <label class="label"><span class="label-text font-semibold">Descuentos ($)</span></label>
                                <input type="number" step="0.01" min="0" id="edit_descuentos" name="descuentos" class="input input-bordered w-full font-bold text-error" required onchange="recalcularTotalEdit()" onkeyup="recalcularTotalEdit()">
                            </div>
                        </div>

                        <div class="form-control mt-4">
                            <label class="label"><span class="label-text font-semibold">Método de Pago</span></label>
                            <select name="metodo_pago" id="edit_metodo_pago" class="select select-bordered w-full" required>
                                <option value="EFECTIVO">Efectivo</option>
                                <option value="TRANSFERENCIA">Transferencia</option>
                                <option value="CHEQUE">Cheque</option>
                            </select>
                        </div>

                        <div class="form-control mt-4 mb-4">
                            <label class="label"><span class="label-text font-semibold">Observaciones</span></label>
                            <textarea name="observaciones" id="edit_observaciones" class="textarea textarea-bordered h-24"></textarea>
                        </div>
                        
                        <div class="card bg-success/10 border border-success/20 mt-4 rounded-lg">
                            <div class="card-body p-4 text-center">
                                <div class="text-sm text-gray-600 font-semibold mb-1">Monto Líquido Modificado</div>
                                <div class="text-3xl font-bold text-success" id="edit-display-total">$0.00</div>
                            </div>
                        </div>
                    </div>

                    <div class="md:col-span-2 flex flex-col space-y-4">
                        <div class="card bg-white border border-gray-200 flex-1 flex flex-col">
                            <div class="card-body p-4 flex flex-col h-full overflow-hidden">
                                <div class="flex justify-between items-center mb-3">
                                    <h4 class="font-bold text-gray-700 text-sm uppercase tracking-wide"><i class="fas fa-wrench mr-1"></i> Trabajos del Pago</h4>
                                </div>
                                <div id="loading-trabajos-edit" class="text-center py-8 hidden">
                                    <span class="loading loading-spinner text-primary"></span>
                                </div>
                                <div class="overflow-y-auto max-h-[500px] flex-1 border rounded-lg bg-gray-50 relative custom-scrollbar">
                                    <table class="table table-sm w-full">
                                        <thead class="bg-gray-100 sticky top-0 z-10 shadow-sm">
                                            <tr>
                                                <th><label><input type="checkbox" id="check-all-edit" class="checkbox checkbox-xs" onchange="toggleAllChecksEdit(this)" disabled /></label></th>
                                                <th>Fecha</th>
                                                <th>Orden / OT</th>
                                                <th>Actividad</th>
                                                <th>Monto Personalizado (Q)</th>
                                            </tr>
                                        </thead>
                                        <tbody id="lista-trabajos-edit-body">
                                            <tr><td colspan="5" class="text-center py-8 text-gray-400">Seleccione un pago...</td></tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="modal-action mt-6 border-t pt-4">
                    <button type="button" class="btn btn-ghost" onclick="document.getElementById('modal_editar_pago').close()">Cancelar</button>
                    <button type="submit" class="btn btn-primary" id="btn-submit-edit" disabled>
                        <i class="fas fa-save"></i> Guardar Cambios
                    </button>
                </div>
            </form>
        </div>
    </dialog>

@endsection

@push('scripts')
    <script>
        // Override Swal globally for this page to use generic Tailwind/DaisyUI classes
        if (window.Swal) {
            window.Swal = window.Swal.mixin({
                customClass: {
                    confirmButton: 'btn bg-blue-600 hover:bg-blue-700 text-white border-none mx-2',
                    cancelButton: 'btn bg-white hover:bg-gray-100 text-gray-700 border border-gray-300 mx-2',
                    denyButton: 'btn bg-red-500 hover:bg-red-600 text-white border-none mx-2',
                    popup: 'rounded-2xl shadow-2xl border border-gray-100',
                    title: 'text-gray-800 font-bold text-lg',
                    htmlContainer: 'text-gray-600'
                },
                buttonsStyling: false
            });
        }

        document.addEventListener('DOMContentLoaded', () => {
            // Establecer fechas por defecto: primer y ultimo dia del mes actial
            const date = new Date();
            const firstDay = new Date(date.getFullYear(), date.getMonth(), 1);
            const lastDay = new Date(date.getFullYear(), date.getMonth() + 1, 0);

            document.getElementById('fecha_inicio_periodo').value = firstDay.toISOString().split('T')[0];
            document.getElementById('fecha_fin_periodo').value = lastDay.toISOString().split('T')[0];

            cargarTablaPagos();
        });

        function limpiarFiltros() {
            document.getElementById('filtro_user_id').value = '';
            document.getElementById('filtro_mes').value = '';
            cargarTablaPagos();
        }

        async function cargarTablaPagos() {
            const tbody = document.getElementById('tabla-pagos-body');
            const loader = document.getElementById('loading-pagos');
            const userId = document.getElementById('filtro_user_id')?.value || '';
            const mes = document.getElementById('filtro_mes')?.value || '';

            tbody.innerHTML = '';
            loader.classList.remove('hidden');

            try {
                const queryParams = new URLSearchParams();
                if (userId) queryParams.append('user_id', userId);
                if (mes) queryParams.append('mes', mes);

                const url = `{{ route("panel.planilla.pagos.list") }}?${queryParams.toString()}`;

                const resp = await fetch(url);
                const data = await resp.json();

                loader.classList.add('hidden');

                window.pagosActuales = data; // Cache data for editing

                if (data.length === 0) {
                    tbody.innerHTML = `<tr><td colspan="6" class="text-center py-6 text-gray-500">No hay pagos registrados para estos filtros</td></tr>`;
                    return;
                }

                data.forEach(p => {
                    const tr = document.createElement('tr');
                    const badgeModificado = p.modificado ? '<span class="badge badge-warning text-xs font-bold border-none ml-2" title="Este pago fue modificado posteriormente">MODIFICADO</span>' : '';

                    tr.innerHTML = `
                            <td class="font-medium text-gray-800">
                                <div class="flex items-center gap-3">
                                    <div class="avatar placeholder">
                                        <div class="bg-gray-200 text-gray-600 rounded-full w-8 h-8 flex items-center justify-center">
                                            <span class="text-xs uppercase font-bold">${p.trabajador.name.substring(0, 2)}</span>
                                        </div>
                                    </div>
                                    <div class="font-bold">${p.trabajador.name}</div>
                                </div>
                            </td>
                            <td class="text-gray-600 font-medium">${new Date(p.fecha_pago.split('T')[0] + 'T12:00:00Z').toLocaleDateString()}</td>
                            <td class="text-xs text-gray-500">
                                ${p.fecha_inicio_periodo ? new Date(p.fecha_inicio_periodo.split('T')[0] + 'T12:00:00Z').toLocaleDateString() : '-'} 
                                al 
                                ${p.fecha_fin_periodo ? new Date(p.fecha_fin_periodo.split('T')[0] + 'T12:00:00Z').toLocaleDateString() : '-'}
                            </td>
                            <td class="font-black text-success tracking-tight flex items-center gap-1">
                                Q ${parseFloat(p.monto_total).toFixed(2)} ${badgeModificado}
                            </td>
                            <td><span class="badge badge-info bg-blue-50 text-blue-700 border-none badge-sm uppercase font-bold">${p.metodo_pago}</span></td>
                            <td class="text-right flex items-center justify-end gap-1">
                                <button class="btn btn-sm text-orange-500 bg-transparent border-none shadow-none hover:bg-orange-50 transition-colors tooltip tooltip-left" data-tip="Modificar" onclick="abrirEditarPago(${p.id})">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <button class="btn btn-sm text-blue-500 bg-transparent border-none shadow-none hover:bg-blue-50 transition-colors tooltip tooltip-left" data-tip="Detalles" onclick="verDetallesPago(${p.id})">
                                    <i class="fas fa-eye"></i>
                                </button>
                                <button class="btn btn-sm text-red-500 bg-transparent border-none shadow-none hover:bg-red-50 transition-colors tooltip tooltip-left" data-tip="Eliminar" onclick="eliminarPago(${p.id})">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </td>
                        `;
                    tbody.appendChild(tr);
                });
            } catch (err) {
                console.error(err);
                loader.classList.add('hidden');
                tbody.innerHTML = `<tr><td colspan="6" class="text-center text-red-500">Error al cargar pagos</td></tr>`;
            }
        }

        window.abrirEditarPago = async function(id) {
            const pago = window.pagosActuales.find(p => p.id === id);
            if (!pago) return;

            document.getElementById('edit_pago_id').value = pago.id;
            document.getElementById('edit_sueldo_base').value = parseFloat(pago.sueldo_base || 0).toFixed(2);
            document.getElementById('edit_descuentos').value = parseFloat(pago.descuentos || 0).toFixed(2);
            document.getElementById('edit_metodo_pago').value = pago.metodo_pago;
            document.getElementById('edit_observaciones').value = pago.observaciones || '';

            const tbodyEdit = document.getElementById('lista-trabajos-edit-body');
            const loaderEdit = document.getElementById('loading-trabajos-edit');
            tbodyEdit.innerHTML = '';
            loaderEdit.classList.remove('hidden');
            document.getElementById('check-all-edit').disabled = true;

            document.getElementById('modal_editar_pago').showModal();

            try {
                const url = `{{ url('/panel/planilla/pagos') }}/trabajador-data-edit/${id}`;
                const resp = await fetch(url);
                const data = await resp.json();

                loaderEdit.classList.add('hidden');

                if (data.trabajos.length === 0) {
                    tbodyEdit.innerHTML = `<tr><td colspan="5" class="text-center py-6 text-gray-400">No hay trabajos asociados a este trabajador</td></tr>`;
                    document.getElementById('btn-submit-edit').disabled = true;
                    return;
                }

                document.getElementById('check-all-edit').disabled = false;
                
                data.trabajos.forEach(t => {
                    const isChecked = t.pago_trabajador_id === pago.id;
                    const tr = document.createElement('tr');
                    tr.classList.add('hover:bg-blue-50/50', 'transition-colors');

                    let badgeClass = 'badge-ghost';
                    if (t.tipo_actividad === 'mecanica') badgeClass = 'badge-primary';
                    if (t.tipo_actividad === 'diagnostico') badgeClass = 'badge-info';

                    let vehiculo = t.orden?.vehiculo?.placa || 'N/A';
                    if (t.orden && t.orden.vehiculo && t.orden.vehiculo.modelo) {
                        vehiculo += ' (' + t.orden.vehiculo.modelo.nombre + ')';
                    }

                    let montoCalculado = t.monto_pago || 0;
                    if (!t.monto_pago && t.monto_pago !== 0) {
                        // Calculate default if not set
                        let base = parseFloat(t.precio_cliente || 0) - parseFloat(t.descuento_cliente || 0);
                        let porcentaje_monto = parseFloat(t.valor_pago_mecanico || 0);
                        if (t.tipo_pago_mecanico === 'porcentaje') {
                            montoCalculado = base * (porcentaje_monto / 100);
                        } else {
                            montoCalculado = porcentaje_monto;
                        }
                    }

                    tr.innerHTML = `
                            <td>
                                <label><input type="checkbox" name="trabajos[]" value="${t.id}" class="checkbox checkbox-sm checkbox-primary tr-checkbox-edit" onchange="recalcularTotalEdit()" ${isChecked ? 'checked' : ''} /></label>
                            </td>
                            <td class="text-gray-500 font-bold text-xs">${new Date(t.created_at.split('T')[0]).toLocaleDateString()}</td>
                            <td>
                                <div class="font-black text-blue-600 tracking-tighter">${t.orden?.codigo_orden || '-'}</div>
                                <div class="text-[9px] font-bold text-gray-400 uppercase break-words max-w-[150px]">${vehiculo}</div>
                            </td>
                            <td class="max-w-xs">
                                <span class="badge ${badgeClass} border-none font-bold badge-sm uppercase mb-1">${t.tipo_actividad}</span>
                                <div class="text-xs text-gray-600 font-medium italic whitespace-normal break-words">${t.descripcion}</div>
                            </td>
                            <td>
                                <div class="flex items-center">
                                    <span class="text-gray-500 font-black px-1">Q</span>
                                    <input type="number" step="0.01" min="0" name="montos[${t.id}]" class="input input-bordered input-xs w-20 monto-trabajo-edit font-mono font-black text-emerald-600 text-right bg-white shadow-sm" value="${parseFloat(montoCalculado || 0).toFixed(2)}" onkeyup="recalcularTotalEdit()" onchange="recalcularTotalEdit()">
                                </div>
                            </td>
                        `;
                    tbodyEdit.appendChild(tr);
                });

                recalcularTotalEdit();
            } catch (err) {
                console.error(err);
                Swal.fire('Error', 'No se pudieron cargar los trabajos para editar', 'error');
            }
        };

        window.toggleAllChecksEdit = function(source) {
            const checkboxes = document.querySelectorAll('.tr-checkbox-edit');
            checkboxes.forEach(cb => { cb.checked = source.checked; });
            recalcularTotalEdit();
        };

        window.recalcularTotalEdit = function() {
            let total = 0;
            const filas = document.querySelectorAll('#lista-trabajos-edit-body tr');
            let checkedCount = 0;

            filas.forEach(tr => {
                const cb = tr.querySelector('.tr-checkbox-edit');
                if (cb && cb.checked) {
                    checkedCount++;
                    const inputMonto = tr.querySelector('.monto-trabajo-edit');
                    if (inputMonto) {
                        total += parseFloat(inputMonto.value) || 0;
                    }
                }
            });

            const sueldoBase = parseFloat(document.getElementById('edit_sueldo_base').value) || 0;
            const descuentos = parseFloat(document.getElementById('edit_descuentos').value) || 0;

            let totalPagar = total + sueldoBase - descuentos;
            if (totalPagar < 0) totalPagar = 0;

            document.getElementById('edit-display-total').innerText = 'Q ' + totalPagar.toFixed(2);

            const btn = document.getElementById('btn-submit-edit');
            const allCbs = document.querySelectorAll('.tr-checkbox-edit');
            if (allCbs.length > 0) {
                document.getElementById('check-all-edit').checked = (checkedCount === allCbs.length);
                btn.disabled = checkedCount === 0;
            } else {
                btn.disabled = true;
            }
        };

        window.guardarEdicionPago = async function() {
            const id = document.getElementById('edit_pago_id').value;
            const btn = document.getElementById('btn-submit-edit');

            btn.innerHTML = '<span class="loading loading-spinner loading-sm"></span> Guardando...';
            btn.disabled = true;

            try {
                const formData = new FormData(document.getElementById('form-editar-pago'));
                formData.append('_method', 'PUT'); // Fake put because forms use post

                const url = `{{ url('/panel/planilla/pagos') }}/${id}`;
                
                const resp = await fetch(url, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json'
                    },
                    body: formData
                });

                const data = await resp.json();

                if (resp.ok && data.success) {
                    Swal.fire({
                        icon: 'success',
                        title: '¡Actualizado!',
                        text: data.message,
                        timer: 2000,
                        showConfirmButton: false
                    });
                    document.getElementById('modal_editar_pago').close();
                    cargarTablaPagos();
                } else {
                    throw new Error(data.message || 'Error al actualizar el pago. Revise los datos.');
                }
            } catch (err) {
                console.error(err);
                Swal.fire('Error', err.message, 'error');
            } finally {
                btn.innerHTML = '<i class="fas fa-save"></i> Guardar Cambios';
                btn.disabled = false;
            }
        };

        window.eliminarPago = function(id) {
            Swal.fire({
                title: '¿Estás seguro?',
                text: "El pago será eliminado y todos los trabajos volverán a figurar como PENDIENTES. Esta acción no se puede deshacer.",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#aaa',
                confirmButtonText: '<i class="fas fa-trash"></i> Sí, eliminar pago',
                cancelButtonText: 'Cancelar'
            }).then(async (result) => {
                if (result.isConfirmed) {
                    try {
                        const url = `{{ url('/panel/planilla/pagos') }}/${id}`;
                        const resp = await fetch(url, {
                            method: 'DELETE',
                            headers: {
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                                'Accept': 'application/json'
                            }
                        });
                        const data = await resp.json();

                        if (resp.ok && data.success) {
                            Swal.fire({
                                icon: 'success',
                                title: '¡Eliminado!',
                                text: data.message,
                                timer: 2000,
                                showConfirmButton: false
                            });
                            cargarTablaPagos();
                            cargarDatosTrabajador(); // Recarga si la vista activa está con usuario seleccionado
                        } else {
                            throw new Error(data.message || 'Error al intentar eliminar');
                        }
                    } catch (error) {
                        Swal.fire('Error', error.message, 'error');
                    }
                }
            });
        };

        async function verDetallesPago(id) {
            const modal = document.getElementById('modal_detalle_pago');
            const loader = document.getElementById('loading-detalles');
            const contenido = document.getElementById('contenido-detalles');
            const tbody = document.getElementById('lista-detalles-trabajos');

            modal.showModal();
            loader.classList.remove('hidden');
            contenido.classList.add('hidden');
            tbody.innerHTML = '';

            try {
                const url = `{{ url('/panel/planilla/pagos') }}/${id}/detalles`;
                const resp = await fetch(url);
                const data = await resp.json();

                const pago = data.pago;
                const trabajos = data.trabajos;

                document.getElementById('det-trabajador').innerText = pago.trabajador.name;
                document.getElementById('det-fecha').innerText = new Date(pago.fecha_pago.split('T')[0] + 'T12:00:00Z').toLocaleDateString();
                document.getElementById('det-sueldo').innerText = `Q ${parseFloat(pago.sueldo_base || 0).toFixed(2)}`;
                document.getElementById('det-descuentos').innerText = `Q ${parseFloat(pago.descuentos || 0).toFixed(2)}`;
                document.getElementById('det-metodo').innerText = pago.metodo_pago;
                document.getElementById('det-monto').innerText = `Q ${parseFloat(pago.monto_total).toFixed(2)}`;

                const obsContainer = document.getElementById('det-observaciones-container');
                if (pago.observaciones) {
                    document.getElementById('det-observaciones').innerText = pago.observaciones;
                    obsContainer.classList.remove('hidden');
                } else {
                    obsContainer.classList.add('hidden');
                }

                if (trabajos.length === 0) {
                    tbody.innerHTML = `<tr><td colspan="5" class="text-center py-4 text-gray-500">No hay trabajos registrados para este pago.</td></tr>`;
                } else {
                    trabajos.forEach(t => {
                        const tr = document.createElement('tr');
                        tr.innerHTML = `
                                <td class="text-xs text-gray-500">${t.fecha}</td>
                                <td class="font-bold text-blue-600">${t.orden_codigo}</td>
                                <td class="text-xs font-bold text-gray-600">${t.vehiculo}</td>
                                <td>
                                    <span class="badge ${t.actividad === 'mecanica' ? 'badge-primary' : 'badge-info'} badge-sm font-bold uppercase text-[9px] mb-1">${t.actividad}</span>
                                    <div class="text-xs italic text-gray-500 truncate max-w-[200px]" title="${t.descripcion}">${t.descripcion}</div>
                                </td>
                                <td class="font-black text-emerald-600">Q ${parseFloat(t.monto).toFixed(2)}</td>
                            `;
                        tbody.appendChild(tr);
                    });
                }

                loader.classList.add('hidden');
                contenido.classList.remove('hidden');

            } catch (err) {
                console.error(err);
                loader.classList.add('hidden');
                Swal.fire('Error', 'No se pudieron cargar los detalles del pago', 'error');
                modal.close();
            }
        }

        async function cargarDatosTrabajador() {
            const userId = document.getElementById('user_id').value;
            if (!userId) return;

            const inicio = document.getElementById('fecha_inicio_periodo').value;
            const fin = document.getElementById('fecha_fin_periodo').value;

            // Mostrar loaders
            document.getElementById('stats-asistencia').classList.add('hidden');
            document.getElementById('loading-asistencia').classList.remove('hidden');

            document.getElementById('lista-trabajos-body').innerHTML = '';
            document.getElementById('loading-trabajos').classList.remove('hidden');
            document.getElementById('check-all').disabled = true;
            document.getElementById('check-all').checked = false;

            recalcularTotal();

            try {
                const url = `{{ route('panel.planilla.pagos.trabajadorData', ['userId' => ':id']) }}`
                    .replace(':id', userId) +
                    `?inicio=${inicio}&fin=${fin}`;

                const resp = await fetch(url);
                const data = await resp.json();

                // Llenar asistencia
                document.getElementById('loading-asistencia').classList.add('hidden');
                const stAsistencia = document.getElementById('stats-asistencia');
                stAsistencia.classList.remove('hidden');

                stAsistencia.innerHTML = `
                        <div class="bg-white p-3 rounded-lg shadow-sm border border-gray-100">
                            <div class="text-xs text-gray-500 uppercase">Presente</div>
                            <div class="text-2xl font-bold text-success">${data.asistencia.presente}</div>
                        </div>
                        <div class="bg-white p-3 rounded-lg shadow-sm border border-gray-100">
                            <div class="text-xs text-gray-500 uppercase">Ausente</div>
                            <div class="text-2xl font-bold text-error">${data.asistencia.ausente}</div>
                        </div>
                        <div class="bg-white p-3 rounded-lg shadow-sm border border-gray-100">
                            <div class="text-xs text-gray-500 uppercase">Tardanzas</div>
                            <div class="text-2xl font-bold text-warning">${data.asistencia.tardanzas}</div>
                        </div>
                        <div class="bg-white p-3 rounded-lg shadow-sm border border-gray-100">
                            <div class="text-xs text-gray-500 uppercase">Permisos</div>
                            <div class="text-2xl font-bold text-info">${data.asistencia.permiso}</div>
                        </div>
                    `;

                // Llenar trabajos
                document.getElementById('loading-trabajos').classList.add('hidden');
                const tbodyTr = document.getElementById('lista-trabajos-body');

                if (data.trabajos.length === 0) {
                    tbodyTr.innerHTML = `<tr><td colspan="5" class="text-center py-6 text-gray-400">No hay trabajos pendientes para cobrar</td></tr>`;
                    document.getElementById('btn-submit').disabled = true;
                    return;
                }

                document.getElementById('check-all').disabled = false;
                document.getElementById('btn-submit').disabled = false;

                data.trabajos.forEach(t => {
                    const tr = document.createElement('tr');
                    tr.classList.add('hover:bg-blue-50/50', 'transition-colors');

                    // Mapear color basado en tipo de actividad
                    let badgeClass = 'badge-ghost';
                    if (t.tipo_actividad === 'mecanica') badgeClass = 'badge-primary';
                    if (t.tipo_actividad === 'diagnostico') badgeClass = 'badge-info';

                    let vehiculo = t.orden?.vehiculo?.placa || 'N/A';
                    if (t.orden && t.orden.vehiculo && t.orden.vehiculo.modelo) {
                        vehiculo += ' (' + t.orden.vehiculo.modelo.nombre + ')';
                    }

                    // CÁLCULO DE PAGO
                    let montoCalculado = 0;
                    let textoCalculo = '';

                    let base = parseFloat(t.precio_cliente || 0) - parseFloat(t.descuento_cliente || 0);
                    let porcentaje_monto = parseFloat(t.valor_pago_mecanico || 0);

                    if (t.tipo_pago_mecanico === 'porcentaje') {
                        montoCalculado = base * (porcentaje_monto / 100);
                        textoCalculo = `<div class="text-[9px] text-gray-400 font-bold tracking-widest uppercase mt-1">${porcentaje_monto}% de Q.${base.toFixed(2)}</div>`;
                    } else {
                        montoCalculado = porcentaje_monto;
                        textoCalculo = `<div class="text-[9px] text-gray-400 font-bold tracking-widest uppercase mt-1">Monto Fijo</div>`;
                    }

                    tr.innerHTML = `
                            <td>
                                <label>
                                    <input type="checkbox" name="trabajos[]" value="${t.id}" class="checkbox checkbox-sm checkbox-primary tr-checkbox" onchange="recalcularTotal()" />
                                </label>
                            </td>
                            <td class="text-gray-500 font-bold text-xs">${new Date(t.created_at).toLocaleDateString()}</td>
                            <td>
                            <div class="font-black text-blue-600 tracking-tighter">${t.orden?.codigo_orden || '-'}</div>
                            <div class="text-[9px] font-bold text-gray-400 uppercase break-words max-w-[150px]">${vehiculo}</div>
                        </td>
                        <td class="max-w-xs">
                            <span class="badge ${badgeClass} border-none font-bold badge-sm uppercase mb-1">${t.tipo_actividad}</span>
                            <div class="text-xs text-gray-600 font-medium italic whitespace-normal break-words">${t.descripcion}</div>
                        </td>
                            <td>
                                <div class="flex items-center">
                                    <span class="text-gray-500 font-black px-1">Q</span>
                                    <input type="number" step="0.01" min="0" name="montos[${t.id}]" class="input input-bordered input-xs w-20 monto-trabajo font-mono font-black text-emerald-600 text-right bg-white shadow-sm" value="${montoCalculado.toFixed(2)}" onkeyup="recalcularTotal()" onchange="recalcularTotal()">
                                </div>
                                ${textoCalculo}
                            </td>
                        `;
                    tbodyTr.appendChild(tr);
                });

            } catch (err) {
                console.error(err);
                Swal.fire('Error', 'No se pudieron cargar los datos del trabajador', 'error');
            }
        }

        function toggleAllChecks(source) {
            const checkboxes = document.querySelectorAll('.tr-checkbox');
            checkboxes.forEach(cb => {
                cb.checked = source.checked;
            });
            recalcularTotal();
        }

        function recalcularTotal() {
            let total = 0;
            const filas = document.querySelectorAll('#lista-trabajos-body tr');
            let checkedCount = 0;

            filas.forEach(tr => {
                const cb = tr.querySelector('.tr-checkbox');
                if (cb && cb.checked) {
                    checkedCount++;
                    const inputMonto = tr.querySelector('.monto-trabajo');
                    if (inputMonto) {
                        const val = parseFloat(inputMonto.value) || 0;
                        total += val;
                    }
                }
            });

            // Leer sueldo base y descuentos
            const sueldoBase = parseFloat(document.getElementById('sueldo_base').value) || 0;
            const descuentos = parseFloat(document.getElementById('descuentos').value) || 0;

            let totalPagar = total + sueldoBase - descuentos;
            if (totalPagar < 0) totalPagar = 0;

            document.getElementById('display-total').innerText = 'Q ' + totalPagar.toFixed(2);
            document.getElementById('input-monto_total').value = totalPagar.toFixed(2);

            // Desactivar boton si no hay nada seleccionado (aunque sea 0 de monto, debe haber trabajos)
            const btn = document.getElementById('btn-submit');

            // Verifica todos
            const allCbs = document.querySelectorAll('.tr-checkbox');
            const checkAll = document.getElementById('check-all');
            if (allCbs.length > 0) {
                checkAll.checked = (checkedCount === allCbs.length);
                btn.disabled = checkedCount === 0;
            } else {
                btn.disabled = true;
            }
        }

        async function procesarPago() {
            // Collect data
            const formData = new FormData(document.getElementById('form-nuevo-pago'));

            // Ensure at least one checked
            const trabajos = formData.getAll('trabajos[]');
            if (trabajos.length === 0) {
                Swal.fire('Atención', 'Debe seleccionar al menos un trabajo para procesar el pago.', 'warning');
                return;
            }

            const btn = document.getElementById('btn-submit');
            btn.innerHTML = '<span class="loading loading-spinner loading-sm"></span> Procesando...';
            btn.disabled = true;

            try {
                const resp = await fetch('{{ route("panel.planilla.pagos.store") }}', {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json'
                    },
                    body: formData
                });

                const data = await resp.json();

                if (resp.ok && data.success) {
                    Swal.fire({
                        icon: 'success',
                        title: '¡Pagado!',
                        text: data.message,
                        timer: 2000,
                        showConfirmButton: false
                    });

                    document.getElementById('modal_nuevo_pago').close();
                    document.getElementById('form-nuevo-pago').reset();
                    document.getElementById('lista-trabajos-body').innerHTML = `<tr><td colspan="5" class="text-center py-8 text-gray-400">Seleccione un trabajador para ver sus trabajos pendientes</td></tr>`;
                    document.getElementById('stats-asistencia').innerHTML = '';
                    document.getElementById('display-total').innerText = '$0.00';

                    cargarTablaPagos();
                } else {
                    throw new Error(data.message || 'Error de servidor');
                }
            } catch (err) {
                console.error(err);
                Swal.fire('Error', err.message, 'error');
            } finally {
                btn.innerHTML = '<i class="fas fa-save"></i> Procesar Pago';
                btn.disabled = false;
            }
        }
    </script>
@endpush