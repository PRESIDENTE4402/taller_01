@extends('layouts.panel')

@section('title', 'Gestión de Sucursales')
@section('subtitle', 'Administración de talleres y puntos de servicio.')

@section('content')

@push('styles')
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin=""/>
    <style>
        #mapPicker { z-index: 1; isolation: isolate; } /* Asegurar que se muestre en el modal y no solape Navbar u otros modals */
        .leaflet-container { z-index: 1 !important; }
        .leaflet-pane { z-index: 1 !important; }
        .leaflet-top, .leaflet-bottom { z-index: 10 !important; }
    </style>
@endpush

    {{-- Contenedor Principal con Efecto Tech --}}
    <div class="max-w-5xl mx-auto relative">

        {{-- Decoración de Fondo (Glow sutil Oscuro) --}}
        <div class="absolute -top-10 -right-10 w-64 h-64 bg-slate-900/10 rounded-full blur-3xl pointer-events-none"></div>
        <div class="absolute -bottom-10 -left-10 w-64 h-64 bg-blue-900/10 rounded-full blur-3xl pointer-events-none"></div>

        {{-- Header de Acciones --}}
        <div class="flex flex-col sm:flex-row justify-between items-center gap-6 mb-8 relative z-10">

            {{-- Búsqueda Futurista --}}
            <div class="relative w-full sm:w-80 group">
                <div
                    class="absolute -inset-0.5 bg-gradient-to-r from-blue-300 to-cyan-300 rounded-lg blur opacity-30 group-hover:opacity-75 transition duration-500">
                </div>
                <div class="relative flex items-center bg-white rounded-lg">
                    <i class="fas fa-search absolute left-4 text-gray-400 group-hover:text-blue-500 transition-colors"></i>
                    <input type="text" id="searchInput" placeholder="Buscar sucursal..."
                        class="w-full py-3 pl-12 pr-4 bg-transparent border-none focus:ring-0 text-gray-700 placeholder-gray-400 font-medium rounded-lg"
                        style="outline: none;">
                </div>
            </div>

            {{-- Botón Nueva Sucursal (Efecto Neon/Tech) --}}
            <button onclick="openModal()"
                class="relative inline-flex items-center justify-center px-8 py-3 overflow-hidden font-bold text-white transition-all duration-300 bg-gray-900 rounded-lg group hover:scale-105 shadow-lg hover:shadow-cyan-500/50">
                <span
                    class="absolute top-0 right-0 inline-block w-4 h-4 transition-all duration-500 ease-in-out bg-cyan-500 rounded group-hover:-mr-4 group-hover:-mt-4">
                    <span class="absolute top-0 right-0 w-5 h-5 rotate-45 translate-x-1/2 -translate-y-1/2 bg-white"></span>
                </span>
                <span
                    class="absolute bottom-0 rotate-180 left-0 inline-block w-4 h-4 transition-all duration-500 ease-in-out bg-blue-600 rounded group-hover:-ml-4 group-hover:-mb-4">
                    <span class="absolute top-0 right-0 w-5 h-5 rotate-45 translate-x-1/2 -translate-y-1/2 bg-white"></span>
                </span>
                <span
                    class="absolute bottom-0 left-0 w-full h-full transition-all duration-500 ease-in-out delay-200 -translate-x-full bg-gradient-to-r from-blue-600 to-cyan-500 rounded-lg group-hover:translate-x-0"></span>
                <span class="relative w-full text-left flex items-center gap-2">
                    <i class="fas fa-plus"></i> Nueva Sucursal
                </span>
            </button>
        </div>

        {{-- Contenedor de Grid de Tarjetas --}}
        <div id="sucursalesGrid" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-2 gap-6 relative">
            {{-- Las tarjetas serán inyectadas aquí por JS --}}
        </div>

        {{-- Empty State --}}
        <div id="emptyState" class="hidden flex-col items-center justify-center py-16 text-center">
            <div class="p-6 rounded-full mb-4">
                <i class="fas fa-building text-slate-300 text-4xl"></i>
            </div>
            <h3 class="text-slate-800 font-bold text-lg">No hay sucursales registradas</h3>
            <p class="text-slate-500 text-sm mt-2 max-w-xs mx-auto">Comienza agregando tu primera sucursal o taller de
                servicio.</p>
        </div>
    </div>

    {{-- Modal Tech --}}
    <div id="sucursalModal" class="fixed inset-0 z-50 hidden" aria-labelledby="modal-title" role="dialog" aria-modal="true">
        {{-- Backdrop con Blur INTENSO --}}
        <div class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm transition-opacity opacity-0" id="modalBackdrop"></div>

        <div class="fixed inset-0 z-10 w-screen overflow-y-auto">
            <div class="flex min-h-full items-end justify-center p-4 text-center sm:items-center sm:p-0">

                {{-- Panel del Modal --}}
                <div class="relative transform overflow-hidden rounded-2xl bg-white text-left shadow-2xl transition-all sm:my-8 sm:w-full sm:max-w-4xl opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95 border border-white/20"
                    id="modalPanel">

                    {{-- Barra Superior Decorativa --}}
                    <div class="absolute top-0 left-0 w-full h-1.5 bg-gradient-to-r from-blue-600 to-cyan-400"></div>

                    <div class="px-8 pt-8 pb-6">
                        <div class="text-center sm:text-left">
                            <h3 class="text-xl font-bold leading-6 text-gray-900 flex items-center gap-2" id="modalTitle">
                                <i class="fas fa-store text-cyan-500"></i>
                                <span>Registro de Sucursal</span>
                            </h3>
                            <p class="text-sm text-gray-500 mt-1">Ingresa los datos del taller o sucursal.</p>

                            <div class="mt-6">
                                <form id="sucursalForm" onsubmit="saveSucursal(event)">
                                    <input type="hidden" id="sucursalId">

                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-x-8 gap-y-2">
                                        {{-- Columna Izquierda: Datos --}}
                                        <div>
                                            {{-- Nombre --}}
                                            <div class="group relative z-0 w-full mb-6">
                                                <input type="text" id="nombreSucursal" name="nombre"
                                                    class="block py-2.5 px-0 w-full text-sm text-gray-900 bg-transparent border-0 border-b-2 border-gray-300 appearance-none focus:outline-none focus:ring-0 focus:border-blue-600 peer"
                                                    placeholder=" " required />
                                                <label for="nombreSucursal"
                                                    class="peer-focus:font-medium absolute text-sm text-gray-500 duration-300 transform -translate-y-6 scale-75 top-3 -z-10 origin-[0] peer-focus:left-0 peer-focus:text-blue-600 peer-placeholder-shown:scale-100 peer-placeholder-shown:translate-y-0 peer-focus:scale-75 peer-focus:-translate-y-6">
                                                    Nombre Sucursal (Ej. Taller Norte)
                                                </label>
                                                <span class="text-xs text-red-500 mt-1 hidden font-medium" id="errorNombre"></span>
                                            </div>

                                            {{-- Dirección --}}
                                            <div class="group relative z-0 w-full mb-6">
                                                <input type="text" id="direccionSucursal" name="direccion"
                                                    class="block py-2.5 px-0 w-full text-sm text-gray-900 bg-transparent border-0 border-b-2 border-gray-300 appearance-none focus:outline-none focus:ring-0 focus:border-blue-600 peer"
                                                    placeholder=" " required />
                                                <label for="direccionSucursal"
                                                    class="peer-focus:font-medium absolute text-sm text-gray-500 duration-300 transform -translate-y-6 scale-75 top-3 -z-10 origin-[0] peer-focus:left-0 peer-focus:text-blue-600 peer-placeholder-shown:scale-100 peer-placeholder-shown:translate-y-0 peer-focus:scale-75 peer-focus:-translate-y-6">
                                                    Dirección Física
                                                </label>
                                                <span class="text-xs text-red-500 mt-1 hidden font-medium" id="errorDireccion"></span>
                                            </div>

                                            {{-- Teléfono y Capacidad (una misma fila en desktop si hubiese espacio, pero están apilados ok) --}}
                                            <div class="grid grid-cols-2 gap-4">
                                                <div class="group relative z-0 w-full mb-6">
                                                    <input type="text" id="telefonoSucursal" name="telefono"
                                                        class="block py-2.5 px-0 w-full text-sm text-gray-900 bg-transparent border-0 border-b-2 border-gray-300 appearance-none focus:outline-none focus:ring-0 focus:border-blue-600 peer"
                                                        placeholder=" " required />
                                                    <label for="telefonoSucursal"
                                                        class="peer-focus:font-medium absolute text-sm text-gray-500 duration-300 transform -translate-y-6 scale-75 top-3 -z-10 origin-[0] peer-focus:left-0 peer-focus:text-blue-600 peer-placeholder-shown:scale-100 peer-placeholder-shown:translate-y-0 peer-focus:scale-75 peer-focus:-translate-y-6">
                                                        Teléfono
                                                    </label>
                                                    <span class="text-xs text-red-500 mt-1 hidden font-medium" id="errorTelefono"></span>
                                                </div>

                                                <div class="group relative z-0 w-full mb-6">
                                                    <input type="number" id="capacidadSucursal" name="capacidad_bahias" min="1"
                                                        class="block py-2.5 px-0 w-full text-sm text-gray-900 bg-transparent border-0 border-b-2 border-gray-300 appearance-none focus:outline-none focus:ring-0 focus:border-blue-600 peer"
                                                        placeholder=" " required />
                                                    <label for="capacidadSucursal"
                                                        class="peer-focus:font-medium absolute text-sm text-gray-500 duration-300 transform -translate-y-6 scale-75 top-3 -z-10 origin-[0] peer-focus:left-0 peer-focus:text-blue-600 peer-placeholder-shown:scale-100 peer-placeholder-shown:translate-y-0 peer-focus:scale-75 peer-focus:-translate-y-6">
                                                        Bahías
                                                    </label>
                                                    <span class="text-xs text-red-500 mt-1 hidden font-medium" id="errorCapacidad"></span>
                                                </div>
                                            </div>

                                            {{-- Ciudad --}}
                                            <div class="group relative z-0 w-full mb-6">
                                                <input type="text" id="ciudadSucursal" name="ciudad"
                                                    class="block py-2.5 px-0 w-full text-sm text-gray-900 bg-transparent border-0 border-b-2 border-gray-300 appearance-none focus:outline-none focus:ring-0 focus:border-blue-600 peer"
                                                    placeholder=" " />
                                                <label for="ciudadSucursal"
                                                    class="peer-focus:font-medium absolute text-sm text-gray-500 duration-300 transform -translate-y-6 scale-75 top-3 -z-10 origin-[0] peer-focus:left-0 peer-focus:text-blue-600 peer-placeholder-shown:scale-100 peer-placeholder-shown:translate-y-0 peer-focus:scale-75 peer-focus:-translate-y-6">
                                                    Ciudad
                                                </label>
                                            </div>

                                            {{-- Activa --}}
                                            <div class="flex items-center gap-3 mb-6">
                                                <input type="checkbox" id="activaSucursal" name="activa" checked
                                                    class="w-4 h-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500">
                                                <label for="activaSucursal" class="text-sm text-gray-700">Sucursal activa (visible en el mapa público)</label>
                                            </div>
                                        </div>

                                        {{-- Columna Derecha: Mapa --}}
                                        <div>
                                            {{-- Buscar por Dirección --}}
                                            <div class="mb-4">
                                                <div class="relative">
                                                    <input type="text" id="buscarDireccionMapa" class="block w-full rounded-lg border-0 py-2.5 pl-4 pr-12 text-sm text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-blue-600 transition-all bg-gray-50" placeholder="Ej. Zona 10, Ciudad de Guatemala..." onkeydown="if(event.key === 'Enter'){ event.preventDefault(); searchAddress(); }">
                                                    <button type="button" onclick="searchAddress()" class="absolute inset-y-0 right-0 flex items-center justify-center w-10 text-gray-400 hover:text-white hover:bg-blue-600 rounded-r-lg transition-colors border-l border-gray-300 hover:border-blue-600">
                                                        <i class="fas fa-search"></i>
                                                    </button>
                                                </div>
                                                <span id="searchResultFeedback" class="text-xs text-gray-500 mt-1 hidden"></span>
                                            </div>

                                            {{-- Coordenadas --}}
                                            <div class="mb-4">
                                                <p class="text-xs text-gray-500 mb-2"><i
                                                        class="fas fa-map-pin mr-1 text-blue-500"></i> Haz clic en el mapa para
                                                    fijar la ubicación manualmente:</p>
                                                <div id="mapPicker" class="relative w-full rounded-xl overflow-hidden shadow-sm hover:shadow-md transition-shadow"
                                                    style="height:320px; border:1px solid #cbd5e1; z-index:1;">
                                                </div>
                                            </div>

                                            <div class="grid grid-cols-2 gap-4">
                                                <div class="group relative z-0 w-full mb-4">
                                                    <input type="number" step="any" id="latitudSucursal" name="latitud"
                                                        class="block py-2.5 px-0 w-full text-sm text-gray-900 bg-transparent border-0 border-b-2 border-gray-300 appearance-none focus:outline-none focus:ring-0 focus:border-blue-600 peer"
                                                        placeholder=" " />
                                                    <label for="latitudSucursal"
                                                        class="peer-focus:font-medium absolute text-sm text-gray-500 duration-300 transform -translate-y-6 scale-75 top-3 -z-10 origin-[0] peer-focus:left-0 peer-focus:text-blue-600 peer-placeholder-shown:scale-100 peer-placeholder-shown:translate-y-0 peer-focus:scale-75 peer-focus:-translate-y-6">
                                                        Latitud
                                                    </label>
                                                </div>
                                                <div class="group relative z-0 w-full mb-4">
                                                    <input type="number" step="any" id="longitudSucursal" name="longitud"
                                                        class="block py-2.5 px-0 w-full text-sm text-gray-900 bg-transparent border-0 border-b-2 border-gray-300 appearance-none focus:outline-none focus:ring-0 focus:border-blue-600 peer"
                                                        placeholder=" " />
                                                    <label for="longitudSucursal"
                                                        class="peer-focus:font-medium absolute text-sm text-gray-500 duration-300 transform -translate-y-6 scale-75 top-3 -z-10 origin-[0] peer-focus:left-0 peer-focus:text-blue-600 peer-placeholder-shown:scale-100 peer-placeholder-shown:translate-y-0 peer-focus:scale-75 peer-focus:-translate-y-6">
                                                        Longitud
                                                    </label>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>

                    <div class="bg-gray-50/80 px-8 py-4 sm:flex sm:flex-row-reverse gap-3 border-t border-gray-100">
                        <button type="button"
                            onclick="document.getElementById('sucursalForm').dispatchEvent(new Event('submit', {cancelable: true, bubbles: true}))"
                            class="inline-flex w-full justify-center rounded-lg bg-gray-900 px-5 py-2.5 text-sm font-bold text-white shadow-lg hover:shadow-cyan-500/30 hover:bg-black transition-all sm:w-auto">
                            Guardar Sucursal
                        </button>
                        <button type="button" onclick="closeModal()"
                            class="mt-3 inline-flex w-full justify-center rounded-lg bg-white px-5 py-2.5 text-sm font-bold text-gray-700 shadow-sm ring-1 ring-inset ring-gray-200 hover:bg-gray-50 sm:mt-0 sm:w-auto">
                            Cancelar
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Scripts --}}
    <script>
        const API_URL = "{{ route('panel.mantenimientos.sucursales.index') }}";
        const CSRF_TOKEN = "{{ csrf_token() }}";
    </script>
@endsection


@push('scripts')
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>
    @vite(['resources/js/panel/sucursales.js'])
@endpush