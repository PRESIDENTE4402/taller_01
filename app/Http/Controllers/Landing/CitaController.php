<?php

namespace App\Http\Controllers\Landing;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Cliente;
use App\Models\Vehiculo;
use App\Models\Cita;
use App\Models\MarcaVehiculo;
use App\Models\ModeloVehiculo;
use App\Models\VersionVehiculo;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class CitaController extends Controller
{
    public function store(Request $request)
    {
        // 1. Validación estricta con verificación de dominio de correo y formato de teléfono
        $validator = \Illuminate\Support\Facades\Validator::make($request->all(), [
            'nombre' => 'required|string|min:3|max:100',
            // rfc,dns: verifica que el correo esté bien escrito Y que el dominio (ej. gmail.com) realmente exista en internet
            'email' => 'required|email:rfc,dns', 
            // regex: Permite opcionalmente un +, seguido de entre 8 y 15 números y opcionalmente espacios
            'telefono' => ['required', 'string', 'regex:/^[\+]?[(]?[0-9]{3}[)]?[-\s\.]?[0-9]{3}[-\s\.]?[0-9]{4,6}$|^[0-9]{8,12}$/'], 
            'placa' => 'required|string',
            'marca' => 'required|string',
            'modelo' => 'nullable|string',
            'version' => 'nullable|string',
            'motivo' => 'required|string|min:5',
            'fecha' => 'required|date',
            'hora' => 'required|string',
            'sucursal_id' => 'required|exists:sucursales,id'
        ], [
            'email.email' => 'El correo electrónico ingresado no es válido o su dominio no existe.',
            'telefono.regex' => 'El número de teléfono debe ser real y contener al menos 8 dígitos numéricos.',
            'nombre.min' => 'Por favor ingrese su nombre y apellido.',
            'motivo.min' => 'Por favor describa el motivo de la cita detalladamente.'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()->first()
            ], 422);
        }

        $validated = $validator->validated();


        // === PREVENCIÓN DE SPAM Y DUPLICIDAD ===
        $placaLimpia = strtoupper(str_replace([' ', '-'], '', $validated['placa']));
        
        // A. Evitar que el mismo vehículo tenga múltiples citas pendientes
        $vehiculoExistente = Vehiculo::where('placa', $placaLimpia)->first();
        if ($vehiculoExistente) {
            $citaPendiente = Cita::where('vehiculo_id', $vehiculoExistente->id)
                ->whereIn('estado', ['pendiente', 'confirmada'])
                ->exists();
                
            if ($citaPendiente) {
                return response()->json([
                    'success' => false,
                    'message' => 'Este vehículo (Placa: '.$placaLimpia.') ya tiene una cita activa. Por favor espere a ser atendido o contáctenos directamente.'
                ], 422);
            }
        }

        // B. Evitar que el mismo correo o teléfono agende de forma masiva (Máx 2 citas en 24 horas)
        $citasRecientes = Cita::whereHas('cliente', function($query) use ($validated) {
            $query->where('email', $validated['email'])
                  ->orWhere('telefono', $validated['telefono']);
        })->where('created_at', '>=', Carbon::now()->subHours(24))->count();

        if ($citasRecientes >= 2) {
            return response()->json([
                'success' => false,
                'message' => 'Por motivos de seguridad, ha alcanzado el límite de citas permitidas (2) por día con sus datos de contacto. Por favor llámenos si necesita asistencia urgente.'
            ], 429);
        }
        // =======================================


        try {
            DB::beginTransaction();

            // 2. Gestión de CLIENTE (Buscar o Crear)
            // Si el cliente ya existe, se mantiene su situación actual.
            // Si es nuevo, se crea como 'prospecto'.
            $cliente = Cliente::firstOrCreate(
                ['email' => $validated['email']], // Búsqueda principal por email
                [
                    'nombre_completo' => $validated['nombre'],
                    'telefono' => $validated['telefono'],
                    'direccion' => 'Dirección pendiente', // Opcional
                    'situacion' => 'prospecto' // Nuevo campo
                ]
            );

            // Actualizar datos de contacto si el cliente existe (opcional, pero útil)
            if (!$cliente->wasRecentlyCreated) {
                // Solo actualizamos si es prospecto o si queremos mantener datos frescos
                // Por seguridad, actualizamos teléfono y nombre
                $cliente->update([
                    'nombre_completo' => $validated['nombre'],
                    'telefono' => $validated['telefono']
                ]);
            }

            // 3. Gestión de VEHÍCULO
            $nombreMarca = trim(strtoupper($validated['marca']));
            $nombreModelo = isset($validated['modelo']) ? trim(strtoupper($validated['modelo'])) : 'MODELO BASE';
            $nombreVersion = isset($request->version) ? trim(strtoupper($request->version)) : '';
            
            $mensajeAdicionalVehiculo = "";

            // Buscar si la marca existe en BD (para relacionarla correctamente)
            $marca = MarcaVehiculo::where('nombre', $nombreMarca)->first();

            if ($marca) {
                // Marca existe, procedemos normal con Modelo
                $modelo = ModeloVehiculo::firstOrCreate(
                    ['marca_id' => $marca->id, 'nombre' => $nombreModelo]
                );
                
                $versionId = null;
                if ($nombreVersion) {
                    $version = VersionVehiculo::firstOrCreate(
                        ['modelo_id' => $modelo->id, 'nombre' => $nombreVersion]
                    );
                    $versionId = $version->id;
                }
            } else {
                // Marca NO existe: Usar GENERICO
                $marca = MarcaVehiculo::firstOrCreate(['nombre' => 'GENERICA']); 
                $modelo = ModeloVehiculo::firstOrCreate(['marca_id' => $marca->id, 'nombre' => 'GENERICO']);
                $versionId = null; 

                // Guardamos el detalle real en el texto para que el asesor lo corrija después
                $mensajeAdicionalVehiculo = " [Vehículo Ingresado: $nombreMarca - $nombreModelo - $nombreVersion]";
            }

            // Crear/Buscar Vehículo
            $placa = strtoupper(str_replace([' ', '-'], '', $validated['placa']));

            $vehiculo = Vehiculo::firstOrCreate(
                ['placa' => $placa],
                [
                    'cliente_id' => $cliente->id,
                    'marca_id' => $marca->id,
                    'modelo_id' => $modelo->id,
                    'version_id' => $versionId,
                    'anio' => date('Y'),
                    'vin' => null,
                    'situacion' => 'prospecto' // Nuevo campo
                ]
            );

            // Si el vehículo ya existía pero estaba asignado a otro cliente (caso raro de venta),
            // lo reasignamos al cliente actual. O si es nuevo, aseguramos la relación.
            if ($vehiculo->cliente_id !== $cliente->id) {
                $vehiculo->update(['cliente_id' => $cliente->id]);
            }

            // 4. Crear CITA
            $fechaHora = Carbon::parse($validated['fecha'] . ' ' . $validated['hora'], 'America/Guatemala')->setTimezone('UTC');
            
            // Concatenar detalles al motivo
            $motivoFinal = $validated['motivo'] . $mensajeAdicionalVehiculo;

            $cita = Cita::create([
                'cliente_id' => $cliente->id,
                'vehiculo_id' => $vehiculo->id,
                'sucursal_id' => $validated['sucursal_id'],
                'fecha_programada' => $fechaHora,
                'motivo_cita' => $motivoFinal,
                'origen' => 'web',
                'estado' => 'pendiente'
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Cita agendada correctamente. Nos pondremos en contacto.',
                'cita_id' => $cita->id
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Error al procesar la cita: ' . $e->getMessage()
            ], 500);
        }
    }

    public function getBranches()
    {
        $sucursales = \App\Models\Sucursal::orderBy('nombre', 'asc')->get(['id', 'nombre']);
        return response()->json($sucursales);
    }

    public function getBrands()
    {
        $marcas = MarcaVehiculo::orderBy('nombre', 'asc')->get(['id', 'nombre']);
        return response()->json($marcas);
    }
    
    public function getModels($marcaId)
    {
        $modelos = ModeloVehiculo::where('marca_id', $marcaId)->orderBy('nombre', 'asc')->get(['id', 'nombre']);
        return response()->json($modelos);
    }

    public function getVersions($modeloId)
    {
        $versiones = VersionVehiculo::where('modelo_id', $modeloId)->orderBy('nombre', 'asc')->get(['id', 'nombre']);
        return response()->json($versiones);
    }

    public function clientLookup(Request $request)
    {
        $contactInfo = $request->query('query'); // email or phone
        if (!$contactInfo) {
            return response()->json(['success' => false, 'message' => 'Término de búsqueda requerido'], 400);
        }

        $cliente = Cliente::with(['vehiculos.marca', 'vehiculos.modelo', 'vehiculos.version'])
            ->where('email', $contactInfo)
            ->orWhere('telefono', $contactInfo)
            ->first();

        if (!$cliente) {
            return response()->json(['success' => false, 'message' => 'No se encontró ningún cliente con ese correo o teléfono'], 404);
        }

        // Formatear vehículos
        $vehiculosFormat = $cliente->vehiculos->map(function ($v) {
            return [
                'id' => $v->id,
                'placa' => $v->placa,
                'detalles_texto' => $v->marca?->nombre . ' ' . $v->modelo?->nombre . ' ' . $v->version?->nombre,
                'marca_id' => $v->marca_id,
                'modelo_id' => $v->modelo_id,
                'version_id' => $v->version_id,
            ];
        });

        return response()->json([
            'success' => true,
            'cliente' => [
                'nombre' => $cliente->nombre_completo,
                'email' => $cliente->email,
                'telefono' => $cliente->telefono,
            ],
            'vehiculos' => $vehiculosFormat
        ]);
    }
}
