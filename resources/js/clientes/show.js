// Client Logic
const clientDataEl = document.getElementById('client-data');
const config = {
    esEmpresa: clientDataEl.dataset.esEmpresa === 'true',
    routes: {
        clientBase: clientDataEl.dataset.clientBase,
        vehicleStore: clientDataEl.dataset.vehicleStore,
        vehicleBase: clientDataEl.dataset.vehicleBase,
        getBrands: clientDataEl.dataset.getBrands,
        listModelsByMarca: clientDataEl.dataset.listModelsByMarca,
        listVersionsByModelo: clientDataEl.dataset.listVersionsByModelo // New
    }
};

function editCliente() {
    document.getElementById('clientModal').classList.remove('hidden');
    document.getElementById('es_empresa').checked = config.esEmpresa;
    document.getElementById('password').value = ''; // Always empty on open
    toggleEmpresaFields();
}

function closeClientModal() {
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

function showAlert(title, message, icon = 'success') {
    if (typeof Swal !== 'undefined') {
        Swal.fire({
            icon: icon,
            title: title,
            text: message,
            timer: 2000,
            showConfirmButton: false
        });
    } else {
        alert(message);
    }
}

function updateClientDOM(data) {
    // data is the full request data (formData converted) or we need the response from server with full model?
    // The server returns success: true, but maybe not the model?
    // ClienteController.php update method:
    // return back()->with('success', ...) or json?
    // Wait, ClienteController update method needs to return the updated client model for this to work perfectly 
    // without reloading. If it just returns success, we might have to use the form data.
    // Let's use form data for optimistic update or better, we should fetch/return the model.
    // Assuming we use the values from the form for now as they are what we sent.

    document.getElementById('clientName').textContent = document.getElementById('nombre_completo').value;
    document.getElementById('clientPhone').textContent = document.getElementById('telefono').value;
    document.getElementById('clientEmail').textContent = document.getElementById('email').value || 'Sin email';
    document.getElementById('clientNit').textContent = document.getElementById('nit').value || 'N/A';
    document.getElementById('clientAddress').textContent = document.getElementById('direccion').value || 'Sin dirección registrada';

    const isEmpresa = document.getElementById('es_empresa').checked;
    config.esEmpresa = isEmpresa; // Update local config

    const badge = document.getElementById('clientBadge');
    const badgeIcon = document.getElementById('clientBadgeIcon');
    const badgeText = document.getElementById('clientBadgeText');
    const empresaBlock = document.getElementById('infoEmpresaBlock');
    const empresaText = document.getElementById('clientEmpresa');

    if (isEmpresa) {
        badge.classList.remove('badge-ghost');
        badge.classList.add('badge-primary');
        badgeIcon.className = 'fas fa-building';
        badgeText.textContent = 'Corporativo';
        empresaBlock.classList.remove('hidden');
        empresaText.textContent = document.getElementById('empresa').value;
    } else {
        badge.classList.remove('badge-primary');
        badge.classList.add('badge-ghost');
        badgeIcon.className = 'fas fa-user';
        badgeText.textContent = 'Particular';
        empresaBlock.classList.add('hidden');
    }
}

function saveClient(event) {
    event.preventDefault();
    const form = event.target;
    const formData = new FormData(form);
    const id = document.getElementById('clientId').value;
    const url = `${config.routes.clientBase}/${id}`;

    fetch(url, {
        method: 'POST', // Laravel Method Spoofing
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
                updateClientDOM();
                closeClientModal();
                showAlert('¡Actualizado!', 'Datos del cliente actualizados correctamente.');
            }
        })
        .catch(error => {
            console.error(error);
            let msg = 'Error al guardar';
            if (error.errors) msg = Object.values(error.errors).flat().join('\n');
            else if (error.message) msg = error.message;
            showAlert('Error', msg, 'error');
        });
}

// Vehicle Logic
function openVehicleModal() {
    document.getElementById('vehicleModal').classList.remove('hidden');
    document.getElementById('vehicleForm').reset();
    document.getElementById('vehicleId').value = '';
    document.getElementById('vehicleModalTitle').innerText = 'Nuevo Vehículo';

    // Clear models and versions
    document.getElementById('modelo_id').innerHTML = '';
    document.getElementById('version_id').innerHTML = '<option value="">Seleccione Modelo primero</option>';
    document.getElementById('version_id').disabled = true;

    if (document.getElementById('marca_id').options.length <= 1) {
        loadBrands();
    }
}

// NOTE: editVehicle is not called from blade anymore, checking usage...
// Actually, looking at show.blade.php, there is an edit button for vehicles in the table.
// <button class="btn btn-ghost btn-xs text-blue-600"><i class="fas fa-edit"></i></button>
// It does NOT have an onclick/id attached in strict mode yet?
// Wait, the table loop in Step 704 line 154 has NO onclick handler!
// So editVehicle functionality was BROKEN or missing in the blade file I viewed?
// "TODO: Calcular última visita"
// Line 154: <button class="btn btn-ghost btn-xs text-blue-600"><i class="fas fa-edit"></i></button>
// It seems I need to attach it there too.


function closeVehicleModal() {
    document.getElementById('vehicleModal').classList.add('hidden');
}

function loadBrands() {
    // using window.routes.getBrands
    return fetch(config.routes.getBrands)
        .then(response => response.json())
        .then(data => {
            const select = document.getElementById('marca_id');
            select.innerHTML = '<option value="">Seleccione...</option>';
            data.forEach(marca => {
                select.innerHTML += `<option value="${marca.id}">${marca.nombre}</option>`;
            });
        });
}

// Models logic needed - reused/adapted from Citas logic
function loadModels(marcaId) {
    if (!marcaId) {
        document.getElementById('modelo_id').innerHTML = '<option value="">Seleccione Marca primero</option>';
        document.getElementById('version_id').innerHTML = '<option value="">Seleccione Modelo primero</option>';
        document.getElementById('version_id').disabled = true;
        return Promise.resolve();
    }

    const url = config.routes.listModelsByMarca.replace('PLACEHOLDER', marcaId); // 0 acts as placeholder

    return fetch(url)
        .then(response => response.json())
        .then(data => {
            const select = document.getElementById('modelo_id');
            select.innerHTML = '<option value="">Seleccione...</option>'; // Default option
            if (data.length === 0) {
                select.innerHTML = '<option value="">No hay modelos registrados</option>';
                return;
            }

            data.forEach(modelo => {
                select.innerHTML += `<option value="${modelo.id}">${modelo.nombre}</option>`;
            });

            // Reset version
            document.getElementById('version_id').innerHTML = '<option value="">Seleccione Modelo primero</option>';
            document.getElementById('version_id').disabled = true;
        })
        .catch(error => {
            console.error('Error loading models:', error);
            document.getElementById('modelo_id').innerHTML = '<option value="">Error al cargar modelos</option>';
        });
}

function loadVersions(modeloId) {
    if (!modeloId) {
        document.getElementById('version_id').innerHTML = '<option value="">Seleccione Modelo primero</option>';
        document.getElementById('version_id').disabled = true;
        return Promise.resolve();
    }

    const url = config.routes.listVersionsByModelo.replace('PLACEHOLDER', modeloId);

    return fetch(url)
        .then(response => response.json())
        .then(data => {
            const select = document.getElementById('version_id');
            select.innerHTML = '<option value="">-- Sin Versión Especifica --</option>'; // Default null option
            select.disabled = false;

            if (data.length > 0) {
                data.forEach(version => {
                    select.innerHTML += `<option value="${version.id}">${version.nombre}</option>`;
                });
            } else {
                select.innerHTML = '<option value="">No hay versiones disponibles</option>';
            }
        })
        .catch(error => {
            console.error('Error loading versions:', error);
            document.getElementById('version_id').innerHTML = '<option value="">Error loading versions</option>';
        });
}

function updateVehicleInTable(vehiculo) {
    const tbody = document.getElementById('vehiclesTableBody');
    // Sanitize and prepare data
    const rowId = `vehicle-row-${vehiculo.id}`;
    let row = document.getElementById(rowId);

    // Badge logic (not really complex here)
    const vehiculoJson = JSON.stringify(vehiculo).replace(/'/g, "&#39;");
    const versionText = vehiculo.version ? `<span class="badge badge-sm badge-ghost ml-1">${vehiculo.version.nombre}</span>` : '';

    // Logic for last visit in JS
    let lastVisitDate = '-';
    // Check if latest_orden is loaded and has fecha_recepcion
    if (vehiculo.latest_orden && vehiculo.latest_orden.fecha_recepcion) {
        // Parse date. Assuming ISO format or standard DB format
        const date = new Date(vehiculo.latest_orden.fecha_recepcion);
        // Format as dd/mm/yyyy
        const day = String(date.getDate()).padStart(2, '0');
        const month = String(date.getMonth() + 1).padStart(2, '0');
        const year = date.getFullYear();
        lastVisitDate = `${day}/${month}/${year}`;
    }

    const rowContent = `
        <td>
            <div class="flex items-center gap-3">
                <div class="avatar placeholder">
                    <div class="w-10 rounded bg-slate-100 text-slate-400">
                        <i class="fas fa-car text-lg"></i>
                    </div>
                </div>
                <div>
                    <div class="font-bold flex items-center gap-2">
                        ${vehiculo.marca.nombre} 
                        ${versionText}
                    </div>
                    <div class="text-xs opacity-50">${vehiculo.modelo.nombre}</div>
                </div>
            </div>
        </td>
        <td><span class="badge badge-outline font-mono font-bold">${vehiculo.placa}</span></td>
        <td>${vehiculo.anio}</td>
        <td class="text-slate-500 text-sm">${lastVisitDate}</td>
        <td class="text-right">
            <button class="btn btn-ghost btn-xs text-blue-600 btn-edit-vehicle" data-vehicle='${vehiculoJson}'><i class="fas fa-edit"></i></button>
        </td>
    `;

    if (row) {
        // Update existing
        row.innerHTML = rowContent;
    } else {
        // Create new
        row = document.createElement('tr');
        row.id = rowId;
        row.className = 'hover';
        row.innerHTML = rowContent;
        tbody.appendChild(row);

        // Hide empty state if explicit
        const emptyState = document.getElementById('vehiclesEmptyState');
        const tableContainer = document.getElementById('vehiclesTableContainer');
        if (emptyState) emptyState.classList.add('hidden');
        if (tableContainer) tableContainer.classList.remove('hidden');
    }
}

function saveVehicle(event) {
    event.preventDefault();
    const form = event.target;
    const formData = new FormData(form);
    const id = document.getElementById('vehicleId').value;
    const isEdit = id !== '';

    let url = config.routes.vehicleStore;
    let method = 'POST';

    if (isEdit) {
        url = `${config.routes.vehicleBase}/${id}`;
        formData.append('_method', 'PUT');
    }

    // Convert empty version to null implicitly or explicitly?
    // Laravel handles empty string as null if using Nullable middleware, but let's be safe
    // if (formData.get('version_id') === '') {
    //     formData.set('version_id', null); // Or remove it if backend expects absence for null
    // }

    fetch(url, {
        method: method,
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
                if (data.vehiculo) {
                    updateVehicleInTable(data.vehiculo);
                } else {
                    // Fallback if no vehicle returned (should not happen with my controller change)
                    location.reload();
                }
                closeVehicleModal();
                showAlert('¡Vehículo Guardado!', data.message);
            }
        })
        .catch(error => {
            console.error(error);
            let msg = 'Error al guardar';
            if (error.errors) msg = Object.values(error.errors).flat().join('\n');
            else if (error.message) msg = error.message;
            showAlert('Error', msg, 'error');
        });
}

// Event Listeners
document.addEventListener('DOMContentLoaded', () => {
    // Client Actions
    const btnEditClient = document.getElementById('btnEditClient');
    if (btnEditClient) btnEditClient.addEventListener('click', () => editCliente());

    const btnCancelClient = document.getElementById('btnCancelClient');
    if (btnCancelClient) btnCancelClient.addEventListener('click', closeClientModal);

    const clientBackdrop = document.getElementById('clientModalBackdrop');
    if (clientBackdrop) clientBackdrop.addEventListener('click', closeClientModal);

    const clientForm = document.getElementById('clientForm');
    if (clientForm) clientForm.addEventListener('submit', saveClient);

    const esEmpresaInput = document.getElementById('es_empresa');
    if (esEmpresaInput) esEmpresaInput.addEventListener('change', toggleEmpresaFields);


    // Vehicle Actions
    const btnAddVehicle = document.getElementById('btnAddVehicle');
    if (btnAddVehicle) btnAddVehicle.addEventListener('click', openVehicleModal);

    const btnRegisterFirst = document.getElementById('btnRegisterFirstVehicle');
    if (btnRegisterFirst) btnRegisterFirst.addEventListener('click', openVehicleModal);

    const btnCancelVehicle = document.getElementById('btnCancelVehicle');
    if (btnCancelVehicle) btnCancelVehicle.addEventListener('click', closeVehicleModal);

    const vehicleBackdrop = document.getElementById('vehicleModalBackdrop');
    if (vehicleBackdrop) vehicleBackdrop.addEventListener('click', closeVehicleModal);

    const vehicleForm = document.getElementById('vehicleForm');
    if (vehicleForm) vehicleForm.addEventListener('submit', saveVehicle);


    const marcaInput = document.getElementById('marca_id');
    if (marcaInput) marcaInput.addEventListener('change', (e) => loadModels(e.target.value));

    const modeloInput = document.getElementById('modelo_id');
    if (modeloInput) modeloInput.addEventListener('change', (e) => loadVersions(e.target.value));

    // Delegation for dynamic/list elements
    document.addEventListener('click', function (e) {
        const editBtn = e.target.closest('.btn-edit-vehicle');
        if (editBtn) {
            const vehicleData = JSON.parse(editBtn.dataset.vehicle);
            editVehicle(vehicleData);
        }
    });
});

function editVehicle(vehiculo) {
    document.getElementById('vehicleModal').classList.remove('hidden');
    document.getElementById('vehicleModalTitle').innerText = 'Editar Vehículo';
    document.getElementById('vehicleId').value = vehiculo.id;

    if (document.getElementById('marca_id').options.length <= 1) {
        loadBrands().then(() => {
            populateVehicleForm(vehiculo);
        });
    } else {
        populateVehicleForm(vehiculo);
    }
}

function populateVehicleForm(vehiculo) {
    document.getElementById('marca_id').value = vehiculo.marca_id;
    document.getElementById('placa').value = vehiculo.placa;
    document.getElementById('anio').value = vehiculo.anio;
    document.getElementById('color').value = vehiculo.color || '';
    document.getElementById('vin').value = vehiculo.vin || '';

    loadModels(vehiculo.marca_id).then(() => {
        document.getElementById('modelo_id').value = vehiculo.modelo_id;
        // Load versions after model is set
        loadVersions(vehiculo.modelo_id).then(() => {
            document.getElementById('version_id').value = vehiculo.version_id || '';
        });
    });
}

