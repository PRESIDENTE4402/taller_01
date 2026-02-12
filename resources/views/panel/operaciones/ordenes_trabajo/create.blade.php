@extends('layouts.panel')

@section('title', 'Nueva Orden de Trabajo')
@section('subtitle', 'Recepción de Vehículo')

@section('content')
<form action="{{ route('panel.operaciones.ordenes_trabajo.store') }}" method="POST" id="ordenForm" class="space-y-6">
    @csrf

    <!-- Header: Datos Generales (Card Similar a la Factura) -->
    <div class="bg-white p-6 rounded-xl shadow-sm border border-gray-100">
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 text-sm">
            <!-- Cliente -->
            <div class="lg:col-span-2 border p-3 rounded-lg bg-gray-50">
                <h4 class="font-bold text-gray-700 border-b pb-1 mb-2">DATOS DEL CLIENTE</h4>
                @if(isset($cliente))
                <div class="grid grid-cols-2 gap-2">
                    <p><span class="font-bold">Cliente:</span> {{ $cliente->nombre_completo }}</p>
                    <p><span class="font-bold">Tel:</span> {{ $cliente->telefono }}</p>
                    <p><span class="font-bold">Email:</span> {{ $cliente->email }}</p>
                    <input type="hidden" name="cliente_id" value="{{ $cliente->id }}">
                </div>
                @else
                <!-- Walk-in Inputs -->
                <div class="space-y-3 relative">
                    <input type="hidden" name="cliente_id" id="walkInClienteId">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                        <div class="relative">
                            <label class="block text-xs font-bold text-gray-500">Nombre Completo</label>
                            <input type="text" name="new_cliente[nombre]" id="inputClienteNombre" class="input input-sm input-bordered w-full uppercase" placeholder="BUSCAR O INGRESAR NOMBRE" required autocomplete="off" oninput="this.value = this.value.toUpperCase()">
                            <ul id="listClientes" class="absolute z-50 bg-white border border-gray-200 w-full rounded-md shadow-lg max-h-48 overflow-y-auto hidden"></ul>
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-gray-500">Teléfono</label>
                            <input type="text" name="new_cliente[telefono]" id="inputClienteTelefono" class="input input-sm input-bordered w-full" placeholder="5555-5555" required>
                        </div>
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-gray-500">Email (Opcional)</label>
                        <input type="email" name="new_cliente[email]" id="inputClienteEmail" class="input input-sm input-bordered w-full" placeholder="cliente@email.com">
                    </div>
                    <div class="text-xs text-blue-600 italic">
                        <i class="fas fa-info-circle"></i> Busque un cliente existente o ingrese uno nuevo.
                    </div>
                </div>
                @endif
            </div>

            <!-- Vehiculo -->
            <div class="lg:col-span-2 border p-3 rounded-lg bg-gray-50">
                <h4 class="font-bold text-gray-700 border-b pb-1 mb-2">DATOS DEL VEHÍCULO</h4>
                @if(isset($vehiculo))
                <div class="grid grid-cols-2 md:grid-cols-4 gap-2">
                    <div class="col-span-2"><span class="font-bold">Marca:</span> {{ $vehiculo->marca->nombre }}</div>
                    <div class="col-span-2"><span class="font-bold">Línea/Modelo:</span> {{ $vehiculo->modelo->nombre }}</div>
                    <div><span class="font-bold">Placas:</span> {{ $vehiculo->placa }}</div>
                    <div><span class="font-bold">Color:</span> <input type="text" name="color" class="w-full border-b border-gray-400 bg-transparent py-0 px-1 focus:outline-none" value="{{ $vehiculo->color }}"></div>
                    <!-- Campos extra que quizas no estan en DB aun pero pide el form -->
                    <div><span class="font-bold">Año:</span> {{ $vehiculo->anio }}</div>
                </div>
                <input type="hidden" name="vehiculo_id" value="{{ $vehiculo->id }}">
                @else
                <!-- Walk-in Vehicle Inputs -->
                <div class="space-y-3">
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
                        <div>
                            <label class="block text-xs font-bold text-gray-500">Placa</label>
                            <input type="text" name="new_vehiculo[placa]" class="input input-sm input-bordered w-full uppercase" placeholder="P-123ABC" required oninput="this.value = this.value.toUpperCase()">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-gray-500">Marca</label>
                            <input type="text" name="new_vehiculo[marca]" id="inputMarca" list="listMarcas" class="input input-sm input-bordered w-full uppercase" placeholder="TOYOTA, HONDA..." required autocomplete="off" oninput="this.value = this.value.toUpperCase()">
                            <datalist id="listMarcas"></datalist>
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-gray-500">Modelo/Línea</label>
                            <input type="text" name="new_vehiculo[modelo]" id="inputModelo" list="listModelos" class="input input-sm input-bordered w-full uppercase" placeholder="COROLLA, CIVIC..." required autocomplete="off" oninput="this.value = this.value.toUpperCase()">
                            <datalist id="listModelos"></datalist>
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-gray-500">Versión (Opcional)</label>
                            <input type="text" name="new_vehiculo[version]" id="inputVersion" list="listVersiones" class="input input-sm input-bordered w-full uppercase" placeholder="LE, XLE..." autocomplete="off" oninput="this.value = this.value.toUpperCase()">
                            <datalist id="listVersiones"></datalist>
                        </div>
                    </div>
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs font-bold text-gray-500">Color</label>
                            <input type="text" name="color" class="input input-sm input-bordered w-full" placeholder="Rojo, Azul..." required>
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-gray-500">Año</label>
                            <input type="number" name="new_vehiculo[anio]" class="input input-sm input-bordered w-full" placeholder="2020" required>
                        </div>
                    </div>
                </div>
                @endif
            </div>

            <!-- Datos de Ingreso -->
            <div class="border p-3 rounded-lg bg-gray-50">
                <h4 class="font-bold text-gray-700 border-b pb-1 mb-2">RECEPCIÓN</h4>
                <div class="space-y-2">
                    <div>
                        <label class="block text-xs font-bold text-gray-500">FECHA</label>
                        <input type="text" value="{{ now()->format('Y-m-d') }}" readonly class="w-full bg-transparent font-medium">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-gray-500">HORA</label>
                        <input type="text" value="{{ now()->format('H:i') }}" readonly class="w-full bg-transparent font-medium">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-gray-500">KILOMETRAJE</label>
                        <input type="number" name="kilometraje" class="input input-sm input-bordered w-full" required placeholder="Ej: 154000">
                    </div>
                </div>
            </div>

            <!-- Nivel Combustible (Gauge Visual) -->
            <div class="border p-3 rounded-lg bg-gray-50 flex flex-col items-center justify-center">
                <h4 class="font-bold text-gray-700 w-full border-b pb-1 mb-2 text-center">COMBUSTIBLE</h4>
                <div class="fuel-gauge-container relative w-full max-w-[200px] h-24">
                    <!-- SVG Gauge -->
                    <svg viewBox="0 0 200 100" class="w-full h-full">
                        <path d="M 20 90 A 80 80 0 0 1 180 90" fill="none" stroke="#e5e7eb" stroke-width="15" />
                        <path id="fuelLevelPath" d="M 20 90 A 80 80 0 0 1 180 90" fill="none" stroke="#3b82f6" stroke-width="15" stroke-dasharray="251.2" stroke-dashoffset="125.6" class="transition-all duration-500 ease-out" />

                        <!-- Ticks -->
                        <text x="15" y="100" class="text-xs fill-gray-500 font-bold">E</text>
                        <text x="55" y="45" class="text-xs fill-gray-500 font-bold">1/4</text>
                        <text x="95" y="20" class="text-xs fill-gray-500 font-bold">1/2</text>
                        <text x="140" y="45" class="text-xs fill-gray-500 font-bold">3/4</text>
                        <text x="180" y="100" class="text-xs fill-gray-500 font-bold">F</text>
                    </svg>
                    <input type="range" name="nivel_combustible_val" id="fuelRange" min="0" max="100" value="50" class="absolute bottom-0 w-full opacity-0 cursor-pointer h-full">
                    <input type="hidden" name="nivel_combustible" id="fuelInput" value="1/2">
                </div>
                <p class="text-center font-bold text-blue-600 mt-[-10px]" id="fuelLabel">1/2 Tank</p>
            </div>
        </div>
    </div>

    @if(isset($cita))
    <input type="hidden" name="cita_id" value="{{ $cita->id }}">
    <div class="alert alert-warning shadow-sm text-sm py-2">
        <i class="fas fa-info-circle"></i>
        <span><strong>Nota de Cita:</strong> {{ $cita->motivo_cita }}</span>
    </div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        <!-- Left: Checklist Detailed -->
        <div class="lg:col-span-2 bg-white p-6 rounded-xl shadow-sm border border-gray-100">
            <h3 class="font-bold text-gray-800 mb-4 border-b pb-2">Inventario de Recepción</h3>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-x-8 gap-y-2 text-sm">
                <!-- Column 1 -->
                <div>
                    <!-- Documentos -->
                    <div class="flex items-center justify-between py-2 border-b border-gray-100 group hover:bg-gray-50">
                        <span class="text-gray-600">Documentos</span>
                        <div class="flex gap-2">
                            <label class="inline-flex items-center"><input type="checkbox" name="inv[documentos][original]" class="checkbox checkbox-xs rounded border-gray-400"> <span class="ml-1 text-xs">Or</span></label>
                            <label class="inline-flex items-center"><input type="checkbox" name="inv[documentos][copia]" class="checkbox checkbox-xs rounded border-gray-400"> <span class="ml-1 text-xs">Co</span></label>
                        </div>
                    </div>

                    @php
                    $simpleItems1 = [
                    'encendedor' => 'Encendedor',
                    'radio' => 'Radio/Frontal',
                    'llavero' => 'Llavero',
                    'control_alarma' => 'Control Alarma',
                    'bateria' => 'Batería',
                    'tricket' => 'Tricket (Gato)',
                    'barilla' => 'Barilla',
                    'llave_seguridad' => 'Llave Seguridad',
                    'llave_chuchos' => 'Llave de Chuchos',
                    ];
                    @endphp

                    @foreach($simpleItems1 as $key => $label)
                    <div class="flex items-center justify-between py-2 border-b border-gray-100 hover:bg-gray-50">
                        <span class="text-gray-600">{{ $label }}</span>
                        <div class="flex gap-3">
                            <label class="inline-flex items-center cursor-pointer">
                                <input type="radio" name="inv[{{$key}}]" value="1" class="radio radio-xs radio-primary"> <span class="ml-1 text-xs">Si</span>
                            </label>
                            <label class="inline-flex items-center cursor-pointer">
                                <input type="radio" name="inv[{{$key}}]" value="0" class="radio radio-xs bg-gray-200" checked> <span class="ml-1 text-xs">No</span>
                            </label>
                        </div>
                    </div>
                    @endforeach
                </div>

                <!-- Column 2 -->
                <div>
                    @php
                    $simpleItems2 = [
                    'llanta_repuesto' => 'Llanta Repuesto',
                    'herramientas' => 'Herramientas',
                    'extinguidor' => 'Extinguidor',
                    'cables' => 'Cables Corriente',
                    'antena' => 'Antena',
                    'tapon_tanque' => 'Tapón Tanque',
                    'chibola' => 'Chibola Palanca',
                    'jalador' => 'Jalador',
                    ];
                    $qtyItems = [
                    'tapones_ruedas' => 'Tapones Ruedas',
                    'chuchos' => 'Chuchos (Tuercas)',
                    'plumillas' => 'Plumillas',
                    'alfombras' => 'Alfombras',
                    'retrovisores' => 'Retrovisores',
                    'triangulos' => 'Triangulos',
                    'valvulas' => 'Válvulas',
                    ]
                    @endphp

                    @foreach($simpleItems2 as $key => $label)
                    <div class="flex items-center justify-between py-2 border-b border-gray-100 hover:bg-gray-50">
                        <span class="text-gray-600">{{ $label }}</span>
                        <div class="flex gap-3">
                            <label class="inline-flex items-center cursor-pointer">
                                <input type="radio" name="inv[{{$key}}]" value="1" class="radio radio-xs radio-primary"> <span class="ml-1 text-xs">Si</span>
                            </label>
                            <label class="inline-flex items-center cursor-pointer">
                                <input type="radio" name="inv[{{$key}}]" value="0" class="radio radio-xs bg-gray-200" checked> <span class="ml-1 text-xs">No</span>
                            </label>
                        </div>
                    </div>
                    @endforeach

                    <!-- Quantity Items -->
                    @foreach($qtyItems as $key => $label)
                    <div class="flex items-center justify-between py-2 border-b border-gray-100 hover:bg-gray-50">
                        <span class="text-gray-600">{{ $label }}</span>
                        <div class="flex items-center gap-2">
                            <label class="inline-flex items-center cursor-pointer">
                                <input type="checkbox" name="inv[{{$key}}][check]" value="1" class="checkbox checkbox-xs rounded border-gray-400 toggle-qty" data-target="qty-{{$key}}">
                                <span class="ml-1 text-xs">Si</span>
                            </label>
                            <input type="number" id="qty-{{$key}}" name="inv[{{$key}}][cant]" class="input input-xs input-bordered w-12 text-center hidden" placeholder="#">
                        </div>
                    </div>
                    @endforeach

                    <!-- Tapiceria Special Case -->
                    <div class="flex items-center justify-between py-2 border-b border-gray-100 hover:bg-gray-50">
                        <span class="text-gray-600">Tapicería</span>
                        <div class="flex gap-2">
                            <label class="inline-flex items-center cursor-pointer" title="Mala">
                                <input type="radio" name="inv[tapiceria]" value="M" class="radio radio-xs radio-error"> <span class="ml-1 text-xs">M</span>
                            </label>
                            <label class="inline-flex items-center cursor-pointer" title="Regular">
                                <input type="radio" name="inv[tapiceria]" value="R" class="radio radio-xs radio-warning"> <span class="ml-1 text-xs">R</span>
                            </label>
                            <label class="inline-flex items-center cursor-pointer" title="Buena">
                                <input type="radio" name="inv[tapiceria]" value="B" class="radio radio-xs radio-success" checked> <span class="ml-1 text-xs">B</span>
                            </label>
                        </div>
                    </div>

                </div>
            </div>

            <div class="mt-4">
                <label class="font-bold text-gray-700 text-sm">Observaciones Generales</label>
                <textarea name="descripcion" rows="3" class="w-full border-gray-300 rounded-lg focus:ring-blue-500 mt-1" placeholder="Ej: Cliente no deja llave de chuchos. Trae golpe en puerta derecha..."></textarea>
            </div>

            <div class="mt-4">
                <label class="font-bold text-gray-700 text-sm">Falla / Servicio Solicitado</label>
                <textarea name="falla_cliente" rows="3" class="w-full textarea textarea-bordered focus:border-blue-500 mt-1 bg-yellow-50 text-gray-900" placeholder="Describa el trabajo a realizar..." required>{{ isset($cita) ? $cita->motivo_cita : '' }}</textarea>
            </div>
        </div>

        <!-- Right: Damage Canvas -->
        <div class="lg:col-span-1 space-y-4 lg:sticky lg:top-24 h-fit">
            <div class="bg-white p-4 rounded-xl shadow-sm border border-gray-100 flex flex-col">
                <h3 class="font-bold text-gray-800 mb-2 border-b pb-2">Daños Reportados</h3>
                <div class="mb-2">
                    <label class="block text-xs text-gray-500 mb-1">1. Subir Foto del Vehículo (Opcional)</label>
                    <input type="file" id="damageImageUpload" accept="image/*" class="file-input file-input-bordered file-input-xs w-full max-w-xs" />
                </div>
                <p class="text-xs text-gray-500 mb-2">2. Haga clic en la imagen para marcar daños (X).</p>

                <div class="relative flex-grow flex items-center justify-center bg-gray-50 border rounded-lg overflow-hidden" id="canvasContainer">
                    <!-- Placeholder Car Image - You would replace this with your actual image path -->
                    <!-- Drawing Canvas Overlay -->
                    <canvas id="damageCanvas" class="absolute top-0 left-0 w-full h-full cursor-crosshair z-10"></canvas>
                    <!-- Background Image -->
                    <!-- Background Image (Removed static img, handled by Canvas now) -->
                    <!-- <img src="..." ...> Removed to allow dynamic canvas background -->
                    <input type="hidden" name="danos_image" id="danosImageInput">
                </div>
                <div class="flex justify-between mt-2">
                    <button type="button" id="clearCanvas" class="btn btn-xs btn-outline btn-error">Limpiar</button>
                    <span class="text-xs text-gray-400">Clic para marcar daño</span>
                </div>
            </div>
        </div>

    </div>

    <!-- Static Footer (moved from fixed to avoid covering content) -->
    <div class="mt-8 bg-white border border-gray-100 p-4 rounded-xl shadow-sm flex justify-end gap-4">
        <a href="{{ route('panel.operaciones.ordenes_trabajo.index') }}" class="btn bg-gray-100 hover:bg-gray-200 text-gray-600 font-bold px-6 rounded-xl">Cancelar</a>
        <button type="submit" class="btn bg-blue-600 text-white font-bold px-8 rounded-xl shadow-lg hover:bg-blue-700 transition-all">
            <i class="fas fa-check-circle mr-2"></i> Generar Orden
        </button>
    </div>
</form>
@endsection

@push('scripts')
<script>
    // Fuel Gauge Logic
    const fuelRange = document.getElementById('fuelRange');
    const fuelPath = document.getElementById('fuelLevelPath');
    const fuelLabel = document.getElementById('fuelLabel');
    const fuelInput = document.getElementById('fuelInput');

    // Total Length of the arc is approx 251.2 (r=80, half circle)
    const arcLength = 251.2;

    function updateFuel(val) {
        // Map 0-100 to stroke-dashoffset (251.2 to 0)
        // Full (100) -> offset 0
        // Empty (0) -> offset 251.2
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

    fuelRange.addEventListener('input', (e) => updateFuel(e.target.value));
    setTimeout(() => updateFuel(50), 100); // Init

    // Toggle Qty Inputs
    document.querySelectorAll('.toggle-qty').forEach(chk => {
        chk.addEventListener('change', function() {
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

    // Canvas Logic
    const canvas = document.getElementById('damageCanvas');
    const ctx = canvas.getContext('2d');
    const container = document.getElementById('canvasContainer');
    const imageUpload = document.getElementById('damageImageUpload');
    const defaultImageSrc = "https://st3.depositphotos.com/1092008/13606/v/450/depositphotos_136061320-stock-illustration-car-sedan-top-view-icon.jpg";

    let currentImage = new Image();
    currentImage.crossOrigin = "anonymous";
    currentImage.src = defaultImageSrc;

    // Store marks as relative coordinates {x: 0.5, y: 0.5}
    let marks = [];

    currentImage.onload = function() {
        resizeCanvas();
    };

    function resizeCanvas() {
        canvas.width = container.offsetWidth;
        canvas.height = container.offsetHeight;
        redrawAll();
    }

    function redrawAll() {
        if (!currentImage.complete) return;

        ctx.clearRect(0, 0, canvas.width, canvas.height);

        // 1. Draw Image (Contain)
        const hRatio = canvas.width / currentImage.width;
        const vRatio = canvas.height / currentImage.height;
        const ratio = Math.min(hRatio, vRatio);

        const centerShift_x = (canvas.width - currentImage.width * ratio) / 2;
        const centerShift_y = (canvas.height - currentImage.height * ratio) / 2;

        ctx.drawImage(currentImage, 0, 0, currentImage.width, currentImage.height,
            centerShift_x, centerShift_y, currentImage.width * ratio, currentImage.height * ratio);

        // 2. Draw Marks
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

    imageUpload.addEventListener('change', function(e) {
        if (this.files && this.files[0]) {
            const reader = new FileReader();
            reader.onload = function(evt) {
                const img = new Image();
                img.onload = function() {
                    currentImage = img;
                    marks = []; // Clear marks on new image
                    redrawAll();
                }
                img.src = evt.target.result;
            };
            reader.readAsDataURL(this.files[0]);
        }
    });

    window.addEventListener('resize', resizeCanvas);
    setTimeout(resizeCanvas, 500);

    // Click to add mark
    canvas.addEventListener('click', function(e) {
        const rect = canvas.getBoundingClientRect();
        const x = e.clientX - rect.left;
        const y = e.clientY - rect.top;

        // Save as relative coordinates
        marks.push({
            x: x / canvas.width,
            y: y / canvas.height
        });

        redrawAll();
    });

    document.getElementById('clearCanvas').addEventListener('click', () => {
        marks = [];
        redrawAll();
    });

    function saveCanvas() {
        document.getElementById('danosImageInput').value = canvas.toDataURL();
    }

    // Submit Handler
    document.getElementById('ordenForm').addEventListener('submit', async function(e) {
        e.preventDefault();
        const btn = this.querySelector('button[type="submit"]');
        btn.disabled = true;
        btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Procesando...';

        try {
            const formData = new FormData(this);
            const response = await fetch(this.action, {
                method: 'POST',
                body: formData,
                headers: {
                    'Accept': 'application/json'
                }
            });
            const result = await response.json();

            if (result.success) {
                Swal.fire('Éxito', result.message, 'success').then(() => window.location.href = result.redirect);
            } else {
                Swal.fire('Error', result.message, 'error');
                btn.disabled = false;
                btn.innerHTML = '<i class="fas fa-check-circle"></i> Generar Orden';
            }

        } catch (error) {
            console.error(error);
            Swal.fire('Error', 'Ocurrió un error inesperado', 'error');
            btn.disabled = false;
            btn.innerHTML = '<i class="fas fa-check-circle"></i> Generar Orden';
        }
    });

    // Dynamic Vehicle Inputs Logic
    const inputMarca = document.getElementById('inputMarca');
    const inputModelo = document.getElementById('inputModelo');
    const inputVersion = document.getElementById('inputVersion');

    const listMarcas = document.getElementById('listMarcas');
    const listModelos = document.getElementById('listModelos');
    const listVersiones = document.getElementById('listVersiones');

    let marcasMap = {}; // Name -> ID
    let modelosMap = {}; // Name -> ID

    // 1. Load Marcas on Init
    if (inputMarca) {
        fetch("{{ route('panel.mantenimientos.marcas.list') }}")
            .then(r => r.json())
            .then(data => {
                listMarcas.innerHTML = '';
                marcasMap = {}; // Reset
                data.forEach(m => {
                    const nombreUpper = m.nombre.toUpperCase();
                    const opt = document.createElement('option');
                    opt.value = nombreUpper;
                    listMarcas.appendChild(opt);
                    marcasMap[nombreUpper] = m.id;
                });
            });

        // 2. On Marca Change -> Load Modelos
        inputMarca.addEventListener('input', function() {
            const marcaName = this.value.toUpperCase();
            const marcaId = marcasMap[marcaName];

            inputModelo.value = '';
            listModelos.innerHTML = '';
            inputVersion.value = '';
            listVersiones.innerHTML = '';
            modelosMap = {};

            if (marcaId) {
                // Fetch Modelos for this Marca
                const url = "{{ route('panel.mantenimientos.modelos.listByMarca', ':id') }}".replace(':id', marcaId);
                fetch(url)
                    .then(r => r.json())
                    .then(data => {
                        listModelos.innerHTML = ''; // Clear again to be safe
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

        // 3. On Modelo Change -> Load Versiones
        inputModelo.addEventListener('input', function() {
            const modeloName = this.value.toUpperCase();
            const modeloId = modelosMap[modeloName];

            inputVersion.value = '';
            listVersiones.innerHTML = '';

            if (modeloId) {
                // Fetch Versiones for this Modelo
                const url = "{{ route('panel.mantenimientos.versiones.listByModelo', ':id') }}".replace(':id', modeloId);
                fetch(url)
                    .then(r => r.json())
                    .then(data => {
                        listVersiones.innerHTML = ''; // Clear
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

    // Client Autocomplete Logic
    const inputClienteNombre = document.getElementById('inputClienteNombre');
    const inputClienteTelefono = document.getElementById('inputClienteTelefono');
    const inputClienteEmail = document.getElementById('inputClienteEmail');
    const listClientes = document.getElementById('listClientes');
    const walkInClienteId = document.getElementById('walkInClienteId');
    let debounceTimer;

    if (inputClienteNombre) {
        inputClienteNombre.addEventListener('input', function() {
            const term = this.value;
            clearTimeout(debounceTimer);

            // Si limpia el campo o escribe algo nuevo, reseteamos el ID para forzar creación o búsqueda nueva
            walkInClienteId.value = '';

            if (term.length < 2) {
                listClientes.classList.add('hidden');
                return;
            }

            debounceTimer = setTimeout(() => {
                fetch(`{{ route('panel.operaciones.citas.searchClients') }}?term=${term}`)
                    .then(r => r.json())
                    .then(data => {
                        listClientes.innerHTML = '';
                        if (data.length > 0) {
                            listClientes.classList.remove('hidden');
                            data.forEach(client => {
                                const li = document.createElement('li');
                                li.className = "px-4 py-2 hover:bg-gray-100 cursor-pointer text-xs text-gray-700 border-b border-gray-50 flex flex-col";
                                li.innerHTML = `<span class="font-bold">${client.nombre_completo}</span><span class="text-gray-500">${client.telefono}</span>`;
                                li.onclick = () => selectClient(client);
                                listClientes.appendChild(li);
                            });
                        } else {
                            listClientes.classList.add('hidden');
                        }
                    });
            }, 300);
        });

        // Hide list on click outside
        document.addEventListener('click', function(e) {
            if (e.target !== inputClienteNombre && e.target !== listClientes) {
                listClientes.classList.add('hidden');
            }
        });
    }

    function selectClient(client) {
        inputClienteNombre.value = client.nombre_completo.toUpperCase();
        inputClienteTelefono.value = client.telefono;
        inputClienteEmail.value = client.email || '';
        walkInClienteId.value = client.id;
        listClientes.classList.add('hidden');

        // Visual feedback
        inputClienteNombre.classList.add('border-green-500', 'bg-green-50');
        setTimeout(() => inputClienteNombre.classList.remove('border-green-500', 'bg-green-50'), 1000);
    }
</script>
@endpush