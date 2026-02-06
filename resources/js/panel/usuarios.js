document.addEventListener('DOMContentLoaded', function () {
    loadUsers();
    loadRoles();

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

const modal = document.getElementById('assignRoleModal');
const modalBackdrop = document.getElementById('modalBackdrop');
const modalPanel = document.getElementById('modalPanel');
const roleSelect = document.getElementById('roleSelect');

async function loadRoles() {
    try {
        const response = await fetch(`${API_URL}/roles-list`);
        allRoles = await response.json();

        roleSelect.innerHTML = '<option value="">-- Sin Rol --</option>';
        allRoles.forEach(role => {
            const css = document.createElement('option');
            css.value = role.id;
            css.textContent = role.nombre;
            roleSelect.appendChild(css);
        });
    } catch (e) {
        console.error(e);
    }
}

async function loadUsers() {
    const grid = document.getElementById('usersGrid');
    const empty = document.getElementById('emptyState');

    try {
        const response = await fetch(`${API_URL}/list`);
        const users = await response.json();

        grid.innerHTML = '';

        if (users.length === 0) {
            empty.classList.remove('hidden');
            empty.classList.add('flex');
            return;
        }
        empty.classList.add('hidden');
        empty.classList.remove('flex');

        users.forEach(user => {
            // Determinar rol actual (asumimos el primero si tiene varios, o 'Sin Rol')
            const currentRole = user.roles.length > 0 ? user.roles[0].nombre : 'Sin Rol';
            const currentRoleId = user.roles.length > 0 ? user.roles[0].id : '';

            const card = document.createElement('div');
            card.className = 'bg-white p-5 rounded-xl shadow-sm border border-gray-100 flex justify-between items-center hover:shadow-md transition-shadow';
            card.dataset.name = user.name;
            card.dataset.email = user.email;

            card.innerHTML = `
                <div class="flex items-center gap-4">
                    <div class="h-12 w-12 rounded-full bg-gradient-to-br from-blue-500 to-indigo-600 flex items-center justify-center text-white font-bold text-xl shadow-md">
                        ${user.name.charAt(0).toUpperCase()}
                    </div>
                    <div>
                        <h4 class="font-bold text-gray-800 leading-tight">${user.name}</h4>
                        <p class="text-xs text-gray-500 mb-1">${user.email}</p>
                        <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium ${user.roles.length > 0 ? 'bg-blue-100 text-blue-800' : 'bg-gray-100 text-gray-800'}">
                            ${currentRole}
                        </span>
                    </div>
                </div>
                <div>
                    <button onclick="openAssignModal(${user.id}, '${user.name.replace(/'/g, "\\'")}', '${user.email}', '${currentRoleId}')" 
                        class="text-sm bg-gray-50 hover:bg-blue-50 text-blue-600 hover:text-blue-700 font-medium py-2 px-4 rounded-lg transition-colors border border-gray-200">
                        <i class="fas fa-user-tag mr-1"></i> Asignar
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

    document.getElementById('modalUserInitial').textContent = name.charAt(0).toUpperCase();
    document.getElementById('modalUserName').textContent = name;
    document.getElementById('modalUserEmail').textContent = email;
    roleSelect.value = roleId || "";

    modal.classList.remove('hidden');
    setTimeout(() => {
        modalBackdrop.classList.remove('opacity-0');
        modalPanel.classList.remove('opacity-0', 'translate-y-4', 'sm:translate-y-0', 'sm:scale-95');
        modalPanel.classList.add('opacity-100', 'translate-y-0', 'sm:scale-100');
    }, 10);
}

function closeModal() {
    modalBackdrop.classList.add('opacity-0');
    modalPanel.classList.remove('opacity-100', 'translate-y-0', 'sm:scale-100');
    modalPanel.classList.add('opacity-0', 'translate-y-4', 'sm:translate-y-0', 'sm:scale-95');
    setTimeout(() => {
        modal.classList.add('hidden');
    }, 300);
}

async function saveAssignment(e) {
    e.preventDefault();

    const roleId = roleSelect.value;
    if (!roleId) {
        // Podríamos permitir desasignar, pero la lógica del controlador necesita role_id required.
        // Si queremos desasignar, el controlador debería ajustarse o enviar un valor especial.
        // Por ahora exigimos seleccionar un rol.
        Swal.fire({
            icon: 'warning',
            text: 'Por favor selecciona un rol.',
            toast: true,
            position: 'top-end',
            showConfirmButton: false,
            timer: 3000
        });
        return;
    }

    try {
        const response = await fetch(`${API_URL}/${currentUserId}/assign-role`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': CSRF_TOKEN,
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
                timer: 3000
            });
        }
    } catch (e) {
        console.error(e);
    }
}

window.loadUsers = loadUsers;
window.openAssignModal = openAssignModal;
window.closeModal = closeModal;
window.saveAssignment = saveAssignment;
