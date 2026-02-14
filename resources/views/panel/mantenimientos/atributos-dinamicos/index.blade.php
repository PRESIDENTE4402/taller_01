@extends('layouts.panel')

@section('content')
<div class="container mx-auto px-4 py-8">
    <!-- Header -->
    <div class="flex justify-between items-center mb-8">
        <div>
            <h1 class="text-3xl font-bold text-slate-900">⚙️ Configuración de Atributos Dinámicos</h1>
            <p class="text-slate-600 mt-2">Define características personalizadas para cada categoría de productos</p>
        </div>
        <button class="px-6 py-3 bg-blue-600 text-white rounded-lg hover:bg-blue-700 font-bold"
                onclick="openNewAttributeModal()">
            + Nuevo Atributo
        </button>
    </div>

    <!-- Selector de Categoría -->
    <div class="bg-white rounded-lg shadow p-6 mb-8">
        <label class="block text-sm font-bold mb-2">Selecciona una Categoría</label>
        <select id="categoriaSelect" class="w-full px-4 py-3 border border-slate-300 rounded-lg"
                onchange="loadAttributes()">
            <option value="">-- Todas las categorías --</option>
        </select>
    </div>

    <!-- Lista de Atributos -->
    <div class="bg-white rounded-lg shadow overflow-hidden">
        <div class="px-6 py-4 bg-slate-50 border-b border-slate-200">
            <h2 class="text-xl font-bold text-slate-900">Atributos de la Categoría</h2>
        </div>

        <div id="attributesList" class="divide-y divide-slate-200">
            <div class="text-center py-8">
                <p class="text-slate-500">Selecciona una categoría para ver sus atributos</p>
            </div>
        </div>
    </div>
</div>

<!-- Modal para crear/editar atributo -->
<div id="attributeModal" class="hidden fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 overflow-y-auto">
    <div class="bg-white rounded-lg max-w-3xl w-full mx-4 my-8">
        <div class="px-6 py-4 bg-slate-50 border-b border-slate-200 flex justify-between items-center">
            <h2 class="text-xl font-bold" id="modalTitle">Nuevo Atributo</h2>
            <button class="text-2xl" onclick="closeAttributeModal()">&times;</button>
        </div>

        <div class="p-6 overflow-y-auto max-h-96">
            <form id="attributeForm" onsubmit="submitAttribute(event)">
                <input type="hidden" id="attributeId">

                <!-- Información básica -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
                    <div>
                        <label class="block text-sm font-bold mb-2">Nombre del Atributo</label>
                        <input type="text" id="attributeName" class="w-full px-4 py-2 border border-slate-300 rounded"
                               placeholder="viscosidad, rin, color" required>
                    </div>
                    <div>
                        <label class="block text-sm font-bold mb-2">Tipo de Dato</label>
                        <select id="dataType" class="w-full px-4 py-2 border border-slate-300 rounded" 
                                onchange="updateValidationFields()" required>
                            <option value="text">Texto</option>
                            <option value="number">Número</option>
                            <option value="date">Fecha</option>
                            <option value="select">Dropdown (Opciones)</option>
                            <option value="boolean">Sí/No</option>
                        </select>
                    </div>
                </div>

                <!-- Opciones (para select) -->
                <div id="optionsField" class="mb-6 hidden">
                    <label class="block text-sm font-bold mb-2">Opciones (separadas por coma)</label>
                    <textarea id="options" class="w-full px-4 py-2 border border-slate-300 rounded"
                              placeholder="Opción 1, Opción 2, Opción 3"></textarea>
                </div>

                <!-- Validación avanzada -->
                <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 mb-6">
                    <h3 class="font-bold mb-4">🔒 Validación Avanzada</h3>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <!-- Longitud -->
                        <div id="lengthFields" class="hidden">
                            <label class="block text-sm font-bold mb-2">Longitud Mínima</label>
                            <input type="number" id="minLength" class="w-full px-4 py-2 border border-slate-300 rounded" min="0">
                        </div>
                        <div id="lengthFieldsMax" class="hidden">
                            <label class="block text-sm font-bold mb-2">Longitud Máxima</label>
                            <input type="number" id="maxLength" class="w-full px-4 py-2 border border-slate-300 rounded" min="0">
                        </div>

                        <!-- Valores numéricos -->
                        <div id="minValueField" class="hidden">
                            <label class="block text-sm font-bold mb-2">Valor Mínimo</label>
                            <input type="number" id="minValue" step="0.01" class="w-full px-4 py-2 border border-slate-300 rounded">
                        </div>
                        <div id="maxValueField" class="hidden">
                            <label class="block text-sm font-bold mb-2">Valor Máximo</label>
                            <input type="number" id="maxValue" step="0.01" class="w-full px-4 py-2 border border-slate-300 rounded">
                        </div>

                        <!-- Regex -->
                        <div id="regexField" class="hidden md:col-span-2">
                            <label class="block text-sm font-bold mb-2">Patrón Regex (Validación)</label>
                            <input type="text" id="regexPattern" class="w-full px-4 py-2 border border-slate-300 rounded"
                                   placeholder="Ej: ^[A-Z0-9-]+$">
                        </div>
                    </div>
                </div>

                <!-- Configuración -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
                    <div>
                        <label class="block text-sm font-bold mb-2">Orden de Visualización</label>
                        <input type="number" id="displayOrder" class="w-full px-4 py-2 border border-slate-300 rounded" value="0">
                    </div>
                    <label class="flex items-center">
                        <input type="checkbox" id="isRequired" class="w-5 h-5 text-blue-600">
                        <span class="ml-2 text-sm font-bold">Requerido</span>
                    </label>
                    <label class="flex items-center">
                        <input type="checkbox" id="isActive" class="w-5 h-5 text-blue-600" checked>
                        <span class="ml-2 text-sm font-bold">Activo</span>
                    </label>
                </div>

                <!-- Texto de ayuda -->
                <div class="mb-6">
                    <label class="block text-sm font-bold mb-2">Texto de Ayuda</label>
                    <textarea id="helpText" class="w-full px-4 py-2 border border-slate-300 rounded"
                              placeholder="Ayuda para el usuario final"></textarea>
                </div>

                <!-- Traducciones -->
                <div class="bg-green-50 border border-green-200 rounded-lg p-4 mb-6">
                    <h3 class="font-bold mb-4">🌐 Traducciones (Multiidioma)</h3>
                    
                    <div id="translationsContainer">
                        <div class="translation-item mb-4 p-4 bg-white border border-green-200 rounded">
                            <select class="locale w-full px-4 py-2 border border-slate-300 rounded mb-2">
                                <option value="es">Español</option>
                                <option value="en">English</option>
                                <option value="fr">Français</option>
                                <option value="pt">Português</option>
                            </select>
                            <input type="text" placeholder="Etiqueta / Nombre" class="label w-full px-4 py-2 border border-slate-300 rounded mb-2">
                            <textarea placeholder="Descripción" class="description w-full px-4 py-2 border border-slate-300 rounded"></textarea>
                        </div>
                    </div>

                    <button type="button" onclick="addTranslation()" class="mt-2 px-4 py-2 bg-green-600 text-white rounded hover:bg-green-700">
                        + Agregar Idioma
                    </button>
                </div>

                <div class="flex gap-4">
                    <button type="submit" class="flex-1 px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700 font-bold">
                        ✅ Guardar Atributo
                    </button>
                    <button type="button" class="flex-1 px-4 py-2 bg-slate-300 text-slate-900 rounded hover:bg-slate-400"
                            onclick="closeAttributeModal()">
                        Cancelar
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@endsection

@push('scripts')
    @vite(['resources/js/panel/atributos-dinamicos.js'])
@endpush
