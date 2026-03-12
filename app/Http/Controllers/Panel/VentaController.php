<?php

namespace App\Http\Controllers\Panel;

use App\Http\Controllers\Controller;
use App\Models\Venta;
use App\Models\VentaDetalle;
use App\Models\Repuesto;
use App\Models\MovimientoInventario;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class VentaController extends Controller
{
    public function index()
    {
        return view('panel.operaciones.ventas.index');
    }

    public function list(Request $request)
    {
        $usuario = Auth::user();
        $sucursalesIds = $usuario->sucursales->pluck('id');

        $query = Venta::whereIn('sucursal_id', $sucursalesIds)
            ->with(['usuario', 'sucursal']);

        if ($request->filled('fecha_inicio')) {
            $query->whereDate('created_at', '>=', $request->fecha_inicio);
        }

        if ($request->filled('fecha_fin')) {
            $query->whereDate('created_at', '<=', $request->fecha_fin);
        }

        // Obtener el total filtrado antes de paginar
        $totalFiltrado = (clone $query)->where('estado', '!=', 'anulada')->sum('total');
        $countFiltrado = (clone $query)->count();

        $ventas = $query->orderBy('created_at', 'desc')
            ->paginate(15);

        return response()->json([
            'ventas' => $ventas,
            'total_filtrado' => $totalFiltrado,
            'count_filtrado' => $countFiltrado
        ]);
    }

    public function show($id)
    {
        $venta = Venta::with(['detalles.repuesto', 'usuario', 'sucursal'])->findOrFail($id);
        return response()->json($venta);
    }

    public function store(Request $request)
    {
        $request->validate([
            'cliente_nombre' => 'nullable|string|max:255',
            'notas' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.id' => 'required|exists:repuestos,id',
            'items.*.cantidad' => 'required|numeric|min:0.01',
        ]);

        try {
            return DB::transaction(function () use ($request) {
                $total = 0;
                $costo_total = 0;
                $items_procesados = [];

                foreach ($request->items as $itemData) {
                    $repuesto = Repuesto::findOrFail($itemData['id']);
                    
                    if ($repuesto->stock_actual < $itemData['cantidad']) {
                        throw new \Exception("Stock insuficiente para: {$repuesto->nombre}");
                    }

                    $subtotal = $repuesto->precio_venta * $itemData['cantidad'];
                    $costo_subtotal = $repuesto->precio_costo * $itemData['cantidad'];
                    
                    $total += $subtotal;
                    $costo_total += $costo_subtotal;

                    $items_procesados[] = [
                        'repuesto_id' => $repuesto->id,
                        'cantidad' => $itemData['cantidad'],
                        'precio_unitario' => $repuesto->precio_venta,
                        'costo_unitario' => $repuesto->precio_costo,
                        'subtotal' => $subtotal,
                        'repuesto_model' => $repuesto // Para actualizar stock después
                    ];
                }

                // Generar Folio
                $lastVenta = Venta::orderBy('id', 'desc')->first();
                $lastId = $lastVenta ? $lastVenta->id : 0;
                $folio = 'VNT-' . str_pad($lastId + 1, 6, '0', STR_PAD_LEFT);

                $sucursal_id = session('sucursal_id') 
                    ?? $request->header('X-Sucursal-Id') 
                    ?? Auth::user()->sucursales->first()?->id;

                if (!$sucursal_id) {
                    throw new \Exception("No se ha podido determinar la sucursal para esta venta.");
                }

                $venta = Venta::create([
                    'folio' => $folio,
                    'sucursal_id' => $sucursal_id,
                    'user_id' => Auth::id(),
                    'cliente_id' => $request->cliente_id,
                    'cliente_nombre' => $request->cliente_nombre ?? 'Venta Mostrador',
                    'total' => $total,
                    'costo_total' => $costo_total,
                    'notas' => $request->notas,
                    'estado' => 'completada'
                ]);

                foreach ($items_procesados as $ip) {
                    VentaDetalle::create([
                        'venta_id' => $venta->id,
                        'repuesto_id' => $ip['repuesto_id'],
                        'cantidad' => $ip['cantidad'],
                        'precio_unitario' => $ip['precio_unitario'],
                        'costo_unitario' => $ip['costo_unitario'],
                        'subtotal' => $ip['subtotal']
                    ]);

                    // Registrar Movimiento de Inventario
                    $repuesto = $ip['repuesto_model'];
                    $stockAnterior = $repuesto->stock_actual;
                    $stockNuevo = $stockAnterior - $ip['cantidad'];

                    MovimientoInventario::create([
                        'repuesto_id' => $repuesto->id,
                        'sucursal_id' => $venta->sucursal_id,
                        'user_id' => Auth::id(),
                        'tipo' => 'salida',
                        'cantidad' => $ip['cantidad'],
                        'stock_anterior' => $stockAnterior,
                        'stock_nuevo' => $stockNuevo,
                        'motivo' => 'venta_directa',
                        'notas' => "Venta Folio: {$venta->folio}"
                    ]);

                    $repuesto->update(['stock_actual' => $stockNuevo]);
                }

                return response()->json([
                    'success' => true,
                    'message' => 'Venta procesada correctamente.',
                    'venta' => $venta
                ]);
            });
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 422);
        }
    }

    public function processReturn(Request $request, $id)
    {
        $venta = Venta::with('detalles')->findOrFail($id);
        
        if ($venta->estado === 'devuelta') {
            return response()->json(['success' => false, 'message' => 'Esta venta ya fue devuelta.'], 422);
        }

        try {
            return DB::transaction(function () use ($venta, $request) {
                foreach ($venta->detalles as $detalle) {
                    $repuesto = Repuesto::findOrFail($detalle->repuesto_id);
                    $stockAnterior = $repuesto->stock_actual;
                    $stockNuevo = $stockAnterior + $detalle->cantidad;

                    MovimientoInventario::create([
                        'repuesto_id' => $repuesto->id,
                        'sucursal_id' => $venta->sucursal_id,
                        'user_id' => Auth::id(),
                        'tipo' => 'entrada',
                        'cantidad' => $detalle->cantidad,
                        'stock_anterior' => $stockAnterior,
                        'stock_nuevo' => $stockNuevo,
                        'motivo' => 'devolucion',
                        'notas' => "Devolución de Venta Folio: {$venta->folio}. " . ($request->motivo ?? '')
                    ]);

                    $repuesto->update(['stock_actual' => $stockNuevo]);
                }

                $venta->update(['estado' => 'devuelta']);

                return response()->json([
                    'success' => true,
                    'message' => 'Devolución procesada correctamente (Stock reintegrado).'
                ]);
            });
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
        }
    }
}
