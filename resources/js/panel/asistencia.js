const API_URL = '/panel/rrhh/asistencias';

// Elements
const btnStart = document.getElementById('btn-start-scanner');
const btnStop = document.getElementById('btn-stop-scanner');
const liveTableBody = document.getElementById('live-table-body');
const emptyState = document.getElementById('empty-state');
const filterDate = document.getElementById('filter-date');
const filterName = document.getElementById('filter-name');

let html5QrcodeScanner = null;
let isProcessing = false;

// --- 1. Scanner Logic ---
// ... (mantenemos lógica de escaneo igual, pero quitamos referencias a statusCard)

function onScanSuccess(decodedText, decodedResult) {
    if (isProcessing) return;
    isProcessing = true;
    let userId = decodedText;
    try {
        const data = JSON.parse(decodedText);
        if (data.id) userId = data.id;
    } catch (e) { }
    verifyAndPrompt(userId);
    if (html5QrcodeScanner) html5QrcodeScanner.pause();
}

function onScanFailure(error) { }

if (btnStart) {
    btnStart.addEventListener('click', () => {
        if (typeof Html5Qrcode === 'undefined') return;
        html5QrcodeScanner = new Html5Qrcode("reader");
        const config = { fps: 10, qrbox: { width: 250, height: 250 } };
        html5QrcodeScanner.start({ facingMode: "environment" }, config, onScanSuccess, onScanFailure)
            .then(() => {
                btnStart.classList.add('hidden');
                btnStop.classList.remove('hidden');
            });
    });
}

if (btnStop) {
    btnStop.addEventListener('click', () => {
        if (html5QrcodeScanner) {
            html5QrcodeScanner.stop().then(() => {
                html5QrcodeScanner.clear();
                btnStart.classList.remove('hidden');
                btnStop.classList.add('hidden');
            });
        }
    });
}

// --- 2. Verification & Modal Logic ---
let temporaryUserId = null;

function verifyAndPrompt(userId) {
    fetch(`${API_URL}/check-status/${userId}`)
        .then(res => res.json())
        .then(data => {
            temporaryUserId = userId;
            openConfirmModal(data);
        })
        .catch(err => {
            if (html5QrcodeScanner) {
                setTimeout(() => { if (html5QrcodeScanner) html5QrcodeScanner.resume(); isProcessing = false; }, 2000);
            } else {
                isProcessing = false;
            }
            Swal.fire({ icon: 'error', title: 'Error', text: 'Usuario no encontrado' });
        });
}

// ... (Mantenemos Modal Elements y logic de openConfirmModal igual, pero sin statusCard)
const confirmModal = document.getElementById('confirmModal');
const confirmBackdrop = document.getElementById('confirmBackdrop');
const confirmPanel = document.getElementById('confirmPanel');
const modalUserName = document.getElementById('modalUserName');
const radioEntrada = document.getElementById('radioEntrada');
const radioSalida = document.getElementById('radioSalida');
const selectTipoAsistencia = document.getElementById('selectTipoAsistencia');
const selectEstado = document.getElementById('selectEstado');
const badgeLlegada = document.getElementById('badgeLlegada');
const modalInputEntrada = document.getElementById('modalInputEntrada');
const modalInputSalida = document.getElementById('modalInputSalida');
const modalObservaciones = document.getElementById('modalObservaciones');
const confirmForm = document.getElementById('confirmForm');

let originalTimeEntrada = '';
let originalTimeSalida = '';

if (confirmForm) {
    confirmForm.addEventListener('submit', function (e) {
        window.submitAttendance(e);
    });
}

function openConfirmModal(data) {
    if (!modalUserName) return;
    modalUserName.textContent = data.user.display_name || data.user.name;
    const now = new Date();
    const hh = String(now.getHours()).padStart(2, '0');
    const mm = String(now.getMinutes()).padStart(2, '0');
    const nowStr = `${hh}:${mm}`;

    let valEntrada = '';
    let valSalida = '';
    modalObservaciones.value = '';
    selectTipoAsistencia.value = 'presente';

    if (data.asistencia) {
        valEntrada = data.asistencia.hora_entrada ? data.asistencia.hora_entrada.substring(0, 5) : '';
        valSalida = data.asistencia.hora_salida ? data.asistencia.hora_salida.substring(0, 5) : '';
        if (data.asistencia.observaciones) modalObservaciones.value = data.asistencia.observaciones;
        if (data.asistencia.tipo) selectTipoAsistencia.value = data.asistencia.tipo;
    }

    if (data.accion_sugerida === 'entrada' && !valEntrada) valEntrada = nowStr;
    if (data.accion_sugerida === 'salida' && !valSalida) valSalida = nowStr;

    if (modalInputEntrada) modalInputEntrada.value = valEntrada;
    if (modalInputSalida) modalInputSalida.value = valSalida;
    originalTimeEntrada = valEntrada;
    originalTimeSalida = valSalida;

    if (data.accion_sugerida === 'salida') radioSalida.checked = true;
    else radioEntrada.checked = true;

    const suggestedState = data.estado_sugerido || 'a_tiempo';
    selectEstado.value = suggestedState;

    if (suggestedState === 'tardanza' || suggestedState === 'falta_injustificada') {
        badgeLlegada.textContent = 'LLEGADA TARDÍA';
        badgeLlegada.className = 'badge badge-lg font-bold uppercase tracking-wider p-4 bg-orange-600 text-white border-none shadow-lg';
    } else if (suggestedState === 'a_tiempo') {
        badgeLlegada.textContent = 'A TIEMPO';
        badgeLlegada.className = 'badge badge-lg font-bold uppercase tracking-wider p-4 bg-green-600 text-white border-none shadow-lg';
    } else {
        badgeLlegada.textContent = suggestedState.replace('_', ' ');
        badgeLlegada.className = 'badge badge-lg font-bold uppercase tracking-wider p-4 bg-gray-600 text-white border-none shadow-lg';
    }

    if (confirmModal) confirmModal.classList.remove('hidden');
    setTimeout(() => {
        if (confirmBackdrop) confirmBackdrop.classList.remove('opacity-0');
        if (confirmPanel) {
            confirmPanel.classList.remove('opacity-0', 'translate-y-4', 'sm:translate-y-0', 'sm:scale-95');
            confirmPanel.classList.add('opacity-100', 'translate-y-0', 'sm:scale-100');
        }
    }, 10);
}

window.closeConfirmModal = function () {
    if (confirmBackdrop) confirmBackdrop.classList.add('opacity-0');
    if (confirmPanel) {
        confirmPanel.classList.remove('opacity-100', 'translate-y-0', 'sm:scale-100');
        confirmPanel.classList.add('opacity-0', 'translate-y-4', 'sm:translate-y-0', 'sm:scale-95');
    }
    setTimeout(() => {
        if (confirmModal) confirmModal.classList.add('hidden');
        if (html5QrcodeScanner) html5QrcodeScanner.resume();
        isProcessing = false;
        temporaryUserId = null;
    }, 300);
}

window.submitAttendance = function (e) {
    if (e) e.preventDefault();
    if (!temporaryUserId) return;
    const accion = radioEntrada.checked ? 'entrada' : 'salida';
    const tipo = selectTipoAsistencia.value;
    const estado = selectEstado.value;
    let obs = modalObservaciones.value;
    const nuevaEntrada = modalInputEntrada ? modalInputEntrada.value : '';
    const nuevaSalida = modalInputSalida ? modalInputSalida.value : '';

    if (nuevaEntrada && nuevaEntrada !== originalTimeEntrada) {
        obs = (obs ? obs + " | " : "") + `Entrada Editada: ${originalTimeEntrada || '--:--'} -> ${nuevaEntrada}`;
    }
    if (nuevaSalida && nuevaSalida !== originalTimeSalida) {
        obs = (obs ? obs + " | " : "") + `Salida Editada: ${originalTimeSalida || '--:--'} -> ${nuevaSalida}`;
    }

    const submitBtn = confirmForm ? confirmForm.querySelector('button[type="submit"]') : null;
    if (submitBtn) submitBtn.disabled = true;

    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');

    fetch(`${API_URL}/register`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': csrfToken,
            'Accept': 'application/json'
        },
        body: JSON.stringify({
            user_id: temporaryUserId,
            accion: accion,
            tipo_asistencia: tipo,
            estado: estado,
            observaciones: obs,
            hora_entrada: nuevaEntrada,
            hora_salida: nuevaSalida
        })
    })
        .then(res => res.json())
        .then(data => {
            if (data.type === 'error') throw new Error(data.message);
            Swal.fire({
                icon: 'success',
                title: 'Confirmado',
                text: data.message,
                timer: 2000,
                showConfirmButton: false,
                background: '#1A1B1E', color: '#fff',
                toast: true, position: 'top-end'
            });
            loadAttendanceReport();
            closeConfirmModal();
        })
        .catch(err => {
            Swal.fire({ icon: 'error', title: 'Error', text: err.message || 'Error al registrar', background: '#1A1B1E', color: '#fff' });
        })
        .finally(() => {
            if (submitBtn) submitBtn.disabled = false;
        });
}

// --- 3. Reporting & Filtering ---

function loadAttendanceReport() {
    const fecha = filterDate?.value || new Date().toISOString().split('T')[0];
    const term = filterName?.value || '';

    fetch(`${API_URL}/list?fecha=${fecha}&term=${encodeURIComponent(term)}`)
        .then(res => res.json())
        .then(data => {
            renderTable(data);
        });
}

function renderTable(data) {
    if (!liveTableBody) return;
    liveTableBody.innerHTML = '';

    if (data.length === 0) {
        if (emptyState) emptyState.classList.remove('hidden');
        return;
    }
    if (emptyState) emptyState.classList.add('hidden');

    data.forEach(item => {
        const entryTime = item.hora_entrada ? item.hora_entrada.substring(0, 5) : '--:--';
        const exitTime = item.hora_salida ? item.hora_salida.substring(0, 5) : '--:--';

        // Badge Logic
        let badgeClass = 'bg-emerald-500/10 text-emerald-500 border-emerald-500/20';
        let statusText = item.estado || 'Presente';

        if (item.tipo === 'falta') {
            badgeClass = 'bg-red-500/10 text-red-500 border-red-500/20';
            statusText = 'FALTA';
        } else if (item.estado === 'tardanza') {
            badgeClass = 'bg-amber-500/10 text-amber-500 border-amber-500/20';
            statusText = 'RETRASO';
        } else if (item.estado === 'pendiente') {
            badgeClass = 'bg-blue-500/10 text-blue-500 border-blue-500/20';
            statusText = 'PENDIENTE';
        }

        const row = document.createElement('tr');
        row.className = `group hover:bg-white/5 transition-colors ${!item.registrado ? 'opacity-70' : ''}`;

        row.innerHTML = `
            <td class="py-4 px-6 border-b border-gray-800/50">
                <div class="flex items-center gap-3">
                    <div class="w-8 h-8 rounded-lg bg-[#2C2E33] flex items-center justify-center text-xs font-bold text-gray-400 group-hover:bg-[#1C69D4] group-hover:text-white transition-colors">
                        ${item.nombre.charAt(0)}${item.nombre.split(' ').length > 1 ? item.nombre.split(' ')[1].charAt(0) : ''}
                    </div>
                    <div class="flex flex-col">
                        <span class="text-sm font-bold text-white group-hover:text-[#1C69D4] transition-colors">${item.nombre}</span>
                        <span class="text-[10px] text-gray-500 uppercase tracking-wider font-semibold">${item.sucursal || 'Sin Sucursal'}</span>
                    </div>
                </div>
            </td>
            <td class="py-4 px-4 text-center border-b border-gray-800/50">
                <span class="font-mono text-sm ${item.hora_entrada ? 'text-emerald-400 font-extrabold' : 'text-gray-600'}">
                    ${entryTime}
                </span>
            </td>
            <td class="py-4 px-4 text-center border-b border-gray-800/50">
                <span class="font-mono text-sm ${item.hora_salida ? 'text-rose-400 font-extrabold' : 'text-gray-600'}">
                    ${exitTime}
                </span>
            </td>
            <td class="py-4 px-4 text-center border-b border-gray-800/50">
                <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold border ${badgeClass} uppercase tracking-tighter">
                    ${statusText.replace('_', ' ')}
                </span>
            </td>
            <td class="py-4 px-4 border-b border-gray-800/50">
                <div class="text-[11px] text-gray-500 line-clamp-1 max-w-[150px] mx-auto text-center italic" title="${item.observaciones || ''}">
                    ${item.observaciones || '<span class="opacity-20">-</span>'}
                </div>
            </td>
            <td class="py-4 px-4 text-center border-b border-gray-800/50">
                <div class="flex items-center justify-center gap-1">
                    <button onclick="verifyAndPrompt(${item.user_id})" 
                            class="p-2 rounded-lg text-blue-400 hover:bg-blue-500/10 hover:text-white transition-all" 
                            title="Registrar / Editar">
                        <i class="fas fa-edit text-xs"></i>
                    </button>
                    ${item.registrado ? `
                        <button onclick="deleteAttendance(${item.id})" 
                                class="p-2 rounded-lg text-rose-400 hover:bg-rose-500/10 hover:text-white transition-all" 
                                title="Eliminar">
                            <i class="fas fa-trash-alt text-xs"></i>
                        </button>
                    ` : ''}
                </div>
            </td>
        `;
        liveTableBody.appendChild(row);
    });
}

// Event Listeners for filters
if (filterDate) filterDate.addEventListener('change', loadAttendanceReport);
if (filterName) filterName.addEventListener('input', () => {
    clearTimeout(window.searchTimer);
    window.searchTimer = setTimeout(loadAttendanceReport, 500);
});

// Make global for onclick handlers
window.verifyAndPrompt = verifyAndPrompt;

// Delete Logic
window.deleteAttendance = function (id) {
    Swal.fire({
        title: '¿Eliminar registro?',
        text: "Esta acción no se puede deshacer.",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#E11D48',
        cancelButtonColor: '#4B5563',
        confirmButtonText: 'Sí, eliminar',
        cancelButtonText: 'Cancelar',
        background: '#1A1B1E',
        color: '#fff'
    }).then((result) => {
        if (result.isConfirmed) {
            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
            fetch(`${API_URL}/${id}`, {
                method: 'DELETE',
                headers: { 'X-CSRF-TOKEN': csrfToken }
            }).then(() => {
                Swal.fire({
                    icon: 'success',
                    title: 'Eliminado',
                    toast: true,
                    position: 'top-end',
                    showConfirmButton: false,
                    timer: 2000,
                    background: '#1A1B1E', color: '#fff'
                });
                loadAttendanceReport();
            });
        }
    });
}

// Manual Input (Live Search for main scanner search) - We can keep it or remove it if redundant
const searchInput = document.getElementById('manual-user-search');
const searchResults = document.getElementById('search-results');
if (searchInput && searchResults) {
    searchInput.addEventListener('input', (e) => {
        const term = e.target.value.trim();
        if (term.length < 2) { searchResults.classList.add('hidden'); return; }
        fetch(`${API_URL}/search-users?term=${encodeURIComponent(term)}`)
            .then(res => res.json())
            .then(users => {
                searchResults.innerHTML = '';
                users.forEach(user => {
                    const li = document.createElement('li');
                    li.className = 'px-4 py-3 text-sm text-white hover:bg-blue-600 cursor-pointer border-b border-gray-700';
                    li.innerHTML = `<span class="font-bold">${user.name}</span>`;
                    li.onclick = () => { verifyAndPrompt(user.id); searchResults.classList.add('hidden'); };
                    searchResults.appendChild(li);
                });
                searchResults.classList.remove('hidden');
            });
    });
}

// Clock
setInterval(() => {
    const liveClock = document.getElementById('live-clock');
    if (liveClock) liveClock.textContent = new Date().toLocaleTimeString('es-CL');
}, 1000);

// Init
loadAttendanceReport();
