@extends('layouts.panel')

@section('title', 'Recepción de Vehículo')
@section('subtitle', 'Recepción de Vehículo')

@section('content')
<form action="{{ route('panel.operaciones.ordenes_trabajo.store') }}" method="POST" id="ordenForm" class="space-y-6" enctype="multipart/form-data">
    @csrf

    <!-- Header: Datos Generales (Card Similar a la Factura) -->
    <!-- Floating Header Actions (Top Sticky) -->
    <div class="sticky top-0 z-[60] -mx-4 sm:-mx-6 px-4 sm:px-6 py-3 bg-white/90 backdrop-blur-md border-b border-blue-100 shadow-sm flex flex-col sm:flex-row justify-between items-center gap-4 mb-6 transition-all duration-300">
        <div class="flex items-center gap-3 w-full sm:w-auto">
            <div class="hidden sm:flex h-10 w-10 bg-blue-900 rounded-lg items-center justify-center text-white shadow-lg">
                <i class="fas fa-file-invoice text-xl"></i>
            </div>
            <div>
                <h1 class="text-lg font-extrabold text-blue-900 leading-none">Nueva Orden</h1>
                <p class="text-[10px] text-gray-500 font-medium uppercase tracking-wider mt-1">Recepción de Vehículo</p>
            </div>
        </div>

        <div class="flex items-center justify-between sm:justify-end gap-2 w-full sm:w-auto">
            <button type="button" id="btnLimpiar" class="btn btn-ghost hover:bg-red-50 text-gray-400 hover:text-red-500 btn-xs sm:btn-sm flex gap-2">
                <i class="fas fa-eraser"></i> <span class="hidden lg:inline">Limpiar</span>
            </button>

            <div class="flex gap-2">
                <a href="{{ route('panel.operaciones.ordenes_trabajo.index') }}" class="btn btn-sm sm:btn-md bg-white hover:bg-gray-100 text-gray-500 font-bold px-4 sm:px-6 rounded-xl border-gray-200">
                    Cancelar
                </a>
                <button type="submit" class="btn btn-sm sm:btn-md bg-blue-900 hover:bg-blue-800 text-white font-bold px-6 sm:px-10 rounded-xl shadow-lg border-none transform active:scale-95 transition-all">
                    <i class="fas fa-save mr-2"></i> GENERAR ORDEN
                </button>
            </div>
        </div>
    </div>

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
        <div id="previewFotosGrid" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6 min-h-[200px]">
            <!-- Cards injected via JS -->
            <div id="emptyPhotosMsg" class="col-span-full flex flex-col items-center justify-center text-gray-400 border-2 border-dashed border-blue-100 rounded-xl py-16 bg-blue-50/20">
                <div class="text-6xl mb-4 text-blue-200"><i class="fas fa-images"></i></div>
                <p class="text-lg font-medium text-blue-900/40">No hay fotos seleccionadas</p>
                <p class="text-sm">Use los botones de arriba para capturar o subir imágenes</p>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 pb-12">
        <!-- Left: Inventory Detailed (8 cols) -->
        <div class="lg:col-span-8 space-y-6">
            <div class="bg-white p-6 rounded-2xl shadow-sm border border-blue-100 flex flex-col">
                <div class="flex items-center justify-between border-b border-gray-100 pb-4 mb-6">
                    <div>
                        <h3 class="font-black text-blue-900 flex items-center gap-2 tracking-tight">
                            <i class="fas fa-tasks text-blue-600"></i> INVENTARIO DE RECEPCIÓN
                        </h3>
                        <p class="text-[10px] text-gray-400 font-bold uppercase tracking-widest mt-0.5">Estado físico y accesorios del vehículo</p>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-x-10 gap-y-1">
                    <!-- Section 1 -->
                    <div class="space-y-1">
                        <div class="flex items-center gap-2 mb-3 bg-blue-50 py-1 px-3 rounded-lg w-fit">
                            <i class="fas fa-file-invoice text-blue-600 text-[10px]"></i>
                            <span class="text-[10px] font-black text-blue-900 uppercase">Documentos y Accesorios</span>
                        </div>

                        <!-- Documents Special -->
                        <div class="flex items-center justify-between py-2.5 px-3 border-b border-gray-50 hover:bg-gray-50 transition-colors group">
                            <span class="text-xs font-bold text-gray-600 group-hover:text-blue-900 transition-colors uppercase">Documentos</span>
                            <div class="flex gap-4">
                                <label class="flex items-center gap-2 cursor-pointer group/sub">
                                    <input type="checkbox" name="inv[documentos][original]" class="checkbox checkbox-xs rounded border-gray-300 checkbox-primary">
                                    <span class="text-[10px] font-black text-gray-400 group-hover/sub:text-blue-900 mt-0.5">ORIGINAL</span>
                                </label>
                                <label class="flex items-center gap-2 cursor-pointer group/sub">
                                    <input type="checkbox" name="inv[documentos][copia]" class="checkbox checkbox-xs rounded border-gray-300 checkbox-primary">
                                    <span class="text-[10px] font-black text-gray-400 group-hover/sub:text-blue-900 mt-0.5">COPIA</span>
                                </label>
                            </div>
                        </div>

                        @php
                        $items1 = [
                        'encendedor' => 'Encendedor',
                        'radio' => 'Radio / Frontal',
                        'llavero' => 'Llavero',
                        'control_alarma' => 'Control Alarma',
                        'bateria' => 'Batería',
                        'tricket' => 'Tricket (Gato)',
                        'barilla' => 'Barilla de Gato',
                        'llave_seguridad' => 'Llave Seguridad',
                        'llave_chuchos' => 'Llave de Chuchos',
                        ];
                        @endphp

                        @foreach($items1 as $key => $label)
                        <div class="flex items-center justify-between py-2 px-3 border-b border-gray-50 hover:bg-gray-50 rounded-lg transition-colors group">
                            <span class="text-xs font-bold text-gray-600 group-hover:text-blue-900 transition-colors uppercase">{{ $label }}</span>
                            <div class="flex gap-1 bg-gray-100 p-1 rounded-lg">
                                <label class="flex items-center justify-center p-1.5 cursor-pointer rounded-md transition-all has-[:checked]:bg-blue-900 has-[:checked]:text-white hover:bg-white border-none">
                                    <input type="radio" name="inv[{{$key}}]" value="1" class="hidden">
                                    <span class="text-[10px] font-black px-2">SÍ</span>
                                </label>
                                <label class="flex items-center justify-center p-1.5 cursor-pointer rounded-md transition-all has-[:checked]:bg-gray-400 has-[:checked]:text-white hover:bg-white border-none">
                                    <input type="radio" name="inv[{{$key}}]" value="0" class="hidden" checked>
                                    <span class="text-[10px] font-black px-2">NO</span>
                                </label>
                            </div>
                        </div>
                        @endforeach
                        <!-- Special Row: Tapicería (3 States) -->
                        <div class="flex items-center justify-between py-2.5 px-3 border-b border-gray-50 hover:bg-gray-50 rounded-lg transition-colors group">
                            <span class="text-xs font-bold text-gray-600 group-hover:text-blue-900 transition-colors uppercase">Estado Tapicería</span>
                            <div class="flex gap-1 bg-gray-100 p-1 rounded-lg">
                                <label class="flex items-center justify-center p-1.5 cursor-pointer rounded-md transition-all has-[:checked]:bg-emerald-600 has-[:checked]:text-white hover:bg-white border-none shadow-sm">
                                    <input type="radio" name="inv[tapiceria]" value="buena" class="hidden">
                                    <span class="text-[9px] font-black px-1">BUENA</span>
                                </label>
                                <label class="flex items-center justify-center p-1.5 cursor-pointer rounded-md transition-all has-[:checked]:bg-amber-500 has-[:checked]:text-white hover:bg-white border-none shadow-sm">
                                    <input type="radio" name="inv[tapiceria]" value="regular" class="hidden" checked>
                                    <span class="text-[9px] font-black px-1">REGULAR</span>
                                </label>
                                <label class="flex items-center justify-center p-1.5 cursor-pointer rounded-md transition-all has-[:checked]:bg-rose-600 has-[:checked]:text-white hover:bg-white border-none shadow-sm">
                                    <input type="radio" name="inv[tapiceria]" value="mala" class="hidden">
                                    <span class="text-[9px] font-black px-1">MALA</span>
                                </label>
                            </div>
                        </div>
                    </div>

                    <!-- Section 2 -->
                    <div class="space-y-1">
                        <div class="flex items-center gap-2 mb-3 bg-amber-50 py-1 px-3 rounded-lg w-fit">
                            <i class="fas fa-tools text-amber-600 text-[10px]"></i>
                            <span class="text-[10px] font-black text-amber-900 uppercase">Herramientas y Exterior</span>
                        </div>

                        @php
                        $items2 = [
                        'llanta_repuesto' => 'Llanta Repuesto',
                        'herramientas' => 'Estuche Herram.',
                        'extinguidor' => 'Extinguidor',
                        'cables' => 'Cables Corriente',
                        'antena' => 'Antena',
                        'tapon_tanque' => 'Tapón Tanque',
                        'chibola' => 'Chibola Palanca',
                        ];
                        $qtyItems = [
                        'tapones_ruedas' => 'Tapones Ruedas',
                        'chuchos' => 'Chuchos (Tuercas)',
                        'plumillas' => 'Plumillas',
                        'alfombras' => 'Alfombras',
                        'retrovisores' => 'Retrovisores',
                        'triangulos' => 'Triangulos',
                        ]
                        @endphp

                        @foreach($items2 as $key => $label)
                        <div class="flex items-center justify-between py-2 px-3 border-b border-gray-50 hover:bg-gray-50 rounded-lg transition-colors group">
                            <span class="text-xs font-bold text-gray-600 group-hover:text-blue-900 transition-colors uppercase">{{ $label }}</span>
                            <div class="flex gap-1 bg-gray-100 p-1 rounded-lg">
                                <label class="flex items-center justify-center p-1.5 cursor-pointer rounded-md transition-all has-[:checked]:bg-blue-900 has-[:checked]:text-white hover:bg-white border-none">
                                    <input type="radio" name="inv[{{$key}}]" value="1" class="hidden">
                                    <span class="text-[10px] font-black px-2 text-center">SÍ</span>
                                </label>
                                <label class="flex items-center justify-center p-1.5 cursor-pointer rounded-md transition-all has-[:checked]:bg-gray-400 has-[:checked]:text-white hover:bg-white border-none">
                                    <input type="radio" name="inv[{{$key}}]" value="0" class="hidden" checked>
                                    <span class="text-[10px] font-black px-2 text-center">NO</span>
                                </label>
                            </div>
                        </div>
                        @endforeach

                        @foreach($qtyItems as $key => $label)
                        <div class="flex items-center justify-between py-3 px-3 border-b border-gray-50 hover:bg-gray-50 rounded-lg transition-colors group">
                            <span class="text-xs font-bold text-gray-600 group-hover:text-amber-900 transition-colors uppercase">{{ $label }}</span>
                            <div class="flex items-center gap-3">
                                <label class="flex items-center gap-2 cursor-pointer">
                                    <input type="checkbox" name="inv[{{$key}}][check]" value="1" class="checkbox checkbox-xs rounded border-gray-300 checkbox-warning toggle-qty" data-target="qty-{{$key}}">
                                    <span class="text-[10px] font-black text-gray-400 mt-0.5">¿TRAE?</span>
                                </label>
                                <input type="number" id="qty-{{$key}}" name="inv[{{$key}}][cant]" class="input input-xs input-bordered w-12 text-center hidden font-black text-blue-900 border-amber-200 bg-amber-50" value="0">
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>

                <!-- Observations -->
                <div class="mt-8 bg-slate-50 p-5 rounded-2xl border border-dashed border-slate-200">
                    <label class="text-[10px] font-black text-slate-500 uppercase tracking-widest flex items-center gap-2 mb-3">
                        <i class="fas fa-comment-alt"></i> Observaciones de Inventario
                    </label>
                    <textarea name="inventario_observaciones" rows="2" class="w-full textarea textarea-sm bg-white border-slate-200 focus:border-blue-900 focus:outline-none text-gray-800 placeholder-slate-300 font-medium" placeholder="Escriba detalles como rayones en el tablero, tapicería manchada, etc."></textarea>
                </div>
            </div>

            <!-- Falla Section -->
            <div class="bg-gradient-to-br from-white to-blue-50/30 p-8 rounded-2xl shadow-sm border border-blue-100 flex flex-col gap-4">
                <div class="flex items-center gap-3">
                    <div class="h-10 w-10 bg-blue-900 rounded-xl flex items-center justify-center text-white shadow-xl">
                        <i class="fas fa-exclamation-triangle"></i>
                    </div>
                    <div>
                        <h3 class="font-black text-blue-900 leading-none">FALLA O MOTIVO DE INGRESO</h3>
                        <p class="text-[10px] text-gray-400 font-bold uppercase tracking-widest mt-1">Describa detalladamente el servicio solicitado</p>
                    </div>
                </div>
                <textarea name="falla_cliente" rows="4" class="w-full textarea textarea-bordered border-blue-200 focus:border-blue-900 text-lg font-bold text-blue-900 shadow-inner" placeholder="Escriba el motivo aquí..." required>{{ isset($cita) ? $cita->motivo_cita : '' }}</textarea>
            </div>
        </div>

        <!-- Right: Damage Report (4 cols) -->
        <div class="lg:col-span-4 space-y-4 lg:sticky lg:top-24 h-fit">
            <div class="bg-white p-6 rounded-2xl shadow-xl shadow-blue-900/5 border border-blue-100 flex flex-col">
                <div class="flex items-center justify-between mb-6">
                    <div>
                        <h3 class="font-black text-blue-900 tracking-tight leading-none uppercase">Reporte de Daños</h3>
                        <p class="text-[9px] text-gray-400 font-bold uppercase tracking-widest mt-1">Marque golpes o rayones (X)</p>
                    </div>
                    <div class="badge badge-error gap-1 text-white text-[9px] font-bold py-3 px-3">
                        <i class="fas fa-marker"></i> GOLPES
                    </div>
                </div>

                <!-- Damage Controls -->
                <div class="grid grid-cols-2 gap-2 mb-4">
                    <button type="button" id="btnDamageCamera" class="btn btn-sm bg-blue-900 hover:bg-blue-800 text-white border-none shadow-md gap-2">
                        <i class="fas fa-camera"></i> CAMARA
                    </button>
                    <label for="damageImageUpload" class="btn btn-sm btn-outline border-blue-900 text-blue-900 hover:bg-blue-900 hover:text-white gap-2">
                        <i class="fas fa-cloud-upload-alt"></i> SUBIR
                    </label>
                    <input type="file" id="damageImageUpload" accept="image/*" class="hidden" />
                </div>

                <div class="relative w-full aspect-[4/3] bg-slate-100 rounded-xl border border-dashed border-slate-300 overflow-hidden group mb-4" id="canvasContainer">
                    <!-- Overlay Instructions -->
                    <div id="canvasPlaceholder" class="absolute inset-0 flex flex-col items-center justify-center text-slate-400 group-hover:text-blue-600 transition-colors pointer-events-none">
                        <i class="fas fa-car-side text-5xl mb-3"></i>
                        <span class="text-[10px] font-black uppercase tracking-widest">Tome foto o suba una</span>
                    </div>

                    <canvas id="damageCanvas" class="absolute inset-0 w-full h-full cursor-crosshair z-10"></canvas>
                    <input type="hidden" name="danos_image" id="danosImageInput">
                </div>

                <div class="flex flex-col gap-2 relative z-20">
                    <div class="flex items-center justify-between">
                        <div class="flex gap-1">
                            <button type="button" id="undoMark" class="btn btn-xs btn-outline border-blue-900 text-blue-900 hover:bg-blue-900 hover:text-white gap-1 tooltip tooltip-top" data-tip="Deshacer último">
                                <i class="fas fa-undo"></i>
                            </button>
                            <button type="button" id="clearCanvas" class="btn btn-xs btn-ghost text-red-500 hover:bg-red-50 gap-2 font-black uppercase tracking-tighter">
                                <i class="fas fa-broom"></i> LIMPIAR
                            </button>
                        </div>
                        <div class="flex gap-1">
                            <button type="button" id="btnDeleteDamagePhoto" class="btn btn-xs btn-ghost text-red-600 gap-1 tooltip tooltip-top font-black uppercase tracking-tighter" data-tip="Quitar Foto Actual">
                                <i class="fas fa-times"></i>
                            </button>
                            <button type="button" id="addDamageToGallery" class="btn btn-xs bg-green-600 hover:bg-green-700 text-white border-none gap-1 font-black uppercase tracking-tighter shadow-lg">
                                <i class="fas fa-plus"></i> AGREGAR DAÑO
                            </button>
                        </div>
                    </div>
                    <div class="flex justify-between items-center mt-1">
                        <div class="text-[9px] font-bold text-gray-400 uppercase tracking-tighter italic">
                            <i class="fas fa-info-circle text-blue-600"></i> Clic para marcar daño
                        </div>
                    </div>
                </div>
            </div>

            <!-- Damage Gallery (Multiple Photos) -->
            <div class="mt-6 border-t border-gray-100 pt-6">
                <div class="flex items-center justify-between mb-4">
                    <h4 class="text-[10px] font-black text-gray-500 uppercase tracking-widest">Fotos de Daños Guardadas</h4>
                    <div id="damageCountBadge" class="badge badge-outline border-gray-300 text-[10px] font-black">0 FOTOS</div>
                </div>

                <div id="previewDanosGrid" class="grid grid-cols-2 gap-3 min-h-[80px]">
                    <!-- Damage cards will appear here -->
                    <div id="emptyDanosMsg" class="col-span-2 flex flex-col items-center justify-center p-6 bg-gray-50 rounded-xl border-2 border-dashed border-gray-100 opacity-60">
                        <i class="fas fa-images text-2xl text-gray-300 mb-2"></i>
                        <span class="text-[9px] font-black text-gray-400 uppercase tracking-widest">No hay fotos guardadas</span>
                    </div>
                </div>
            </div>
        </div>
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