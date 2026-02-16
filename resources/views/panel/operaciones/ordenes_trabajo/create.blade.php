@extends('layouts.panel')

@section('title', 'Recepción de Vehículo')
@section('subtitle', 'Recepción de Vehículo')

@section('content')
<form action="{{ route('panel.operaciones.ordenes_trabajo.store') }}" method="POST" id="ordenForm" class="space-y-6" enctype="multipart/form-data">
    @csrf

    <!-- Header: Datos Generales (Card Similar a la Factura) -->
    <!-- Header: Datos Generales (Diseño Moderno) -->
    <!-- Header: Datos Generales (Diseño Moderno) -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 pb-20 bg-gradient-to-br from-blue-900 to-slate-900 p-6 rounded-xl">

        <!-- Columna Izquierda: Datos (Cliente + Vehículo) -->
        <div class="lg:col-span-8 space-y-6">
            <!-- Cliente Card -->
            <div class="card bg-white shadow-lg shadow-blue-900/10 border border-blue-900/20">
                <div class="card-body p-5">
                    <h2 class="card-title text-sm font-bold text-gray-500 border-b pb-2 mb-4">
                        <i class="fas fa-user-circle text-blue-900"></i> DATOS DEL CLIENTE
                    </h2>

                    @if(isset($cliente))
                    <!-- Smart Edit Controls Client -->
                    <div class="alert alert-warning shadow-sm mb-4 p-3 text-sm">
                        <i class="fas fa-info-circle"></i>
                        <div class="flex flex-col sm:flex-row gap-4 w-full justify-between items-center">
                            <span class="font-bold">Cliente Pre-cargado</span>
                            <div class="flex gap-4">
                                <label class="flex items-center gap-2 cursor-pointer">
                                    <input type="radio" name="accion_cliente" value="update" checked class="radio radio-xs radio-primary">
                                    <span>Actualizar Datos</span>
                                </label>
                                <label class="flex items-center gap-2 cursor-pointer">
                                    <input type="radio" name="accion_cliente" value="create" class="radio radio-xs radio-primary">
                                    <span>Nuevo Cliente</span>
                                </label>
                            </div>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div class="form-control w-full">
                            <label class="label"><span class="label-text font-bold text-blue-900">Nombre Completo</span></label>
                            <input type="text" name="new_cliente[nombre]" value="{{ $cliente->nombre_completo }}" class="input input-sm input-bordered border-yellow-400 w-full uppercase focus:ring-2 focus:ring-blue-900 hover:shadow-md hover:-translate-y-0.5 transition-all duration-300" required oninput="this.value = this.value.toUpperCase()">
                        </div>
                        <div class="form-control w-full">
                            <label class="label"><span class="label-text font-bold text-blue-900">Teléfono</span></label>
                            <input type="text" name="new_cliente[telefono]" value="{{ $cliente->telefono }}" class="input input-sm input-bordered border-yellow-400 w-full focus:ring-2 focus:ring-blue-900 hover:shadow-md hover:-translate-y-0.5 transition-all duration-300" required>
                        </div>
                        <div class="form-control w-full md:col-span-2">
                            <label class="label"><span class="label-text font-bold text-blue-900">Email</span></label>
                            <input type="email" name="new_cliente[email]" value="{{ $cliente->email }}" class="input input-sm input-bordered border-yellow-400 w-full focus:ring-2 focus:ring-blue-900 hover:shadow-md hover:-translate-y-0.5 transition-all duration-300">
                        </div>
                        <input type="hidden" name="cliente_id" value="{{ $cliente->id }}">
                    </div>
                    @else
                    <!-- Walk-in Inputs -->
                    <input type="hidden" name="cliente_id" id="walkInClienteId">
                    <input type="hidden" name="accion_cliente" id="accionClienteInput" value="create">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 relative">
                        <div class="form-control w-full">
                            <label class="label"><span class="label-text font-bold text-blue-900">Nombre (Buscar/Nuevo)</span></label>
                            <div class="relative">
                                <input type="text" name="new_cliente[nombre]" id="inputClienteNombre" class="input input-sm input-bordered border-yellow-400 w-full uppercase focus:ring-2 focus:ring-blue-900 hover:shadow-md hover:-translate-y-0.5 transition-all duration-300" placeholder="Escriba para buscar..." required autocomplete="off" oninput="this.value = this.value.toUpperCase()">
                                <ul id="listClientes" class="absolute z-50 bg-white border border-gray-200 w-full rounded-md shadow-xl max-h-48 overflow-y-auto hidden top-full mt-1"></ul>
                            </div>
                        </div>
                        <div class="form-control w-full">
                            <label class="label"><span class="label-text font-bold text-blue-900">Teléfono</span></label>
                            <input type="text" name="new_cliente[telefono]" id="inputClienteTelefono" class="input input-sm input-bordered border-yellow-400 w-full focus:ring-2 focus:ring-blue-900 hover:shadow-md hover:-translate-y-0.5 transition-all duration-300" placeholder="Ej: 5555-5555" required>
                        </div>
                        <div class="form-control w-full">
                            <label class="label"><span class="label-text font-bold text-blue-900">Email (Opcional)</span></label>
                            <input type="email" name="new_cliente[email]" id="inputClienteEmail" class="input input-sm input-bordered border-yellow-400 w-full focus:ring-2 focus:ring-blue-900 hover:shadow-md hover:-translate-y-0.5 transition-all duration-300" placeholder="cliente@email.com">
                        </div>
                        <div class="flex items-end pb-1">
                            <p class="text-xs text-gray-500 italic"><i class="fas fa-search"></i> Busque un cliente existente o llene los datos para uno nuevo.</p>
                        </div>
                    </div>
                    @endif
                </div>
            </div>

            <!-- Vehiculo Card -->
            <div class="card bg-white shadow-lg shadow-blue-900/10 border border-blue-900/20">
                <div class="card-body p-5">
                    <h2 class="card-title text-sm font-bold text-gray-500 border-b pb-2 mb-4">
                        <i class="fas fa-car text-blue-900"></i> DATOS DEL VEHÍCULO
                    </h2>
                    <input type="hidden" name="vehiculo_id" value="{{ isset($vehiculo) ? $vehiculo->id : '' }}">

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div class="form-control w-full">
                            <label class="label"><span class="label-text font-bold text-blue-900">Placa (Buscador)</span></label>
                            <div class="relative">
                                <input type="text" name="new_vehiculo[placa]" id="inputPlaca" class="input input-sm input-bordered border-yellow-400 w-full uppercase font-bold text-blue-900 focus:ring-2 focus:ring-blue-900 hover:shadow-md hover:-translate-y-0.5 transition-all duration-300"
                                    value="{{ isset($vehiculo) ? $vehiculo->placa : '' }}"
                                    placeholder="P-123ABC" required autocomplete="off" oninput="this.value = this.value.toUpperCase()">
                                <ul id="listVehiculos" class="absolute z-50 bg-white border border-gray-200 w-full rounded-md shadow-xl max-h-48 overflow-y-auto hidden top-full mt-1"></ul>
                            </div>
                        </div>
                        <div class="form-control w-full">
                            <label class="label"><span class="label-text font-bold text-blue-900">Marca</span></label>
                            <input type="text" name="new_vehiculo[marca]" id="inputMarca" list="listMarcas" class="input input-sm input-bordered border-yellow-400 w-full uppercase focus:ring-2 focus:ring-blue-900 hover:shadow-md hover:-translate-y-0.5 transition-all duration-300"
                                value="{{ isset($vehiculo) ? $vehiculo->marca->nombre : '' }}"
                                placeholder="TOYOTA..." required autocomplete="off" oninput="this.value = this.value.toUpperCase()">
                            <datalist id="listMarcas"></datalist>
                        </div>
                        <div class="form-control w-full">
                            <label class="label"><span class="label-text font-bold text-blue-900">Modelo</span></label>
                            <input type="text" name="new_vehiculo[modelo]" id="inputModelo" list="listModelos" class="input input-sm input-bordered border-yellow-400 w-full uppercase focus:ring-2 focus:ring-blue-900 hover:shadow-md hover:-translate-y-0.5 transition-all duration-300"
                                value="{{ isset($vehiculo) ? $vehiculo->modelo->nombre : '' }}"
                                placeholder="COROLLA..." required autocomplete="off" oninput="this.value = this.value.toUpperCase()">
                            <datalist id="listModelos"></datalist>
                        </div>
                        <div class="form-control w-full">
                            <label class="label"><span class="label-text font-bold text-blue-900">Año</span></label>
                            <input type="number" name="new_vehiculo[anio]" class="input input-sm input-bordered border-yellow-400 w-full focus:ring-2 focus:ring-blue-900 hover:shadow-md hover:-translate-y-0.5 transition-all duration-300"
                                value="{{ isset($vehiculo) ? $vehiculo->anio : '' }}" placeholder="2020" required>
                        </div>
                        <div class="form-control w-full">
                            <label class="label"><span class="label-text font-bold text-blue-900">Color</span></label>
                            <input type="text" name="color" class="input input-sm input-bordered border-yellow-400 w-full focus:ring-2 focus:ring-blue-900 hover:shadow-md hover:-translate-y-0.5 transition-all duration-300"
                                value="{{ isset($vehiculo) ? $vehiculo->color : '' }}" placeholder="Gris, Rojo..." required>
                        </div>
                        <div class="form-control w-full">
                            <label class="label"><span class="label-text font-bold text-blue-900">Versión (Opcional)</span></label>
                            <input type="text" name="new_vehiculo[version]" id="inputVersion" list="listVersiones" class="input input-sm input-bordered border-yellow-400 w-full uppercase focus:ring-2 focus:ring-blue-900 hover:shadow-md hover:-translate-y-0.5 transition-all duration-300"
                                value="{{ isset($vehiculo) && $vehiculo->version ? $vehiculo->version->nombre : '' }}"
                                placeholder="LE, XLE..." autocomplete="off" oninput="this.value = this.value.toUpperCase()">
                            <datalist id="listVersiones"></datalist>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Columna Derecha: Recepción y Combustible -->
        <div class="lg:col-span-4 space-y-6">

            <!-- Datos de Ingreso -->
            <div class="card bg-white shadow-lg shadow-blue-900/10 border border-blue-900/20">
                <div class="card-body p-5">
                    <h2 class="card-title text-sm font-bold text-gray-500 border-b pb-2 mb-4">
                        <i class="fas fa-clock text-blue-900"></i> DETALLES DE RECEPCIÓN
                    </h2>
                    <div class="grid grid-cols-2 gap-4">
                        <div class="form-control">
                            <label class="label"><span class="label-text text-xs text-blue-900">FECHA</span></label>
                            <div class="font-mono text-sm font-bold bg-blue-50/50 p-2 rounded border border-blue-100 text-blue-900">{{ now()->format('Y-m-d') }}</div>
                        </div>
                        <div class="form-control">
                            <label class="label"><span class="label-text text-xs text-blue-900">HORA</span></label>
                            <div class="font-mono text-sm font-bold bg-blue-50/50 p-2 rounded border border-blue-100 text-blue-900">{{ now()->format('H:i') }}</div>
                        </div>
                    </div>
                    <div class="form-control mt-3">
                        <label class="label"><span class="label-text font-bold text-blue-900">Kilometraje Actual</span></label>
                        <div class="relative">
                            <input type="number" name="kilometraje" class="input input-bordered border-yellow-400 w-full font-mono font-bold text-lg text-right pr-8 focus:ring-2 focus:ring-blue-900 hover:shadow-md hover:-translate-y-0.5 transition-all duration-300" required placeholder="0">
                            <span class="absolute right-3 top-3 text-xs font-bold text-gray-400">KM</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Nivel Combustible (Nuevo Diseño) -->
            <div class="card bg-white shadow-lg shadow-blue-900/10 border border-blue-900/20">
                <div class="card-body p-5 flex flex-col items-center">
                    <h2 class="card-title text-sm font-bold text-gray-500 w-full border-b pb-2 mb-4 text-center">
                        <i class="fas fa-gas-pump text-blue-900"></i> NIVEL DE COMBUSTIBLE
                    </h2>

                    <div class="w-full max-w-[260px]">
                        <!-- Labels -->
                        <div class="flex justify-between text-[10px] font-bold text-gray-400 mb-1 px-1">
                            <span>E</span>
                            <span>1/4</span>
                            <span>1/2</span>
                            <span>3/4</span>
                            <span>F</span>
                        </div>

                        <!-- Segmented Bar Bar -->
                        <div class="relative w-full h-8 bg-gray-100 rounded-lg overflow-hidden flex border border-gray-300 shadow-inner">
                            <div id="fuel-seg-1" class="h-full flex-1 border-r border-white/50 bg-gray-200 transition-all duration-300"></div>
                            <div id="fuel-seg-2" class="h-full flex-1 border-r border-white/50 bg-gray-200 transition-all duration-300"></div>
                            <div id="fuel-seg-3" class="h-full flex-1 border-r border-white/50 bg-gray-200 transition-all duration-300"></div>
                            <div id="fuel-seg-4" class="h-full flex-1 border-r border-white/50 bg-gray-200 transition-all duration-300"></div>
                            <div id="fuel-seg-5" class="h-full flex-1 bg-gray-200 transition-all duration-300"></div>
                        </div>

                        <!-- Range Input -->
                        <!-- Using accent-blue-900 to try and force navy color, or text-blue-900 -->
                        <input type="range" name="nivel_combustible_val" id="fuelRange" min="0" max="100" value="50" step="1"
                            class="range range-xs range-primary mt-4 w-full text-blue-900" />

                        <input type="hidden" name="nivel_combustible" id="fuelInput" value="1/2">

                        <div class="text-center mt-2">
                            <p class="font-extrabold text-xl text-blue-900" id="fuelLabel">1/2 Tanque</p>
                        </div>
                    </div>
                </div>
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
    <div class="bg-white p-6 rounded-xl shadow-sm border border-blue-100/50">
        <h4 class="font-bold text-gray-700 border-b pb-2 mb-4 flex items-center justify-between">
            <span><i class="fas fa-camera text-blue-900 mr-2"></i>FOTOS DE RECEPCIÓN</span>
            <span class="text-xs font-normal text-gray-500 bg-gray-100 px-2 py-1 rounded">Mínimo sugerido: 4 fotos</span>
        </h4>

        <!-- Controls -->
        <div class="flex flex-col sm:flex-row gap-4 items-start sm:items-center mb-6 bg-blue-50/30 p-4 rounded-lg border border-blue-100/50">
            <div class="flex gap-2 w-full sm:w-auto">
                <button type="button" id="btnCamera" class="btn btn-sm gap-2 bg-blue-900 border-blue-900 hover:bg-blue-800 text-white shadow-md hover:shadow-lg transition-all flex-1 sm:flex-none">
                    <i class="fas fa-camera"></i> TOMAR FOTO
                </button>
                <button type="button" id="btnGallery" class="btn btn-sm gap-2 btn-outline border-blue-900 text-blue-900 hover:bg-blue-900 hover:text-white shadow-sm hover:shadow-md transition-all flex-1 sm:flex-none">
                    <i class="fas fa-images"></i> GALERÍA
                </button>
            </div>

            <!-- Hidden Inputs -->
            <input type="file" id="inputCamera" accept="image/*" capture="environment" class="hidden">
            <input type="file" id="inputGallery" accept="image/*" multiple class="hidden">

            <div class="text-xs text-gray-600 flex flex-col mt-2 sm:mt-0">
                <span class="font-bold"><i class="fas fa-info-circle text-blue-600"></i> Tip:</span>
                <span>Use "Tomar Foto" para activar la cámara o "Galería" para seleccionar varias imágenes.</span>
            </div>
        </div>

        <!-- Grid -->
        <div id="previewFotosGrid" class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5 gap-4 min-h-[120px]">
            <!-- Cards injected via JS -->
            <div id="emptyPhotosMsg" class="col-span-full flex flex-col items-center justify-center text-gray-400 border-2 border-dashed border-gray-200 rounded-xl py-8 bg-gray-50/50">
                <div class="text-4xl mb-2 text-gray-300"><i class="fas fa-images"></i></div>
                <p class="text-sm">No hay fotos seleccionadas</p>
                <p class="text-xs">Haga clic en "Agregar Foto" para comenzar</p>
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
    <div class="mt-8 bg-blue-50/50 border border-blue-100 p-4 rounded-xl shadow-sm flex justify-between items-center gap-4">
        <div>
            <button type="button" id="btnLimpiar" class="btn btn-ghost text-gray-500 hover:text-red-500 font-normal btn-sm">
                <i class="fas fa-eraser mr-2"></i> Limpiar Formulario
            </button>
        </div>
        <div class="flex gap-4">
            <a href="{{ route('panel.operaciones.ordenes_trabajo.index') }}" class="btn bg-white hover:bg-gray-50 text-gray-600 font-bold px-6 rounded-xl border-gray-200">
                Cancelar
            </a>
            <button type="submit" class="btn bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-700 hover:to-indigo-700 text-white font-bold px-8 rounded-xl shadow-lg transition-all transform hover:scale-105">
                <i class="fas fa-check-circle mr-2"></i> Generar Orden
            </button>
        </div>
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