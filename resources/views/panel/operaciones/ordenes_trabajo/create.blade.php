@extends('layouts.panel')

@section('title', 'Recepción de Vehículo')
@section('subtitle', 'Recepción de Vehículo')

@section('content')
<form action="{{ route('panel.operaciones.ordenes_trabajo.store') }}" method="POST" id="ordenForm" class="space-y-6" enctype="multipart/form-data">
    @csrf

    <!-- Header: Datos Generales (Card Similar a la Factura) -->
    <div class="bg-white p-6 rounded-xl shadow-sm border border-gray-100">
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 text-sm">
            <!-- Cliente -->
            <div class="lg:col-span-2 border p-3 rounded-lg bg-gray-50">
                <h4 class="font-bold text-gray-700 border-b pb-1 mb-2">DATOS DEL CLIENTE</h4>
                @if(isset($cliente))
                <!-- Smart Edit Controls Client -->
                <div class="mb-3 bg-amber-50 border border-amber-200 rounded-lg p-3 text-sm">
                    <p class="font-bold text-amber-800 mb-1"><i class="fas fa-user-edit mr-1"></i> Cliente Pre-cargado</p>
                    <div class="flex flex-col sm:flex-row gap-4">
                        <label class="flex items-center gap-2 cursor-pointer">
                            <input type="radio" name="accion_cliente" value="update" checked class="radio radio-xs radio-warning">
                            <span>Actualizar (Corregir)</span>
                        </label>
                        <label class="flex items-center gap-2 cursor-pointer">
                            <input type="radio" name="accion_cliente" value="create" class="radio radio-xs radio-warning">
                            <span>Nuevo (Otro)</span>
                        </label>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-bold text-gray-500">Nombre Completo</label>
                        <input type="text" name="new_cliente[nombre]" value="{{ $cliente->nombre_completo }}" class="input input-sm input-bordered w-full uppercase" required oninput="this.value = this.value.toUpperCase()">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-gray-500">Teléfono</label>
                        <input type="text" name="new_cliente[telefono]" value="{{ $cliente->telefono }}" class="input input-sm input-bordered w-full" required>
                    </div>
                    <div class="md:col-span-2">
                        <label class="block text-xs font-bold text-gray-500">Email</label>
                        <input type="email" name="new_cliente[email]" value="{{ $cliente->email }}" class="input input-sm input-bordered w-full">
                    </div>
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
                <!-- Vehicle Inputs (Always Editable) -->
                <div class="space-y-3">
                    <input type="hidden" name="vehiculo_id" value="{{ isset($vehiculo) ? $vehiculo->id : '' }}">

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
                        <div>
                            <label class="block text-xs font-bold text-gray-500">Placa</label>
                            <input type="text" name="new_vehiculo[placa]" id="inputPlaca" class="input input-sm input-bordered w-full uppercase"
                                value="{{ isset($vehiculo) ? $vehiculo->placa : '' }}"
                                placeholder="P-123ABC" required autocomplete="off" oninput="this.value = this.value.toUpperCase()">
                            <ul id="listVehiculos" class="absolute z-50 bg-white border border-gray-200 w-full rounded-md shadow-lg max-h-48 overflow-y-auto hidden"></ul>
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-gray-500">Marca</label>
                            <input type="text" name="new_vehiculo[marca]" id="inputMarca" list="listMarcas" class="input input-sm input-bordered w-full uppercase"
                                value="{{ isset($vehiculo) ? $vehiculo->marca->nombre : '' }}"
                                placeholder="TOYOTA, HONDA..." required autocomplete="off" oninput="this.value = this.value.toUpperCase()">
                            <datalist id="listMarcas"></datalist>
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-gray-500">Modelo/Línea</label>
                            <input type="text" name="new_vehiculo[modelo]" id="inputModelo" list="listModelos" class="input input-sm input-bordered w-full uppercase"
                                value="{{ isset($vehiculo) ? $vehiculo->modelo->nombre : '' }}"
                                placeholder="COROLLA, CIVIC..." required autocomplete="off" oninput="this.value = this.value.toUpperCase()">
                            <datalist id="listModelos"></datalist>
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-gray-500">Versión (Opcional)</label>
                            <input type="text" name="new_vehiculo[version]" id="inputVersion" list="listVersiones" class="input input-sm input-bordered w-full uppercase"
                                value="{{ isset($vehiculo) && $vehiculo->version ? $vehiculo->version->nombre : '' }}"
                                placeholder="LE, XLE..." autocomplete="off" oninput="this.value = this.value.toUpperCase()">
                            <datalist id="listVersiones"></datalist>
                        </div>
                    </div>
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs font-bold text-gray-500">Color</label>
                            <input type="text" name="color" class="input input-sm input-bordered w-full"
                                value="{{ isset($vehiculo) ? $vehiculo->color : '' }}"
                                placeholder="Rojo, Azul..." required>
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-gray-500">Año</label>
                            <input type="number" name="new_vehiculo[anio]" class="input input-sm input-bordered w-full"
                                value="{{ isset($vehiculo) ? $vehiculo->anio : '' }}"
                                placeholder="2020" required>
                        </div>
                    </div>
                </div>
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

    <!-- Fotos de Recepción -->
    <div class="bg-white p-6 rounded-xl shadow-sm border border-gray-100">
        <h4 class="font-bold text-gray-700 border-b pb-1 mb-4">FOTOS DE RECEPCIÓN</h4>
        <div class="space-y-4">
            <div class="flex items-center gap-4">
                <input type="file" name="fotos_recepcion[]" id="fotosRecepcion" multiple accept="image/*" class="file-input file-input-bordered w-full max-w-xs" />
                <span class="text-xs text-gray-500">Seleccione múltiples fotos (Frente, Costados, Trasera, Tablero)</span>
            </div>

            <div id="previewFotos" class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-6 gap-4">
                <!-- Previews will be inserted here -->
            </div>
        </div>
    </div>

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
    window.serverData = {
        routes: {
            searchClients: "{{ route('panel.operaciones.citas.searchClients') }}",
            searchVehicles: "{{ route('panel.operaciones.citas.searchVehicles') }}",
            getClientVehicles: "{{ route('panel.operaciones.citas.getClientVehicles', 'PLACEHOLDER') }}",
            marcasList: "{{ route('panel.mantenimientos.marcas.list') }}",
            modelosList: "{{ route('panel.mantenimientos.modelos.listByMarca', 'PLACEHOLDER') }}",
            versionesList: "{{ route('panel.mantenimientos.versiones.listByModelo', 'PLACEHOLDER') }}",
        }
    };
</script>
@vite('resources/js/operaciones/ordenes/create.js')
@endpush