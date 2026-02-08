
const API_URL = '/panel/rrhh/asistencias';

// Elements
const btnStart = document.getElementById('btn-start-scanner');
const btnStop = document.getElementById('btn-stop-scanner');
const statusCard = document.getElementById('status-card');
const liveTableBody = document.getElementById('live-table-body');
const emptyState = document.getElementById('empty-state');

let html5QrcodeScanner = null;
let isProcessing = false;

// --- 1. Scanner Logic ---

function onScanSuccess(decodedText, decodedResult) {
    if (isProcessing) return;
    isProcessing = true;

    // Intentar parsear si es JSON o usar texto plano como ID
    let userId = decodedText;
    try {
        const data = JSON.parse(decodedText);
        if (data.id) userId = data.id;
    } catch (e) { }

    // En lugar de procesar directo, verificamos y abrimos modal
    verifyAndPrompt(userId);

    // Pause briefly
    if (html5QrcodeScanner) {
        html5QrcodeScanner.pause();
    }
}

function onScanFailure(error) {
    // Console warn optional
}

if (btnStart) {
    btnStart.addEventListener('click', () => {
        if (typeof Html5Qrcode === 'undefined') {
            console.error('Html5Qrcode library not loaded');
            return;
        }

        html5QrcodeScanner = new Html5Qrcode("reader");
        const config = { fps: 10, qrbox: { width: 250, height: 250 } };

        html5QrcodeScanner.start({ facingMode: "environment" }, config, onScanSuccess, onScanFailure)
            .then(() => {
                btnStart.classList.add('hidden');
                btnStop.classList.remove('hidden');
            })
            .catch(err => {
                console.error(err);
                Swal.fire({ icon: 'error', title: 'Error', text: 'No se pudo acceder a la cámara.' });
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
    updateStatusCard(null, 'loading');
    console.log('Verifying ID:', userId);

    fetch(`${API_URL}/check-status/${userId}`)
        .then(res => {
            if (!res.ok) throw new Error('Usuario no encontrado o error en servidor');
            return res.json();
        })
        .then(data => {
            temporaryUserId = userId;

            // Show identified name on Status Card immediately
            const nameEl = document.getElementById('scanned-name');
            const idEl = document.getElementById('scanned-id');
            if (nameEl) {
                nameEl.textContent = data.user.name;
                nameEl.classList.add('text-blue-400');
            }
            if (idEl) idEl.textContent = `ID: ${data.user.id}`;

            openConfirmModal(data);
        })
        .catch(err => {
            // Resume if error
            if (html5QrcodeScanner) {
                setTimeout(() => { if (html5QrcodeScanner) html5QrcodeScanner.resume(); isProcessing = false; }, 2000);
            } else {
                isProcessing = false;
            }

            const soundError = document.getElementById('scan-sound-error');
            if (soundError) soundError.play();
            updateStatusCard(null, 'error', err.message);
        });
}

// Modal Elements
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

    // Name
    modalUserName.textContent = data.user.display_name || data.user.name;

    // Time Setup
    const now = new Date();
    const hh = String(now.getHours()).padStart(2, '0');
    const mm = String(now.getMinutes()).padStart(2, '0');
    const nowStr = `${hh}:${mm}`;

    // Defaults
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

    // Action Selection
    if (data.accion_sugerida === 'salida') {
        radioSalida.checked = true;
    } else {
        radioEntrada.checked = true;
    }

    // Status Badge & Select Defaults
    // Controller sends existing status as suggested if record exists
    const suggestedState = data.estado_sugerido || 'a_tiempo';
    selectEstado.value = suggestedState;

    // Visual Feedback for Status
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

    // Show Modal
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

        // Resume Scanner loop
        if (html5QrcodeScanner) {
            html5QrcodeScanner.resume();
        }
        isProcessing = false;
        temporaryUserId = null;
        updateStatusCard(null); // Clear loading
    }, 300);
}

window.togglePermisoOptions = function () {
    // No longer needed
}

window.submitAttendance = function (e) {
    if (e) e.preventDefault();
    if (!temporaryUserId) return;

    // Harvest Data
    const accion = radioEntrada.checked ? 'entrada' : 'salida';
    const tipo = selectTipoAsistencia.value;
    const estado = selectEstado.value;
    let obs = modalObservaciones.value;

    const nuevaEntrada = modalInputEntrada ? modalInputEntrada.value : '';
    const nuevaSalida = modalInputSalida ? modalInputSalida.value : '';

    // Detect Changes
    if (nuevaEntrada && nuevaEntrada !== originalTimeEntrada) {
        obs = (obs ? obs + " | " : "") + `Entrada Editada: ${originalTimeEntrada || '--:--'} -> ${nuevaEntrada}`;
    }
    if (nuevaSalida && nuevaSalida !== originalTimeSalida) {
        obs = (obs ? obs + " | " : "") + `Salida Editada: ${originalTimeSalida || '--:--'} -> ${nuevaSalida}`;
    }

    // Disable UI
    const submitBtn = confirmForm ? confirmForm.querySelector('button[type="submit"]') : null;
    if (submitBtn) submitBtn.disabled = true;

    const csrfMeta = document.querySelector('meta[name="csrf-token"]');
    const csrfToken = csrfMeta ? csrfMeta.getAttribute('content') : '';

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

            // Success Sound
            const soundSuccess = document.getElementById('scan-sound-success');
            if (soundSuccess) soundSuccess.play();

            updateStatusCard(data);

            Swal.fire({
                icon: 'success',
                title: data.type === 'entrada' ? 'Entrada Confirmada' : 'Salida Confirmada',
                text: data.message,
                timer: 2000,
                showConfirmButton: false,
                background: '#1A1B1E', color: '#fff',
                toast: true, position: 'top-end'
            });

            loadTodayHistory();
            closeConfirmModal();
        })
        .catch(err => {
            console.error(err);
            const soundError = document.getElementById('scan-sound-error');
            if (soundError) soundError.play();

            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: err.message || 'Error al registrar',
                background: '#1A1B1E', color: '#fff'
            });
        })
        .finally(() => {
            if (submitBtn) submitBtn.disabled = false;
        });
}


// --- 3. UI Helpers (Update Status Card, Table, clock etc...) ---
// (Keep existing logic for updateStatusCard, loadTodayHistory, renderTable, Clock)

function updateStatusCard(data, state = 'success', errorMsg = '') {
    const card = document.getElementById('status-card');
    const nameEl = document.getElementById('scanned-name');
    const idEl = document.getElementById('scanned-id');
    const badge = document.getElementById('scan-result-badge');
    const timeEl = document.getElementById('scan-time');

    if (!card) return;

    if (!data && state !== 'error' && state !== 'loading') {
        // Reset state
        nameEl.textContent = 'Scanner Listo';
        idEl.textContent = 'Acerque Código QR';
        badge.classList.add('hidden');
        timeEl.textContent = '--:--';
        return;
    }

    if (state === 'loading') {
        nameEl.textContent = 'Procesando...';
        idEl.textContent = 'Verificando...';
        badge.classList.add('hidden');
        return;
    }

    if (state === 'error') {
        card.classList.add('border-red-500');
        nameEl.textContent = 'Error';
        nameEl.classList.add('text-red-500');
        idEl.textContent = errorMsg;
        badge.classList.add('hidden');
        timeEl.textContent = '--:--';
        setTimeout(() => {
            card.classList.remove('border-red-500');
            nameEl.classList.remove('text-red-500');
            updateStatusCard(null); // Reset
        }, 3000);
        return;
    }

    // Success State
    if (data && data.data) {
        const userIsEntry = data.type === 'entrada';
        nameEl.textContent = data.message.split(': ')[1] || 'Usuario';
        nameEl.classList.remove('text-red-500');

        idEl.textContent = `ID: ${data.data.user_id}`;

        badge.className = `badge badge-lg p-4 font-bold uppercase tracking-wider mb-4 border-none text-white ${userIsEntry ? 'bg-green-600' : 'bg-red-600'}`;
        badge.textContent = userIsEntry ? 'ENTRADA CORRECTA' : 'SALIDA CORRECTA';
        badge.classList.remove('hidden');

        timeEl.textContent = data.time || '--:--';

        // Reset after 3s
        setTimeout(() => {
            updateStatusCard(null);
        }, 5000);
    }
}

// ... Keep loadTodayHistory, renderTable, etc.

function loadTodayHistory() {
    // Reusing the list logic but filtering for today in frontend or creating a param
    const today = new Date().toISOString().split('T')[0];
    fetch(`${API_URL}/list?fecha_total=${today}`)
        .then(res => res.json())
        .then(data => {
            renderTable(data);
        });
}

function renderTable(data) {
    if (!liveTableBody) return;
    liveTableBody.innerHTML = '';

    const todayStr = new Date().toISOString().split('T')[0];
    const todayData = data.filter(d => d.fecha === todayStr);

    if (todayData.length === 0) {
        if (emptyState) emptyState.style.display = 'flex';
        return;
    }
    if (emptyState) emptyState.style.display = 'none';

    todayData.forEach(item => {
        // Formato simple de hora (HH:mm)
        const entryTime = item.hora_entrada ? item.hora_entrada.substring(0, 5) : '--:--';
        const exitTime = item.hora_salida ? item.hora_salida.substring(0, 5) : '--:--';

        // Estilo de Estado (Tardanza vs A Tiempo)
        let statusBadge = 'badge-success text-green-200 bg-green-900/20';
        if (item.estado === 'tardanza' || item.estado === 'falta_injustificada') {
            statusBadge = 'badge-warning text-orange-200 bg-orange-900/20';
        }

        const row = document.createElement('tr');
        row.innerHTML = `
            <td class="text-gray-400 font-mono text-xs whitespace-nowrap">${item.fecha}</td>
            <td class="font-mono text-green-400 font-bold tracking-wide">${entryTime}</td>
            <td class="font-mono text-red-400 font-bold tracking-wide">${exitTime}</td>
            <td class="font-bold text-white">${item.user ? (item.user.display_name || item.user.name) : 'Desconocido'}</td>
            <td><span class="badge badge-sm border-0 bg-gray-700 text-gray-300 capitalize">${(item.tipo || 'presente').replace('_', ' ')}</span></td>
            <td><span class="badge badge-sm border-0 ${statusBadge} uppercase text-[10px] font-bold tracking-wide">${item.estado.replace('_', ' ')}</span></td>
            <td class="text-xs text-gray-500 truncate max-w-[150px]" title="${item.observaciones || ''}">${item.observaciones || ''}</td>
            <td class="text-center">
                <div class="flex items-center justify-center gap-2">
                    <button onclick="verifyAndPrompt(${item.user ? item.user.id : 0})" class="btn btn-xs btn-ghost text-blue-400 hover:text-white" title="Editar / Salida">
                        <i class="fas fa-edit"></i>
                    </button>
                    <button onclick="deleteAttendance(${item.id})" class="btn btn-xs btn-ghost text-red-500 hover:text-white" title="Eliminar">
                        <i class="fas fa-trash-alt"></i>
                    </button>
                </div>
            </td>
        `;
        liveTableBody.insertBefore(row, liveTableBody.firstChild);
    });
}

// Make accessible
window.verifyAndPrompt = verifyAndPrompt;

window.deleteAttendance = function (id) {
    Swal.fire({
        title: '¿Eliminar registro?',
        text: "Esta acción no se puede deshacer. Se borrará la entrada y salida.",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#3085d6',
        confirmButtonText: 'Sí, eliminar',
        cancelButtonText: 'Cancelar',
        background: '#1A1B1E',
        color: '#fff'
    }).then((result) => {
        if (result.isConfirmed) {
            const csrfMeta = document.querySelector('meta[name="csrf-token"]');
            const csrfToken = csrfMeta ? csrfMeta.getAttribute('content') : '';

            fetch(`${API_URL}/${id}`, {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': csrfToken,
                    'Content-Type': 'application/json'
                }
            })
                .then(res => {
                    if (res.ok) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Eliminado',
                            text: 'El registro ha sido eliminado.',
                            timer: 1500,
                            showConfirmButton: false,
                            background: '#1A1B1E',
                            color: '#fff'
                        });
                        loadTodayHistory();
                    } else {
                        Swal.fire({ icon: 'error', title: 'Error', text: 'No se pudo eliminar.', background: '#1A1B1E', color: '#fff' });
                    }
                })
                .catch(err => console.error(err));
        }
    })
}


// --- 4. Manual Input (Live Search) ---
const searchInput = document.getElementById('manual-user-search');
const searchResults = document.getElementById('search-results');
let searchTimeout = null;

if (searchInput && searchResults) {
    searchInput.addEventListener('input', (e) => {
        const term = e.target.value.trim();
        clearTimeout(searchTimeout);

        if (term.length < 2) {
            searchResults.classList.add('hidden');
            searchResults.innerHTML = '';
            return;
        }

        searchTimeout = setTimeout(() => {
            fetch(`${API_URL}/search-users?term=${encodeURIComponent(term)}`)
                .then(res => res.json())
                .then(users => {
                    searchResults.innerHTML = '';

                    if (users.length === 0) {
                        const li = document.createElement('li');
                        li.className = 'px-4 py-3 text-sm text-gray-500 text-center';
                        li.textContent = 'No se encontraron usuarios';
                        searchResults.appendChild(li);
                    } else {
                        users.forEach(user => {
                            const li = document.createElement('li');
                            li.className = 'px-4 py-3 text-sm text-white hover:bg-[#1C69D4] cursor-pointer border-b border-gray-700 last:border-0 transition-colors flex flex-col';
                            li.innerHTML = `
                                <span class="font-bold text-base block py-1">${user.name}</span>
                            `;
                            li.onclick = () => {
                                verifyAndPrompt(user.id);
                                searchInput.value = user.name; // Keep name visible
                                searchResults.classList.add('hidden');
                            };
                            searchResults.appendChild(li);
                        });
                    }
                    searchResults.classList.remove('hidden');
                })
                .catch(err => console.error(err));
        }, 300); // 300ms debounce
    });

    // Close dropdown on click outside
    document.addEventListener('click', (e) => {
        if (!searchInput.contains(e.target) && !searchResults.contains(e.target)) {
            searchResults.classList.add('hidden');
        }
    });
}

// Clock
setInterval(() => {
    const liveClock = document.getElementById('live-clock');
    if (liveClock) {
        liveClock.textContent = new Date().toLocaleTimeString('es-CL');
    }
}, 1000);

// Init
loadTodayHistory();
