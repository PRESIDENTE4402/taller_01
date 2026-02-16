@extends('layouts.panel')

@section('content')
<div class="container mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-bold text-gray-900">Gestión de Clientes</h1>
        <button onclick="openModal('create')" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded shadow">
            <i class="fas fa-plus mr-2"></i> Nuevo Cliente
        </button>
    </div>

    <!-- Filtros y Búsqueda -->
    <div class="mb-6 bg-white rounded-lg shadow p-4">
        <div class="flex gap-4">
            <div class="flex-1">
                <input type="text" id="searchInput" placeholder="Buscar por nombre, teléfono, email o NIT..."
                    class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
            </div>
        </div>
    </div>

    <!-- Tabla -->
    <div class="bg-white rounded-lg shadow overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nombre / Empresa</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Contacto</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">NIT / Dirección</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Vehículos</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Acciones</th>
                    </tr>
                </thead>
                <tbody id="clientesTableBody" class="bg-white divide-y divide-gray-200">
                    <!-- Loaded via JS -->
                </tbody>
            </table>
        </div>
        <div id="pagination" class="px-6 py-4 border-t border-gray-200">
            <!-- Pagination Controls -->
        </div>
    </div>
</div>

<!-- Modal Create/Edit -->
<div id="clientModal" class="fixed inset-0 z-50 hidden overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
    <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
        <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" aria-hidden="true" onclick="closeModal()"></div>

        <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

        <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
            <form id="clientForm" onsubmit="saveClient(event)">
                @csrf
                <input type="hidden" id="clientId" name="id">
                <input type="hidden" id="methodField" name="_method" value="POST">

                <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                    <h3 class="text-lg leading-6 font-medium text-gray-900 mb-4" id="modalTitle">Nuevo Cliente</h3>

                    <div class="grid grid-cols-1 gap-4">
                        <!-- Tipo Cliente -->
                        <div class="flex items-center mb-2">
                            <input type="checkbox" id="es_empresa" name="es_empresa" class="rounded border-gray-300 text-blue-600 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50" onchange="toggleEmpresaFields()">
                            <label for="es_empresa" class="ml-2 block text-sm text-gray-900">
                                ¿Es Empresa?
                            </label>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700">Nombre Completo *</label>
                            <input type="text" name="nombre_completo" id="nombre_completo" required
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                        </div>

                        <div id="empresaField" class="hidden">
                            <label class="block text-sm font-medium text-gray-700">Nombre Empresa *</label>
                            <input type="text" name="empresa" id="empresa"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700">Teléfono *</label>
                            <input type="text" name="telefono" id="telefono" required
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700">Email</label>
                            <input type="email" name="email" id="email"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                        </div>

                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700">NIT</label>
                                <input type="text" name="nit" id="nit"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                            </div>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700">Dirección</label>
                            <textarea name="direccion" id="direccion" rows="2"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"></textarea>
                        </div>
                    </div>
                </div>

                <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                    <button type="submit" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-blue-600 text-base font-medium text-white hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 sm:ml-3 sm:w-auto sm:text-sm">
                        Guardar
                    </button>
                    <button type="button" onclick="closeModal()" class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
                        Cancelar
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        loadClientes();

        // Debounce search
        let timeout;
        document.getElementById('searchInput').addEventListener('input', function() {
            clearTimeout(timeout);
            timeout = setTimeout(() => loadClientes(), 500);
        });
    });

    function loadClientes(page = 1) {
        const search = document.getElementById('searchInput').value;
        const url = `{{ route('panel.clientes.list') }}?page=${page}&search=${search}`;

        fetch(url)
            .then(response => response.json())
            .then(data => {
                const tbody = document.getElementById('clientesTableBody');
                tbody.innerHTML = '';

                data.data.forEach(cliente => {
                    const vehiculosBadge = cliente.vehiculos_count > 0 ?
                        `<span class="bg-green-100 text-green-800 text-xs font-semibold mr-2 px-2.5 py-0.5 rounded">${cliente.vehiculos_count} Autos</span>` :
                        `<span class="bg-gray-100 text-gray-800 text-xs font-semibold mr-2 px-2.5 py-0.5 rounded">Sin Autos</span>`;

                    const row = `
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <a href="/panel/clientes/${cliente.id}" class="text-sm font-medium text-blue-600 hover:underline cursor-pointer">${cliente.nombre_completo}</a>
                                <div class="text-sm text-gray-500">${cliente.es_empresa ? (cliente.empresa || 'Empresa') : 'Particular'}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm text-gray-900">${cliente.telefono}</div>
                                <div class="text-sm text-gray-500">${cliente.email || '-'}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm text-gray-900">NIT: ${cliente.nit || 'N/A'}</div>
                                <div class="text-sm text-gray-500 truncate max-w-xs" title="${cliente.direccion}">${cliente.direccion || '-'}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                ${vehiculosBadge}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                <button onclick='editClient(${JSON.stringify(cliente)})' class="text-indigo-600 hover:text-indigo-900 mr-3">Editar</button>
                                <button onclick="deleteClient(${cliente.id})" class="text-red-600 hover:text-red-900">Eliminar</button>
                            </td>
                        </tr>
                    `;
                    tbody.innerHTML += row;
                });

                renderPagination(data);
            });
    }

    function renderPagination(data) {
        const pagination = document.getElementById('pagination');
        let html = '';

        if (data.last_page > 1) {
            html += `<nav class="flex justify-center"><ul class="flex pl-0 rounded list-none flex-wrap">`;
            // Previous
            if (data.prev_page_url) {
                html += `<li><button onclick="loadClientes(${data.current_page - 1})" class="bg-white border-gray-300 text-gray-500 hover:bg-gray-50 relative inline-flex items-center px-4 py-2 border text-sm font-medium">Anterior</button></li>`;
            }
            // Next
            if (data.next_page_url) {
                html += `<li><button onclick="loadClientes(${data.current_page + 1})" class="bg-white border-gray-300 text-gray-500 hover:bg-gray-50 relative inline-flex items-center px-4 py-2 border text-sm font-medium ml-2">Siguiente</button></li>`;
            }
            html += `</ul></nav>`;
        }
        pagination.innerHTML = html;
    }

    // Modal Logic
    function openModal(mode, data = null) {
        const modal = document.getElementById('clientModal');
        const form = document.getElementById('clientForm');
        const title = document.getElementById('modalTitle');
        const methodField = document.getElementById('methodField');
        const idField = document.getElementById('clientId');

        modal.classList.remove('hidden');

        if (mode === 'create') {
            title.textContent = 'Nuevo Cliente';
            form.reset();
            methodField.value = 'POST';
            idField.value = '';
            toggleEmpresaFields();
        } else {
            title.textContent = 'Editar Cliente';
            methodField.value = 'PUT';
            idField.value = data.id;

            document.getElementById('nombre_completo').value = data.nombre_completo;
            document.getElementById('telefono').value = data.telefono;
            document.getElementById('email').value = data.email || '';
            document.getElementById('nit').value = data.nit || '';
            document.getElementById('direccion').value = data.direccion || '';

            document.getElementById('es_empresa').checked = data.es_empresa;
            document.getElementById('empresa').value = data.empresa || '';

            toggleEmpresaFields();
        }
    }

    function closeModal() {
        document.getElementById('clientModal').classList.add('hidden');
    }

    function toggleEmpresaFields() {
        const isEmpresa = document.getElementById('es_empresa').checked;
        const empresaField = document.getElementById('empresaField');
        const empresaInput = document.getElementById('empresa');

        if (isEmpresa) {
            empresaField.classList.remove('hidden');
            empresaInput.setAttribute('required', 'required');
        } else {
            empresaField.classList.add('hidden');
            empresaInput.removeAttribute('required');
        }
    }

    function editClient(client) {
        openModal('edit', client);
    }

    function saveClient(event) {
        event.preventDefault();
        const form = event.target;
        const formData = new FormData(form);
        const id = document.getElementById('clientId').value;
        const isUpdate = id !== '';

        let url = isUpdate ? `{{ url('panel/clientes') }}/${id}` : `{{ route('panel.clientes.store') }}`;

        // For PUT/PATCH we typically send POST with _method field which we have

        fetch(url, {
                method: 'POST', // Always POST because of FormData, Laravel handles _method
                body: formData,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                    // 'Content-Type': 'multipart/form-data' // Fetch sets this automatically with boundary
                }
            })
            .then(response => {
                if (!response.ok) {
                    return response.json().then(err => {
                        throw err;
                    });
                }
                return response.json();
            })
            .then(data => {
                if (data.success) {
                    // alert(data.message); // Replace with toast if available
                    closeModal();
                    loadClientes();
                }
            })
            .catch(error => {
                console.error(error);
                let msg = 'Error al guardar';
                if (error.errors) {
                    msg = Object.values(error.errors).flat().join('\n');
                } else if (error.message) {
                    msg = error.message;
                }
                alert(msg);
            });
    }

    function deleteClient(id) {
        if (!confirm('¿Está seguro de eliminar este cliente?')) return;

        fetch(`{{ url('panel/clientes') }}/${id}`, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('input[name="_token"]').value,
                    'Content-Type': 'application/json',
                    'Accept': 'application/json'
                },
                body: JSON.stringify({
                    _method: 'DELETE'
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    loadClientes();
                } else {
                    alert(data.message);
                }
            })
            .catch(error => alert('Error al eliminar'));
    }
</script>
@endsection