@extends('layouts.panel')

@section('title', 'Configurar Atributos Dinámicos')

@section('content')
<div class="container mx-auto px-4 py-6">
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        
        <!-- Panel Izquierdo: Categorías -->
        <div class="lg:col-span-1">
            <div class="bg-white rounded-lg shadow p-6">
                <h3 class="text-lg font-bold mb-4 text-slate-900">
                    <i class="fas fa-list mr-2"></i>Categorías
                </h3>
                
                <div id="categoriesList" class="space-y-2">
                    <div class="text-center text-slate-400 py-4">
                        <i class="fas fa-spinner fa-spin"></i> Cargando...
                    </div>
                </div>
            </div>
        </div>

        <!-- Panel Derecho: Configuración de Atributos -->
        <div class="lg:col-span-2">
            <div class="bg-white rounded-lg shadow p-6">
                <div id="noCategorySelected" class="text-center py-12">
                    <i class="fas fa-inbox text-6xl text-slate-300 mb-4"></i>
                    <p class="text-slate-500">Selecciona una categoría para configurar sus atributos</p>
                </div>

                <div id="attributesPanel" style="display: none;">
                    <div class="flex justify-between items-center mb-6">
                        <h3 class="text-lg font-bold text-slate-900">
                            <i class="fas fa-cog mr-2"></i>Atributos de: <span id="selectedCategoryName"></span>
                        </h3>
                        <button id="btnAddAttribute" class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 transition">
                            <i class="fas fa-plus mr-2"></i>Agregar Atributo
                        </button>
                    </div>

                    <!-- Lista de Atributos -->
                    <div id="attributesList" class="space-y-4">
                        <div class="text-center text-slate-400 py-8">
                            <i class="fas fa-spinner fa-spin"></i> Cargando atributos...
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal: Crear/Editar Atributo -->
<div id="attributeModal" class="fixed inset-0 bg-black bg-opacity-50 hidden flex items-center justify-center z-50 p-4">
    <div class="bg-white rounded-lg shadow-lg max-w-2xl w-full max-h-96 overflow-y-auto">
        <div class="bg-gradient-to-r from-blue-600 to-blue-700 text-white p-6">
            <h2 class="text-2xl font-bold" id="modalTitle">Crear Nuevo Atributo</h2>
        </div>

        <form id="attributeForm" class="p-6 space-y-4">
            <input type="hidden" id="attributeId">
            <input type="hidden" id="categoriaId">

            <!-- Nombre del Atributo -->
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-2">
                    Nombre del Atributo <span class="text-red-500">*</span>
                </label>
                <input type="text" id="attributeName" class="w-full border border-slate-300 rounded-lg px-3 py-2" 
                       placeholder="ej: viscosidad, rin, color" required>
                <p class="text-xs text-slate-500 mt-1">Será usado como clave en la base de datos</p>
            </div>

            <!-- Tipo de Dato -->
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-2">
                        Tipo de Dato <span class="text-red-500">*</span>
                    </label>
                    <select id="dataType" class="w-full border border-slate-300 rounded-lg px-3 py-2" required
                            onchange="updateDataTypeUI(this.value)">
                        <option value="">Selecciona tipo...</option>
                        <option value="text">Texto</option>
                        <option value="number">Número</option>
                        <option value="date">Fecha</option>
                        <option value="select">Selección (Dropdown)</option>
                        <option value="boolean">Booleano (Sí/No)</option>
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-2">
                        <input type="checkbox" id="isRequired" class="rounded">
                        ¿Es Obligatorio?
                    </label>
                </div>
            </div>

            <!-- Opciones (para select) -->
            <div id="selectOptionsGroup" style="display: none;">
                <label class="block text-sm font-medium text-slate-700 mb-2">
                    Opciones <span class="text-red-500">*</span>
                </label>
                <div id="optionsList" class="space-y-2 mb-2"></div>
                <button type="button" class="text-blue-600 hover:text-blue-700 text-sm font-medium"
                        onclick="addSelectOption()">
                    <i class="fas fa-plus mr-1"></i>Agregar Opción
                </button>
            </div>

            <!-- Validación: Rangos y Límites -->
            <div id="validationGroup" style="display: none;" class="border-t pt-4">
                <h4 class="font-semibold text-slate-700 mb-3">Validaciones Avanzadas</h4>
                
                <div id="numberValidation" style="display: none;" class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-2">Valor Mínimo</label>
                        <input type="number" id="minValue" class="w-full border border-slate-300 rounded-lg px-3 py-2" 
                               step="0.01" placeholder="ej: 0">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-2">Valor Máximo</label>
                        <input type="number" id="maxValue" class="w-full border border-slate-300 rounded-lg px-3 py-2" 
                               step="0.01" placeholder="ej: 100">
                    </div>
                </div>

                <div id="textValidation" style="display: none;">
                    <div class="grid grid-cols-2 gap-4 mb-4">
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-2">Longitud Mínima</label>
                            <input type="number" id="minLength" class="w-full border border-slate-300 rounded-lg px-3 py-2" 
                                   min="0" placeholder="ej: 3">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-2">Longitud Máxima</label>
                            <input type="number" id="maxLength" class="w-full border border-slate-300 rounded-lg px-3 py-2" 
                                   min="0" placeholder="ej: 50">
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-2">Patrón Regex (opcional)</label>
                        <input type="text" id="regexPattern" class="w-full border border-slate-300 rounded-lg px-3 py-2" 
                               placeholder="ej: ^[0-9]{3}-[0-9]{2}$">
                        <p class="text-xs text-slate-500 mt-1">Ejemplo: Código con formato específico</p>
                    </div>
                </div>
            </div>

            <!-- Texto de Ayuda y Traducción -->
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-2">Texto de Ayuda</label>
                <textarea id="helpText" class="w-full border border-slate-300 rounded-lg px-3 py-2" 
                          placeholder="ej: Selecciona la viscosidad según el manual del vehículo" 
                          rows="2"></textarea>
                <p class="text-xs text-slate-500 mt-1">Se mostrará debajo del campo para guiar al usuario</p>
            </div>

            <!-- Botones -->
            <div class="flex justify-end gap-3 pt-4 border-t">
                <button type="button" onclick="closeAttributeModal()" class="px-4 py-2 border border-slate-300 rounded-lg hover:bg-slate-50">
                    Cancelar
                </button>
                <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
                    Guardar Atributo
                </button>
            </div>
        </form>
    </div>
</div>

@endsection

@push('scripts')
<script>
const API_BASE = "{{ route('panel.mantenimientos.atributos_dinamicos.listByCategoria', '') }}";
const CSRF_TOKEN = "{{ csrf_token() }}";

let currentCategoryId = null;

// Cargar categorías
function loadCategories() {
    fetch("{{ route('panel.mantenimientos.categorias.list') }}")
        .then(res => res.json())
        .then(data => {
            const list = document.getElementById('categoriesList');
            list.innerHTML = '';

            if (data.length === 0) {
                list.innerHTML = '<p class="text-slate-500 text-sm">No hay categorías</p>';
                return;
            }

            data.forEach(cat => {
                const btn = document.createElement('button');
                btn.type = 'button';
                btn.className = 'w-full text-left px-4 py-2 rounded-lg border-l-4 border-transparent hover:bg-slate-50 transition categoria-btn';
                btn.dataset.categoryId = cat.id;
                btn.innerHTML = `
                    <div class="font-medium text-slate-900">${cat.nombre}</div>
                    <div class="text-xs text-slate-500">${cat.descripcion || 'Sin descripción'}</div>
                `;
                btn.onclick = () => selectCategory(cat.id, cat.nombre);
                list.appendChild(btn);
            });
        });
}

// Seleccionar categoría
function selectCategory(categoryId, categoryName) {
    currentCategoryId = categoryId;

    // Actualizar UI
    document.querySelectorAll('.categoria-btn').forEach(btn => {
        btn.classList.remove('border-blue-600', 'bg-blue-50');
        if (btn.dataset.categoryId == categoryId) {
            btn.classList.add('border-blue-600', 'bg-blue-50');
        }
    });

    document.getElementById('noCategorySelected').style.display = 'none';
    document.getElementById('attributesPanel').style.display = 'block';
    document.getElementById('selectedCategoryName').textContent = categoryName;

    loadAttributes(categoryId);
}

// Cargar atributos de categoría
function loadAttributes(categoryId) {
    const list = document.getElementById('attributesList');
    list.innerHTML = '<div class="text-center text-slate-400 py-8"><i class="fas fa-spinner fa-spin"></i> Cargando...</div>';

    fetch(`${API_BASE}/${categoryId}`)
        .then(res => res.json())
        .then(data => {
            list.innerHTML = '';

            if (!data.data || data.data.length === 0) {
                list.innerHTML = '<p class="text-slate-500 text-center py-8">No hay atributos configurados</p>';
                return;
            }

            data.data.forEach(attr => {
                const card = createAttributeCard(attr);
                list.appendChild(card);
            });
        });
}

// Crear card de atributo
function createAttributeCard(attr) {
    const card = document.createElement('div');
    card.className = 'border border-slate-200 rounded-lg p-4 hover:shadow-md transition';
    card.innerHTML = `
        <div class="flex justify-between items-start mb-2">
            <div>
                <h4 class="font-semibold text-slate-900">${attr.attribute_name}</h4>
                <p class="text-sm text-slate-500">Tipo: <span class="bg-blue-100 text-blue-700 px-2 py-0.5 rounded text-xs">${attr.data_type}</span></p>
            </div>
            <div class="flex gap-2">
                <button onclick="editAttribute(${attr.id})" class="text-blue-600 hover:text-blue-700">
                    <i class="fas fa-edit"></i>
                </button>
                <button onclick="deleteAttribute(${attr.id})" class="text-red-600 hover:text-red-700">
                    <i class="fas fa-trash"></i>
                </button>
            </div>
        </div>
        <p class="text-sm text-slate-600">${attr.help_text || 'Sin descripción'}</p>
        ${attr.is_required ? '<span class="inline-block mt-2 bg-red-100 text-red-700 text-xs px-2 py-1 rounded">Obligatorio</span>' : ''}
    `;
    return card;
}

// Abrir modal para crear
document.getElementById('btnAddAttribute').addEventListener('click', () => {
    if (!currentCategoryId) {
        alert('Selecciona una categoría primero');
        return;
    }
    document.getElementById('attributeId').value = '';
    document.getElementById('attributeForm').reset();
    document.getElementById('modalTitle').textContent = 'Crear Nuevo Atributo';
    document.getElementById('categoriaId').value = currentCategoryId;
    document.getElementById('attributeModal').classList.remove('hidden');
});

// Cerrar modal
function closeAttributeModal() {
    document.getElementById('attributeModal').classList.add('hidden');
}

// Actualizar UI según tipo de dato
function updateDataTypeUI(dataType) {
    document.getElementById('selectOptionsGroup').style.display = 
        dataType === 'select' ? 'block' : 'none';
    document.getElementById('validationGroup').style.display = 
        ['text', 'number'].includes(dataType) ? 'block' : 'none';
    document.getElementById('numberValidation').style.display = 
        dataType === 'number' ? 'grid' : 'none';
    document.getElementById('textValidation').style.display = 
        dataType === 'text' ? 'block' : 'none';
}

// Agregar opción para select
function addSelectOption() {
    const list = document.getElementById('optionsList');
    const div = document.createElement('div');
    div.className = 'flex gap-2';
    div.innerHTML = `
        <input type="text" placeholder="Opción..." class="flex-1 border border-slate-300 rounded px-3 py-1 select-option">
        <button type="button" onclick="this.parentElement.remove()" class="text-red-600 hover:text-red-700">
            <i class="fas fa-trash"></i>
        </button>
    `;
    list.appendChild(div);
}

// Guardar atributo
document.getElementById('attributeForm').addEventListener('submit', async (e) => {
    e.preventDefault();

    const options = Array.from(document.querySelectorAll('.select-option'))
        .map(input => input.value)
        .filter(v => v);

    const payload = {
        categoria_id: document.getElementById('categoriaId').value,
        attribute_name: document.getElementById('attributeName').value,
        data_type: document.getElementById('dataType').value,
        is_required: document.getElementById('isRequired').checked,
        options: options || null,
        help_text: document.getElementById('helpText').value,
        min_value: document.getElementById('minValue').value || null,
        max_value: document.getElementById('maxValue').value || null,
        min_length: document.getElementById('minLength').value || null,
        max_length: document.getElementById('maxLength').value || null,
        validation_regex: document.getElementById('regexPattern').value || null,
    };

    const method = document.getElementById('attributeId').value ? 'PUT' : 'POST';
    const url = document.getElementById('attributeId').value 
        ? `{{ route('panel.mantenimientos.atributos_dinamicos.update', '') }}/${document.getElementById('attributeId').value}`
        : "{{ route('panel.mantenimientos.atributos_dinamicos.store') }}";

    try {
        const res = await fetch(url, {
            method,
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': CSRF_TOKEN
            },
            body: JSON.stringify(payload)
        });

        if (res.ok) {
            closeAttributeModal();
            loadAttributes(currentCategoryId);
        } else {
            alert('Error al guardar atributo');
        }
    } catch (err) {
        console.error(err);
        alert('Error en la solicitud');
    }
});

// Eliminar atributo
async function deleteAttribute(id) {
    if (!confirm('¿Estás seguro?')) return;

    try {
        const res = await fetch(`{{ route('panel.mantenimientos.atributos_dinamicos.destroy', '') }}/${id}`, {
            method: 'DELETE',
            headers: { 'X-CSRF-TOKEN': CSRF_TOKEN }
        });

        if (res.ok) {
            loadAttributes(currentCategoryId);
        }
    } catch (err) {
        console.error(err);
    }
}

// Cargar al inicio
loadCategories();
</script>
@endpush
