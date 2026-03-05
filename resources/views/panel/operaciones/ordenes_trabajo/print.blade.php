<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Orden #{{ $orden->codigo_orden }}</title>
    <!-- Use a reliable FontAwesome CDN for print -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap');

        body {
            font-family: 'Inter', sans-serif;
            background-color: #f3f4f6;
            color: #1f2937;
            margin: 0;
            padding: 0;
            -webkit-print-color-adjust: exact;
        }

        @media print {
            body { background-color: white !important; }
            .no-print { display: none !important; }
            .page-container {
                box-shadow: none !important;
                margin: 0 !important;
                padding: 1cm !important; /* Reduced padding for more space */
                width: 100% !important;
                max-width: none !important;
                min-height: auto !important;
            }
            .page-break { page-break-before: always; }
        }

        .page-container {
            max-width: 210mm;
            min-height: 297mm;
            margin: 20px auto;
            background: white;
            padding: 1.5cm;
            box-shadow: 0 4px 6px -1px rgb(0 0 0 / 0.1);
            display: flex;
            flex-direction: column;
        }

        .header-title {
            font-size: 24px;
            font-weight: 800;
            letter-spacing: -0.025em;
            color: #0f172a;
        }

        .section-header {
            background-color: #f8fafc;
            border-bottom: 2px solid #e2e8f0;
            padding: 8px 12px;
            margin: 20px 0 12px 0;
            font-size: 11px;
            font-weight: 800;
            color: #0f172a;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .label-small { color: #64748b; font-size: 10px; font-weight: 700; text-transform: uppercase; }
        .data-value { color: #1e293b; font-size: 13px; font-weight: 600; }

        /* Placa Refinada */
        .placa-box {
            border: 2px solid #1e293b;
            color: #1e293b;
            padding: 4px 12px;
            border-radius: 4px;
            display: inline-block;
            font-family: 'Inter', sans-serif;
            font-size: 18px;
            font-weight: 800;
            letter-spacing: 1px;
            background: #fff;
        }

        /* Foto Grid Refinado */
        .inspection-container {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 15px;
            margin-top: 10px;
        }

        .damage-diagram-box {
            border: 1px solid #e2e8f0;
            border-radius: 12px;
            padding: 10px;
            background: #fcfcfc;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            height: 250px;
        }

        .photo-list {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 10px;
        }

        .photo-item img {
            width: 100%;
            height: 120px;
            object-fit: cover;
            border-radius: 8px;
            border: 1px solid #e2e8f0;
        }

        /* Tabla de Presupuesto Limpia */
        .budget-table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0;
            border: 1px solid #e2e8f0;
            border-radius: 12px;
            overflow: hidden;
        }

        .budget-table th {
            background-color: #f8fafc;
            padding: 12px 15px;
            font-size: 10px;
            font-weight: 800;
            color: #64748b;
            text-transform: uppercase;
            border-bottom: 1px solid #e2e8f0;
        }

        .budget-table td {
            padding: 15px;
            border-bottom: 1px solid #f1f5f9;
            vertical-align: top;
        }

        .budget-total-row td {
            background-color: #0f172a;
            color: white;
            padding: 20px 15px;
        }

        .badge {
            font-size: 9px;
            font-weight: 800;
            padding: 2px 8px;
            border-radius: 4px;
            text-transform: uppercase;
        }
        .badge-service { background: #eff6ff; color: #1d4ed8; }
        .badge-parts { background: #fff7ed; color: #c2410c; }
        .badge-client { background: #f0fdf4; color: #166534; }

        .signature-section {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 40px;
            margin-top: auto;
            padding-top: 40px;
        }

        .signature-box {
            text-align: center;
        }
        .signature-line {
            border-top: 1px solid #cbd5e1;
            margin-bottom: 8px;
        }
    </style>
</head>

<body>

    <div class="fixed top-4 right-4 z-50 no-print">
        <button onclick="window.print()" class="bg-blue-600 text-white px-8 py-3 rounded-xl font-bold shadow-xl hover:bg-blue-700">
            <i class="fas fa-print mr-2"></i> Procesar Impresión
        </button>
    </div>

    <!-- PÁGINA 1: RECEPCIÓN -->
    <div class="page-container">
        <!-- Encabezado -->
        <div class="flex justify-between items-start mb-6 border-b pb-4">
            <div class="flex items-center gap-4">
                <img src="https://upload.wikimedia.org/wikipedia/commons/4/44/BMW.svg.png" class="w-12 h-12 object-contain">
                <div>
                    <div class="header-title">TECNIMECÁNICA CALIFORNIA</div>
                    <div class="text-[10px] text-slate-400 uppercase font-bold tracking-widest mt-1">Especialistas en ingeniería alemana</div>
                </div>
            </div>
            <div class="text-right">
                <div class="text-[10px] font-black text-blue-600 uppercase">Orden de Trabajo</div>
                <div class="text-2xl font-black text-slate-900">#{{ $orden->codigo_orden }}</div>
            </div>
        </div>

        <!-- Datos principales -->
        <div class="grid grid-cols-2 gap-8">
            <div class="space-y-1">
                <div class="section-header" style="margin-top:0"><i class="fas fa-user-check"></i> Propietario</div>
                <div class="grid grid-cols-3 gap-2">
                    <span class="label-small">Nombre:</span>
                    <span class="data-value col-span-2">{{ $orden->cliente->nombre_completo }}</span>
                    <span class="label-small">Teléfono:</span>
                    <span class="data-value col-span-2">{{ $orden->cliente->telefono }}</span>
                    <span class="label-small">Email:</span>
                    <span class="data-value col-span-2 truncate text-[11px]">{{ $orden->cliente->email ?? 'N/A' }}</span>
                </div>
            </div>
            <div class="space-y-1">
                <div class="section-header" style="margin-top:0"><i class="fas fa-car-side"></i> Vehículo</div>
                <div class="grid grid-cols-3 gap-2">
                    <span class="label-small">Unidad:</span>
                    <span class="data-value col-span-2">{{ $orden->vehiculo->marca->nombre ?? '' }} {{ $orden->vehiculo->modelo->nombre ?? '' }}</span>
                    <span class="label-small">Año/Color:</span>
                    <span class="data-value col-span-2">{{ $orden->vehiculo->anio }} | {{ $orden->color }}</span>
                    <span class="label-small">Placas:</span>
                    <span class="col-span-2"><span class="placa-box">{{ $orden->vehiculo->placa }}</span></span>
                </div>
            </div>
        </div>

        <!-- Estado -->
        <div class="grid grid-cols-2 gap-8 mt-4">
            <div class="bg-slate-50 p-4 rounded-xl flex justify-between items-center">
                <span class="label-small">KMS Entrada:</span>
                <span class="text-xl font-black font-mono">{{ number_format($orden->kilometraje_entrada) }}</span>
            </div>
            <div class="bg-slate-50 p-4 rounded-xl flex justify-between items-center">
                <span class="label-small">Combustible:</span>
                <span class="text-lg font-black">{{ $orden->nivel_combustible ?? 'N/A' }}</span>
            </div>
        </div>

        <!-- Falla -->
        <div class="section-header"><i class="fas fa-exclamation-circle"></i> Síntomas y Diagnóstico Inicial</div>
        <div class="p-4 bg-white border border-slate-200 rounded-xl italic text-slate-700 text-sm leading-relaxed">
            "{{ $orden->falla_cliente }}"
        </div>

        <!-- Inventario - Condensado -->
        @php
            $inventario = $orden->inventario_recepcion;
            if (is_string($inventario)) { $inventario = json_decode($inventario, true); }
        @endphp
        @if(is_array($inventario))
        <div class="section-header"><i class="fas fa-clipboard-list"></i> Inventario de Accesorios</div>
        <div class="grid grid-cols-5 gap-y-3 gap-x-2 px-2">
            @foreach($inventario as $key => $val)
                @if(!is_array($val))
                <div class="flex items-center gap-1.5 text-[10px] {{ ($val == 1 || $val === 'true') ? 'font-bold text-slate-800' : 'text-slate-300' }}">
                    <i class="fas {{ ($val == 1 || $val === 'true') ? 'fa-check-circle text-blue-500' : 'fa-circle-notch text-slate-200' }}"></i>
                    <span class="uppercase truncate">{{ str_replace('_', ' ', $key) }}</span>
                </div>
                @endif
            @endforeach
        </div>
        @endif

        <!-- Daños y Fotos - REDISEÑADO -->
        <div class="section-header"><i class="fas fa-camera"></i> Evidencia Visual de Recepción</div>
        <div class="inspection-container">
            <div class="damage-diagram-box">
                <span class="label-small mb-4">Diagrama de Carrocería</span>
                @if($orden->danos_imagen_url)
                    <img src="{{ $orden->danos_imagen_url }}" style="max-width:90%; max-height:180px; object-contain;">
                @else
                    <div class="text-slate-300 text-[10px] italic text-center">Sin diagrama<br>registrado</div>
                @endif
            </div>
            
            <div class="photo-list">
                @forelse($orden->archivos->take(4) as $archivo)
                    <div class="photo-item">
                        <img src="{{ asset($archivo->url) }}" alt="Evidencia">
                    </div>
                @empty
                    <div class="col-span-2 flex items-center justify-center h-[250px] bg-slate-50 border border-dashed border-slate-200 rounded-xl text-slate-400 text-[11px] italic">
                        Sin fotografías registradas en la recepción
                    </div>
                @endforelse
            </div>
        </div>

        <!-- Firma -->
        <div class="signature-section">
            <div class="signature-box">
                <div class="signature-line"></div>
                <div class="text-[10px] font-black uppercase text-slate-800">Firma del Propietario</div>
                <div class="text-[8px] text-slate-400 mt-1 italic leading-tight">Autorizo las pruebas y acepto el inventario detallado</div>
            </div>
            <div class="signature-box">
                <div class="signature-line"></div>
                <div class="text-[10px] font-black uppercase text-slate-800">Responsable de Recepción</div>
                <div class="text-[8px] text-slate-400 mt-1">Sello y Firma - Tecnimecánica California</div>
            </div>
        </div>
    </div>

    <!-- PÁGINA 2: PRESUPUESTO -->
    <div class="page-break"></div>
    <div class="page-container">
        <!-- Header Presupuesto -->
        <div class="flex justify-between items-center mb-8 border-b pb-4">
            <div class="flex items-center gap-3">
                <div class="bg-blue-600 text-white w-10 h-10 rounded-lg flex items-center justify-center font-black">P</div>
                <div>
                    <div class="text-xl font-black text-slate-900 uppercase">Presupuesto Estimado</div>
                    <div class="text-[10px] text-slate-400 font-bold uppercase tracking-widest">Orden de Servicio #{{ $orden->codigo_orden }}</div>
                </div>
            </div>
            <div class="text-right">
                <div class="text-[9px] font-bold text-slate-300 uppercase">Documento Preliminar</div>
                <div class="text-xs font-bold text-slate-500 mt-1">{{ now()->format('d/m/Y H:i A') }}</div>
            </div>
        </div>

        @php 
            $granTotal = 0;
            $totalManoObra = 0;
            $descripcionesManoObra = [];
            foreach($orden->bitacoras as $task) {
                $subt = floatval($task->precio_cliente ?? 0) - floatval($task->descuento_cliente ?? 0);
                $totalManoObra += $subt;
                $descripcionesManoObra[] = $task->descripcion;
            }
            $granTotal += $totalManoObra;
        @endphp

        <!-- Datos del presupuesto -->
        <div class="grid grid-cols-2 gap-6 mb-8">
            <div class="bg-slate-50 p-5 rounded-2xl">
                <span class="label-small block mb-1">Cliente / Propietario</span>
                <span class="text-lg font-black uppercase italic">{{ $orden->cliente->nombre_completo }}</span>
            </div>
            <div class="bg-slate-50 p-5 rounded-2xl">
                <span class="label-small block mb-1">Unidad a Reparar</span>
                <span class="text-lg font-black uppercase italic">{{ $orden->vehiculo->placa }} | {{ $orden->vehiculo->marca->nombre ?? '' }}</span>
            </div>
        </div>

        <table class="budget-table">
            <thead>
                <tr>
                    <th width="50%">Descripción del Cargo</th>
                    <th width="15%" class="text-center">Categoría</th>
                    <th width="10%" class="text-right">Cant.</th>
                    <th width="25%" class="text-right">Subtotal</th>
                </tr>
            </thead>
            <tbody>
                <!-- Mano de Obra -->
                @if($totalManoObra > 0)
                <tr>
                    <td>
                        <div class="font-black text-slate-800 text-sm uppercase mb-1">Mano de Obra Certificada</div>
                        <div class="text-[11px] text-slate-500 italic leading-relaxed">{{ implode(', ', $descripcionesManoObra) }}</div>
                    </td>
                    <td class="text-center"><span class="badge badge-service">Servicio</span></td>
                    <td class="text-right font-mono font-bold text-slate-400">1.00</td>
                    <td class="text-right font-mono font-black text-lg text-slate-900 italic">Q.{{ number_format($totalManoObra, 2) }}</td>
                </tr>
                @endif

                <!-- Repuestos -->
                @foreach($orden->detalles->where('estado', '!=', 'rechazado') as $pieza)
                @php 
                    $precioUnitario = floatval($pieza->suministrado_por == 'cliente' ? 0 : ($pieza->precio_unitario ?? 0));
                    $subtRep = floatval($pieza->cantidad ?? 1) * $precioUnitario;
                    $granTotal += $subtRep;
                @endphp
                <tr>
                    <td>
                        <div class="font-black text-slate-700 text-xs uppercase">{{ $pieza->repuesto ? $pieza->repuesto->nombre : $pieza->descripcion_manual }}</div>
                        <div class="text-[9px] text-slate-400 font-bold uppercase mt-1">{{ $pieza->repuesto ? $pieza->repuesto->codigo : 'CONSUMO DIRECTO' }}</div>
                    </td>
                    <td class="text-center">
                        <span class="badge {{ $pieza->suministrado_por == 'cliente' ? 'badge-client' : 'badge-parts' }}">
                            {{ $pieza->suministrado_por == 'cliente' ? 'Propio' : 'Almacén' }}
                        </span>
                    </td>
                    <td class="text-right font-mono font-bold text-slate-600">{{ number_format($pieza->cantidad ?? 1, 2) }}</td>
                    <td class="text-right font-mono font-black text-lg text-slate-900 italic">Q.{{ number_format($subtRep, 2) }}</td>
                </tr>
                @endforeach
            </tbody>
            <tfoot>
                <tr class="budget-total-row">
                    <td colspan="3" class="text-right align-middle">
                        <span class="text-xs font-black uppercase tracking-[0.3em] text-blue-400">Inversión Total Estimada</span>
                    </td>
                    <td class="text-right">
                        <div class="text-3xl font-black italic tracking-tighter">Q.{{ number_format($granTotal, 2) }}</div>
                    </td>
                </tr>
            </tfoot>
        </table>

        <div class="mt-8 grid grid-cols-2 gap-10">
            <div class="bg-blue-50/50 p-6 rounded-2xl border border-blue-100 italic text-[10px] text-slate-600 leading-relaxed shadow-sm">
                <strong>CLAUSULA DE PRESUPUESTO:</strong> Esta estimación se basa en la inspección visual. Cualquier reparación adicional detectada durante la ejecución será notificada de inmediato. Los precios de repuestos pueden variar sin previo aviso según disponibilidad de proveedor.
            </div>
            
            <div class="flex flex-col justify-end gap-6 text-center px-10">
                <div class="signature-line"></div>
                <div class="text-[10px] font-black uppercase text-slate-800">Autorización del Cliente</div>
            </div>
        </div>
    </div>

    <script>
        window.onload = function() {
            setTimeout(() => {
                window.print();
            }, 800);
        };
    </script>
</body>

</html>