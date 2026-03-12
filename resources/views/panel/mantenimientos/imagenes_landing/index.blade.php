@extends('layouts.panel')

@section('title', 'Gestión de Imágenes Landing')
@section('subtitle', 'Administra el contenido visual de la página de bienvenida')

@section('content')
    <div class="max-w-7xl mx-auto relative px-4 sm:px-6 lg:px-8">

        {{-- DecoraciÃ³n de Fondo (Glow sutil Oscuro) --}}
        <div class="absolute -top-10 -right-10 w-64 h-64 bg-slate-900/10 rounded-full blur-3xl pointer-events-none"></div>
        <div class="absolute -bottom-10 -left-10 w-64 h-64 bg-blue-900/10 rounded-full blur-3xl pointer-events-none"></div>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Prompt:ital,wght@0,300;0,400;0,500;0,600;0,700;1,400&display=swap" rel="stylesheet">
    <link href="https://cdn.quilljs.com/1.3.6/quill.snow.css" rel="stylesheet">

        {{-- Header de Acciones --}}
        <div class="flex flex-col sm:flex-row justify-between items-center gap-6 mb-8 relative z-10">
            <div class="flex flex-col sm:flex-row gap-4 w-full sm:w-auto">
                {{-- Filtro Tipo --}}
                <div class="relative w-full sm:w-48 group">
                    <div
                        class="absolute -inset-0.5 bg-gradient-to-r from-blue-300 to-cyan-300 rounded-lg blur opacity-30 group-hover:opacity-75 transition duration-500">
                    </div>
                    <select id="filterType"
                        class="relative w-full py-3 px-4 bg-white border-none focus:ring-0 text-gray-700 font-medium rounded-lg shadow-sm"
                        style="outline: none;">
                        <option value="">Todos los tipos</option>
                        <option value="logo">Logo Empresarial</option>
                        <option value="service">Imagen de Servicio</option>
                        <option value="gallery">Galería de Trabajos</option>
                        <option value="video">Video de Éxito</option>
                        <option value="about">Imagen Acerca De</option>
                        <option value="solution">Sección Soluciones</option>
                        <option value="client">Sección Clientes</option>
                        <option value="contact">Sección Contacto</option>
                    </select>
                </div>

                {{-- Filtro Estado --}}
                <div class="relative w-full sm:w-48 group">
                    <div
                        class="absolute -inset-0.5 bg-gradient-to-r from-blue-300 to-cyan-300 rounded-lg blur opacity-30 group-hover:opacity-75 transition duration-500">
                    </div>
                    <select id="filterActive"
                        class="relative w-full py-3 px-4 bg-white border-none focus:ring-0 text-gray-700 font-medium rounded-lg shadow-sm"
                        style="outline: none;">
                        <option value="">Todos los estados</option>
                        <option value="1">Activos</option>
                        <option value="0">Inactivos</option>
                    </select>
                </div>
            </div>

            {{-- Botón Nueva Imagen --}}
            <button type="button" onclick="openNewImageModal()"
                class="relative inline-flex items-center justify-center px-8 py-3 overflow-hidden font-bold text-white transition-all duration-300 bg-gray-900 rounded-lg group hover:scale-105 shadow-lg hover:shadow-cyan-500/50 w-full sm:w-auto">
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
                <span class="relative w-full text-left flex items-center justify-center gap-2">
                    <i class="fas fa-plus"></i> Agregar Imagen
                </span>
            </button>
        </div>

        {{-- Grid de Imágenes --}}
        <div id="imagesGrid" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6 relative z-10">
            <!-- Cargado por JavaScript -->
        </div>

        {{-- Empty State --}}
        <div id="emptyState" class="hidden flex-col items-center justify-center py-16 text-center">
            <div class="p-6 rounded-full mb-4">
                <i class="fas fa-images text-slate-300 text-6xl"></i>
            </div>
            <h3 class="text-slate-800 font-bold text-xl">Sin Imágenes</h3>
            <p class="text-slate-500 text-md mt-2 max-w-md mx-auto">No hay imÃ¡genes que coincidan con los filtros actuales
                o
                aún no has subido ninguna.</p>
        </div>

        {{-- Sección Sucursales / WhatsApp --}}
        <div class="mt-16 mb-8 relative z-10">
            <div class="flex items-center gap-4 mb-8">
                <div class="h-10 w-2 bg-gradient-to-b from-blue-600 to-cyan-400 rounded-full"></div>
                <div>
                    <h2 class="text-2xl font-bold text-slate-800">Configuración de WhatsApp</h2>
                    <p class="text-slate-500 text-sm">Administra los números de contacto para cada sucursal en la landing
                        page</p>
                </div>
            </div>

            <div id="branchesContainer" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mb-12">
                <!-- Cargado por JS -->
            </div>
        </div>

    </div>

    <!-- Modal Editar Teléfono Sucursal -->
    <div id="branchModal" class="fixed inset-0 z-[60] hidden" aria-labelledby="modal-title" role="dialog" aria-modal="true">
        <div class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm transition-opacity opacity-0" id="branchModalBackdrop">
        </div>
        <div class="fixed inset-0 z-10 w-screen overflow-y-auto">
            <div class="flex min-h-full items-end justify-center p-4 text-center sm:items-center sm:p-0">
                <div class="relative transform overflow-hidden rounded-2xl bg-white text-left shadow-2xl transition-all sm:my-8 sm:w-full sm:max-w-md opacity-0 translate-y-4"
                    id="branchModalPanel">
                    <div class="absolute top-0 left-0 w-full h-1.5 bg-emerald-500"></div>
                    <div class="bg-slate-900 px-6 py-4 flex justify-between items-center">
                        <h5 class="text-white font-bold text-lg flex items-center gap-2">
                            <i class="fab fa-whatsapp text-emerald-400"></i>
                            <span>Editar WhatsApp</span>
                        </h5>
                        <button type="button" class="text-slate-400 hover:text-white" onclick="closeBranchModal()">
                            <i class="fas fa-times text-xl"></i>
                        </button>
                    </div>
                    <div class="p-6">
                        <form id="branchForm">
                            <input type="hidden" id="editBranchId">
                            <div class="space-y-4">
                                <div>
                                    <label class="block text-sm font-semibold text-slate-700 mb-1">Sucursal</label>
                                    <input type="text" id="editBranchName"
                                        class="w-full bg-slate-100 rounded-lg border-none py-2 px-3 text-slate-500 font-medium"
                                        readonly>
                                </div>
                                <div>
                                    <label for="editBranchPhone"
                                        class="block text-sm font-semibold text-slate-700 mb-1">Número de WhatsApp <span
                                            class="text-red-500">*</span></label>
                                    <div class="relative">
                                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                            <i class="fas fa-phone text-slate-400"></i>
                                        </div>
                                        <input type="text" id="editBranchPhone"
                                            class="w-full rounded-lg border-slate-300 shadow-sm focus:border-emerald-500 focus:ring-emerald-500 py-2 pl-10 pr-3"
                                            required placeholder="Ej: 50212345678">
                                    </div>
                                    <p class="mt-2 text-xs text-slate-500italic">Incluye el código de país sin el signo +
                                        (Ej: 502 para Guatemala)</p>
                                </div>
                            </div>
                        </form>
                    </div>
                    <div class="bg-slate-50 border-t border-slate-200 py-4 px-6 flex justify-end gap-3 rounded-b-2xl">
                        <button type="button"
                            class="px-4 py-2 text-sm font-semibold text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50"
                            onclick="closeBranchModal()">
                            Cancelar
                        </button>
                        <button type="button"
                            class="px-4 py-2 text-sm font-semibold text-white bg-emerald-600 rounded-lg hover:bg-emerald-700 transition-all flex items-center gap-2"
                            onclick="saveBranchPhone()">
                            <i class="fas fa-check"></i> Actualizar
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Tailwind: Crear/Editar Imagen -->
    <div id="formModal" class="fixed inset-0 z-50 hidden" aria-labelledby="modal-title" role="dialog" aria-modal="true">
        <!-- Backdrop -->
        <div class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm transition-opacity opacity-0" id="modalBackdrop"></div>

        <div class="fixed inset-0 z-10 w-screen overflow-y-auto">
            <div class="flex min-h-full items-end justify-center p-4 text-center sm:items-center sm:p-0">

                <!-- Panel -->
                <div class="relative transform overflow-hidden rounded-2xl bg-white text-left shadow-2xl transition-all sm:my-8 sm:w-full sm:max-w-3xl opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                    id="modalPanel">

                    <div class="absolute top-0 left-0 w-full h-1.5 bg-gradient-to-r from-blue-600 to-cyan-400"></div>

                    <div class="bg-gradient-to-r from-slate-900 to-slate-800 px-6 py-4 flex justify-between items-center">
                        <h5 class="text-white font-bold text-lg flex items-center gap-2">
                            <i class="fas fa-image text-cyan-400"></i>
                            <span id="formTitle">Agregar Nueva Imagen</span>
                        </h5>
                        <button type="button" class="text-slate-400 hover:text-white" onclick="closeModal()">
                            <i class="fas fa-times text-xl"></i>
                        </button>
                    </div>

                    <div class="p-6 bg-slate-50">
                        <form id="imageForm" class="space-y-5">
                            <input type="hidden" id="imageId" value="">

                            <!-- Cargar Imagen Area -->
                            <div class="bg-white p-5 rounded-xl border border-slate-200 shadow-sm">
                                <label class="block text-sm font-semibold text-slate-700 mb-2">Cargar Imagen</label>
                                <div id="dropZone"
                                    class="border-2 border-dashed border-slate-300 rounded-xl p-8 text-center bg-slate-50 hover:bg-slate-100 hover:border-blue-400 transition-all duration-200 cursor-pointer flex flex-col items-center justify-center min-h-[160px]">
                                    <i class="fas fa-cloud-upload-alt text-4xl text-blue-500 mb-3"></i>
                                    <p class="text-slate-600 font-medium mb-1">Arrastra la imagen aquí o haz clic para
                                        seleccionar</p>
                                    <span class="text-xs text-slate-400">Formatos: JPG, PNG, GIF, WebP | Máx: 5MB</span>
                                    <input type="file" id="fileInput" accept="image/*" class="hidden">
                                </div>

                                <!-- Preview de Imagen -->
                                <div id="previewContainer" class="mt-4 hidden text-center">
                                    <div class="relative inline-block mx-auto group rounded-xl overflow-hidden shadow-md">
                                        <img id="imagePreview" src="" alt="Preview"
                                            class="max-w-full h-48 object-contain bg-slate-100">
                                        <div
                                            class="absolute inset-0 bg-black/40 opacity-0 group-hover:opacity-100 transition-opacity duration-200 flex items-center justify-center">
                                            <button type="button" id="clearImageBtn"
                                                class="bg-red-500 hover:bg-red-600 text-white rounded-full w-10 h-10 flex items-center justify-center shadow-lg transform hover:scale-110 transition-transform">
                                                <i class="fas fa-trash-alt"></i>
                                            </button>
                                        </div>
                                    </div>
                                    <input type="hidden" id="cloudinaryPublicId" value="">
                                    <input type="hidden" id="cloudinaryUrl" value="">
                                    <div
                                        class="mt-3 flex items-center justify-center gap-2 text-sm font-medium text-emerald-600 bg-emerald-50 py-2 px-4 rounded-full w-fit mx-auto">
                                        <i class="fas fa-check-circle"></i>
                                        <span id="uploadStatus">Imagen lista</span>
                                    </div>
                                </div>
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                                <!-- Tipo -->
                                <div>
                                    <label for="imageType" class="block text-sm font-semibold text-slate-700 mb-1">Tipo de
                                        Imagen <span class="text-red-500">*</span></label>
                                    <select id="imageType"
                                        class="w-full rounded-lg border-slate-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 py-2 px-3"
                                        required>
                                        <option value="">Selecciona un tipo</option>
                                        <option value="logo">Logo Empresarial</option>
                                        <option value="service">Imagen de Servicio</option>
                                        <option value="gallery">Galería de Trabajos</option>
                                        <option value="video">Video de Éxito</option>
                                        <option value="about">Imagen Acerca De</option>
                                        <option value="solution">Sección Soluciones</option>
                                        <option value="client">Sección Clientes</option>
                                        <option value="contact">Sección Contacto</option>
                                    </select>
                                </div>
                                <!-- Título -->
                                <div>
                                    <label for="imageTitle" class="block text-sm font-semibold text-slate-700 mb-1">Título
                                        <span class="text-red-500">*</span></label>
                                    <input type="text" id="imageTitle"
                                        class="w-full rounded-lg border-slate-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 py-2 px-3"
                                        required placeholder="Ej: Logo Oficial 2026">
                                </div>
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                                <!-- Alt Text -->
                                <div>
                                    <label for="imageAlt" class="block text-sm font-semibold text-slate-700 mb-1">Texto
                                        Alternativo (SEO) <span class="text-red-500">*</span></label>
                                    <input type="text" id="imageAlt"
                                        class="w-full rounded-lg border-slate-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 py-2 px-3"
                                        required placeholder="Ej: Logo de Tecnimecanica California">
                                </div>
                                <!-- Orden -->
                                <div>
                                    <label for="imageOrder" class="block text-sm font-semibold text-slate-700 mb-1">Orden de
                                        Visualización</label>
                                    <input type="number" id="imageOrder"
                                        class="w-full rounded-lg border-slate-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 py-2 px-3"
                                        value="0" placeholder="0, 1, 2... (menor = primero)">
                                </div>
                            </div>

                            <!-- Descripción -->
                            <div>
                                <label for="imageDescription" class="block text-sm font-semibold text-slate-700 mb-1">Descripción / Contenido <span class="text-xs font-normal text-blue-500">(soporta colores, tamaños e íconos)</span></label>
                                <div id="quillEditor" style="height: 200px; background: white;" class="rounded-b-lg border-slate-300"></div>
                                <input type="hidden" id="imageDescription">
                            </div>

                            <!-- Estado -->
                            <div class="bg-white p-4 rounded-xl border border-slate-200 flex items-center justify-between">
                                <div>
                                    <span class="block text-sm font-semibold text-slate-700">Estado de Visibilidad</span>
                                    <span class="text-xs text-slate-500">¿Mostrar en la página de bienvenida?</span>
                                </div>
                                <div class="form-check form-switch cursor-pointer">
                                    <input type="checkbox" id="imageActive" class="form-check-input w-12 h-6 cursor-pointer"
                                        checked>
                                </div>
                            </div>
                        </form>
                    </div>

                    <div class="bg-slate-50 border-t border-slate-200 py-4 px-6 flex justify-end gap-3 rounded-b-2xl">
                        <button type="button"
                            class="px-5 py-2.5 text-sm font-semibold text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 shadow-sm transition-all"
                            onclick="closeModal()">
                            Cancelar
                        </button>
                        <button type="button"
                            class="px-5 py-2.5 text-sm font-semibold text-white bg-blue-600 rounded-lg hover:bg-blue-700 shadow-md shadow-blue-500/30 transition-all flex items-center gap-2"
                            id="saveBtn">
                            <i class="fas fa-save"></i> <span id="saveBtnText">Guardar Imagen</span>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

@endsection

@push('scripts')
    <script src="https://cdn.quilljs.com/1.3.6/quill.min.js"></script>
    <script>
        window.quillConfig = {
            theme: 'snow',
            placeholder: 'Escribe aquí el contenido enriquecido...',
            modules: {
                toolbar: [
                    [{ 'font': [] }, { 'size': ['small', false, 'large', 'huge'] }],
                    [{ 'color': [] }, { 'background': [] }],
                    ['bold', 'italic', 'underline', 'strike'],
                    [{ 'list': 'ordered'}, { 'list': 'bullet' }],
                    [{ 'align': [] }],
                    ['link', 'clean']
                ]
            }
        };
    </script>
    @vite(['resources/js/panel/imagenes_landing.js'])
@endpush