@extends('layouts.panel')

@section('title', 'Reporte de Inventario por Sucursal')

@section('content')
<div class="container mx-auto px-4 py-6">
    <div class="mb-6">
        <h1 class="text-3xl font-bold text-slate-900 mb-2">
            <i class="fas fa-chart-bar mr-2 text-blue-600"></i>Inventario por Sucursal
        </h1>
        <p class="text-slate-600">Análisis completo del stock en todas tus sucursales</p>
    </div>

    <!-- Filtros -->
    <div class="bg-white rounded-lg shadow p-6 mb-6">
        <form method="GET" class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-2">Sucursal</label>
                <select name="sucursal_id" class="w-full border border-slate-300 rounded-lg px-3 py-2">
                    <option value="">Todas las sucursales</option>
                    @foreach($sucursales as $sucursal)
                        <option value="{{ $sucursal->id }}" {{ request('sucursal_id') == $sucursal->id ? 'selected' : '' }}>
                            {{ $sucursal->nombre }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block text-sm font-medium text-slate-700 mb-2">Categoría</label>
                <select name="categoria_id" class="w-full border border-slate-300 rounded-lg px-3 py-2">
                    <option value="">Todas las categorías</option>
                    @foreach($categorias as $categoria)
                        <option value="{{ $categoria->id }}" {{ request('categoria_id') == $categoria->id ? 'selected' : '' }}>
                            {{ $categoria->nombre }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block text-sm font-medium text-slate-700 mb-2">&nbsp;</label>
                <label class="flex items-center gap-2">
                    <input type="checkbox" name="stock_bajo" value="1" {{ request('stock_bajo') ? 'checked' : '' }}>
                    <span class="text-sm">Solo stock bajo</span>
                </label>
            </div>

            <div>
                <label class="block text-sm font-medium text-slate-700 mb-2">&nbsp;</label>
                <button type="submit" class="w-full bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 transition">
                    <i class="fas fa-filter mr-2"></i>Filtrar
                </button>
            </div>
        </form>
    </div>

    <!-- Resumen -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
        <div class="bg-gradient-to-br from-blue-50 to-blue-100 border border-blue-200 rounded-lg p-4">
            <p class="text-sm text-blue-700 font-medium">Total Productos</p>
            <p class="text-3xl font-bold text-blue-900">{{ $repuestos->total() }}</p>
        </div>

        <div class="bg-gradient-to-br from-green-50 to-green-100 border border-green-200 rounded-lg p-4">
            <p class="text-sm text-green-700 font-medium">Stock Total</p>
            <p class="text-3xl font-bold text-green-900">{{ $repuestos->sum('stock_actual') }}</p>
        </div>

        <div class="bg-gradient-to-br from-purple-50 to-purple-100 border border-purple-200 rounded-lg p-4">
            <p class="text-sm text-purple-700 font-medium">Valor Inventario</p>
            <p class="text-3xl font-bold text-purple-900">${{ number_format($totalValorInventario, 2) }}</p>
        </div>

        <div class="bg-gradient-to-br from-orange-50 to-orange-100 border border-orange-200 rounded-lg p-4">
            <p class="text-sm text-orange-700 font-medium">Stock Bajo</p>
            <p class="text-3xl font-bold text-orange-900">
                {{ $repuestos->where('stock_actual', '<=', 'stock_minimo')->count() }}
            </p>
        </div>
    </div>

    <!-- Tabla -->
    <div class="bg-white rounded-lg shadow overflow-hidden">
        <table class="w-full">
            <thead class="bg-slate-50 border-b border-slate-200">
                <tr>
                    <th class="px-6 py-3 text-left text-sm font-semibold text-slate-700">Producto</th>
                    <th class="px-6 py-3 text-left text-sm font-semibold text-slate-700">Categoría</th>
                    <th class="px-6 py-3 text-left text-sm font-semibold text-slate-700">Stock</th>
                    <th class="px-6 py-3 text-left text-sm font-semibold text-slate-700">Min.</th>
                    <th class="px-6 py-3 text-right text-sm font-semibold text-slate-700">Costo Unit.</th>
                    <th class="px-6 py-3 text-right text-sm font-semibold text-slate-700">Venta Unit.</th>
                    <th class="px-6 py-3 text-right text-sm font-semibold text-slate-700">Valor Total</th>
                    <th class="px-6 py-3 text-center text-sm font-semibold text-slate-700">Estado</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-200">
                @forelse($repuestos as $repuesto)
                    @php
                        $esStockBajo = $repuesto->stock_actual <= $repuesto->stock_minimo;
                        $valorTotal = $repuesto->stock_actual * $repuesto->precio_costo;
                    @endphp
                    <tr class="hover:bg-slate-50 transition">
                        <td class="px-6 py-4">
                            <div>
                                <p class="font-medium text-slate-900">{{ $repuesto->nombre }}</p>
                                <p class="text-xs text-slate-500">{{ $repuesto->codigo_interno }}</p>
                            </div>
                        </td>
                        <td class="px-6 py-4 text-sm text-slate-700">{{ $repuesto->categoria->nombre ?? 'S/C' }}</td>
                        <td class="px-6 py-4 text-sm font-medium text-slate-900">
                            {{ $repuesto->stock_actual }}
                            @if($esStockBajo)
                                <span class="ml-2 inline-block bg-red-100 text-red-700 px-2 py-0.5 rounded text-xs">⚠️ Bajo</span>
                            @endif
                        </td>
                        <td class="px-6 py-4 text-sm text-slate-700">{{ $repuesto->stock_minimo }}</td>
                        <td class="px-6 py-4 text-sm text-right text-slate-900">${{ number_format($repuesto->precio_costo, 2) }}</td>
                        <td class="px-6 py-4 text-sm text-right text-slate-900">${{ number_format($repuesto->precio_venta, 2) }}</td>
                        <td class="px-6 py-4 text-sm text-right font-semibold text-slate-900">
                            ${{ number_format($valorTotal, 2) }}
                        </td>
                        <td class="px-6 py-4 text-center text-sm">
                            @if($esStockBajo)
                                <span class="inline-block bg-red-100 text-red-700 px-3 py-1 rounded-full text-xs font-medium">
                                    <i class="fas fa-exclamation-circle mr-1"></i>Alerta
                                </span>
                            @else
                                <span class="inline-block bg-green-100 text-green-700 px-3 py-1 rounded-full text-xs font-medium">
                                    <i class="fas fa-check-circle mr-1"></i>OK
                                </span>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="px-6 py-12 text-center text-slate-500">
                            <i class="fas fa-inbox text-4xl mb-3 opacity-50"></i>
                            <p>No hay productos que coincidan con los filtros</p>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>

        <!-- Paginación -->
        <div class="bg-slate-50 px-6 py-4 border-t border-slate-200">
            {{ $repuestos->links() }}
        </div>
    </div>

    <!-- Acciones -->
    <div class="mt-6 flex gap-3">
        <a href="{{ route('panel.mantenimientos.reportes.inventario-pdf', request()->query()) }}" 
           class="bg-red-600 text-white px-4 py-2 rounded-lg hover:bg-red-700 transition">
            <i class="fas fa-file-pdf mr-2"></i>Descargar PDF
        </a>
        <a href="{{ route('panel.mantenimientos.reportes.stock-bajo') }}" 
           class="bg-orange-600 text-white px-4 py-2 rounded-lg hover:bg-orange-700 transition">
            <i class="fas fa-exclamation-triangle mr-2"></i>Stock Bajo
        </a>
    </div>
</div>
@endsection
