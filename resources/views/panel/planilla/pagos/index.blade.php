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

    <!-- Filtros y herramientas pordían ir acá -->
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
    <div class="modal-box w-11/12 max-w-5xl rounded-lg">
        <form method="dialog">
            <button class="btn btn-sm btn-circle btn-ghost absolute right-2 top-2">✕</button>
        </form>
        <h3 class="font-bold text-lg text-gray-800 border-b pb-3 mb-4"><i class="fas fa-money-check-alt text-success mr-2"></i> Procesar Pago a Trabajador</h3>

        <form id="form-nuevo-pago" onsubmit="event.preventDefault(); procesarPago();">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">

                <!-- Columna Izquierda: Datos del Pago -->
                <div class="md:col-span-1 space-y-4">
                    <div class="form-control">
                        <label class="label"><span class="label-text font-semibold">Seleccionar Trabajador</span></label>
                        <select name="user_id" id="user_id" class="select select-bordered w-full" required onchange="cargarDatosTrabajador()">
                            <option value="" disabled selected>Seleccione un trabajador...</option>
                            @foreach($trabajadores as $trabajador)
                            <option value="{{ $trabajador->id }}">{{ $trabajador->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="form-control">
                        <label class="label"><span class="label-text font-semibold">Fecha de Pago</span></label>
                        <input type="date" name="fecha_pago" id="fecha_pago" class="input input-bordered w-full" value="{{ date('Y-m-d') }}" required>
                    </div>

                    <div class="grid grid-cols-2 gap-2">
                        <div class="form-control">
                            <label class="label"><span class="label-text font-semibold text-xs text-gray-500">Inicio Período (Ref)</span></label>
                            <input type="date" name="fecha_inicio_periodo" id="fecha_inicio_periodo" class="input input-bordered input-sm w-full" onchange="cargarDatosTrabajador()">
                        </div>
                        <div class="form-control">
                            <label class="label"><span class="label-text font-semibold text-xs text-gray-500">Fin Período (Ref)</span></label>
                            <input type="date" name="fecha_fin_periodo" id="fecha_fin_periodo" class="input input-bordered input-sm w-full" onchange="cargarDatosTrabajador()">
                        </div>
                    </div>

                    <div class="form-control">
                        <label class="label"><span class="label-text font-semibold">Método de Pago</span></label>
                        <select name="metodo_pago" id="metodo_pago" class="select select-bordered w-full" required>
                            <option value="EFECTIVO">Efectivo</option>
                            <option value="TRANSFERENCIA">Transferencia</option>
                            <option value="CHEQUE">Cheque</option>
                        </select>
                    </div>

                    <div class="form-control">
                        <label class="label"><span class="label-text font-semibold">Observaciones</span></label>
                        <textarea name="observaciones" id="observaciones" class="textarea textarea-bordered h-24" placeholder="Alguna nota o concepto especial..."></textarea>
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
                            <h4 class="font-bold text-gray-700 text-sm mb-3 uppercase tracking-wide"><i class="fas fa-clipboard-user mr-1"></i> Resumen de Asistencia del Período</h4>

                            <div id="loading-asistencia" class="text-center py-4 hidden">
                                <span class="loading loading-spinner text-primary"></span>
                            </div>

                            <div id="stats-asistencia" class="grid grid-cols-2 md:grid-cols-4 gap-4 text-center">
                                <div class="bg-white p-2 rounded shadow-sm border border-gray-100 placeholder-box opacity-50">
                                    <div class="text-xs text-gray-400">Seleccione trabajador</div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Trabajos Pendientes -->
                    <div class="card bg-white border border-gray-200 flex-1 flex flex-col">
                        <div class="card-body p-4 flex flex-col h-full overflow-hidden">
                            <div class="flex justify-between items-center mb-3">
                                <h4 class="font-bold text-gray-700 text-sm uppercase tracking-wide"><i class="fas fa-wrench mr-1"></i> Trabajos Pendientes de Pago</h4>
                                <div class="text-xs text-gray-500 italic">No dependen de las fechas de arriba</div>
                            </div>

                            <div id="loading-trabajos" class="text-center py-8 hidden">
                                <span class="loading loading-spinner text-primary"></span>
                            </div>

                            <div class="overflow-y-auto max-h-[300px] flex-1 border rounded-lg bg-gray-50 relative custom-scrollbar">
                                <table class="table table-xs w-full">
                                    <thead class="bg-gray-100 sticky top-0 z-10 shadow-sm">
                                        <tr>
                                            <th>
                                                <label>
                                                    <input type="checkbox" id="check-all" class="checkbox checkbox-xs" onchange="toggleAllChecks(this)" disabled />
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
                <button type="button" class="btn btn-ghost" onclick="document.getElementById('modal_nuevo_pago').close()">Cancelar</button>
                <button type="submit" class="btn btn-primary" id="btn-submit" disabled>
                    <i class="fas fa-save"></i> Procesar Pago
                </button>
            </div>
        </form>
    </div>
</dialog>

@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', () => {
        // Establecer fechas por defecto: primer y ultimo dia del mes actial
        const date = new Date();
        const firstDay = new Date(date.getFullYear(), date.getMonth(), 1);
        const lastDay = new Date(date.getFullYear(), date.getMonth() + 1, 0);

        document.getElementById('fecha_inicio_periodo').value = firstDay.toISOString().split('T')[0];
        document.getElementById('fecha_fin_periodo').value = lastDay.toISOString().split('T')[0];

        cargarTablaPagos();
    });

    async function cargarTablaPagos() {
        const tbody = document.getElementById('tabla-pagos-body');
        const loader = document.getElementById('loading-pagos');

        tbody.innerHTML = '';
        loader.classList.remove('hidden');

        try {
            const resp = await fetch('{{ route("panel.planilla.pagos.list") }}');
            const data = await resp.json();

            loader.classList.add('hidden');

            if (data.length === 0) {
                tbody.innerHTML = `<tr><td colspan="6" class="text-center py-6 text-gray-500">No hay pagos registrados</td></tr>`;
                return;
            }

            data.forEach(p => {
                const tr = document.createElement('tr');
                tr.innerHTML = `
                    <td class="font-medium text-gray-800">
                        <div class="flex items-center gap-3">
                            <div class="avatar placeholder">
                                <div class="bg-gray-200 text-gray-600 rounded-full w-8 h-8">
                                    <span class="text-xs uppercase">${p.trabajador.name.substring(0,2)}</span>
                                </div>
                            </div>
                            ${p.trabajador.name}
                        </div>
                    </td>
                    <td class="text-gray-600">${new Date(p.fecha_pago + 'T12:00:00Z').toLocaleDateString()}</td>
                    <td class="text-xs text-gray-500">
                        ${p.fecha_inicio_periodo ? new Date(p.fecha_inicio_periodo + 'T12:00:00Z').toLocaleDateString() : '-'} 
                        al 
                        ${p.fecha_fin_periodo ? new Date(p.fecha_fin_periodo + 'T12:00:00Z').toLocaleDateString() : '-'}
                    </td>
                    <td class="font-bold text-success">$${parseFloat(p.monto_total).toFixed(2)}</td>
                    <td><span class="badge badge-ghost badge-sm border-gray-200">${p.metodo_pago}</span></td>
                    <td class="text-right">
                        <!-- Botón para ver detalles (Opcional, se puede programar luego) -->
                        <button class="btn btn-xs btn-ghost text-blue-500" title="Ver Detalles"><i class="fas fa-eye"></i></button>
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

                tr.innerHTML = `
                    <td>
                        <label>
                            <input type="checkbox" name="trabajos[]" value="${t.id}" class="checkbox checkbox-sm checkbox-primary tr-checkbox" onchange="recalcularTotal()" />
                        </label>
                    </td>
                    <td class="text-gray-500">${new Date(t.created_at).toLocaleDateString()}</td>
                    <td>
                        <div class="font-medium text-gray-800">${t.orden?.codigo_orden || '-'}</div>
                        <div class="text-[10px] text-gray-400 truncate max-w-[120px]">${vehiculo}</div>
                    </td>
                    <td>
                        <span class="badge ${badgeClass} badge-sm capitalize mb-1">${t.tipo_actividad}</span>
                        <div class="text-xs text-gray-600 truncate max-w-[150px]" title="${t.descripcion}">${t.descripcion}</div>
                    </td>
                    <td>
                        <div class="input-group input-group-sm flex items-center">
                            <span class="text-gray-500 px-1">$</span>
                            <input type="number" step="0.01" min="0" class="input input-bordered input-xs w-20 monto-trabajo text-right" value="0.00" onkeyup="recalcularTotal()" onchange="recalcularTotal()">
                        </div>
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

        document.getElementById('display-total').innerText = '$' + total.toFixed(2);
        document.getElementById('input-monto_total').value = total.toFixed(2);

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