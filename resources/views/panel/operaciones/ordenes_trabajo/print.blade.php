<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hoja de Recepción - {{ $orden->codigo_orden }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        @media print {
            .no-print {
                display: none;
            }

            body {
                padding: 0;
                margin: 0;
            }
        }

        .dotted-border {
            border-bottom: 2px dotted #ccc;
        }
    </style>
</head>

<body class="bg-white text-gray-800 p-8 font-serif">

    <!-- Header / Logo -->
    <div class="flex justify-between items-start mb-8 border-b-2 border-gray-100 pb-6">
        <div>
            <h1 class="text-3xl font-black text-blue-900 uppercase italic">Taller Pro</h1>
            <p class="text-xs text-gray-500 uppercase tracking-widest font-bold">{{ $orden->sucursal->nombre }}</p>
            <p class="text-xs text-gray-400">{{ $orden->sucursal->direccion }}</p>
            <p class="text-xs text-gray-400">Tel: {{ $orden->sucursal->telefono ?? 'N/A' }}</p>
        </div>
        <div class="text-right">
            <h2 class="text-xl font-bold text-gray-700">ORDEN DE TRABAJO</h2>
            <p class="text-2xl font-black text-blue-600">{{ $orden->codigo_orden }}</p>
            <p class="text-sm text-gray-500 mt-1">Fecha: {{ $orden->fecha_recepcion->format('d/m/Y H:i') }}</p>
        </div>
    </div>

    <!-- Client & Vehicle Info -->
    <div class="grid grid-cols-2 gap-8 mb-8">
        <div class="space-y-4">
            <h3 class="bg-gray-100 px-3 py-1 text-xs font-bold uppercase tracking-wider text-gray-600">Datos del Cliente</h3>
            <div class="px-3 space-y-2">
                <p><span class="font-bold text-gray-500 text-xs uppercase w-24 inline-block">Nombre:</span> {{ $orden->cliente->nombre_completo }}</p>
                <p><span class="font-bold text-gray-500 text-xs uppercase w-24 inline-block">Teléfono:</span> {{ $orden->cliente->telefono }}</p>
                <p><span class="font-bold text-gray-500 text-xs uppercase w-24 inline-block">Email:</span> {{ $orden->cliente->email ?? 'N/A' }}</p>
            </div>
        </div>
        <div class="space-y-4">
            <h3 class="bg-gray-100 px-3 py-1 text-xs font-bold uppercase tracking-wider text-gray-600">Datos del Vehículo</h3>
            <div class="px-3 space-y-2">
                <p><span class="font-bold text-gray-500 text-xs uppercase w-24 inline-block">Placa:</span> <span class="font-mono font-bold text-lg">{{ $orden->vehiculo->placa }}</span></p>
                <p><span class="font-bold text-gray-500 text-xs uppercase w-24 inline-block">Marca/Mod:</span> {{ $orden->vehiculo->marca->nombre }} {{ $orden->vehiculo->modelo->nombre }}</p>
                <p><span class="font-bold text-gray-500 text-xs uppercase w-24 inline-block">Color/Año:</span> {{ $orden->color }} / {{ $orden->vehiculo->anio }}</p>
            </div>
        </div>
    </div>

    <!-- Reception Details -->
    <div class="grid grid-cols-3 gap-8 mb-8">
        <div class="col-span-2 space-y-4">
            <h3 class="bg-gray-100 px-3 py-1 text-xs font-bold uppercase tracking-wider text-gray-600">Motivo del Ingreso / Fallas Reportadas</h3>
            <div class="p-4 border border-gray-100 rounded-lg min-h-[100px] text-sm leading-relaxed italic">
                {{ $orden->falla_cliente }}
            </div>
        </div>
        <div class="space-y-4">
            <h3 class="bg-gray-100 px-3 py-1 text-xs font-bold uppercase tracking-wider text-gray-600">Estado al Ingreso</h3>
            <div class="px-3 space-y-3">
                <div class="flex justify-between items-center border-b border-gray-50 pb-1">
                    <span class="text-xs text-gray-500 uppercase">Kilometraje:</span>
                    <span class="font-bold">{{ number_format($orden->kilometraje_entrada) }} KM</span>
                </div>
                <div class="flex justify-between items-center border-b border-gray-50 pb-1 text-xs">
                    <span class="text-gray-500 uppercase">Combustible:</span>
                    <span class="font-bold">{{ $orden->nivel_combustible }}</span>
                </div>
                <div class="flex justify-between items-center border-b border-gray-50 pb-1 text-xs">
                    <span class="text-gray-500 uppercase">Recibido por:</span>
                    <span class="font-bold">{{ $orden->receptor->name }}</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Inventory / Checklist (Compact) -->
    <div class="mb-8">
        <h3 class="bg-gray-100 px-3 py-1 text-xs font-bold uppercase tracking-wider text-gray-600 mb-4">Inventario de Recepción</h3>
        <div class="grid grid-cols-4 gap-x-6 gap-y-2 text-[10px] uppercase">
            @php
            $inv = is_string($orden->inventario_recepcion) ? json_decode($orden->inventario_recepcion, true) : $orden->inventario_recepcion;
            @endphp
            @if($inv)
            @foreach($inv as $key => $val)
            <div class="flex items-center gap-2">
                <div class="w-3 h-3 border border-gray-400 flex items-center justify-center {{ $val === 'on' || $val === true ? 'bg-black' : '' }}">
                    @if($val === 'on' || $val === true) <span class="text-white">✓</span> @endif
                </div>
                <span>{{ str_replace('_', ' ', $key) }}</span>
            </div>
            @endforeach
            @endif
        </div>
    </div>

    <!-- Damage Map (if exists) -->
    @if($orden->danos_imagen_url)
    <div class="mb-8">
        <h3 class="bg-gray-100 px-3 py-1 text-xs font-bold uppercase tracking-wider text-gray-600 mb-4">Mapa de Daños Externos</h3>
        <div class="flex justify-center">
            <img src="{{ asset($orden->danos_imagen_url) }}" class="max-h-64 border rounded p-2">
        </div>
    </div>
    @endif

    <!-- Photographs -->
    @if($orden->archivos->isNotEmpty())
    <div class="mb-8">
        <h3 class="bg-gray-100 px-3 py-1 text-xs font-bold uppercase tracking-wider text-gray-600 mb-4">Fotografías de Recepción</h3>
        <div class="grid grid-cols-4 gap-4">
            @foreach($orden->archivos->where('tipo', 'recepcion') as $foto)
            <div class="space-y-1">
                <img src="{{ asset($foto->url) }}" class="w-full h-32 object-cover rounded border">
                <p class="text-[9px] text-center text-gray-500 uppercase">{{ $foto->titulo ?? 'Sin título' }}</p>
            </div>
            @endforeach
        </div>
    </div>
    @endif

    <!-- Signatures -->
    <div class="grid grid-cols-2 gap-20 mt-20 pt-10">
        <div class="text-center border-t-2 border-gray-200">
            <p class="text-xs font-bold text-gray-600 uppercase mt-2">Firma del Cliente</p>
            <p class="text-[8px] text-gray-400 mt-1 italic">Autorizo los trabajos y el presupuesto inicial</p>
        </div>
        <div class="text-center border-t-2 border-gray-200">
            <p class="text-xs font-bold text-gray-600 uppercase mt-2">Firma del Receptor</p>
            <p class="text-[8px] text-gray-400 mt-1 italic">{{ $orden->receptor->name }}</p>
        </div>
    </div>

    <!-- Actions (No Print) -->
    <div class="no-print mt-10 flex gap-4 justify-center">
        <button onclick="window.print()" class="btn bg-blue-600 hover:bg-blue-700 text-white font-bold py-3 px-8 rounded-xl shadow-lg flex items-center gap-2">
            <i class="fas fa-print"></i> Imprimir Ahora
        </button>
        <a href="{{ route('panel.operaciones.ordenes_trabajo.index') }}" class="btn bg-gray-100 hover:bg-gray-200 text-gray-600 font-bold py-3 px-8 rounded-xl">
            Volver al Listado
        </a>
    </div>

    <script src="https://kit.fontawesome.com/your-code.js" crossorigin="anonymous"></script>
</body>

</html>