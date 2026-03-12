// Config
let routes = {};

function init() {
    const dataEl = document.getElementById('client-list-data');
    if (dataEl) {
        routes = {
            list: dataEl.dataset.routeList,
            store: dataEl.dataset.routeStore,
            base: dataEl.dataset.routeBase
        };
    }
    loadClientes();
    setupEventListeners();
}

function loadClientes(page = 1) {
    const search = document.getElementById('searchInput').value;
    const url = `${routes.list}?page=${page}&search=${search}`;

    fetch(url)
        .then(response => response.json())
        .then(data => {
            const tbody = document.getElementById('clientesTableBody');
            tbody.innerHTML = '';

            data.data.forEach(cliente => {
                const vehiculosBadge = cliente.vehiculos_count > 0 ?
                    `<span class="bg-green-100 text-green-800 text-xs font-semibold mr-2 px-2.5 py-0.5 rounded">${cliente.vehiculos_count} Autos</span>` :
                    `<span class="bg-gray-100 text-gray-800 text-xs font-semibold mr-2 px-2.5 py-0.5 rounded">Sin Autos</span>`;

                // We use data attributes for actions instead of onclick
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
                            <button class="text-indigo-600 hover:text-indigo-900 mr-3 btn-edit-client" data-client='${JSON.stringify(cliente).replace(/'/g, "&#39;")}'>Editar</button>
                            <button class="text-red-600 hover:text-red-900 btn-delete-client" data-id="${cliente.id}">Eliminar</button>
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
        if (data.prev_page_url) {
            html += `<li><button class="bg-white border-gray-300 text-gray-500 hover:bg-gray-50 relative inline-flex items-center px-4 py-2 border text-sm font-medium btn-page" data-page="${data.current_page - 1}">Anterior</button></li>`;
        }
        if (data.next_page_url) {
            html += `<li><button class="bg-white border-gray-300 text-gray-500 hover:bg-gray-50 relative inline-flex items-center px-4 py-2 border text-sm font-medium ml-2 btn-page" data-page="${data.current_page + 1}">Siguiente</button></li>`;
        }
        html += `</ul></nav>`;
    }
    pagination.innerHTML = html;
}

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
        document.getElementById('password').value = ''; // Always empty on open for safety

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

function saveClient(event) {
    event.preventDefault();
    const form = event.target;
    const formData = new FormData(form);
    const id = document.getElementById('clientId').value;
    const isUpdate = id !== '';

    let url = isUpdate ? `${routes.base}/${id}` : routes.store;

    fetch(url, {
        method: 'POST',
        body: formData,
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'Accept': 'application/json'
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
                closeModal();
                loadClientes();
            }
        })
        .catch(error => {
            console.error(error);
            let msg = 'Ocurrió un error inesperado al guardar el cliente.';
            if (error.errors && typeof error.errors === 'object') {
                // Toma solo el primer error para no saturar la pantalla
                msg = Object.values(error.errors)[0][0]; 
            } else if (error.message) {
                msg = error.message;
            }
            
            Swal.fire({
                icon: 'warning',
                title: 'No se pudo guardar',
                text: msg,
                background: '#1e293b',
                color: '#ffffff',
                confirmButtonColor: '#3b82f6'
            });
        });
}

function deleteClient(id) {
    if (!confirm('¿Está seguro de eliminar este cliente?')) return;

    // Need csrf for delete if not using formData
    const token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

    fetch(`${routes.base}/${id}`, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': token,
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

function setupEventListeners() {
    // Static
    const btnCreate = document.getElementById('btnCreateClient');
    if (btnCreate) btnCreate.addEventListener('click', () => openModal('create'));

    const btnCancel = document.getElementById('btnCancelClient');
    if (btnCancel) btnCancel.addEventListener('click', closeModal);

    const backdrop = document.getElementById('modalBackdrop');
    if (backdrop) backdrop.addEventListener('click', closeModal);

    const form = document.getElementById('clientForm');
    if (form) form.addEventListener('submit', saveClient);

    const esEmpresa = document.getElementById('es_empresa');
    if (esEmpresa) esEmpresa.addEventListener('change', toggleEmpresaFields);

    const searchInput = document.getElementById('searchInput');
    if (searchInput) {
        let timeout;
        searchInput.addEventListener('input', function () {
            clearTimeout(timeout);
            timeout = setTimeout(() => loadClientes(), 500);
        });
    }

    // Dynamic (Delegation for Table and Pagination)
    document.addEventListener('click', function (e) {
        // Edit btn
        const editBtn = e.target.closest('.btn-edit-client');
        if (editBtn) {
            const clientData = JSON.parse(editBtn.dataset.client);
            openModal('edit', clientData);
        }

        // Delete btn
        const deleteBtn = e.target.closest('.btn-delete-client');
        if (deleteBtn) {
            deleteClient(deleteBtn.dataset.id);
        }

        // Pagination
        const pageBtn = e.target.closest('.btn-page');
        if (pageBtn) {
            loadClientes(pageBtn.dataset.page);
        }
    });

}

document.addEventListener('DOMContentLoaded', init);

