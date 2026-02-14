const API_CATEGORIAS_URL = "{{ route('panel.mantenimientos.categorias.list') }}";
const API_ATRIBUTOS_URL = "{{ route('panel.mantenimientos.atributos_dinamicos.listByCategoria', '') }}";

document.addEventListener('DOMContentLoaded', function() {
    loadCategorias();
});

// Cargar categorías
function loadCategorias() {
    fetch(API_CATEGORIAS_URL)
        .then(response => response.json())
        .then(data => {
            const select = document.getElementById('categoriaSelect');
            select.innerHTML = '<option value="">-- Todas las categorías --</option>';
            
            if (data.length) {
                data.forEach(cat => {
                    select.innerHTML += `<option value="${cat.id}">${cat.nombre}</option>`;
                });
            }
        });
}

// Cargar atributos de categoría
function loadAttributes() {
    const categoriaId = document.getElementById('categoriaSelect').value;
    const attributesList = document.getElementById('attributesList');

    if (!categoriaId) {
        attributesList.innerHTML = '<div class="text-center py-8"><p class="text-slate-500">Selecciona una categoría</p></div>';
        return;
    }

    fetch(`${API_ATRIBUTOS_URL}/${categoriaId}`)
        .then(response => response.json())
        .then(data => {
            if (data.success && data.data.length > 0) {
                attributesList.innerHTML = data.data.map(attr => `
                    <div class="px-6 py-4 hover:bg-slate-50">
                        <div class="flex justify-between items-start">
                            <div class="flex-1">
                                <h3 class="font-bold text-lg">${attr.attribute_name}</h3>
                                <p class="text-sm text-slate-600 mt-1">
                                    Tipo: <strong>${getTipoNames(attr.data_type)}</strong> |
                                    ${attr.is_required ? '<span class="text-red-600">Requerido</span>' : '<span class="text-gray-600">Opcional</span>'} |
                                    ${!attr.is_active ? '<span class="text-orange-600">Inactivo</span>' : 'Activo'}
                                </p>
                                ${attr.help_text ? `<p class="text-xs text-slate-500 mt-2">💡 ${attr.help_text}</p>` : ''}
                                
                                ${attr.data_type === 'select' && attr.options ? `
                                    <div class="mt-2">
                                        <span class="text-xs text-slate-600">Opciones:</span>
                                        <div class="flex flex-wrap gap-1 mt-1">
                                            ${attr.options.map(opt => `<span class="px-2 py-1 bg-blue-100 text-blue-700 rounded text-xs">${opt}</span>`).join('')}
                                        </div>
                                    </div>
                                ` : ''}

                                ${attr.min_length || attr.max_length ? `
                                    <p class="text-xs text-slate-600 mt-2">
                                        📏 Longitud: ${attr.min_length || '0'} - ${attr.max_length || '∞'} caracteres
                                    </p>
                                ` : ''}

                                ${attr.min_value || attr.max_value ? `
                                    <p class="text-xs text-slate-600 mt-2">
                                        🔢 Rango: ${attr.min_value || '-∞'} - ${attr.max_value || '∞'}
                                    </p>
                                ` : ''}

                                ${attr.regex_pattern ? `
                                    <p class="text-xs text-slate-600 mt-2">
                                        🔍 Regex: <code>${attr.regex_pattern}</code>
                                    </p>
                                ` : ''}
                            </div>
                            <div class="flex gap-2">
                                <button onclick="editAttribute(${attr.id})" class="px-3 py-2 bg-blue-600 text-white rounded hover:bg-blue-700">
                                    ✏️ Editar
                                </button>
                                <button onclick="deleteAttribute(${attr.id})" class="px-3 py-2 bg-red-600 text-white rounded hover:bg-red-700">
                                    🗑️ Borrar
                                </button>
                            </div>
                        </div>
                    </div>
                `).join('');
            } else {
                attributesList.innerHTML = '<div class="text-center py-8"><p class="text-slate-500">No hay atributos configurados para esta categoría</p></div>';
            }
        });
}

// Nombres legibles de tipos
function getTipoNames(tipo) {
    const tipos = {
        'text': '📝 Texto',
        'number': '🔢 Número',
        'date': '📅 Fecha',
        'select': '📋 Dropdown',
        'boolean': '✓ Sí/No'
    };
    return tipos[tipo] || tipo;
}

// Abrir modal nuevo atributo
function openNewAttributeModal() {
    const categoriaId = document.getElementById('categoriaSelect').value;
    
    if (!categoriaId) {
        alert('❌ Selecciona una categoría primero');
        return;
    }

    document.getElementById('attributeId').value = '';
    document.getElementById('modalTitle').textContent = 'Nuevo Atributo';
    document.getElementById('attributeForm').reset();
    resetValidationFields();
    document.getElementById('attributeModal').classList.remove('hidden');
}

// Editar atributo
function editAttribute(attributeId) {
    // TODO: Cargar datos del atributo
    openNewAttributeModal();
}

// Cerrar modal
function closeAttributeModal() {
    document.getElementById('attributeModal').classList.add('hidden');
}

// Actualizar campos de validación según tipo
function updateValidationFields() {
    const tipo = document.getElementById('dataType').value;
    
    resetValidationFields();

    switch(tipo) {
        case 'text':
            document.getElementById('lengthFields').classList.remove('hidden');
            document.getElementById('lengthFieldsMax').classList.remove('hidden');
            document.getElementById('regexField').classList.remove('hidden');
            break;
        case 'number':
            document.getElementById('minValueField').classList.remove('hidden');
            document.getElementById('maxValueField').classList.remove('hidden');
            break;
        case 'select':
            document.getElementById('optionsField').classList.remove('hidden');
            break;
    }
}

// Resetear campos de validación
function resetValidationFields() {
    document.getElementById('optionsField').classList.add('hidden');
    document.getElementById('lengthFields').classList.add('hidden');
    document.getElementById('lengthFieldsMax').classList.add('hidden');
    document.getElementById('minValueField').classList.add('hidden');
    document.getElementById('maxValueField').classList.add('hidden');
    document.getElementById('regexField').classList.add('hidden');
}

// Agregar idioma adicional
function addTranslation() {
    const container = document.getElementById('translationsContainer');
    const newItem = document.createElement('div');
    newItem.className = 'translation-item mb-4 p-4 bg-white border border-green-200 rounded';
    newItem.innerHTML = `
        <select class="locale w-full px-4 py-2 border border-slate-300 rounded mb-2">
            <option value="es">Español</option>
            <option value="en">English</option>
            <option value="fr">Français</option>
            <option value="pt">Português</option>
        </select>
        <input type="text" placeholder="Etiqueta / Nombre" class="label w-full px-4 py-2 border border-slate-300 rounded mb-2">
        <textarea placeholder="Descripción" class="description w-full px-4 py-2 border border-slate-300 rounded"></textarea>
    `;
    container.appendChild(newItem);
}

// Enviar atributo
function submitAttribute(e) {
    e.preventDefault();

    const categoriaId = document.getElementById('categoriaSelect').value;
    const attributeId = document.getElementById('attributeId').value;

    const data = {
        categoria_id: parseInt(categoriaId),
        attribute_name: document.getElementById('attributeName').value,
        data_type: document.getElementById('dataType').value,
        is_required: document.getElementById('isRequired').checked,
        is_active: document.getElementById('isActive').checked,
        display_order: parseInt(document.getElementById('displayOrder').value) || 0,
        help_text: document.getElementById('helpText').value,
        regex_pattern: document.getElementById('regexPattern').value || null,
        min_length: parseInt(document.getElementById('minLength').value) || null,
        max_length: parseInt(document.getElementById('maxLength').value) || null,
        min_value: parseFloat(document.getElementById('minValue').value) || null,
        max_value: parseFloat(document.getElementById('maxValue').value) || null,
    };

    // Procesar opciones
    if (data.data_type === 'select') {
        data.options = document.getElementById('options').value
            .split(',')
            .map(o => o.trim())
            .filter(o => o);
    }

    // Procesar traducciones
    const translations = [];
    document.querySelectorAll('.translation-item').forEach(item => {
        const locale = item.querySelector('.locale').value;
        const label = item.querySelector('.label').value;
        const description = item.querySelector('.description').value;
        
        if (label) {
            translations.push({ locale, label, description });
        }
    });
    data.translations = translations;

    const method = attributeId ? 'PUT' : 'POST';
    const url = attributeId 
        ? `${API_ATRIBUTOS_URL}/${attributeId}`
        : API_ATRIBUTOS_URL.replace('{categoriaId}', '');

    fetch(url, {
        method,
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('[name="csrf-token"]').content
        },
        body: JSON.stringify(data)
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('✅ Atributo guardado exitosamente');
            closeAttributeModal();
            loadAttributes();
        } else {
            alert('❌ Error: ' + data.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('❌ Error al guardar atributo');
    });
}

// Eliminar atributo
function deleteAttribute(attributeId) {
    if (!confirm('¿Eliminar este atributo?')) return;

    fetch(`${API_ATRIBUTOS_URL}/${attributeId}`, {
        method: 'DELETE',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('[name="csrf-token"]').content
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('✅ Atributo eliminado');
            loadAttributes();
        } else {
            alert('❌ Error: ' + data.message);
        }
    });
}
