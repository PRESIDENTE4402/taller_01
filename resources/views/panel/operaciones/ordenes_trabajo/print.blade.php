<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Orden de Recepción #{{ $orden->codigo_orden }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        @media print {
            body {
                -webkit-print-color-adjust: exact;
            }

            .no-print {
                display: none;
            }

            .page-break {
                page-break-before: always;
            }
        }

        .logo-bmw {
            width: 80px;
            height: auto;
        }
    </style>
</head>

<body class="bg-gray-100 font-sans text-gray-800">

    <!-- Print Controls -->
    <div class="fixed top-4 right-4 z-50 no-print flex gap-2">
        <button onclick="window.print()"
            class="bg-blue-900 text-white px-6 py-2 rounded-full shadow-lg font-bold hover:bg-blue-800 transition-all flex items-center gap-2">
            <i class="fas fa-print"></i> Imprimir
        </button>
        <button onclick="window.close()"
            class="bg-gray-500 text-white px-6 py-2 rounded-full shadow-lg font-bold hover:bg-gray-600 transition-all">
            Cerrar
        </button>
    </div>

    <!-- Main Container (A4 approx width) -->
    <div class="max-w-[210mm] mx-auto bg-white shadow-xl my-8 p-10 min-h-screen relative overflow-hidden">

        <!-- Watermark -->
        <div
            class="absolute inset-0 flex items-center justify-center opacity-[0.03] pointer-events-none overflow-hidden">
            <img src="https://upload.wikimedia.org/wikipedia/commons/4/44/BMW.svg.png"
                class="w-[80%] grayscale transform rotate-12">
        </div>

        <!-- Header -->
        <div class="relative z-10 border-b-2 border-blue-900 pb-6 mb-8 flex justify-between items-start">
            <div class="flex items-center gap-6">
                <img src="https://upload.wikimedia.org/wikipedia/commons/4/44/BMW.svg.png" alt="BMW Logo"
                    class="w-20 h-20 object-contain">
                <div>
                    <h1 class="text-3xl font-black text-slate-900 tracking-tighter leading-none">TECNIMECÁNICA</h1>
                    <h2 class="text-xl font-bold text-blue-900 tracking-widest uppercase mb-1">CALIFORNIA</h2>
                    <p class="text-xs text-gray-500 font-medium">Especialistas en BMW & Mini Cooper</p>
                    <p class="text-xs text-gray-400">Av. Las Américas 12-34, Zona 13, Guatemala</p>
                    <p class="text-xs text-gray-400">PBX: 2233-4455 | info@tecnimecanica.com</p>
                </div>
            </div>
            <div class="text-right">
                <div class="bg-blue-900 text-white px-4 py-1 rounded-bl-xl shadow-md inline-block mb-2">
                    <span class="font-bold text-lg">ORDEN #{{ $orden->codigo_orden }}</span>
                </div>
                <div class="text-sm font-bold text-gray-600">
                    <p>Fecha:
                        {{ $orden->fecha_recepcion ? $orden->fecha_recepcion->format('d/m/Y') : now()->format('d/m/Y') }}
                    </p>
                    <p>Hora:
                        {{ $orden->fecha_recepcion ? $orden->fecha_recepcion->format('H:i A') : now()->format('H:i A') }}
                    </p>
                </div>
                <div
                    class="mt-2 text-xs font-bold px-2 py-1 rounded bg-{{ $orden->tipo_orden == 'garantia' ? 'green' : ($orden->tipo_orden == 'cortesia' ? 'yellow' : 'blue') }}-100 text-{{ $orden->tipo_orden == 'garantia' ? 'green' : ($orden->tipo_orden == 'cortesia' ? 'yellow' : 'blue') }}-800 inline-block uppercase">
                    {{ ucfirst($orden->tipo_orden) }}
                </div>
            </div>
        </div>

        <!-- Info Grid -->
        <div class="relative z-10 grid grid-cols-2 gap-8 mb-8">
            <!-- Cliente -->
            <div class="bg-gray-50 p-4 rounded-xl border border-gray-100">
                <h3
                    class="text-xs font-black text-blue-900 uppercase tracking-widest border-b border-gray-200 pb-2 mb-3">
                    <i class="fas fa-user-circle mr-1"></i> Información del Cliente
                </h3>
                <div class="space-y-1 text-sm">
                    <p><span class="font-bold text-gray-500 w-20 inline-block">Nombre:</span> <span
                            class="font-semibold">{{ $orden->cliente->nombre_completo }}</span></p>
                    <p><span class="font-bold text-gray-500 w-20 inline-block">Teléfono:</span>
                        {{ $orden->cliente->telefono }}
                    </p>
                    <p><span class="font-bold text-gray-500 w-20 inline-block">Email:</span>
                        {{ $orden->cliente->email ?? 'N/A' }}
                    </p>
                    <p><span class="font-bold text-gray-500 w-20 inline-block">NIT:</span>
                        {{ $orden->cliente->nit ?? 'C/F' }}
                    </p>
                    @if($orden->cliente->es_empresa)
                        <p><span class="font-bold text-gray-500 w-20 inline-block">Empresa:</span>
                            {{ $orden->cliente->empresa }}
                        </p>
                    @endif
                </div>
            </div>

            <!-- Vehiculo -->
            <div class="bg-gray-50 p-4 rounded-xl border border-gray-100">
                <h3
                    class="text-xs font-black text-blue-900 uppercase tracking-widest border-b border-gray-200 pb-2 mb-3">
                    <i class="fas fa-car mr-1"></i> Datos del Vehículo
                </h3>
                <div class="grid grid-cols-2 gap-x-2 gap-y-1 text-sm">
                    <p><span class="font-bold text-gray-500">Marca:</span>
                        {{ $orden->vehiculo->marca->nombre ?? 'N/A' }}
                    </p>
                    <p><span class="font-bold text-gray-500">Modelo:</span>
                        {{ $orden->vehiculo->modelo->nombre ?? 'N/A' }}
                    </p>
                    <p><span class="font-bold text-gray-500">Año:</span> {{ $orden->vehiculo->anio }}</p>
                    <p><span class="font-bold text-gray-500">Color:</span> {{ $orden->color }}</p>
                    <p class="col-span-2 bg-white px-2 py-1 rounded border border-gray-200 mt-1">
                        <span class="font-bold text-gray-500">PLACA:</span>
                        <span class="font-black text-lg ml-2">{{ $orden->vehiculo->placa }}</span>
                    </p>
                    <p class="col-span-2 mt-1"><span class="font-bold text-gray-500">VIN:</span> <span
                            class="font-mono text-xs">{{ $orden->vehiculo->vin ?? 'N/A' }}</span></p>
                </div>
            </div>
        </div>

        <!-- Initial Status (KM & Fuel) -->
        <div class="relative z-10 grid grid-cols-2 gap-8 mb-8">
            <div class="border border-gray-200 rounded-lg p-3 flex items-center justify-between shadow-sm">
                <div class="text-xs font-bold text-gray-500 uppercase">Kilometraje Entrada</div>
                <div class="text-xl font-black text-slate-800 font-mono">
                    {{ number_format($orden->kilometraje_entrada) }} KM
                </div>
            </div>
            <div class="border border-gray-200 rounded-lg p-3 flex items-center justify-between shadow-sm">
                <div class="text-xs font-bold text-gray-500 uppercase">Nivel Combustible</div>
                <div class="text-xl font-black text-slate-800">{{ $orden->nivel_combustible ?? 'N/A' }}</div>
            </div>
        </div>

        <!-- Falla / Solicitud -->
        <div class="relative z-10 mb-8">
            <h3 class="bg-blue-900 text-white text-xs font-black uppercase tracking-widest py-2 px-4 rounded-t-lg">
                <i class="fas fa-clipboard-list mr-2"></i> Solicitud del Cliente / Falla Reportada
            </h3>
            <div class="border border-gray-200 p-4 rounded-b-lg bg-gray-50 min-h-[80px]">
                <p class="text-gray-800 font-medium italic">{{ $orden->falla_cliente }}</p>
            </div>
        </div>

        <!-- Planificación y Costos (New) -->
        @php 
            $granTotal = 0;
            
            // Agrupar Mano de Obra
            $totalManoObra = 0;
            $descripcionesManoObra = [];
            foreach($orden->bitacoras as $task) {
                // Sumar todos los cobros menos los descuentos
                $subt = floatval($task->precio_cliente ?? 0) - floatval($task->descuento_cliente ?? 0);
                $totalManoObra += $subt;
                $descripcionesManoObra[] = trim($task->descripcion);
            }
            $textoManoObra = count($descripcionesManoObra) > 0 ? implode(', ', $descripcionesManoObra) : '';
            $granTotal += $totalManoObra;
        @endphp

        <div class="relative z-10 mb-8">
            <h3 class="bg-slate-800 text-white text-xs font-black uppercase tracking-widest py-2 px-4 rounded-t-lg flex items-center gap-2">
                <i class="fas fa-tools"></i> Presupuesto Estimado
            </h3>
            <div class="border border-gray-200 p-0 rounded-b-lg bg-white overflow-hidden shadow-sm">
                <table class="w-full text-sm text-left" style="page-break-inside: auto;">
                    <thead class="bg-gray-50 text-xs text-gray-500 uppercase font-black border-b border-gray-200">
                        <tr>
                            <th class="px-4 py-3">Descripción</th>
                            <th class="px-4 py-3 text-center">Tipo</th>
                            <th class="px-4 py-3 text-right">Cant.</th>
                            <th class="px-4 py-3 text-right">Precio Unitario</th>
                            <th class="px-4 py-3 text-right">Subtotal</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        
                        <!-- Mano de Obra Consolidada -->
                        @if($totalManoObra > 0 || count($descripcionesManoObra) > 0)
                        <tr class="hover:bg-gray-50">
                            <td class="px-4 py-3 font-medium text-gray-800">
                                <span class="font-bold">Servicios de Mano de Obra</span>
                                <div class="text-[10px] text-gray-500 mt-0.5 leading-tight italic">{{ $textoManoObra }}</div>
                            </td>
                            <td class="px-4 py-3 text-center text-[10px] text-blue-600 font-bold uppercase tracking-widest align-top">Servicio</td>
                            <td class="px-4 py-3 text-right text-gray-500 font-mono align-top">1.00</td>
                            <td class="px-4 py-3 text-right text-gray-500 font-mono align-top">Q.{{ number_format($totalManoObra, 2) }}</td>
                            <td class="px-4 py-3 text-right font-black text-gray-800 font-mono align-top">Q.{{ number_format($totalManoObra, 2) }}</td>
                        </tr>
                        @endif

                        <!-- Repuestos -->
                        @foreach($orden->detalles->where('estado', '!=', 'rechazado') as $pieza)
                        @php 
                            $precioUnitario = floatval($pieza->precio_unitario ?? 0);
                            // Si lo trae el cliente o es de rechazo interno se puede ajustar.
                            if ($pieza->suministrado_por == 'cliente') {
                                $precioUnitario = 0;
                            }
                            $subtotalRep = floatval($pieza->cantidad ?? 1) * $precioUnitario;
                            $granTotal += $subtotalRep;
                        @endphp
                        <tr class="hover:bg-gray-50" style="page-break-inside: avoid;">
                            <td class="px-4 py-3 font-medium text-gray-800 align-top">
                                {{ $pieza->repuesto ? $pieza->repuesto->nombre : $pieza->descripcion_manual }}
                            </td>
                            <td class="px-4 py-3 text-center text-[10px] {{ $pieza->suministrado_por == 'cliente' ? 'text-green-600' : 'text-orange-600' }} font-bold uppercase tracking-widest align-top">
                                Repuesto {{ $pieza->suministrado_por == 'cliente' ? '(Cliente)' : '' }}
                            </td>
                            <td class="px-4 py-3 text-right text-gray-500 font-mono align-top">{{ number_format($pieza->cantidad ?? 1, 2) }}</td>
                            <td class="px-4 py-3 text-right text-gray-500 font-mono align-top">Q.{{ number_format($precioUnitario, 2) }}</td>
                            <td class="px-4 py-3 text-right font-black text-gray-800 font-mono align-top">Q.{{ number_format($subtotalRep, 2) }}</td>
                        </tr>
                        @endforeach
                        
                        @if($orden->bitacoras->isEmpty() && $orden->detalles->isEmpty())
                        <tr>
                            <td colspan="5" class="px-4 py-6 text-center text-gray-400 font-medium italic">En evaluación, ingrese repuestos y mano de obra a facturar.</td>
                        </tr>
                        @endif
                    </tbody>
                    @if($granTotal > 0)
                    <tfoot class="bg-blue-50/50 border-t-2 border-slate-800">
                        <tr>
                            <td colspan="4" class="px-4 py-4 text-right font-black text-slate-800 uppercase tracking-widest text-xs">Total Estimado</td>
                            <td class="px-4 py-4 text-right font-black text-blue-700 font-mono text-xl">Q.{{ number_format($granTotal, 2) }}</td>
                        </tr>
                    </tfoot>
                    @endif
                </table>
            </div>
            @if($granTotal > 0)
            <p class="text-[9px] text-gray-400 mt-1 italic text-right">* Precios sujetos a modificación si se encuentran daños mecánicos/eléctricos ocultos durante el desarme inicial.</p>
            @endif
        </div>

        <!-- Inventory Checklist -->
        @php
            $inventario = $orden->inventario_recepcion;
            if (is_string($inventario)) {
                $inventario = json_decode($inventario, true);
            }
        @endphp

        @if(is_array($inventario) && count($inventario) > 0)
            <div class="relative z-10 mb-8 page-break-inside-avoid">
                <h3 class="text-xs font-black text-gray-500 uppercase tracking-widest border-b-2 border-gray-200 pb-1 mb-4">
                    Inventario de Recepción
                </h3>
                <div class="grid grid-cols-3 gap-2 text-xs">
                    @foreach($inventario as $key => $val)
                        @if(is_array($val))
                            <!-- Special cases like documents -->
                            @foreach($val as $subKey => $subVal)
                                <div class="flex items-center gap-2">
                                    <i class="far {{ $subVal ? 'fa-check-square text-blue-900' : 'fa-square text-gray-300' }}"></i>
                                    <span class="uppercase font-semibold {{ $subVal ? 'text-gray-800' : 'text-gray-400' }}">
                                        {{ str_replace('_', ' ', $key) }} ({{ $subKey }})
                                    </span>
                                </div>
                            @endforeach
                        @else
                            <div class="flex items-center gap-2">
                                <i
                                    class="far {{ ($val == 1 || $val === 'true') ? 'fa-check-square text-blue-900' : 'fa-square text-gray-300' }}"></i>
                                <span
                                    class="uppercase font-semibold {{ ($val == 1 || $val === 'true') ? 'text-gray-800' : 'text-gray-400' }}">
                                    {{ str_replace('_', ' ', $key) }}
                                </span>
                            </div>
                        @endif
                    @endforeach
                </div>
            </div>
        @endif

        <!-- Damage Report (Visual) -->
        <div class="relative z-10 mb-8 page-break-inside-avoid">
            <h3 class="text-xs font-black text-gray-500 uppercase tracking-widest border-b-2 border-gray-200 pb-1 mb-4">
                Reporte de Daños
            </h3>
            <div class="grid grid-cols-2 gap-4">
                @if($orden->danos_imagen_url)
                    <div class="border border-gray-200 rounded-lg p-1">
                        <p class="text-[10px] text-center text-gray-400 mb-1 uppercase font-bold">Diagrama de Daños</p>
                        <img src="{{ $orden->danos_imagen_url }}" class="w-full h-auto object-contain bg-gray-50 rounded">
                    </div>
                @endif

                @if($orden->archivos->count() > 0)
                    <div class="border border-gray-200 rounded-lg p-1">
                        <p class="text-[10px] text-center text-gray-400 mb-1 uppercase font-bold">Evidencia Fotográfica</p>
                        <div class="grid grid-cols-3 gap-1">
                            @foreach($orden->archivos->take(6) as $archivo)
                                <img src="{{ asset($archivo->url) }}"
                                    class="w-full h-20 object-cover rounded border border-gray-100">
                            @endforeach
                        </div>
                        @if($orden->archivos->count() > 6)
                            <p class="text-[10px] text-center mt-1 text-gray-400">+ {{ $orden->archivos->count() - 6 }} fotos
                                adicionales</p>
                        @endif
                    </div>
                @endif
            </div>
        </div>

        <!-- Footer Signatures -->
        <div class="relative z-10 mt-12 grid grid-cols-2 gap-20 pt-12">
            <div class="text-center pt-8 border-t border-gray-300">
                <p class="font-bold text-gray-800">{{ $orden->cliente->nombre_completo }}</p>
                <p class="text-xs text-gray-400 uppercase tracking-wider mt-1">Firma de Cliente / Aceptación</p>
                <p class="text-[10px] text-gray-400 mt-2 leading-tight px-4">
                    Al firmar acepta los términos y condiciones de servicio. El taller no se hace responsable por
                    objetos de valor no reportados en el inventario.
                </p>
            </div>
            <div class="text-center pt-8 border-t border-gray-300">
                <p class="font-bold text-gray-800">{{ $orden->receptor->name ?? 'Asesor de Servicio' }}</p>
                <p class="text-xs text-gray-400 uppercase tracking-wider mt-1">Firma de Receptor</p>
                <p class="text-[10px] text-gray-400 mt-2">
                    TECNIMECÁNIC A CALIFORNIA - El arte de la ingeniería alemana.
                </p>
            </div>
        </div>

    </div>

    <script>
        window.onload = function() {
            setTimeout(() => {
                window.print();
            }, 500); // Slight delay to ensure images render
        };
    </script>
</body>

</html>