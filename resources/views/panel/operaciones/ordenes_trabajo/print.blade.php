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
            background-color: white; /* Changed to white to avoid grey boxes */
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
                padding: 1cm !important;
                width: 100% !important;
                max-width: none !important;
                min-height: auto !important;
            }
            .page-break { page-break-before: always; }
        }

        .page-container {
            max-width: 210mm;
            min-height: 297mm;
            margin: 0 auto; /* Removed margin top/bottom for preview */
            background: white;
            padding: 1.5cm;
            box-shadow: none; /* No shadow */
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

        /* Foto Gallery Full Width */
        .photo-gallery {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 15px;
            margin-top: 10px;
        }

        .photo-item img {
            width: 100%;
            height: 180px;
            object-fit: cover;
            border-radius: 12px;
            border: 1px solid #e2e8f0;
        }

        .no-photos {
            grid-column: span 3;
            height: 100px;
            display: flex;
            align-items: center;
            justify-content: center;
            border: 2px dashed #e2e8f0;
            border-radius: 12px;
            color: #94a3b8;
            font-style: italic;
            font-size: 12px;
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
            text-align: left;
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
            gap: 80px; /* Wider gap */
            margin-top: auto;
            padding-top: 100px; /* Big space for signatures */
        }

        .signature-box {
            text-align: center;
        }
        .signature-line {
            border-top: 2px solid #cbd5e1;
            margin-bottom: 10px;
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
                <div class="text-sm font-bold text-slate-400 mt-1">{{ now()->format('d/m/Y') }}</div>
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
                <span class="text-xl font-black font-mono">{{ number_format($orden->kilometraje_entrada) }} <small class="text-xs">KM</small></span>
            </div>
            <div class="bg-slate-50 p-4 rounded-xl flex justify-between items-center">
                <span class="label-small">Combustible:</span>
                <span class="text-lg font-black">{{ $orden->nivel_combustible ?? 'N/A' }}</span>
            </div>
        </div>

        <!-- Falla -->
        <div class="section-header"><i class="fas fa-exclamation-circle"></i> Síntomas y Diagnóstico Inicial</div>
        <div class="p-4 bg-white border border-slate-100 rounded-xl italic text-slate-700 text-sm leading-relaxed">
            "{{ $orden->falla_cliente }}"
        </div>

        <!-- Inventario -->
        @php
            $inventario = $orden->inventario_recepcion;
            if (is_string($inventario)) { $inventario = json_decode($inventario, true); }
        @endphp
        @if(is_array($inventario))
        <div class="section-header"><i class="fas fa-clipboard-list"></i> Checklist de Accesorios</div>
        <div class="grid grid-cols-5 gap-y-3 gap-x-2 px-2">
            @foreach($inventario as $key => $val)
                @if(!is_array($val))
                <div class="flex items-center gap-1.5 text-[10px] {{ ($val == 1 || $val === 'true') ? 'font-bold text-slate-800' : 'text-slate-300' }}">
                    <i class="fas {{ ($val == 1 || $val === 'true') ? 'fa-check-circle text-blue-500' : 'fa-circle-notch text-slate-100' }}"></i>
                    <span class="uppercase truncate">{{ str_replace('_', ' ', $key) }}</span>
                </div>
                @endif
            @endforeach
        </div>
        @endif

        <!-- Fotos -->
        <div class="section-header"><i class="fas fa-camera"></i> Evidencia Fotográfica de Ingreso</div>
        <div class="photo-gallery">
            @forelse($orden->archivos as $archivo)
                <div class="photo-item">
                    <img src="{{ asset($archivo->url) }}" alt="Evidencia">
                </div>
            @empty
                <div class="no-photos">Sin fotografías registradas en la recepción</div>
            @endforelse
        </div>

        <!-- Firma -->
        <div class="signature-section">
            <div class="signature-box">
                <div class="signature-line"></div>
                <div class="text-[11px] font-black uppercase text-slate-800">Firma del Propietario</div>
                <div class="text-[9px] text-slate-400 mt-2 italic leading-tight px-10">Acepto los términos de servicio e inventario detallado.</div>
            </div>
            <div class="signature-box">
                <div class="signature-line"></div>
                <div class="text-[11px] font-black uppercase text-slate-800">Responsable de Recepción</div>
                <div class="text-[9px] text-slate-400 mt-2 px-10">Sello y Firma - Tecnimecánica California</div>
            </div>
        </div>
    </div>

    <!-- PÁGINA 2: PRESUPUESTO -->
    <div class="page-break"></div>
    <div class="page-container">
        <!-- Header Presupuesto -->
        <div class="flex justify-between items-center mb-10 pb-6 border-b-2 border-slate-50">
            <div class="flex items-center gap-3">
                <div class="bg-blue-600 text-white w-10 h-10 rounded-lg flex items-center justify-center font-black">P</div>
                <div>
                    <div class="text-xl font-black text-slate-900 uppercase">Presupuesto Estimado</div>
                    <div class="text-[10px] text-slate-400 font-bold uppercase tracking-widest mt-0.5">Orden #{{ $orden->codigo_orden }}</div>
                </div>
            </div>
            <div class="text-right">
                <div class="text-[10px] font-black text-slate-300 uppercase">Documento Informativo</div>
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
            <div class="bg-slate-50 p-5 rounded-2xl border border-slate-100">
                <span class="label-small block mb-1">Cliente / Propietario</span>
                <span class="text-lg font-black uppercase italic">{{ $orden->cliente->nombre_completo }}</span>
            </div>
            <div class="bg-slate-50 p-5 rounded-2xl border border-slate-100">
                <span class="label-small block mb-1">Unidad a Reparar</span>
                <span class="text-lg font-black uppercase italic">{{ $orden->vehiculo->placa }} | {{ $orden->vehiculo->marca->nombre ?? '' }}</span>
            </div>
        </div>

        <table class="budget-table">
            <thead>
                <tr>
                    <th width="50%">Descripción del Cargo</th>
                    <th width="15%" style="text-align:center">Origen</th>
                    <th width="10%" style="text-align:right">Cant.</th>
                    <th width="25%" style="text-align:right">Subtotal</th>
                </tr>
            </thead>
            <tbody>
                <!-- Mano de Obra -->
                @if($totalManoObra > 0)
                <tr>
                    <td>
                        <div class="font-black text-slate-800 text-base uppercase mb-1">Mano de Obra Certificada</div>
                        <div class="text-[11px] text-slate-500 italic leading-relaxed">{{ implode(', ', $descripcionesManoObra) }}</div>
                    </td>
                    <td style="text-align:center; vertical-align:middle"><span class="badge badge-service">Servicio</span></td>
                    <td style="text-align:right; vertical-align:middle" class="font-mono font-bold text-slate-400">GLB</td>
                    <td style="text-align:right; vertical-align:middle" class="font-mono font-black text-xl text-slate-900 italic">Q.{{ number_format($totalManoObra, 2) }}</td>
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
                        <div class="font-black text-slate-700 text-sm uppercase">{{ $pieza->repuesto ? $pieza->repuesto->nombre : $pieza->descripcion_manual }}</div>
                        <div class="text-[9px] text-slate-400 font-bold uppercase mt-1">{{ $pieza->repuesto ? $pieza->repuesto->codigo : 'SUMINISTRO DIRECTO' }}</div>
                    </td>
                    <td style="text-align:center; vertical-align:middle">
                        <span class="badge {{ $pieza->suministrado_por == 'cliente' ? 'badge-client' : 'badge-parts' }}">
                            {{ $pieza->suministrado_por == 'cliente' ? 'Propio' : 'Almacén' }}
                        </span>
                    </td>
                    <td style="text-align:right; vertical-align:middle" class="font-mono font-bold text-slate-600">{{ number_format($pieza->cantidad ?? 1, 2) }}</td>
                    <td style="text-align:right; vertical-align:middle" class="font-mono font-black text-xl text-slate-900 italic">Q.{{ number_format($subtRep, 2) }}</td>
                </tr>
                @endforeach
            </tbody>
            <tfoot>
                <tr class="budget-total-row">
                    <td colspan="3" style="text-align:right; vertical-align:middle">
                        <span class="text-xs font-black uppercase tracking-[0.4em] text-blue-400">Total Inversión Estimada</span>
                    </td>
                    <td style="text-align:right; vertical-align:middle">
                        <div class="text-4xl font-black italic tracking-tighter">Q.{{ number_format($granTotal, 2) }}</div>
                    </td>
                </tr>
            </tfoot>
        </table>

        <div class="mt-12 space-y-12">
            <div class="p-6 bg-slate-50 border-2 border-slate-100 rounded-2xl italic text-[10px] text-slate-500 leading-relaxed max-w-2xl">
                <strong>NOTA DE PRESUPUESTO:</strong> Este presupuesto es una estimación sujeta a cambios tras el desarme y diagnóstico avanzado. Los precios tienen una vigencia de 48 horas. Al autorizar este servicio, el cliente acepta un margen de tolerancia sugerido por hallazgos mecánicos.
            </div>
            
            <div class="grid grid-cols-2 gap-20">
                <div class="text-center">
                    <div class="signature-line"></div>
                    <div class="text-[11px] font-black uppercase text-slate-800">Autorización del Cliente</div>
                    <div class="text-[9px] text-slate-400 mt-1">Firma y Aceptación de Cargos</div>
                </div>
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