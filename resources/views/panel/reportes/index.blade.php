@extends('layouts.panel')

@section('content')
<div class="container mx-auto px-4 py-8">
    <!-- Header -->
    <div class="flex justify-between items-center mb-8">
        <div>
            <h1 class="text-3xl font-bold text-slate-900">📊 Reportes de Inventario</h1>
            <p class="text-slate-600 mt-2">Genera y analiza reportes detallados de tu inventario</p>
        </div>
    </div>

    <!-- Tabs de Tipos de Reportes -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-8">
        <!-- Inventario General -->
        <div class="bg-white rounded-lg shadow p-6 cursor-pointer hover:shadow-lg transition"
             onclick="showReportForm('inventario-general')">
            <div class="flex items-center mb-4">
                <div class="text-4xl">📦</div>
                <h3 class="text-xl font-bold ml-4">Inventario General</h3>
            </div>
            <p class="text-slate-600">Total de productos, valores y stock disponible</p>
            <button class="mt-4 px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700"
                    onclick="openReportForm('inventario-general')">
                Generar Reporte
            </button>
        </div>

        <!-- Bajo Stock -->
        <div class="bg-white rounded-lg shadow p-6 cursor-pointer hover:shadow-lg transition"
             onclick="showReportForm('bajo-stock')">
            <div class="flex items-center mb-4">
                <div class="text-4xl">⚠️</div>
                <h3 class="text-xl font-bold ml-4">Bajo Stock</h3>
            </div>
            <p class="text-slate-600">Productos con inventario por debajo del mínimo</p>
            <button class="mt-4 px-4 py-2 bg-orange-600 text-white rounded hover:bg-orange-700"
                    onclick="openReportForm('bajo-stock')">
                Generar Reporte
            </button>
        </div>

        <!-- Por Categoría -->
        <div class="bg-white rounded-lg shadow p-6 cursor-pointer hover:shadow-lg transition"
             onclick="showReportForm('por-categoria')">
            <div class="flex items-center mb-4">
                <div class="text-4xl">📂</div>
                <h3 class="text-xl font-bold ml-4">Por Categoría</h3>
            </div>
            <p class="text-slate-600">Análisis detallado por cada categoría de productos</p>
            <button class="mt-4 px-4 py-2 bg-green-600 text-white rounded hover:bg-green-700"
                    onclick="openReportForm('por-categoria')">
                Generar Reporte
            </button>
        </div>
    </div>

    <!-- Lista de Reportes -->
    <div class="bg-white rounded-lg shadow overflow-hidden">
        <div class="px-6 py-4 bg-slate-50 border-b border-slate-200">
            <h2 class="text-xl font-bold text-slate-900">Reportes Generados Recientemente</h2>
        </div>

        <div id="reportesList" class="divide-y divide-slate-200">
            <div class="text-center py-8">
                <p class="text-slate-500">Cargando reportes...</p>
            </div>
        </div>
    </div>
</div>

<!-- Modal para generar reportes -->
<div id="reportModal" class="hidden fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
    <div class="bg-white rounded-lg max-w-2xl w-full mx-4 max-h-96 overflow-y-auto">
        <div class="px-6 py-4 bg-slate-50 border-b border-slate-200 flex justify-between items-center">
            <h2 class="text-xl font-bold" id="modalTitle">Generar Reporte</h2>
            <button class="text-2xl" onclick="closeReportForm()">&times;</button>
        </div>

        <div class="p-6">
            <form id="reportForm" onsubmit="submitReport(event)">
                <!-- Selector de Categoría (para inventario general) -->
                <div id="categoriasField" class="mb-4 hidden">
                    <label class="block text-sm font-bold mb-2">Categoría (Opcional)</label>
                    <select id="categoriaSelect" class="w-full px-4 py-2 border border-slate-300 rounded">
                        <option value="">Todas las categorías</option>
                    </select>
                </div>

                <div class="flex gap-4">
                    <button type="submit" class="flex-1 px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700">
                        ✅ Generar
                    </button>
                    <button type="button" class="flex-1 px-4 py-2 bg-slate-300 text-slate-900 rounded hover:bg-slate-400"
                            onclick="closeReportForm()">
                        Cancelar
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@endsection

@push('scripts')
    @vite(['resources/js/panel/reportes.js'])
@endpush
