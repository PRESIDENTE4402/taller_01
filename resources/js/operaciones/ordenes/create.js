document.addEventListener('DOMContentLoaded', function () {
    let selectedPhotos = [];
    let selectedDamagePhotos = [];

    // --- Fuel Gauge Logic (Segmented) ---
    const fuelRange = document.getElementById('fuelRange');
    const fuelLabel = document.getElementById('fuelLabel');
    const fuelInput = document.getElementById('fuelInput');

    // Segments
    const segments = [
        document.getElementById('fuel-seg-1'),
        document.getElementById('fuel-seg-2'),
        document.getElementById('fuel-seg-3'),
        document.getElementById('fuel-seg-4'),
        document.getElementById('fuel-seg-5')
    ];

    function updateFuel(inputVal) {
        if (!fuelLabel || !fuelInput) return;

        const val = parseInt(inputVal); // Ensure integer

        let label = '';
        let dbVal = '';
        let colorClass = 'bg-blue-900'; // Default Navy
        let textClass = 'text-blue-900'; // Default Navy

        // Determine Label & Base Color
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
            colorClass = 'bg-yellow-500'; // Gold/Amber
            textClass = 'text-yellow-600';
        } else if (val < 85) {
            label = '3/4 Tanque';
            dbVal = '3/4';
            colorClass = 'bg-blue-800';
            textClass = 'text-blue-800';
        } else {
            label = 'Full (F)';
            dbVal = 'F';
            colorClass = 'bg-blue-950'; // Deep Navy
            textClass = 'text-blue-950';
        }

        if (fuelLabel) fuelLabel.textContent = label;
        if (fuelInput) fuelInput.value = dbVal;

        // Update Label Color
        fuelLabel.className = `font-extrabold text-xl ${textClass}`;

        // Update Segments
        segments.forEach((seg, index) => {
            if (!seg) return;
            // Reset base classes
            seg.className = `h-full flex-1 border-r border-white/50 transition-all duration-300 bg-gray-200`;

            // Thresholds
            const threshold = (index * 20) + 5;

            if (val >= threshold) {
                seg.classList.remove('bg-gray-200');
                seg.classList.add(colorClass);
            }
        });
    }

    if (fuelRange) {
        fuelRange.addEventListener('input', (e) => updateFuel(e.target.value));
        // Force update on load with current value
        setTimeout(() => updateFuel(fuelRange.value), 200);
    }

    // --- Toggle Qty Inputs ---
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

    // --- Canvas Logic ---
    const canvas = document.getElementById('damageCanvas');
    const container = document.getElementById('canvasContainer');
    const imageUpload = document.getElementById('damageImageUpload');
    const clearCanvasBtn = document.getElementById('clearCanvas');
    const danosImageInput = document.getElementById('danosImageInput');

    if (canvas && container) {
        const ctx = canvas.getContext('2d');
        let currentImage = new Image();
        let marks = [];

        // Call resize once on start
        resizeCanvas();


        function resizeCanvas() {
            if (!container) return;
            canvas.width = container.offsetWidth;
            canvas.height = container.offsetHeight;
            redrawAll();
        }

        function saveCanvas() {
            if (danosImageInput) danosImageInput.value = canvas.toDataURL();
        }

        function redrawAll() {
            ctx.clearRect(0, 0, canvas.width, canvas.height);
            if (currentImage && currentImage.complete && currentImage.src) {
                // Draw Image (Contain)
                const hRatio = canvas.width / currentImage.width;
                const vRatio = canvas.height / currentImage.height;
                const ratio = Math.min(hRatio, vRatio);
                const centerShift_x = (canvas.width - currentImage.width * ratio) / 2;
                const centerShift_y = (canvas.height - currentImage.height * ratio) / 2;

                ctx.drawImage(currentImage, 0, 0, currentImage.width, currentImage.height,
                    centerShift_x, centerShift_y, currentImage.width * ratio, currentImage.height * ratio);
            }

            // Draw Marks
            marks.forEach(mark => {
                drawMark(mark.x * canvas.width, mark.y * canvas.height);
            });

            saveCanvas();
        }

        function drawMark(x, y) {
            ctx.strokeStyle = '#ef4444'; // Red
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
                    reader.onload = function (evt) {
                        setDamageImage(evt.target.result);
                    };
                    reader.readAsDataURL(this.files[0]);
                }
            });
        }

        window.addEventListener('resize', resizeCanvas);
        setTimeout(resizeCanvas, 500);

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
                            <div class="absolute top-1 right-1">
                                <button type="button" onclick="event.stopPropagation(); removeDamagePhoto('${photo.id}')" class="btn btn-xs btn-circle btn-error text-white opacity-80 hover:opacity-100">
                                    <i class="fas fa-times"></i>
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

        if (btnAddDamage) {
            btnAddDamage.addEventListener('click', () => {
                // Modified check: check if currentImage has a valid src
                if (!currentImage.complete || !currentImage.src || currentImage.src === "" || currentImage.src.includes('blob:')) {
                    // Blob check might be tricky, let's just check if it's drawn or if marks exist? 
                    // Actually, if it's from camera it's a dataURL or blob.
                }

                // If marks is empty, maybe warn?
                if (marks.length === 0) {
                    Swal.fire({
                        title: '¿Guardar sin marcas?',
                        text: 'No ha marcado ningún daño en la foto.',
                        icon: 'question',
                        showCancelButton: true,
                        confirmButtonText: 'Sí, guardar así',
                        cancelButtonText: 'No, marcar daño'
                    }).then((result) => {
                        if (result.isConfirmed) saveToDamageGallery();
                    });
                } else {
                    saveToDamageGallery();
                }
            });
        }

        function saveToDamageGallery() {
            const dataUrl = canvas.toDataURL('image/jpeg', 0.85);
            const uniqueId = Date.now() + Math.random().toString(36).substr(2, 9);

            selectedDamagePhotos.push({
                id: uniqueId,
                src: dataUrl
            });

            if (btnDeleteDamagePhoto) btnDeleteDamagePhoto.click();
            renderDamagePhotos();

            Swal.fire({
                icon: 'success',
                title: 'Foto Guardada',
                toast: true,
                position: 'top-end',
                showConfirmButton: false,
                timer: 1500
            });
        }
    }


    // --- Submit Handler ---
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

                // --- Append Manual Photos ---
                if (typeof selectedPhotos !== 'undefined' && selectedPhotos.length > 0) {
                    selectedPhotos.forEach((photoObj, index) => {
                        formData.append(`fotos_recepcion[${index}]`, photoObj.file);
                        const titleInput = document.getElementById(`title-${photoObj.id}`);
                        const titleVal = titleInput ? titleInput.value : photoObj.title;
                        formData.append(`fotos_titulos[${index}]`, titleVal);
                    });
                }

                // --- Append Damage Gallery Photos ---
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

    // --- Dynamic Vehicle Inputs Logic ---
    const inputMarca = document.getElementById('inputMarca');
    const inputModelo = document.getElementById('inputModelo');
    const inputVersion = document.getElementById('inputVersion');
    const listMarcas = document.getElementById('listMarcas');
    const listModelos = document.getElementById('listModelos');
    const listVersiones = document.getElementById('listVersiones');

    let marcasMap = {};
    let modelosMap = {};

    if (inputMarca && listMarcas && window.serverData) {
        console.log("Create.js: Initializing Vehicle Search...");
        fetch(window.serverData.routes.marcasList)
            .then(r => r.json())
            .then(data => {
                console.log("Create.js: Loaded Brands", data.length);
                listMarcas.innerHTML = '';
                marcasMap = {};
                data.forEach(m => {
                    const nombreUpper = m.nombre.toUpperCase();
                    const opt = document.createElement('option');
                    opt.value = nombreUpper;
                    listMarcas.appendChild(opt);
                    marcasMap[nombreUpper] = m.id;
                });
            })
            .catch(err => console.error("Create.js: Error loading brands", err));

        inputMarca.addEventListener('input', function () {
            const marcaName = this.value.toUpperCase();
            console.log("Create.js: Input Brand", marcaName);
            const marcaId = marcasMap[marcaName];

            inputModelo.value = '';
            listModelos.innerHTML = '';
            inputVersion.value = '';
            listVersiones.innerHTML = '';
            modelosMap = {};

            if (marcaId) {
                const url = window.serverData.routes.modelosList.replace('PLACEHOLDER', marcaId);
                fetch(url)
                    .then(r => r.json())
                    .then(data => {
                        listModelos.innerHTML = '';
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

        inputModelo.addEventListener('input', function () {
            const modeloName = this.value.toUpperCase();
            const modeloId = modelosMap[modeloName];

            inputVersion.value = '';
            listVersiones.innerHTML = '';

            if (modeloId) {
                const url = window.serverData.routes.versionesList.replace('PLACEHOLDER', modeloId);
                fetch(url)
                    .then(r => r.json())
                    .then(data => {
                        listVersiones.innerHTML = '';
                        data.forEach(v => {
                            const nombreUpper = v.nombre.toUpperCase();
                            const opt = document.createElement('option');
                            opt.value = nombreUpper;
                            listVersiones.appendChild(opt);
                        });
                    });
            }
        });
    }

    // --- Client Autocomplete Logic ---
    const inputClienteNombre = document.getElementById('inputClienteNombre');
    const inputClienteTelefono = document.getElementById('inputClienteTelefono');
    const inputClienteEmail = document.getElementById('inputClienteEmail');
    const listClientes = document.getElementById('listClientes');
    const walkInClienteId = document.getElementById('walkInClienteId');
    const accionClienteInput = document.getElementById('accionClienteInput');
    let debounceTimer;

    function fillClientData(client) {
        inputClienteNombre.value = client.nombre_completo.toUpperCase();
        inputClienteTelefono.value = client.telefono;
        inputClienteEmail.value = client.email || '';
        if (walkInClienteId) walkInClienteId.value = client.id;
        if (accionClienteInput) accionClienteInput.value = 'update';

        inputClienteNombre.classList.add('border-green-500', 'bg-green-50');
        setTimeout(() => inputClienteNombre.classList.remove('border-green-500', 'bg-green-50'), 1000);
    }

    function selectClient(client) {
        fillClientData(client);
        listClientes.classList.add('hidden');

        // Fetch Client Vehicles
        if (window.serverData.routes.getClientVehicles) {
            const url = window.serverData.routes.getClientVehicles.replace('PLACEHOLDER', client.id);

            fetch(url)
                .then(r => r.json())
                .then(vehicles => {
                    if (vehicles.length === 1) {
                        const v = vehicles[0];
                        Swal.fire({
                            title: '¡Vehículo Encontrado!',
                            html: `<div class="p-4 text-center">
                                <i class="fas fa-car text-5xl text-blue-900 mb-4 animate-bounce"></i>
                                <p class="text-gray-600">El cliente tiene registrado un <strong>${v.texto}</strong></p>
                                <p class="text-xs text-gray-400 mt-2">¿Desea cargar los datos automáticamente?</p>
                            </div>`,
                            showCancelButton: true,
                            confirmButtonText: 'Sí, cargar datos',
                            cancelButtonText: 'No, usar otro',
                            buttonsStyling: false,
                            customClass: {
                                confirmButton: 'btn bg-blue-900 border-blue-900 hover:bg-blue-800 text-white px-8 mx-2',
                                cancelButton: 'btn btn-ghost text-gray-400 mx-2'
                            }
                        }).then((result) => {
                            if (result.isConfirmed) {
                                fillVehicleData(v);
                            }
                        });
                    } else if (vehicles.length > 1) {
                        const options = {};
                        vehicles.forEach(v => {
                            options[v.id] = v.texto;
                        });

                        Swal.fire({
                            title: 'Seleccione un Vehículo',
                            text: 'El cliente tiene varios vehículos registrados.',
                            input: 'select',
                            inputOptions: options,
                            inputPlaceholder: 'Seleccione el vehículo...',
                            showCancelButton: true,
                            showCloseButton: true,
                            confirmButtonText: 'Cargar Seleccionado',
                            cancelButtonText: 'Cancelar',
                            buttonsStyling: false,
                            customClass: {
                                container: 'swal-wide-fix',
                                confirmButton: 'btn bg-blue-900 border-blue-900 hover:bg-blue-800 text-white px-8 mx-2',
                                cancelButton: 'btn btn-ghost text-gray-400 mx-2',
                                input: 'select select-bordered max-w-full'
                            }
                        }).then((result) => {
                            if (result.isConfirmed && result.value) {
                                const selected = vehicles.find(v => v.id == result.value);
                                if (selected) fillVehicleData(selected);
                            }
                        });
                    }
                })
                .catch(err => console.error("Create.js: Error loading client vehicles", err));
        }
    }

    function fillVehicleData(v) {
        // Inputs
        const iPlaca = document.querySelector('input[name="new_vehiculo[placa]"]');
        const iMarca = document.querySelector('input[name="new_vehiculo[marca]"]');
        const iModelo = document.querySelector('input[name="new_vehiculo[modelo]"]');
        const iVersion = document.querySelector('input[name="new_vehiculo[version]"]');
        const iColor = document.querySelector('input[name="color"]');
        const iAnio = document.querySelector('input[name="new_vehiculo[anio]"]');
        const iVehiculoId = document.querySelector('input[name="vehiculo_id"]');

        if (iVehiculoId) iVehiculoId.value = v.id; // Set hidden ID if exists (it does)

        if (iPlaca) iPlaca.value = v.placa;
        if (iMarca) iMarca.value = v.marca;
        if (iModelo) iModelo.value = v.modelo;
        if (iVersion) iVersion.value = v.version || '';
        if (iColor) iColor.value = v.color;
        if (iAnio) iAnio.value = v.anio;

        // Visual Feedback
        [iPlaca, iMarca, iModelo, iVersion, iColor, iAnio].forEach(el => {
            if (el) {
                el.classList.add('bg-blue-50', 'text-blue-700');
                setTimeout(() => el.classList.remove('bg-blue-50', 'text-blue-700'), 1500);
            }
        });

        Swal.fire({
            icon: 'success',
            title: 'Vehículo Cargado',
            toast: true,
            position: 'top-end',
            showConfirmButton: false,
            timer: 2000
        });
    }

    if (inputClienteNombre && listClientes && window.serverData) {
        inputClienteNombre.addEventListener('input', function () {
            const term = this.value;
            clearTimeout(debounceTimer);
            if (walkInClienteId) walkInClienteId.value = '';
            if (accionClienteInput) accionClienteInput.value = 'create';

            if (term.length < 2) {
                listClientes.classList.add('hidden');
                return;
            }

            debounceTimer = setTimeout(() => {
                const url = `${window.serverData.routes.searchClients}?term=${term}`;

                fetch(url)
                    .then(r => {
                        if (!r.ok) throw new Error('Network response was not ok');
                        return r.json();
                    })
                    .then(data => {
                        listClientes.innerHTML = '';
                        if (data.length > 0) {
                            listClientes.classList.remove('hidden');
                            data.forEach(client => {
                                const li = document.createElement('li');
                                li.className = "px-4 py-2 hover:bg-gray-100 cursor-pointer text-xs text-gray-700 border-b border-gray-50 flex flex-col";
                                li.innerHTML = `<span class="font-bold">${client.nombre_completo}</span><span class="text-gray-500">${client.telefono}</span>`;
                                li.addEventListener('click', () => selectClient(client));
                                listClientes.appendChild(li);
                            });
                        } else {
                            listClientes.classList.add('hidden');
                        }
                    })
                    .catch(err => console.error("Create.js: Error searching clients", err));
            }, 300);
        });

        document.addEventListener('click', function (e) {
            if (e.target !== inputClienteNombre && e.target !== listClientes) {
                listClientes.classList.add('hidden');
            }
        });
    }

    // --- Vehicle (Placa) Search Logic ---
    const inputPlaca = document.getElementById('inputPlaca');
    const listVehiculos = document.getElementById('listVehiculos');
    let debounceVehiculo;

    if (inputPlaca && listVehiculos && window.serverData.routes.searchVehicles) {
        inputPlaca.addEventListener('input', function () {
            const term = this.value;
            clearTimeout(debounceVehiculo);

            // Si el input está vacío o es muy corto, ocultamos lista
            if (term.length < 2) {
                listVehiculos.classList.add('hidden');
                return;
            }

            debounceVehiculo = setTimeout(() => {
                const url = `${window.serverData.routes.searchVehicles}?term=${term}`;
                fetch(url)
                    .then(r => r.json())
                    .then(data => {
                        listVehiculos.innerHTML = '';
                        if (data.length > 0) {
                            listVehiculos.classList.remove('hidden');
                            data.forEach(v => {
                                const li = document.createElement('li');
                                li.className = "px-4 py-2 hover:bg-gray-100 cursor-pointer text-xs text-gray-700 border-b border-gray-50 flex flex-col";
                                li.innerHTML = `<span class="font-bold">${v.placa}</span><span class="text-gray-500">${v.marca} ${v.modelo}</span>`;
                                li.addEventListener('click', () => {
                                    fillVehicleData(v);
                                    listVehiculos.classList.add('hidden');

                                    // Check Owner Logic
                                    if (v.cliente) {
                                        Swal.fire({
                                            title: 'Vehículo Registrado',
                                            html: `<div class="text-center p-2">
                                                <i class="fas fa-user-check text-4xl text-blue-900 mb-2"></i>
                                                <p>Este vehículo pertenece a: <br><strong class="text-lg text-blue-900">${v.cliente.nombre_completo}</strong></p>
                                                <p class="text-sm text-gray-500 mt-2">¿Desea cargar sus datos como dueño?</p>
                                            </div>`,
                                            showCancelButton: true,
                                            showDenyButton: true,
                                            confirmButtonText: 'Sí, cargar cliente',
                                            denyButtonText: 'No, es nuevo dueño',
                                            cancelButtonText: 'Cancelar',
                                            buttonsStyling: false,
                                            customClass: {
                                                confirmButton: 'btn bg-blue-900 border-blue-900 text-white px-4 mx-1 btn-sm',
                                                denyButton: 'btn btn-outline border-blue-900 text-blue-900 px-4 mx-1 btn-sm',
                                                cancelButton: 'btn btn-ghost text-gray-400 mx-1 btn-sm'
                                            }
                                        }).then((result) => {
                                            if (result.isConfirmed) {
                                                fillClientData(v.cliente);
                                            } else if (result.isDenied) {
                                                // NEW OWNER LOGIC (Clear inputs)
                                                if (walkInClienteId) walkInClienteId.value = '';
                                                if (accionClienteInput) accionClienteInput.value = 'create';
                                                inputClienteNombre.value = '';
                                                inputClienteTelefono.value = '';
                                                inputClienteEmail.value = '';
                                                inputClienteNombre.focus();
                                                Swal.fire({
                                                    title: 'Nuevo Dueño',
                                                    text: 'Ingrese los datos del nuevo cliente.',
                                                    icon: 'info',
                                                    confirmButtonColor: '#1e3a8a'
                                                });
                                            }
                                        });
                                    }
                                });
                                listVehiculos.appendChild(li);
                            });
                        } else {
                            listVehiculos.classList.add('hidden');
                        }
                    })
                    .catch(err => console.error("Error searching vehicles", err));
            }, 300);
        });

        document.addEventListener('click', function (e) {
            if (e.target !== inputPlaca && e.target !== listVehiculos) {
                listVehiculos.classList.add('hidden');
            }
        });
    }

    // --- PC Camera API Modal (Generic) ---
    async function openCameraModal(onCapture, autoReopen = true) {
        try {
            const stream = await navigator.mediaDevices.getUserMedia({ video: true });

            Swal.fire({
                title: 'Capturar Fotografía',
                html: `
                    <div class="relative rounded-lg overflow-hidden bg-black aspect-video flex items-center justify-center">
                        <video id="cameraFeed" autoplay playsinline class="w-full h-full object-cover"></video>
                        <canvas id="snapshotCanvas" class="hidden"></canvas>
                    </div>
                `,
                showCancelButton: true,
                confirmButtonText: '<i class="fas fa-camera mr-2"></i> Capturar',
                cancelButtonText: 'Cerrar',
                buttonsStyling: false,
                customClass: {
                    confirmButton: 'btn bg-blue-900 border-blue-900 text-white px-8 mx-2',
                    cancelButton: 'btn btn-ghost mx-2'
                },
                didOpen: () => {
                    const video = document.getElementById('cameraFeed');
                    if (video) video.srcObject = stream;
                },
                preConfirm: () => {
                    const video = document.getElementById('cameraFeed');
                    const canvas = document.getElementById('snapshotCanvas');

                    if (video && video.readyState >= 2) {
                        const width = video.videoWidth || 640;
                        const height = video.videoHeight || 480;
                        canvas.width = width;
                        canvas.height = height;
                        const ctx = canvas.getContext('2d');
                        ctx.drawImage(video, 0, 0, width, height);
                        return canvas.toDataURL('image/jpeg', 0.9);
                    } else {
                        Swal.showValidationMessage('La cámara no está lista');
                        return false;
                    }
                },
                willClose: () => {
                    stream.getTracks().forEach(track => track.stop());
                }
            }).then((result) => {
                if (result.isConfirmed && result.value) {
                    fetch(result.value)
                        .then(res => res.blob())
                        .then(blob => {
                            const file = new File([blob], `capture_${Date.now()}.jpg`, { type: 'image/jpeg' });
                            if (onCapture) onCapture(file);

                            if (autoReopen) {
                                setTimeout(() => openCameraModal(onCapture, autoReopen), 400);
                            }
                        });
                }
            });
        } catch (err) {
            console.error("Camera error:", err);
            Swal.fire('Error', 'No se pudo acceder a la cámara.', 'error');
        }
    }

    // --- Photo Selection Handler ---
    function handleFiles(files) {
        if (files && files.length > 0) {
            Array.from(files).forEach(file => {
                if (!file.type.startsWith('image/')) return;

                const reader = new FileReader();
                reader.onload = (e) => {
                    const uniqueId = Date.now() + Math.random().toString(36).substr(2, 9);
                    selectedPhotos.push({
                        id: uniqueId,
                        file: file,
                        src: e.target.result,
                        title: ''
                    });
                    renderPhotos();
                };
                reader.readAsDataURL(file);
            });
            const inputGallery = document.getElementById('inputGallery');
            if (inputGallery) inputGallery.value = '';
        }
    }

    // --- Advanced Photo Logic (Reception Gallery) ---
    const btnCamera = document.getElementById('btnCamera');
    const btnGallery = document.getElementById('btnGallery');
    const inputGallery = document.getElementById('inputGallery');
    const previewGrid = document.getElementById('previewFotosGrid');
    const emptyMsg = document.getElementById('emptyPhotosMsg');

    if (previewGrid) {

        // 1. Trigger Inputs
        if (btnCamera) {
            btnCamera.addEventListener('click', () => {
                openCameraModal((file) => handleFiles([file]), true); // true = auto-reopen for multi-photo
            });
        }

        if (btnGallery && inputGallery) {
            btnGallery.addEventListener('click', () => inputGallery.click());
            inputGallery.addEventListener('change', (e) => handleFiles(e.target.files));
        }

        // 3. Render Function
        window.renderPhotos = function () {
            // Clear current cards BUT keep the "Empty Msg" logic
            // We'll rebuild the grid content depending on array length

            // Remove all existing photo cards (elements with class 'photo-card')
            previewGrid.querySelectorAll('.photo-card').forEach(el => el.remove());

            if (selectedPhotos.length === 0) {
                if (emptyMsg) emptyMsg.classList.remove('hidden');
                // logic handled by css grid
            } else {
                if (emptyMsg) emptyMsg.classList.add('hidden');

                selectedPhotos.forEach(photo => {
                    const card = document.createElement('div');
                    card.className = "photo-card relative group bg-white border border-gray-200 rounded-lg shadow-md overflow-hidden hover:shadow-lg transition-all flex flex-col transform hover:-translate-y-1";
                    card.innerHTML = `
                        <div class="relative h-48 sm:h-64 w-full bg-gray-100 overflow-hidden cursor-pointer" onclick="viewPhoto('${photo.src}')">
                            <img src="${photo.src}" class="w-full h-full object-cover transition-transform duration-500 group-hover:scale-105">
                            <!-- Overlay Actions -->
                            <div class="absolute top-2 right-2 flex gap-1">
                                <button type="button" onclick="event.stopPropagation(); removePhoto('${photo.id}')" class="btn btn-sm btn-circle btn-error text-white shadow-lg opacity-90 hover:opacity-100">
                                    <i class="fas fa-times"></i>
                                </button>
                            </div>
                        </div>
                        <div class="p-3 border-t border-gray-100 bg-white">
                            <input type="text" id="title-${photo.id}" 
                                class="input input-sm w-full input-bordered focus:input-primary text-center font-bold text-gray-700 placeholder-gray-400 bg-gray-50 border-gray-200" 
                                placeholder="Descripción (Ej: Frente)" 
                                value="${photo.title}"
                                oninput="updatePhotoTitle('${photo.id}', this.value)"
                            >
                        </div>
                    `;
                    previewGrid.appendChild(card);
                });
            }
        };

        // 4. Helper Functions (Global for inline onclick)
        window.removePhoto = function (id) {
            selectedPhotos = selectedPhotos.filter(p => p.id !== id);
            renderPhotos();
        };

        window.updatePhotoTitle = function (id, val) {
            const photo = selectedPhotos.find(p => p.id === id);
            if (photo) photo.title = val;
        };

        window.viewPhoto = function (src) {
            Swal.fire({
                imageUrl: src,
                imageAlt: 'Vista Previa',
                showConfirmButton: false,
                showCloseButton: true,
                background: 'transparent',
                backdrop: 'rgba(0,0,0,0.92)', // Darker background for focus
                width: '95%', // Take most of the width
                padding: '0',
                showClass: {
                    popup: 'animate__animated animate__zoomIn animate__faster'
                },
                hideClass: {
                    popup: 'animate__animated animate__zoomOut animate__faster'
                },
                customClass: {
                    popup: 'bg-transparent shadow-none border-none',
                    image: 'rounded-xl shadow-2xl max-h-[90vh] object-contain' // Big image, keeps aspect ratio
                }
            });
        };
    }
    // --- Clear Button Logic ---
    const btnLimpiar = document.getElementById('btnLimpiar');
    if (btnLimpiar) {
        btnLimpiar.addEventListener('click', function () {
            Swal.fire({
                title: '¿Limpiar Formulario?',
                html: `<div class="p-2 text-center">
                <i class="fas fa-trash-alt text-4xl text-red-500 mb-2"></i>
                <p>Se borrarán todos los datos ingresados en el formulario.</p>
            </div>`,
                showCancelButton: true,
                confirmButtonText: 'Sí, limpiar todo',
                cancelButtonText: 'Cancelar',
                buttonsStyling: false,
                customClass: {
                    confirmButton: 'btn btn-error text-white px-8 mx-2',
                    cancelButton: 'btn btn-ghost mx-2'
                }
            }).then((result) => {
                if (result.isConfirmed) {
                    if (ordenForm) ordenForm.reset();
                    selectedPhotos = [];
                    if (window.renderPhotos) window.renderPhotos();

                    if (walkInClienteId) walkInClienteId.value = '';
                    if (accionClienteInput) accionClienteInput.value = 'create';

                    const btnDeleteDamagePhoto = document.getElementById('btnDeleteDamagePhoto');
                    if (btnDeleteDamagePhoto) btnDeleteDamagePhoto.click();

                    selectedDamagePhotos = [];
                    if (window.renderDamagePhotos) window.renderDamagePhotos();

                    if (fuelRange) {
                        fuelRange.value = 50;
                        updateFuel(50);
                    }
                    Swal.fire('Limpio', 'El formulario ha sido reiniciado.', 'success');
                }
            });
        });
    }
    // --- Lógica del Navegador por Pasos (Scroll Spy) ---
    const stepNavs = document.querySelectorAll('.step-nav');
    const sections = ['section-datos', 'section-recepcion', 'section-inventario', 'section-falla', 'section-danos'];

    // Desplazamiento Suave (Smooth Scroll)
    // Escucha el clic en los iconos de navegación y hace scroll suave hasta la sección
    stepNavs.forEach(nav => {
        nav.addEventListener('click', function (e) {
            e.preventDefault();
            const targetId = this.getAttribute('href').substring(1);
            const targetEl = document.getElementById(targetId);
            if (targetEl) {
                targetEl.scrollIntoView({ behavior: 'smooth' });
            }
        });
    });

    // Configuración del Observador (Scroll Spy)
    // Detecta automáticamente en qué sección se encuentra el usuario al hacer scroll
    const observerOptions = {
        root: null,
        rootMargin: '-10% 0px -80% 0px', // Define el área de activación (zona superior de la pantalla)
        threshold: 0
    };

    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                // Obtiene el nombre del paso (ej: 'datos') eliminando el prefijo 'section-'
                const step = entry.target.id.replace('section-', '');
                updateStepper(step);
            }
        });
    }, observerOptions);

    // Registra cada sección en el observador
    sections.forEach(id => {
        const el = document.getElementById(id);
        if (el) observer.observe(el);
    });

    // Función para actualizar visualmente los iconos del stepper (activo vs inactivo)
    function updateStepper(activeStep) {
        stepNavs.forEach(nav => {
            const stepName = nav.getAttribute('data-step');
            const iconCircle = nav.querySelector('div');
            const label = nav.querySelector('span');
            const isActive = stepName === activeStep;

            if (isActive) {
                // Estilo Activo: Azul Navy con relieve y escala aumentada
                iconCircle.className = "w-8 h-8 rounded-full border-2 border-blue-900 flex items-center justify-center bg-blue-900 text-white transition-all scale-110 shadow-md shadow-blue-900/20";
                label.className = "text-[9px] font-black uppercase text-blue-900 tracking-tighter";
            } else {
                // Estilo Inactivo: Gris neutro y fondo blanco
                iconCircle.className = "w-8 h-8 rounded-full border-2 border-gray-200 flex items-center justify-center bg-white text-gray-400 transition-all shadow-sm";
                label.className = "text-[9px] font-black uppercase text-gray-400 tracking-tighter";
            }
        });
    }
});
