document.addEventListener('DOMContentLoaded', function () {
    loadRoles();
    fetchPermissions();
});

let isEditing = false;
let currentId = null;
let allPermissions = [];

const modal = document.getElementById('roleModal');
const modalBackdrop = document.getElementById('modalBackdrop');
const modalPanel = document.getElementById('modalPanel');
const modalTitle = document.getElementById('modalTitle');
const nameInput = document.getElementById('roleName');
const descInput = document.getElementById('roleDesc');
const errorName = document.getElementById('errorName');

async function loadRoles() {
    const grid = document.getElementById('rolesGrid');
    const empty = document.getElementById('emptyState');

    try {
        const response = await fetch(`${API_URL}/list`);
        const data = await response.json();

        grid.innerHTML = '';

        if (data.length === 0) {
            empty.classList.remove('hidden');
            empty.classList.add('flex');
            return;
        }
        empty.classList.add('hidden');
        empty.classList.remove('flex');

        data.forEach(role => {
            const card = document.createElement('div');
            card.className = 'bg-white p-5 rounded-xl shadow-sm border border-gray-100 flex flex-col hover:shadow-md transition-shadow';

            const permsCount = role.permissions ? role.permissions.length : 0;
            const permsBadges = role.permissions ? role.permissions.slice(0, 3).map(p => `
                <span class="inline-flex items-center rounded-md bg-blue-50 px-1.5 py-0.5 text-[10px] font-medium text-blue-700 ring-1 ring-inset ring-blue-700/10">${p.nombre}</span>
            `).join('') : '';

            card.innerHTML = `
                <div class="flex justify-between items-start mb-3">
                    <div class="flex items-center gap-3">
                        <div class="h-10 w-10 rounded-full bg-blue-50 flex items-center justify-center text-blue-600">
                            <i class="fas fa-user-shield"></i>
                        </div>
                        <div>
                            <h4 class="font-bold text-gray-800">${role.nombre}</h4>
                            <span class="text-[10px] text-gray-400 font-medium uppercase tracking-wider">${permsCount} Permisos</span>
                        </div>
                    </div>
                    <div class="flex gap-1">
                        <button onclick='editRole(${JSON.stringify(role)})' class="text-gray-400 hover:text-blue-600 p-1.5"><i class="fas fa-pen"></i></button>
                        <button onclick="deleteRole(${role.id})" class="text-gray-400 hover:text-red-600 p-1.5"><i class="fas fa-trash"></i></button>
                    </div>
                </div>
                <div class="mt-auto">
                    <p class="text-xs text-gray-500 line-clamp-2 mb-3 h-8">${role.descripcion || 'Sin descripción'}</p>
                    <div class="flex flex-wrap gap-1">
                        ${permsBadges}
                        ${permsCount > 3 ? `<span class="text-[9px] text-gray-400"> +${permsCount - 3} más</span>` : ''}
                    </div>
                </div>
            `;
            grid.appendChild(card);
        });
    } catch (e) {
        console.error(e);
    }
}

function openModal(edit = false) {
    modal.classList.remove('hidden');
    setTimeout(() => {
        modalBackdrop.classList.remove('opacity-0');
        modalPanel.classList.remove('opacity-0', 'translate-y-4', 'sm:translate-y-0', 'sm:scale-95');
        modalPanel.classList.add('opacity-100', 'translate-y-0', 'sm:scale-100');
    }, 10);

    if (!edit) {
        isEditing = false;
        document.getElementById('roleForm').reset();
        modalTitle.textContent = 'Nuevo Rol';
    }
}

function closeModal() {
    modalBackdrop.classList.add('opacity-0');
    modalPanel.classList.remove('opacity-100', 'translate-y-0', 'sm:scale-100');
    modalPanel.classList.add('opacity-0', 'translate-y-4', 'sm:translate-y-0', 'sm:scale-95');
    setTimeout(() => {
        modal.classList.add('hidden');
    }, 300);
}

async function fetchPermissions() {
    try {
        const response = await fetch(`${PERMISSIONS_API_URL}/list`);
        allPermissions = await response.json();
        renderPermissionCheckboxes();
    } catch (e) {
        console.error("Error loading permissions:", e);
    }
}

function renderPermissionCheckboxes() {
    const list = document.getElementById('permissionsCheckboxList');
    if (!list) return;

    list.innerHTML = allPermissions.map(p => `
        <label class="flex items-center gap-2 p-2 rounded hover:bg-blue-50 cursor-pointer transition-colors border border-transparent hover:border-blue-100">
            <input type="checkbox" name="permisos[]" value="${p.id}" class="rounded border-gray-300 text-blue-600 focus:ring-blue-500">
            <div class="flex flex-col">
                <span class="text-xs font-semibold text-gray-700">${p.nombre}</span>
                <span class="text-[9px] text-gray-400 font-mono">${p.slug}</span>
            </div>
        </label>
    `).join('');
}

function editRole(role) {
    isEditing = true;
    currentId = role.id;
    nameInput.value = role.nombre;
    descInput.value = role.descripcion || '';
    modalTitle.textContent = 'Editar Rol';

    // Check relevant boxes
    const checkboxes = document.querySelectorAll('#permissionsCheckboxList input[type="checkbox"]');
    checkboxes.forEach(cb => {
        cb.checked = role.permissions ? role.permissions.some(p => p.id == cb.value) : false;
    });

    openModal(true);
}

async function saveRole(e) {
    e.preventDefault();
    errorName.classList.add('hidden');

    const nombre = nameInput.value;
    const descripcion = descInput.value;
    const selectedPerms = Array.from(document.querySelectorAll('#permissionsCheckboxList input[type="checkbox"]:checked'))
        .map(cb => cb.value);

    if (!nombre.trim()) return;

    const url = isEditing ? `${API_URL}/${currentId}` : API_URL;
    const method = isEditing ? 'PUT' : 'POST';

    try {
        const response = await fetch(url, {
            method: method,
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': CSRF_TOKEN,
                'Accept': 'application/json'
            },
            body: JSON.stringify({
                nombre,
                descripcion,
                permisos: selectedPerms
            })
        });

        const result = await response.json();

        if (!response.ok) {
            if (result.errors?.nombre) {
                errorName.textContent = result.errors.nombre[0];
                errorName.classList.remove('hidden');
            }
            return;
        }

        closeModal();
        loadRoles();
        Swal.fire({
            icon: 'success',
            title: isEditing ? 'Actualizado' : 'Creado',
            toast: true,
            position: 'top-end',
            showConfirmButton: false,
            timer: 3000,
            background: '#1e293b',
            color: '#ffffff'
        });

    } catch (error) {
        console.error(error);
    }
}

function deleteRole(id) {
    Swal.fire({
        title: '¿Eliminar Rol?',
        text: "Esta acción no se puede deshacer.",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#ef4444',
        cancelButtonColor: '#3b82f6',
        confirmButtonText: 'Sí, eliminar',
        cancelButtonText: 'Cancelar',
        background: '#0f172a',
        color: '#f8fafc',
        customClass: {
            popup: 'border border-slate-700 rounded-xl'
        }
    }).then(async (result) => {
        if (result.isConfirmed) {
            try {
                const response = await fetch(`${API_URL}/${id}`, {
                    method: 'DELETE',
                    headers: { 'X-CSRF-TOKEN': CSRF_TOKEN }
                });
                if (response.ok) {
                    loadRoles();
                    Swal.fire({
                        title: 'Eliminado',
                        text: 'El rol ha sido eliminado.',
                        icon: 'success',
                        background: '#1e293b',
                        color: '#ffffff',
                        timer: 2000,
                        showConfirmButton: false
                    });
                }
            } catch (e) {
                console.error(e);
            }
        }
    });
}

window.openModal = openModal;
window.closeModal = closeModal;
window.saveRole = saveRole;
window.editRole = editRole;
window.deleteRole = deleteRole;
