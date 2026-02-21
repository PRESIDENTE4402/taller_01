<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Historial de Trabajo - {{ $worker->persona ? $worker->persona->nombres : $worker->name }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        @media print {
            body {
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }

            .no-print {
                display: none;
            }

            .page-break {
                page-break-before: always;
            }
        }
    </style>
</head>

<body class="bg-gray-100 font-sans text-gray-800">

    <div class="fixed top-4 right-4 z-50 no-print flex gap-2">
        <button onclick="window.print()" class="bg-blue-600 text-white px-6 py-2 rounded-full shadow-lg font-bold hover:bg-blue-700 transition-all flex items-center gap-2">
            <i class="fas fa-print"></i> Imprimir a PDF
        </button>
        <button onclick="window.close()" class="bg-gray-500 text-white px-6 py-2 rounded-full shadow-lg font-bold hover:bg-gray-600 transition-all">
            Cerrar
        </button>
    </div>

    <div class="max-w-[210mm] mx-auto bg-white shadow-xl my-8 p-10 min-h-screen relative overflow-hidden">

        <!-- Header -->
        <div class="border-b-4 border-blue-600 pb-6 mb-8 flex justify-between items-center">
            <div>
                <h1 class="text-3xl font-black text-gray-900 tracking-tighter italic uppercase">REPORTE DE ACTIVIDADES</h1>
                <p class="text-blue-600 font-bold uppercase text-xs tracking-widest mt-1">
                    Historial del Colaborador
                    @if($desde || $hasta)
                    <span class="text-gray-400 block mt-1">
                        ( Periodo: {{ $desde ? \Carbon\Carbon::parse($desde)->format('d/m/Y') : 'Inicio' }} - {{ $hasta ? \Carbon\Carbon::parse($hasta)->format('d/m/Y') : 'Actualidad' }} )
                    </span>
                    @endif
                </p>
            </div>
            <div class="text-right">
                <p class="text-[10px] font-black text-gray-400 uppercase">Generado el</p>
                <p class="text-xs font-bold">{{ now()->format('d/m/Y H:i A') }}</p>
            </div>
        </div>

        <!-- Worker Info -->
        <div class="bg-gray-50 p-6 rounded-2xl border border-gray-100 mb-8 flex items-center gap-6">
            <div class="w-16 h-16 rounded-2xl bg-blue-600 flex items-center justify-center text-2xl font-black text-white uppercase">
                {{ substr($worker->persona->nombres ?? $worker->name, 0, 1) }}
            </div>
            <div class="grid grid-cols-2 gap-x-12 flex-1">
                <div>
                    <label class="text-[8px] font-black text-gray-400 uppercase block mb-1">Nombre Completo</label>
                    <p class="text-sm font-black text-gray-800 uppercase">{{ $worker->persona ? $worker->persona->nombres . ' ' . $worker->persona->apellidos : $worker->name }}</p>
                </div>
                <div>
                    <label class="text-[8px] font-black text-gray-400 uppercase block mb-1">Sucursal / Sede</label>
                    <p class="text-sm font-bold text-gray-600 uppercase">{{ $worker->sucursales->first()->nombre ?? 'General' }}</p>
                </div>
            </div>
        </div>

        <!-- History Table -->
        <table class="w-full text-left">
            <thead>
                <tr class="bg-gray-900 text-white text-[9px] font-black uppercase tracking-widest">
                    <th class="py-3 px-4 rounded-tl-lg">Fecha</th>
                    <th class="py-3 px-4">Descripción de la Actividad</th>
                    <th class="py-3 px-4 text-center">Referencia</th>
                    <th class="py-3 px-4 text-center">Duración</th>
                    <th class="py-3 px-4 rounded-tr-lg text-center">Estado</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100 text-xs">
                @foreach($historico as $tarea)
                <tr>
                    <td class="py-4 px-4 font-bold text-gray-800">
                        {{ $tarea->created_at->format('d/m/Y') }}<br>
                        <span class="text-[9px] text-gray-400">{{ $tarea->created_at->format('H:i A') }}</span>
                    </td>
                    <td class="py-4 px-4">
                        <p class="font-bold text-gray-800 italic">"{{ $tarea->descripcion }}"</p>
                        <span class="text-[9px] font-black text-blue-500 uppercase">{{ $tarea->tipo_actividad }}</span>
                    </td>
                    <td class="py-4 px-4 text-center">
                        @if($tarea->orden)
                        <span class="font-black text-blue-600 underline">OT-{{ $tarea->orden->codigo_orden }}</span>
                        @else
                        <span class="text-gray-300">---</span>
                        @endif
                    </td>
                    <td class="py-4 px-4 text-center font-mono font-bold">{{ $tarea->minutos_totales ?? '--' }} min</td>
                    <td class="py-4 px-4 text-center">
                        <span class="text-[9px] font-black uppercase text-{{ $tarea->estado == 'completado' ? 'green' : ($tarea->estado == 'en_progreso' ? 'blue' : 'orange') }}-600">
                            {{ $tarea->estado }}
                        </span>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>

        <!-- Footer -->
        <div class="mt-12 pt-8 border-t border-gray-100 flex justify-between items-end">
            <div>
                <p class="text-[10px] font-bold text-gray-400 uppercase italic">Firma del Supervisor</p>
                <div class="mt-8 border-b border-gray-300 w-48"></div>
            </div>
            <div class="text-right">
                <p class="text-[9px] font-black text-gray-400 uppercase">Tecnimecánica California</p>
                <p class="text-[8px] text-gray-300 tracking-tighter">Sistema de Gestión de Taller v2.0</p>
            </div>
        </div>

    </div>

    <script>
        window.onload = function() {
            setTimeout(() => {
                // window.print(); // Descomentar para auto-impresión
            }, 1000);
        };
    </script>
</body>

</html>