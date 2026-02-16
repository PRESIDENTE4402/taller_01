document.addEventListener('DOMContentLoaded', function () {
    // Almacena las fotos seleccionadas en la galería principal y en la galería de daños
    let selectedPhotos = [];
    let selectedDamagePhotos = [];

    // --- Lógica del Medidor de Combustible (Segmentado) ---
    // Controla la visualización del nivel de gasolina mediante un slider y segmentos visuales
    const fuelRange = document.getElementById('fuelRange');
    const fuelLabel = document.getElementById('fuelLabel');
    const fuelInput = document.getElementById('fuelInput');

    // Referencias a los 5 segmentos visuales de la barra
    const segments = [
        document.getElementById('fuel-seg-1'),
        document.getElementById('fuel-seg-2'),
        document.getElementById('fuel-seg-3'),
        document.getElementById('fuel-seg-4'),
        document.getElementById('fuel-seg-5')
    ];

    /**
     * Actualiza el texto, colores y segmentos de la barra de combustible
     * @param {string|number} inputVal - Valor del slider (0-100)
     */
    function updateFuel(inputVal) {
        if (!fuelLabel || !fuelInput) return;

        const val = parseInt(inputVal);
        let label = '';
        let dbVal = '';
        let colorClass = 'bg-blue-900';
        let textClass = 'text-blue-900';

        // Clasificación por rangos para etiquetas y colores de advertencia
        if (val < 15) {
            label = 'Reserva (E)';
            dbVal = 'R';
            colorClass = 'bg-red-600';
            textClass = 'text-red-600';
        } else if (val < 35) {
            label = '1/4 Tanque';
            dbVal = '1/4';
            colorClass = 'bg-orange-600';
            textClass = 'text-orange-600';
        } else if (val < 60) {
            label = '1/2 Tanque';
            dbVal = '1/2';
            colorClass = 'bg-yellow-500';
            textClass = 'text-yellow-600';
        } else if (val < 85) {
            label = '3/4 Tanque';
            dbVal = '3/4';
            colorClass = 'bg-blue-800';
            textClass = 'text-blue-800';
        } else {
            label = 'Full (F)';
            dbVal = 'F';
            colorClass = 'bg-blue-950';
            textClass = 'text-blue-950';
        }

        // Actualiza el texto visual y el input oculto para el servidor
        if (fuelLabel) fuelLabel.textContent = label;
        if (fuelInput) fuelInput.value = dbVal;
        fuelLabel.className = `font-extrabold text-xl ${textClass}`;

        // Enciende los segmentos según el porcentaje actual
        segments.forEach((seg, index) => {
            if (!seg) return;
            seg.className = `h-full flex-1 border-r border-white/50 transition-all duration-300 bg-gray-200`;
            const threshold = (index * 20) + 5;
            if (val >= threshold) {
                seg.classList.remove('bg-gray-200');
                seg.classList.add(colorClass);
            }
        });
    }

    if (fuelRange) {
        fuelRange.addEventListener('input', (e) => updateFuel(e.target.value));
        setTimeout(() => updateFuel(fuelRange.value), 200);
    }

    // --- Control de Inputs de Cantidad ---
    // Muestra u oculta campos de cantidad (como llantas de repuesto) según el checklist
    document.querySelectorAll('.toggle-qty').forEach(chk => {
        chk.addEventListener('change', function () {
            const targetId = this.dataset.target;
            const input = document.getElementById(targetId);
            if (this.checked) {
                input.classList.remove('hidden');
                input.value = 1;
                input.focus();
            } else {
                input.classList.add('hidden');
                input.value = '';
            }
        });
    });

    // --- Lógica del Canvas de Daños ---
    // Permite marcar "X" sobre una foto del vehículo para reportar daños físicos
    const canvas = document.getElementById('damageCanvas');
    const container = document.getElementById('canvasContainer');
    const imageUpload = document.getElementById('damageImageUpload');
    const clearCanvasBtn = document.getElementById('clearCanvas');
    const danosImageInput = document.getElementById('danosImageInput');

    if (canvas && container) {
        const ctx = canvas.getContext('2d');
        let currentImage = new Image();
        let marks = [];

        resizeCanvas();

        /**
         * Ajusta el tamaño del canvas al contenedor manteniendo la nitidez
         */
        function resizeCanvas() {
            if (!container) return;
            canvas.width = container.offsetWidth;
            canvas.height = container.offsetHeight;
            redrawAll();
        }

        /**
         * Convierte el dibujo actual a Base64 para enviarlo al servidor
         */
        function saveCanvas() {
            if (danosImageInput) danosImageInput.value = canvas.toDataURL();
        }

        /**
         * Redibuja la imagen de fondo y todas las marcas de daño
         */
        function redrawAll() {
            ctx.clearRect(0, 0, canvas.width, canvas.height);
            if (currentImage && currentImage.complete && currentImage.src) {
                const hRatio = canvas.width / currentImage.width;
                const vRatio = canvas.height / currentImage.height;
                const ratio = Math.min(hRatio, vRatio);
                const centerShift_x = (canvas.width - currentImage.width * ratio) / 2;
                const centerShift_y = (canvas.height - currentImage.height * ratio) / 2;

                ctx.drawImage(currentImage, 0, 0, currentImage.width, currentImage.height,
                    centerShift_x, centerShift_y, currentImage.width * ratio, currentImage.height * ratio);
            }

            marks.forEach(mark => {
                drawMark(mark.x * canvas.width, mark.y * canvas.height);
            });

            saveCanvas();
        }

        /**
         * Dibuja una "X" roja en las coordenadas especificadas
         */
        function drawMark(x, y) {
            ctx.strokeStyle = '#ef4444';
            ctx.lineWidth = 3;
            ctx.beginPath();
            const size = 10;
            ctx.moveTo(x - size, y - size);
            ctx.lineTo(x + size, y + size);
            ctx.moveTo(x + size, y - size);
            ctx.lineTo(x - size, y + size);
            ctx.stroke();
        }

        if (imageUpload) {
            imageUpload.addEventListener('change', function (e) {
                if (this.files && this.files[0]) {
                    const reader = new FileReader();
                    reader.onload = (evt) => setDamageImage(evt.target.result);
                    reader.readAsDataURL(this.files[0]);
                }
            });
        }

        window.addEventListener('resize', resizeCanvas);
        setTimeout(resizeCanvas, 500);

        // Registro de marcas al hacer click en el lienzo
        canvas.addEventListener('click', function (e) {
            const rect = canvas.getBoundingClientRect();
            const x = e.clientX - rect.left;
            const y = e.clientY - rect.top;
            marks.push({ x: x / canvas.width, y: y / canvas.height });
            redrawAll();
        });

        if (clearCanvasBtn) {
            clearCanvasBtn.addEventListener('click', () => {
                marks = [];
                redrawAll();
            });
        }

        const undoMark = document.getElementById('undoMark');
        if (undoMark) {
            undoMark.addEventListener('click', () => {
                marks.pop();
                redrawAll();
            });
        }

        const btnDeleteDamagePhoto = document.getElementById('btnDeleteDamagePhoto');
        if (btnDeleteDamagePhoto) {
            btnDeleteDamagePhoto.addEventListener('click', () => {
                currentImage = new Image();
                marks = [];
                if (canvasPlaceholder) canvasPlaceholder.classList.remove('hidden');
                if (imageUpload) imageUpload.value = '';
                redrawAll();
            });
        }

        const canvasPlaceholder = document.getElementById('canvasPlaceholder');
        const btnAddDamage = document.getElementById('addDamageToGallery');
        const previewDanosGrid = document.getElementById('previewDanosGrid');
        const emptyDanosMsg = document.getElementById('emptyDanosMsg');
        const damageCountBadge = document.getElementById('damageCountBadge');

        /**
         * Renderiza las miniaturas de daños guardados en la galería lateral
         */
        window.renderDamagePhotos = function () {
            if (!previewDanosGrid) return;
            previewDanosGrid.querySelectorAll('.damage-card').forEach(el => el.remove());

            if (selectedDamagePhotos.length === 0) {
                if (emptyDanosMsg) emptyDanosMsg.classList.remove('hidden');
                if (damageCountBadge) damageCountBadge.textContent = '0 FOTOS';
            } else {
                if (emptyDanosMsg) emptyDanosMsg.classList.add('hidden');
                if (damageCountBadge) damageCountBadge.textContent = `${selectedDamagePhotos.length} FOTOS`;

                selectedDamagePhotos.forEach(photo => {
                    const card = document.createElement('div');
                    card.className = "damage-card relative group bg-white border border-gray-200 rounded-lg shadow-sm overflow-hidden hover:shadow-md transition-all flex flex-col h-40";
                    card.innerHTML = `
                        <div class="relative h-full w-full bg-gray-100 overflow-hidden cursor-pointer" onclick="viewPhoto('${photo.src}')">
                            <img src="${photo.src}" class="w-full h-full object-cover">
                            <div class="absolute top-2 right-2 z-30">
                                <button type="button" onclick="event.stopPropagation(); removeDamagePhoto('${photo.id}')" 
                                    class="bg-red-600 hover:bg-red-700 text-white w-8 h-8 rounded-full flex items-center justify-center shadow-lg border-2 border-white transition-all transform hover:scale-110 active:scale-95">
                                    <i class="fas fa-times text-xs"></i>
                                </button>
                            </div>
                        </div>
                    `;
                    previewDanosGrid.appendChild(card);
                });
            }
        };

        window.removeDamagePhoto = function (id) {
            selectedDamagePhotos = selectedDamagePhotos.filter(p => p.id !== id);
            renderDamagePhotos();
        };

        /**
         * Carga una imagen base en el canvas para empezar a marcar daños
         */
        function setDamageImage(src) {
            const img = new Image();
            img.onload = function () {
                currentImage = img;
                marks = [];
                if (canvasPlaceholder) canvasPlaceholder.classList.add('hidden');
                redrawAll();
            }
            img.src = src;
        }

        const btnDamageCamera = document.getElementById('btnDamageCamera');
        if (btnDamageCamera) {
            btnDamageCamera.addEventListener('click', () => {
                openCameraModal((file) => {
                    const reader = new FileReader();
                    reader.onload = (e) => setDamageImage(e.target.result);
                    reader.readAsDataURL(file);
                }, false);
            });
        }

        // --- Lógica de Alerta "Guardar sin marcas" ---
        // Verifica si hay una imagen cargada y marcas puestas antes de guardar a la galería
        if (btnAddDamage) {
            btnAddDamage.addEventListener('click', () => {
                // Si no hay marcas, mostramos la alerta con el diseño premium solicitado
                if (marks.length === 0) {
                    Swal.fire({
                        title: '¿Guardar sin marcas?',
                        html: `
                            <div class="p-2">
                                <p class="text-gray-600 mb-6">No ha marcado ningún daño en la foto. ¿Desea guardarla únicamente como evidencia visual?</p>
                                <div class="flex flex-col sm:flex-row gap-3 justify-center">
                                    <button type="button" id="confirmSaveDano" class="bg-[#1e3a8a] !important text-white font-bold py-3 px-6 rounded-xl shadow-lg hover:bg-blue-800 transition-all uppercase text-xs">
                                        <i class="fas fa-save mr-2"></i> Sí, guardar foto
                                    </button>
                                    <button type="button" id="cancelSaveDano" class="bg-[#6b7280] !important text-white font-bold py-3 px-6 rounded-xl shadow-md hover:bg-gray-600 transition-all uppercase text-xs">
                                        No, marcar daño
                                    </button>
                                </div>
                            </div>
                        `,
                        icon: 'warning',
                        showConfirmButton: false, // Desactivamos los botones nativos que fallan
                        showCancelButton: false,
                        didOpen: () => {
                            // Escuchadores manuales para los botones inyectados
                            document.getElementById('confirmSaveDano').addEventListener('click', () => {
                                Swal.close();
                                saveToDamageGallery();
                            });
                            document.getElementById('cancelSaveDano').addEventListener('click', () => {
                                Swal.close();
                            });
                        }
                    });
                } else {
                    saveToDamageGallery();
                }
            });
        }

        /**
         * Exporta el canvas actual y lo añade a la lista de fotos de daños
         */
        function saveToDamageGallery() {
            const dataUrl = canvas.toDataURL('image/jpeg', 0.85);
            const uniqueId = Date.now() + Math.random().toString(36).substr(2, 9);

            selectedDamagePhotos.push({ id: uniqueId, src: dataUrl });

            if (btnDeleteDamagePhoto) btnDeleteDamagePhoto.click();
            renderDamagePhotos();

            // Toast de confirmación premium
            Swal.fire({
                icon: 'success',
                title: 'Evidencia Guardada',
                toast: true,
                position: 'top-end',
                showConfirmButton: false,
                timer: 2000,
                background: '#1e1b4b',
                color: '#ffffff',
                iconColor: '#4ade80'
            });
        }
    }

    // --- Manejo del Envío del Formulario (Submit) ---
    // Recopila todos los datos y fotos antes de enviarlos vía AJAX
    const ordenForm = document.getElementById('ordenForm');
    if (ordenForm) {
        ordenForm.addEventListener('submit', async function (e) {
            e.preventDefault();
            const btn = this.querySelector('button[type="submit"]');
            const originalBtnContent = btn.innerHTML;

            btn.disabled = true;
            btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Procesando...';

            try {
                const formData = new FormData(this);

                // Adjunta fotos de la galería de recepción
                if (typeof selectedPhotos !== 'undefined' && selectedPhotos.length > 0) {
                    selectedPhotos.forEach((photoObj, index) => {
                        formData.append(`fotos_recepcion[${index}]`, photoObj.file);
                        const titleInput = document.getElementById(`title-${photoObj.id}`);
                        formData.append(`fotos_titulos[${index}]`, titleInput ? titleInput.value : photoObj.title);
                    });
                }

                // Adjunta fotos del reporte de daños (Base64)
                if (selectedDamagePhotos && selectedDamagePhotos.length > 0) {
                    selectedDamagePhotos.forEach((photo, index) => {
                        formData.append(`fotos_danos[${index}]`, photo.src);
                    });
                }

                const response = await fetch(this.action, {
                    method: 'POST',
                    body: formData,
                    headers: { 'Accept': 'application/json' }
                });
                const result = await response.json();

                if (result.success) {
                    Swal.fire('Éxito', result.message, 'success').then(() => window.location.href = result.redirect);
                } else {
                    Swal.fire('Error', result.message, 'error');
                    btn.disabled = false;
                    btn.innerHTML = originalBtnContent;
                }
            } catch (error) {
                console.error(error);
                Swal.fire('Error', 'Ocurrió un error inesperado', 'error');
                btn.disabled = false;
                btn.innerHTML = originalBtnContent;
            }
        });
    }

    // --- Buscador Dinámico de Marcas, Modelos y Versiones ---
    const inputMarca = document.getElementById('inputMarca');
    const inputModelo = document.getElementById('inputModelo');
    const inputVersion = document.getElementById('inputVersion');
    const listMarcas = document.getElementById('listMarcas');
    const listModelos = document.getElementById('listModelos');
    const listVersiones = document.getElementById('listVersiones');

    let marcasMap = {};
    let modelosMap = {};

    if (inputMarca && listMarcas && window.serverData) {
        // Carga inicial de marcas
        fetch(window.serverData.routes.marcasList)
            .then(r => r.json())
            .then(data => {
                listMarcas.innerHTML = '';
                data.forEach(m => {
                    const nombreUpper = m.nombre.toUpperCase();
                    const opt = document.createElement('option');
                    opt.value = nombreUpper;
                    listMarcas.appendChild(opt);
                    marcasMap[nombreUpper] = m.id;
                });
            });

        // Evento al cambiar marca: Carga modelos correspondientes
        inputMarca.addEventListener('input', function () {
            const marcaId = marcasMap[this.value.toUpperCase()];
            inputModelo.value = '';
            listModelos.innerHTML = '';
            modelosMap = {};

            if (marcaId) {
                const url = window.serverData.routes.modelosList.replace('PLACEHOLDER', marcaId);
                fetch(url).then(r => r.json()).then(data => {
                    data.forEach(m => {
                        const nombreUpper = m.nombre.toUpperCase();
                        const opt = document.createElement('option');
                        opt.value = nombreUpper;
                        listModelos.appendChild(opt);
                        modelosMap[nombreUpper] = m.id;
                    });
                });
            }
        });

        // Evento al cambiar modelo: Carga versiones correspondientes
        inputModelo.addEventListener('input', function () {
            const modeloId = modelosMap[this.value.toUpperCase()];
            inputVersion.value = '';
            listVersiones.innerHTML = '';

            if (modeloId) {
                const url = window.serverData.routes.versionesList.replace('PLACEHOLDER', modeloId);
                fetch(url).then(r => r.json()).then(data => {
                    data.forEach(v => {
                        const opt = document.createElement('option');
                        opt.value = v.nombre.toUpperCase();
                        listVersiones.appendChild(opt);
                    });
                });
            }
        });
    }

    // --- Autocompletado de Datos del Cliente ---
    const inputClienteNombre = document.getElementById('inputClienteNombre');
    const inputClienteTelefono = document.getElementById('inputClienteTelefono');
    const inputClienteEmail = document.getElementById('inputClienteEmail');
    const listClientes = document.getElementById('listClientes');
    const walkInClienteId = document.getElementById('walkInClienteId');
    const accionClienteInput = document.getElementById('accionClienteInput');
    let debounceTimer;

    /**
     * Llena los inputs con la información de un cliente seleccionado
     */
    function fillClientData(client) {
        inputClienteNombre.value = client.nombre_completo.toUpperCase();
        inputClienteTelefono.value = client.telefono;
        inputClienteEmail.value = client.email || '';
        if (walkInClienteId) walkInClienteId.value = client.id;
        if (accionClienteInput) accionClienteInput.value = 'update';

        inputClienteNombre.classList.add('border-green-500', 'bg-green-50');
        setTimeout(() => inputClienteNombre.classList.remove('border-green-500', 'bg-green-50'), 1000);
    }

    /**
     * Selecciona un cliente y busca si tiene vehículos registrados
     */
    function selectClient(client) {
        fillClientData(client);
        listClientes.classList.add('hidden');

        if (window.serverData.routes.getClientVehicles) {
            const url = window.serverData.routes.getClientVehicles.replace('PLACEHOLDER', client.id);
            fetch(url).then(r => r.json()).then(vehicles => {
                if (vehicles.length > 0) {
                    // Si solo tiene uno, preguntamos para cargarlo directamente
                    if (vehicles.length === 1) {
                        Swal.fire({
                            title: '¡Vehículo Encontrado!',
                            text: `El cliente tiene registrado un ${vehicles[0].texto}. ¿Cargar datos?`,
                            icon: 'question',
                            showCancelButton: true,
                            confirmButtonText: 'Sí, cargar',
                            confirmButtonColor: '#1e3a8a'
                        }).then(res => { if (res.isConfirmed) fillVehicleData(vehicles[0]); });
                    }
                }
            });
        }
    }

    if (inputClienteNombre && listClientes) {
        inputClienteNombre.addEventListener('input', function () {
            const term = this.value;
            clearTimeout(debounceTimer);
            if (term.length < 2) { listClientes.classList.add('hidden'); return; }

            debounceTimer = setTimeout(() => {
                fetch(`${window.serverData.routes.searchClients}?term=${term}`)
                    .then(r => r.json())
                    .then(data => {
                        listClientes.innerHTML = '';
                        if (data.length > 0) {
                            listClientes.classList.remove('hidden');
                            data.forEach(c => {
                                const li = document.createElement('li');
                                li.className = "px-4 py-2 hover:bg-gray-100 cursor-pointer text-xs";
                                li.innerHTML = `<strong>${c.nombre_completo}</strong><br>${c.telefono}`;
                                li.addEventListener('click', () => selectClient(c));
                                listClientes.appendChild(li);
                            });
                        }
                    });
            }, 300);
        });
    }

    // --- Buscador de Vehículo por Placa ---
    const inputPlaca = document.getElementById('inputPlaca');
    const listVehiculos = document.getElementById('listVehiculos');
    let debounceVehiculo;

    /**
     * Llena los campos técnicos según el vehículo elegido
     */
    function fillVehicleData(v) {
        const iVehiculoId = document.querySelector('input[name="vehiculo_id"]');
        if (iVehiculoId) iVehiculoId.value = v.id;
        document.querySelector('input[name="new_vehiculo[placa]"]').value = v.placa;
        document.querySelector('input[name="new_vehiculo[marca]"]').value = v.marca;
        document.querySelector('input[name="new_vehiculo[modelo]"]').value = v.modelo;
        document.querySelector('input[name="new_vehiculo[version]"]').value = v.version || '';
        document.querySelector('input[name="color"]').value = v.color;
        document.querySelector('input[name="new_vehiculo[anio]"]').value = v.anio;

        Swal.fire({ icon: 'success', title: 'Vehículo Cargado', toast: true, position: 'top-end', showConfirmButton: false, timer: 1500 });
    }

    if (inputPlaca && listVehiculos) {
        inputPlaca.addEventListener('input', function () {
            const term = this.value;
            clearTimeout(debounceVehiculo);
            if (term.length < 2) { listVehiculos.classList.add('hidden'); return; }

            debounceVehiculo = setTimeout(() => {
                fetch(`${window.serverData.routes.searchVehicles}?term=${term}`)
                    .then(r => r.json())
                    .then(data => {
                        listVehiculos.innerHTML = '';
                        if (data.length > 0) {
                            listVehiculos.classList.remove('hidden');
                            data.forEach(v => {
                                const li = document.createElement('li');
                                li.className = "px-4 py-2 hover:bg-gray-100 cursor-pointer text-xs";
                                li.innerHTML = `<strong>${v.placa}</strong> - ${v.marca} ${v.modelo}`;
                                li.addEventListener('click', () => {
                                    fillVehicleData(v);
                                    listVehiculos.classList.add('hidden');
                                });
                                listVehiculos.appendChild(li);
                            });
                        }
                    });
            }, 300);
        });
    }

    // --- API de Cámara Genérica (Modal Swal) ---
    /**
     * Abre un modal con acceso a la webcam para capturar fotos instantáneas
     */
    async function openCameraModal(onCapture, autoReopen = true) {
        try {
            const stream = await navigator.mediaDevices.getUserMedia({ video: true });
            Swal.fire({
                title: 'Capturar Fotografía',
                html: `<div class="bg-black aspect-video rounded-lg overflow-hidden"><video id="cameraFeed" autoplay playsinline class="w-full h-full"></video><canvas id="snapshotCanvas" class="hidden"></canvas></div>`,
                showCancelButton: true,
                confirmButtonText: 'Capturar',
                confirmButtonColor: '#1e3a8a',
                didOpen: () => { document.getElementById('cameraFeed').srcObject = stream; },
                preConfirm: () => {
                    const video = document.getElementById('cameraFeed');
                    const canvas = document.getElementById('snapshotCanvas');
                    canvas.width = video.videoWidth; canvas.height = video.videoHeight;
                    canvas.getContext('2d').drawImage(video, 0, 0);
                    return canvas.toDataURL('image/jpeg', 0.9);
                },
                willClose: () => { stream.getTracks().forEach(track => track.stop()); }
            }).then((res) => {
                if (res.isConfirmed) {
                    fetch(res.value).then(r => r.blob()).then(blob => {
                        const file = new File([blob], `cap_${Date.now()}.jpg`, { type: 'image/jpeg' });
                        onCapture(file);
                        if (autoReopen) setTimeout(() => openCameraModal(onCapture, autoReopen), 400);
                    });
                }
            });
        } catch (err) { Swal.fire('Error', 'No se pudo acceder a la cámara.', 'error'); }
    }

    // --- Manejo de Archivos y Fotos de Recepción ---
    function handleFiles(files) {
        Array.from(files).forEach(file => {
            const reader = new FileReader();
            reader.onload = (e) => {
                selectedPhotos.push({ id: Date.now() + Math.random(), file: file, src: e.target.result, title: '' });
                renderPhotos();
            };
            reader.readAsDataURL(file);
        });
    }

    const btnCamera = document.getElementById('btnCamera');
    const btnGallery = document.getElementById('btnGallery');
    const inputGallery = document.getElementById('inputGallery');
    const previewGrid = document.getElementById('previewFotosGrid');

    if (previewGrid) {
        if (btnCamera) btnCamera.addEventListener('click', () => openCameraModal((f) => handleFiles([f]), true));
        if (btnGallery) btnGallery.addEventListener('click', () => inputGallery.click());
        if (inputGallery) inputGallery.addEventListener('change', (e) => handleFiles(e.target.files));

        window.renderPhotos = function () {
            previewGrid.querySelectorAll('.photo-card').forEach(el => el.remove());
            const emptyMsg = document.getElementById('emptyPhotosMsg');
            if (selectedPhotos.length === 0) { emptyMsg.classList.remove('hidden'); }
            else {
                emptyMsg.classList.add('hidden');
                selectedPhotos.forEach(p => {
                    const card = document.createElement('div');
                    card.className = "photo-card bg-white border rounded-lg shadow-sm overflow-hidden h-64 relative";
                    card.innerHTML = `<img src="${p.src}" class="w-full h-48 object-cover cursor-pointer" onclick="viewPhoto('${p.src}')"><div class="p-2"><input type="text" class="input input-sm w-full border-gray-200" placeholder="Título" value="${p.title}" oninput="updatePhotoTitle('${p.id}', this.value)"></div><button onclick="removePhoto('${p.id}')" class="absolute top-1 right-1 btn btn-xs btn-circle btn-error">×</button>`;
                    previewGrid.appendChild(card);
                });
            }
        };

        window.removePhoto = function (id) { selectedPhotos = selectedPhotos.filter(p => p.id != id); renderPhotos(); };
        window.updatePhotoTitle = function (id, val) { const p = selectedPhotos.find(x => x.id == id); if (p) p.title = val; };
        window.viewPhoto = function (src) { Swal.fire({ imageUrl: src, showConfirmButton: false, background: 'rgba(0,0,0,0.8)' }); };
    }

    // --- Botón Limpiar Formulario ---
    const btnLimpiar = document.getElementById('btnLimpiar');
    if (btnLimpiar) {
        btnLimpiar.addEventListener('click', () => {
            Swal.fire({ title: '¿Limpiar Todo?', icon: 'warning', showCancelButton: true, confirmButtonText: 'Sí, borrar', confirmButtonColor: '#dc2626' }).then(res => {
                if (res.isConfirmed) { location.reload(); }
            });
        });
    }

    // --- Navegación Inteligente (Scroll Spy) ---
    const stepNavs = document.querySelectorAll('.step-nav');
    const sections = ['section-datos', 'section-recepcion', 'section-inventario', 'section-falla', 'section-danos'];

    // Scroll suave al clickear iconos
    stepNavs.forEach(nav => {
        nav.addEventListener('click', function (e) {
            e.preventDefault();
            const el = document.getElementById(this.getAttribute('href').substring(1));
            if (el) el.scrollIntoView({ behavior: 'smooth' });
        });
    });

    // Detectar sección activa mediante scroll
    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                const step = entry.target.id.replace('section-', '');
                stepNavs.forEach(nav => {
                    const isActive = nav.getAttribute('data-step') === step;
                    const circle = nav.querySelector('div');
                    circle.className = isActive ? "w-8 h-8 rounded-full border-2 border-blue-900 flex items-center justify-center bg-blue-900 text-white scale-110 shadow-lg" : "w-8 h-8 rounded-full border-2 border-gray-200 flex items-center justify-center bg-white text-gray-400";
                });
            }
        });
    }, { rootMargin: '-20% 0px -70% 0px' });

    sections.forEach(id => { const el = document.getElementById(id); if (el) observer.observe(el); });
});
