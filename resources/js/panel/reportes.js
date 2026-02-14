const API_URL = "{{ route('panel.reportes.list') }}".replace('/list', '');

let currentReportType = null;

// Cargar reportes al abrir página
document.addEventListener('DOMContentLoaded', function() {
    loadReports();
});

// Cargar lista de reportes
function loadReports() {
    fetch(`${API_URL}/list`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const reportesList = document.getElementById('reportesList');
                
                if (data.data.data.length === 0) {
                    reportesList.innerHTML = `
                        <div class="text-center py-8">
                            <p class="text-slate-500">No hay reportes generados aún</p>
                        </div>
                    `;
                    return;
                }

                reportesList.innerHTML = data.data.data.map(reporte => `
                    <div class="px-6 py-4 hover:bg-slate-50 transition">
                        <div class="flex justify-between items-start">
                            <div class="flex-1">
                                <h3 class="font-bold text-slate-900">${reporte.nombre_reporte}</h3>
                                <p class="text-sm text-slate-600 mt-2">
                                    📅 ${new Date(reporte.generated_at).toLocaleString()} | 
                                    👤 ${reporte.creator.name}
                                </p>
                                <div class="mt-3 grid grid-cols-4 gap-4 text-sm">
                                    <div>
                                        <span class="text-slate-600">Productos:</span>
                                        <strong class="block">${reporte.total_productos}</strong>
                                    </div>
                                    <div>
                                        <span class="text-slate-600">Valor Costo:</span>
                                        <strong class="block text-green-600">$${reporte.valor_total_costo.toFixed(2)}</strong>
                                    </div>
                                    <div>
                                        <span class="text-slate-600">Valor Venta:</span>
                                        <strong class="block text-blue-600">$${reporte.valor_total_venta.toFixed(2)}</strong>
                                    </div>
                                    <div>
                                        <span class="text-slate-600">Stock Total:</span>
                                        <strong class="block">${reporte.stock_total}</strong>
                                    </div>
                                </div>
                            </div>
                            <div class="flex gap-2">
                                <button onclick="viewReport(${reporte.id})" class="px-3 py-2 bg-blue-600 text-white rounded hover:bg-blue-700 text-sm">
                                    👁️ Ver
                                </button>
                                <button onclick="downloadReport(${reporte.id})" class="px-3 py-2 bg-green-600 text-white rounded hover:bg-green-700 text-sm">
                                    📥 Descargar PDF
                                </button>
                                <button onclick="deleteReport(${reporte.id})" class="px-3 py-2 bg-red-600 text-white rounded hover:bg-red-700 text-sm">
                                    🗑️ Eliminar
                                </button>
                            </div>
                        </div>
                    </div>
                `).join('');
            }
        })
        .catch(error => console.error('Error:', error));
}

// Abrir formulario para generar reporte
function openReportForm(type) {
    currentReportType = type;
    const titles = {
        'inventario-general': 'Reporte: Inventario General',
        'bajo-stock': 'Reporte: Bajo Stock',
        'por-categoria': 'Reporte: Análisis por Categoría'
    };

    document.getElementById('modalTitle').textContent = titles[type];
    
    // Mostrar campo de categoría solo para inventario general
    if (type === 'inventario-general') {
        document.getElementById('categoriasField').classList.remove('hidden');
    } else {
        document.getElementById('categoriasField').classList.add('hidden');
    }

    document.getElementById('reportModal').classList.remove('hidden');
}

// Cerrar formulario
function closeReportForm() {
    document.getElementById('reportModal').classList.add('hidden');
    document.getElementById('reportForm').reset();
}

// Enviar reporte
function submitReport(e) {
    e.preventDefault();
    
    let endpoint = '';
    const data = {};

    switch(currentReportType) {
        case 'inventario-general':
            endpoint = `${API_URL}/inventario-general`;
            const categoriaId = document.getElementById('categoriaSelect').value;
            if (categoriaId) data.categoria_id = categoriaId;
            break;
        case 'bajo-stock':
            endpoint = `${API_URL}/bajo-stock`;
            break;
        case 'por-categoria':
            endpoint = `${API_URL}/por-categoria`;
            break;
    }

    fetch(endpoint, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('[name="csrf-token"]').content
        },
        body: JSON.stringify(data)
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('✅ Reporte generado exitosamente');
            closeReportForm();
            loadReports();
        } else {
            alert('❌ Error: ' + data.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('❌ Error al generar reporte');
    });
}

// Ver detalle de reporte
function viewReport(reporteId) {
    fetch(`${API_URL}/${reporteId}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                console.log('Reporte:', data.data);
                alert('Detalles del reporte:\n\n' + JSON.stringify(data.data, null, 2));
            }
        });
}

// Descargar PDF
function downloadReport(reporteId) {
    window.location.href = `${API_URL}/${reporteId}/pdf`;
}

// Eliminar reporte
function deleteReport(reporteId) {
    if (!confirm('¿Eliminar este reporte?')) return;

    fetch(`${API_URL}/${reporteId}`, {
        method: 'DELETE',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('[name="csrf-token"]').content
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('✅ Reporte eliminado');
            loadReports();
        } else {
            alert('❌ Error: ' + data.message);
        }
    });
}
