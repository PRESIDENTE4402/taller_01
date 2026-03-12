document.addEventListener('DOMContentLoaded', function () {
    loadUsers();
    loadRoles();
    loadSucursales();

    document.getElementById('searchInput').addEventListener('input', function (e) {
        const term = e.target.value.toLowerCase();
        const cards = document.querySelectorAll('#usersGrid > div');
        let hasVisible = false;

        cards.forEach(card => {
            const name = card.dataset.name.toLowerCase();
            const email = card.dataset.email.toLowerCase();
            if (name.includes(term) || email.includes(term)) {
                card.style.display = 'flex';
                hasVisible = true;
            } else {
                card.style.display = 'none';
            }
        });

        const empty = document.getElementById('emptyState');
        if (!hasVisible && cards.length > 0) {
            empty.classList.remove('hidden');
            empty.classList.add('flex');
        } else {
            empty.classList.add('hidden');
            empty.classList.remove('flex');
        }
    });
});


let currentUserId = null;
let allRoles = [];
let allSucursales = [];
let allUsers = []; // Store users to avoid re-fetching for edit

const getAssignElements = () => ({
    modal: document.getElementById('assignRoleModal'),
    modalBackdrop: document.getElementById('modalBackdrop'),
    modalPanel: document.getElementById('modalPanel'),
    roleSelect: document.getElementById('roleSelect'),
    modalUserInitial: document.getElementById('modalUserInitial'),
    modalUserName: document.getElementById('modalUserName'),
    modalUserEmail: document.getElementById('modalUserEmail')
});

// DOM Elements getter for Create Modal
const getCreateElements = () => ({
    createModal: document.getElementById('createUserModal'),
    createBackdrop: document.getElementById('createBackdrop'),
    createPanel: document.getElementById('createPanel'),
    newUserRole: document.getElementById('newUserRole'),
    newUserSucursal: document.getElementById('newUserSucursal')
});

async function loadRoles() {
    try {
        const response = await fetch(`${window.API_URL}/roles-list`);
        allRoles = await response.json();

        // Populate Assign Role Select if it exists
        const { roleSelect } = getAssignElements();
        if (roleSelect) {
            roleSelect.innerHTML = '<option value="">-- Sin Rol --</option>';
            allRoles.forEach(role => {
                const opt = document.createElement('option');
                opt.value = role.id;
                opt.textContent = role.nombre;
                roleSelect.appendChild(opt);
            });
        }
    } catch (e) {
        console.error(e);
    }
}

async function loadSucursales() {
    try {
        const response = await fetch(`${window.API_URL}/sucursales-list`);
        allSucursales = await response.json();
    } catch (e) {
        console.error(e);
    }
}

async function loadUsers() {
    const grid = document.getElementById('usersGrid');
    const empty = document.getElementById('emptyState');

    try {
        const response = await fetch(`${window.API_URL}/list`);
        const users = await response.json();
        allUsers = users; // Store for valid edit access

        if (!grid) return;
        grid.innerHTML = '';

        if (users.length === 0) {
            if (empty) {
                empty.classList.remove('hidden');
                empty.classList.add('flex');
            }
            return;
        }
        if (empty) {
            empty.classList.add('hidden');
            empty.classList.remove('flex');
        }

        users.forEach(user => {
            const currentRole = user.roles.length > 0 ? user.roles[0].nombre : 'Sin Rol';
            const currentRoleId = user.roles.length > 0 ? user.roles[0].id : '';
            const sucursalName = user.sucursales && user.sucursales.length > 0 ? user.sucursales[0].nombre : 'Sin Sucursal';

            const card = document.createElement('div');
            card.className = 'bg-white rounded-xl shadow-sm border border-gray-100 hover:shadow-md transition-all flex flex-col overflow-hidden group';
            card.dataset.name = user.name;
            card.dataset.email = user.email;

            card.innerHTML = `
                <div class="p-5 flex items-center gap-4">
                    <div class="h-12 w-12 rounded-full bg-gradient-to-br from-blue-500 to-indigo-600 flex items-center justify-center text-white font-bold text-xl shadow-md shrink-0">
                        ${user.name.charAt(0).toUpperCase()}
                    </div>
                    <div class="min-w-0 flex-1">
                        <h4 class="font-bold text-gray-800 leading-tight truncate" title="${user.name}">${user.name}</h4>
                        <p class="text-xs text-gray-500 mb-1 truncate" title="${user.email}">${user.email}</p>
                        <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium ${user.roles.length > 0 ? 'bg-blue-100 text-blue-800' : 'bg-gray-100 text-gray-800'}">
                            ${currentRole}
                        </span>
                        <div class="mt-1 text-xs text-gray-400">
                            <i class="fas fa-store mr-1"></i> ${sucursalName}
                        </div>
                    </div>
                </div>
                <div class="mt-auto border-t border-gray-100 bg-gray-50 flex divide-x divide-gray-200/50">
                    <button onclick="openEditModal(${user.id})" 
                        class="flex-1 py-3 text-xs font-semibold text-gray-600 hover:text-blue-600 hover:bg-white transition-colors">
                        <i class="fas fa-edit mr-1"></i> Editar
                    </button>
                    <button onclick="openAssignModal(${user.id}, '${user.name.replace(/'/g, "\\'")}', '${user.email}', '${currentRoleId}')" 
                        class="flex-1 py-3 text-xs font-semibold text-gray-600 hover:text-indigo-600 hover:bg-white transition-colors">
                        <i class="fas fa-user-shield mr-1"></i> Roles
                    </button>
                </div>
            `;
            grid.appendChild(card);
        });
    } catch (e) {
        console.error(e);
    }
}

function openAssignModal(userId, name, email, roleId) {
    currentUserId = userId;
    const { modal, modalBackdrop, modalPanel, roleSelect, modalUserInitial, modalUserName, modalUserEmail } = getAssignElements();

    if (modalUserInitial) modalUserInitial.textContent = name.charAt(0).toUpperCase();
    if (modalUserName) modalUserName.textContent = name;
    if (modalUserEmail) modalUserEmail.textContent = email;
    if (roleSelect) roleSelect.value = roleId || "";

    if (modal) modal.classList.remove('hidden');
    setTimeout(() => {
        if (modalBackdrop) modalBackdrop.classList.remove('opacity-0');
        if (modalPanel) {
            modalPanel.classList.remove('opacity-0', 'translate-y-4', 'sm:translate-y-0', 'sm:scale-95');
            modalPanel.classList.add('opacity-100', 'translate-y-0', 'sm:scale-100');
        }
    }, 10);
}

function closeModal() {
    const { modal, modalBackdrop, modalPanel } = getAssignElements();

    if (modalBackdrop) modalBackdrop.classList.add('opacity-0');
    if (modalPanel) {
        modalPanel.classList.remove('opacity-100', 'translate-y-0', 'sm:scale-100');
        modalPanel.classList.add('opacity-0', 'translate-y-4', 'sm:translate-y-0', 'sm:scale-95');
    }
    setTimeout(() => {
        if (modal) modal.classList.add('hidden');
    }, 300);
}

async function saveAssignment(e) {
    e.preventDefault();
    const { roleSelect } = getAssignElements();

    const roleId = roleSelect ? roleSelect.value : null;
    if (!roleId) {
        Swal.fire({
            icon: 'warning',
            text: 'Por favor selecciona un rol.',
            toast: true,
            position: 'top-end',
            showConfirmButton: false,
            timer: 3000,
            background: '#1e293b',
            color: '#ffffff'
        });
        return;
    }

    try {
        const response = await fetch(`${window.API_URL}/${currentUserId}/assign-role`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': window.CSRF_TOKEN,
                'Accept': 'application/json'
            },
            body: JSON.stringify({ role_id: roleId })
        });

        if (response.ok) {
            closeModal();
            loadUsers();
            Swal.fire({
                icon: 'success',
                title: 'Rol Asignado',
                text: 'El usuario ha sido actualizado.',
                toast: true,
                position: 'top-end',
                showConfirmButton: false,
                timer: 3000,
                background: '#1e293b',
                color: '#ffffff'
            });
        }
    } catch (e) {
        console.error(e);
    }
}

function openCreateModal() {
    const { createModal, createBackdrop, createPanel, newUserRole, newUserSucursal } = getCreateElements();

    // Populate Roles dropdown if empty
    if (newUserRole && newUserRole.options.length <= 1 && allRoles.length > 0) {
        newUserRole.innerHTML = '<option value="">-- Sin Rol --</option>';
        allRoles.forEach(role => {
            const opt = document.createElement('option');
            opt.value = role.id;
            opt.textContent = role.nombre;
            newUserRole.appendChild(opt);
        });
    }

    // Populate Sucursales dropdown if empty
    if (newUserSucursal && newUserSucursal.options.length <= 1 && allSucursales.length > 0) {
        newUserSucursal.innerHTML = '<option value="">-- Seleccionar Sucursal --</option>';
        allSucursales.forEach(suc => {
            const opt = document.createElement('option');
            opt.value = suc.id;
            opt.textContent = suc.nombre;
            newUserSucursal.appendChild(opt);
        });
    }

    if (createModal) createModal.classList.remove('hidden');
    setTimeout(() => {
        if (createBackdrop) createBackdrop.classList.remove('opacity-0');
        if (createPanel) {
            createPanel.classList.remove('opacity-0', 'translate-y-4', 'sm:translate-y-0', 'sm:scale-95');
            createPanel.classList.add('opacity-100', 'translate-y-0', 'sm:scale-100');
        }
    }, 10);
}

function closeCreateModal() {
    const { createModal, createBackdrop, createPanel } = getCreateElements();

    if (createBackdrop) createBackdrop.classList.add('opacity-0');
    if (createPanel) {
        createPanel.classList.remove('opacity-100', 'translate-y-0', 'sm:scale-100');
        createPanel.classList.add('opacity-0', 'translate-y-4', 'sm:translate-y-0', 'sm:scale-95');
    }
    setTimeout(() => {
        if (createModal) createModal.classList.add('hidden');
        document.getElementById('createUserForm').reset();
    }, 300);
}

async function saveUser(e) {
    e.preventDefault();
    const form = e.target;
    const formData = new FormData(form);
    const data = Object.fromEntries(formData.entries());

    const { newUserRole, newUserSucursal } = getCreateElements();
    const roleId = newUserRole ? newUserRole.value : null;
    const sucursalId = newUserSucursal ? newUserSucursal.value : null;

    if (roleId) data.role_id = roleId;
    if (sucursalId) data.sucursal_id = sucursalId;

    try {
        const response = await fetch(window.API_URL, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': window.CSRF_TOKEN,
                'Accept': 'application/json'
            },
            body: JSON.stringify(data)
        });

        const result = await response.json();

        if (!response.ok) {
            let errorMsg = result.message || 'Ocurrió un error al guardar.';
            if (result.errors && typeof result.errors === 'object') {
                errorMsg = Object.values(result.errors)[0][0];
            }
            throw new Error(errorMsg);
        }

        closeCreateModal();
        loadUsers();
        Swal.fire({
            icon: 'success',
            title: 'Usuario Creado',
            text: 'El usuario y perfil han sido registrados exitosamente.',
            toast: true,
            position: 'top-end',
            showConfirmButton: false,
            timer: 3000,
            background: '#1e293b',
            color: '#ffffff'
        });

    } catch (error) {
        console.error(error);
        Swal.fire({
            icon: 'error',
            title: 'Error',
            text: error.message,
            background: '#1e293b',
            color: '#ffffff'
        });
    }
}

function openEditModal(userId) {
    const user = allUsers.find(u => u.id === userId);
    if (!user) return;

    currentUserId = userId; // Set global currentUserId for update

    const { createModal, createBackdrop, createPanel, newUserRole, newUserSucursal } = getCreateElements();

    // Change Title (Visual only)
    const titleEl = createPanel.querySelector('h3 span');
    if (titleEl) titleEl.textContent = 'Editar Usuario';

    // Populate Form
    document.getElementById('newUserName').value = user.name;
    document.getElementById('newUserEmail').value = user.email;
    document.getElementById('newUserPassword').removeAttribute('required'); // Password optional on edit
    document.getElementById('newUserPassword').placeholder = "Dejar en blanco para mantener actual";

    // Populate Roles dropdown if empty
    if (newUserRole && newUserRole.options.length <= 1 && allRoles.length > 0) {
        newUserRole.innerHTML = '<option value="">-- Sin Rol --</option>';
        allRoles.forEach(role => {
            const opt = document.createElement('option');
            opt.value = role.id;
            opt.textContent = role.nombre;
            newUserRole.appendChild(opt);
        });
    }

    // Populate Sucursales dropdown if empty
    if (newUserSucursal && newUserSucursal.options.length <= 1 && allSucursales.length > 0) {
        newUserSucursal.innerHTML = '<option value="">-- Seleccionar Sucursal --</option>';
        allSucursales.forEach(suc => {
            const opt = document.createElement('option');
            opt.value = suc.id;
            opt.textContent = suc.nombre;
            newUserSucursal.appendChild(opt);
        });
    }

    // Set Role
    if (user.roles && user.roles.length > 0) {
        newUserRole.value = user.roles[0].id;
    }

    // Set Sucursal
    if (user.sucursales && user.sucursales.length > 0) {
        newUserSucursal.value = user.sucursales[0].id;
    }

    // Populate Persona Data
    if (user.persona) {
        const p = user.persona;
        document.getElementById('persNombres').value = p.nombres || '';
        document.getElementById('persApellidos').value = p.apellidos || '';
        document.getElementById('persEdad').value = p.edad || '';
        document.getElementById('persSexo').value = p.sexo || '';
        document.getElementById('persTelefono').value = p.telefono || '';
        document.getElementById('persDireccion').value = p.direccion || '';
        document.getElementById('persCursos').value = p.cursos || '';
    } else {
        // Clear persona fields if no persona but user exists (edge case)
        document.getElementById('persNombres').value = '';
        document.getElementById('persApellidos').value = '';
        document.getElementById('persEdad').value = '';
        document.getElementById('persSexo').value = '';
        document.getElementById('persTelefono').value = '';
        document.getElementById('persDireccion').value = '';
        document.getElementById('persCursos').value = '';
    }

    // Change Form Action
    const form = document.getElementById('createUserForm');
    form.onsubmit = updateUser; // Switch to update handler

    if (createModal) createModal.classList.remove('hidden');
    setTimeout(() => {
        if (createBackdrop) createBackdrop.classList.remove('opacity-0');
        if (createPanel) {
            createPanel.classList.remove('opacity-0', 'translate-y-4', 'sm:translate-y-0', 'sm:scale-95');
            createPanel.classList.add('opacity-100', 'translate-y-0', 'sm:scale-100');
        }
    }, 10);
}

async function updateUser(e) {
    e.preventDefault();
    const form = e.target;
    const formData = new FormData(form);
    const data = Object.fromEntries(formData.entries());

    const { newUserRole, newUserSucursal } = getCreateElements();
    const roleId = newUserRole ? newUserRole.value : null;
    const sucursalId = newUserSucursal ? newUserSucursal.value : null;

    if (roleId) data.role_id = roleId;
    if (sucursalId) data.sucursal_id = sucursalId;

    try {
        const response = await fetch(`${window.API_URL}/${currentUserId}`, {
            method: 'PUT',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': window.CSRF_TOKEN,
                'Accept': 'application/json'
            },
            body: JSON.stringify(data)
        });

        const result = await response.json();

        if (!response.ok) {
            let errorMsg = result.message || 'Ocurrió un error al actualizar.';
            if (result.errors && typeof result.errors === 'object') {
                errorMsg = Object.values(result.errors)[0][0];
            }
            throw new Error(errorMsg);
        }

        closeCreateModal();
        loadUsers();
        Swal.fire({
            icon: 'success',
            title: 'Usuario Actualizado',
            text: 'La información ha sido actualizada exitosamente.',
            toast: true,
            position: 'top-end',
            showConfirmButton: false,
            timer: 3000,
            background: '#1e293b',
            color: '#ffffff'
        });

    } catch (error) {
        console.error(error);
        Swal.fire({
            icon: 'error',
            title: 'Error',
            text: error.message,
            background: '#1e293b',
            color: '#ffffff'
        });
    }
}

window.loadUsers = loadUsers;
window.openAssignModal = openAssignModal;
window.closeModal = closeModal;
window.saveAssignment = saveAssignment;
window.openCreateModal = (isCreate = true) => {
    // Reset form generic logic
    const { createModal, createBackdrop, createPanel, newUserRole, newUserSucursal } = getCreateElements();
    // Populate Roles dropdown if empty
    if (newUserRole && newUserRole.options.length <= 1 && allRoles.length > 0) {
        newUserRole.innerHTML = '<option value="">-- Sin Rol --</option>';
        allRoles.forEach(role => {
            const opt = document.createElement('option');
            opt.value = role.id;
            opt.textContent = role.nombre;
            newUserRole.appendChild(opt);
        });
    }

    // Populate Sucursales dropdown if empty
    if (newUserSucursal && newUserSucursal.options.length <= 1 && allSucursales.length > 0) {
        newUserSucursal.innerHTML = '<option value="">-- Seleccionar Sucursal --</option>';
        allSucursales.forEach(suc => {
            const opt = document.createElement('option');
            opt.value = suc.id;
            opt.textContent = suc.nombre;
            newUserSucursal.appendChild(opt);
        });
    }

    // Reset specific create logic
    const titleEl = createPanel.querySelector('h3 span');
    if (titleEl) titleEl.textContent = 'Nuevo Registro de Personal';
    document.getElementById('createUserForm').reset();
    document.getElementById('newUserPassword').setAttribute('required', 'required');
    document.getElementById('newUserPassword').placeholder = " ";
    document.getElementById('createUserForm').onsubmit = saveUser;

    openCreateModalGeneric();
};
window.closeCreateModal = closeCreateModal;
window.saveUser = saveUser;
window.openEditModal = openEditModal;

function openCreateModalGeneric() {
    const { createModal, createBackdrop, createPanel } = getCreateElements();
    if (createModal) createModal.classList.remove('hidden');
    setTimeout(() => {
        if (createBackdrop) createBackdrop.classList.remove('opacity-0');
        if (createPanel) {
            createPanel.classList.remove('opacity-0', 'translate-y-4', 'sm:translate-y-0', 'sm:scale-95');
            createPanel.classList.add('opacity-100', 'translate-y-0', 'sm:scale-100');
        }
    }, 10);
}
