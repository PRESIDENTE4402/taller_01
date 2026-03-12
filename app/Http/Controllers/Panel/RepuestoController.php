<?php

namespace App\Http\Controllers\Panel;

use App\Http\Controllers\Controller;
use App\Models\Repuesto;
use App\Models\MovimientoInventario;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class RepuestoController extends Controller
{
    public function index()
    {
        return view('panel.mantenimientos.repuestos.index');
    }

    public function list(Request $request)
    {
        $repuestos = Repuesto::forCurrentUser()
            ->with('categoria', 'categoria.attributeSchemas')
            ->orderBy('nombre', 'asc')
            ->get();

        return response()->json($repuestos);
    }

    public function store(Request $request)
    {
        $request->validate([
            'sucursal_id' => 'required|exists:sucursales,id',
            'categoria_id' => 'nullable|exists:categorias,id',
            'nombre' => 'required',
            'precio_costo' => 'required|numeric',
            'precio_venta' => 'required|numeric',
            'stock_actual' => 'required|integer',
            'stock_minimo' => 'nullable|integer',
            'atributos' => 'nullable|array'
        ]);

        try {
            // Generar código interno automático si no viene o para asegurar formato
            $lastId = Repuesto::orderBy('id', 'desc')->first()?->id ?? 0;
            $newCode = 'REP-' . str_pad($lastId + 1, 5, '0', STR_PAD_LEFT);

            // Validar atributos dinámicos según el schema de la categoría
            if ($request->categoria_id && $request->atributos) {
                Repuesto::validateAttributes($request->categoria_id, $request->atributos);
            }

            $data = $request->all();
            $data['codigo_interno'] = $newCode;

            $repuesto = Repuesto::create($data);

            return response()->json([
                'success' => true,
                'message' => 'Repuesto creado exitosamente.',
                'data' => $repuesto->load('categoria')
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 422);
        }
    }

    public function update(Request $request, $id)
    {
        $repuesto = Repuesto::findOrFail($id);

        // Ownership check
        if (!Auth::user()->sucursales->contains($repuesto->sucursal_id)) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $request->validate([
            'codigo_interno' => 'required|unique:repuestos,codigo_interno,' . $id,
            // Add other validations as needed
        ]);

        $repuesto->update($request->all());

        return response()->json(['success' => true, 'message' => 'Repuesto actualizado.']);
    }

    public function destroy($id)
    {
        $repuesto = Repuesto::findOrFail($id);
        if (!Auth::user()->sucursales->contains($repuesto->sucursal_id)) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }
        $repuesto->delete();
        return response()->json(['success' => true, 'message' => 'Repuesto eliminado.']);
    }

    /**
     * Obtener historial de movimientos de un repuesto
     */
    public function getHistory($id)
    {
        $repuesto = Repuesto::findOrFail($id);
        $movimientos = $repuesto->movimientos()
            ->with('usuario')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $movimientos
        ]);
    }

    /**
     * Registrar un movimiento de stock (Ingreso/Egreso manual o venta)
     */
    public function storeMovement(Request $request, $id)
    {
        $repuesto = Repuesto::findOrFail($id);

        $request->validate([
            'tipo' => 'required|in:entrada,salida',
            'cantidad' => 'required|numeric|min:0.01',
            'motivo' => 'required|string',
            'notas' => 'nullable|string'
        ]);

        return DB::transaction(function () use ($request, $repuesto) {
            $stockAnterior = $repuesto->stock_actual;
            $cantidad = $request->cantidad;
            $tipo = $request->tipo;

            if ($tipo === 'salida' && $stockAnterior < $cantidad) {
                return response()->json([
                    'success' => false,
                    'message' => 'Stock insuficiente para realizar el egreso.'
                ], 422);
            }

            $stockNuevo = ($tipo === 'entrada') 
                ? $stockAnterior + $cantidad 
                : $stockAnterior - $cantidad;

            // Registrar movimiento
            MovimientoInventario::create([
                'repuesto_id' => $repuesto->id,
                'sucursal_id' => $repuesto->sucursal_id,
                'user_id' => Auth::id(),
                'tipo' => $tipo,
                'cantidad' => $cantidad,
                'stock_anterior' => $stockAnterior,
                'stock_nuevo' => $stockNuevo,
                'motivo' => $request->motivo,
                'notas' => $request->notas
            ]);

            // Actualizar stock del repuesto
            $repuesto->update(['stock_actual' => $stockNuevo]);

            return response()->json([
                'success' => true,
                'message' => 'Movimiento registrado exitosamente.',
                'new_stock' => $stockNuevo
            ]);
        });
    }

    /**
     * Procesar múltiples movimientos (Venta Directa Multiproducto)
     */
    public function bulkMovement(Request $request)
    {
        $request->validate([
            'movimientos' => 'required|array',
            'movimientos.*.repuesto_id' => 'required|exists:repuestos,id',
            'movimientos.*.tipo' => 'required|in:entrada,salida',
            'movimientos.*.cantidad' => 'required|numeric|min:0.01',
            'movimientos.*.motivo' => 'required|string',
            'movimientos.*.notas' => 'nullable|string'
        ]);

        try {
            return DB::transaction(function () use ($request) {
                foreach ($request->movimientos as $mov) {
                    $repuesto = Repuesto::findOrFail($mov['repuesto_id']);
                    $stockAnterior = $repuesto->stock_actual;
                    $cantidad = $mov['cantidad'];
                    $tipo = $mov['tipo'];

                    if ($tipo === 'salida' && $stockAnterior < $cantidad) {
                        throw new \Exception("Stock insuficiente para: {$repuesto->nombre}");
                    }

                    $stockNuevo = ($tipo === 'entrada') ? $stockAnterior + $cantidad : $stockAnterior - $cantidad;

                    MovimientoInventario::create([
                        'repuesto_id' => $repuesto->id,
                        'sucursal_id' => $repuesto->sucursal_id,
                        'user_id' => Auth::id(),
                        'tipo' => $tipo,
                        'cantidad' => $cantidad,
                        'stock_anterior' => $stockAnterior,
                        'stock_nuevo' => $stockNuevo,
                        'motivo' => $mov['motivo'],
                        'notas' => $mov['notas'] ?? null
                    ]);

                    $repuesto->update(['stock_actual' => $stockNuevo]);
                }

                return response()->json([
                    'success' => true,
                    'message' => 'Venta procesada exitosamente.'
                ]);
            });
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 422);
        }
    }
}
