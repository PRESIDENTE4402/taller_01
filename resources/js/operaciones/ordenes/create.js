document.addEventListener('DOMContentLoaded', function () {

    // --- Fuel Gauge Logic ---
    const fuelRange = document.getElementById('fuelRange');
    const fuelPath = document.getElementById('fuelLevelPath');
    const fuelLabel = document.getElementById('fuelLabel');
    const fuelInput = document.getElementById('fuelInput');
    const arcLength = 251.2;

    function updateFuel(val) {
        if (!fuelPath || !fuelLabel || !fuelInput) return;

        const offset = arcLength - ((val / 100) * arcLength);
        fuelPath.style.strokeDashoffset = offset;

        let label = '';
        let dbVal = '';
        if (val < 10) {
            label = 'Reserva (E)';
            dbVal = 'R';
        } else if (val < 35) {
            label = '1/4 Tanque';
            dbVal = '1/4';
        } else if (val < 60) {
            label = '1/2 Tanque';
            dbVal = '1/2';
        } else if (val < 85) {
            label = '3/4 Tanque';
            dbVal = '3/4';
        } else {
            label = 'Full (F)';
            dbVal = 'F';
        }

        fuelLabel.innerText = label;
        fuelInput.value = dbVal;
    }

    if (fuelRange) {
        fuelRange.addEventListener('input', (e) => updateFuel(e.target.value));
        setTimeout(() => updateFuel(50), 100);
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

    // Default image if needed, or just transparent
    // We use a transparent canvas over a background if needed, or just draw on canvas.
    // The original code tried to load a car image.
    const defaultImageSrc = "https://st3.depositphotos.com/1092008/13606/v/450/depositphotos_136061320-stock-illustration-car-sedan-top-view-icon.jpg";

    if (canvas && container) {
        const ctx = canvas.getContext('2d');
        let currentImage = new Image();
        currentImage.crossOrigin = "anonymous";
        currentImage.src = defaultImageSrc;
        let marks = [];

        currentImage.onload = function () {
            resizeCanvas();
        };

        function resizeCanvas() {
            if (!container) return;
            canvas.width = container.offsetWidth;
            canvas.height = container.offsetHeight;
            redrawAll();
        }

        function redrawAll() {
            if (!currentImage.complete) return;

            ctx.clearRect(0, 0, canvas.width, canvas.height);

            // Draw Image (Contain)
            const hRatio = canvas.width / currentImage.width;
            const vRatio = canvas.height / currentImage.height;
            const ratio = Math.min(hRatio, vRatio);
            const centerShift_x = (canvas.width - currentImage.width * ratio) / 2;
            const centerShift_y = (canvas.height - currentImage.height * ratio) / 2;

            ctx.drawImage(currentImage, 0, 0, currentImage.width, currentImage.height,
                centerShift_x, centerShift_y, currentImage.width * ratio, currentImage.height * ratio);

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
                        const img = new Image();
                        img.onload = function () {
                            currentImage = img;
                            marks = [];
                            redrawAll();
                        }
                        img.src = evt.target.result;
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

        function saveCanvas() {
            if (danosImageInput) danosImageInput.value = canvas.toDataURL();
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
    let debounceTimer;

    function fillClientData(client) {
        inputClienteNombre.value = client.nombre_completo.toUpperCase();
        inputClienteTelefono.value = client.telefono;
        inputClienteEmail.value = client.email || '';
        walkInClienteId.value = client.id;

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
                            title: 'Vehículo Encontrado',
                            text: `El cliente tiene registrado: ${v.texto}. ¿Desea cargarlo?`,
                            icon: 'question',
                            showCancelButton: true,
                            confirmButtonText: 'Sí, cargar',
                            cancelButtonText: 'No, usar otro'
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
                            inputPlaceholder: 'Seleccione...',
                            showCancelButton: true,
                            confirmButtonText: 'Cargar Vehículo',
                            cancelButtonText: 'Cancelar'
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
            walkInClienteId.value = '';

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
                                            text: `Pertenece a: ${v.cliente.nombre_completo}. ¿Desea cargar al cliente?`,
                                            icon: 'info',
                                            showCancelButton: true,
                                            confirmButtonText: 'Sí, es el dueño',
                                            cancelButtonText: 'No, es nuevo dueño'
                                        }).then((result) => {
                                            if (result.isConfirmed) {
                                                fillClientData(v.cliente);
                                            } else {
                                                // New Owner: Confirm we clear client data
                                                walkInClienteId.value = '';
                                                inputClienteNombre.value = '';
                                                inputClienteTelefono.value = '';
                                                inputClienteEmail.value = '';
                                                inputClienteNombre.focus();
                                                Swal.fire('Nuevo Dueño', 'Ingrese los datos del nuevo cliente.', 'info');
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

    // --- Photo Preview Logic ---
    const inputFotos = document.getElementById('fotosRecepcion');
    const previewContainer = document.getElementById('previewFotos');

    if (inputFotos && previewContainer) {
        inputFotos.addEventListener('change', function () {
            previewContainer.innerHTML = '';
            const files = Array.from(this.files);

            files.forEach(file => {
                if (file.type.startsWith('image/')) {
                    const reader = new FileReader();
                    reader.onload = function (e) {
                        const div = document.createElement('div');
                        div.className = "relative group border rounded-lg overflow-hidden h-32 bg-gray-100";
                        div.innerHTML = `
                            <img src="${e.target.result}" class="w-full h-full object-cover">
                            <div class="absolute inset-0 bg-black/40 hidden group-hover:flex items-center justify-center text-white text-xs text-center p-1">
                                ${file.name}
                            </div>
                        `;
                        previewContainer.appendChild(div);
                    }
                    reader.readAsDataURL(file);
                }
            });
        });
    }
});
