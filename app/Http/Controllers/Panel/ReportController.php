<?php

namespace App\Http\Controllers\Panel;

use App\Http\Controllers\Controller;
use App\Models\InventoryReport;
use App\Models\Repuesto;
use App\Models\Categoria;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class ReportController extends Controller
{
    /**
     * Mostrar página de reportes
     */
    public function index()
    {
        return view('panel.reportes.index');
    }

    /**
     * Listar reportes generados
     */
    public function list(Request $request)
    {
        $reportes = InventoryReport::forCurrentUser()
            ->with('creator', 'categoria')
            ->recent()
            ->paginate(15);

        return response()->json([
            'success' => true,
            'data' => $reportes
        ]);
    }

    /**
     * Generar reporte de inventario general
     */
    public function generateInventarioGeneral(Request $request)
    {
        try {
            $sucursalId = session('sucursal_id');
            $categoriaId = $request->categoria_id;

            $query = Repuesto::forSucursal($sucursalId);

            if ($categoriaId) {
                $query->where('categoria_id', $categoriaId);
            }

            $repuestos = $query->get();

            $totalProductos = $repuestos->count();
            $valorTotalCosto = $repuestos->sum('precio_costo');
            $valorTotalVenta = $repuestos->sum('precio_venta');
            $stockTotal = $repuestos->sum('stock_actual');

            $datosReporte = [
                'productos' => $repuestos->map(fn($r) => [
                    'id' => $r->id,
                    'codigo' => $r->codigo_interno,
                    'nombre' => $r->nombre,
                    'categoria' => $r->categoria->nombre ?? null,
                    'precio_costo' => $r->precio_costo,
                    'precio_venta' => $r->precio_venta,
                    'ganancia_unitaria' => $r->precio_venta - $r->precio_costo,
                    'stock' => $r->stock_actual,
                    'valor_total_costo' => $r->precio_costo * 1,
                    'valor_total_venta' => $r->precio_venta * 1,
                ])->toArray(),
                'resumen' => [
                    'total_productos' => $totalProductos,
                    'valor_total_costo' => $valorTotalCosto,
                    'valor_total_venta' => $valorTotalVenta,
                    'ganancia_total' => $valorTotalVenta - $valorTotalCosto,
                    'margen_ganancia' => $valorTotalCosto > 0 
                        ? round((($valorTotalVenta - $valorTotalCosto) / $valorTotalCosto) * 100, 2)
                        : 0,
                    'stock_total' => $stockTotal,
                ]
            ];

            $reporte = InventoryReport::create([
                'sucursal_id' => $sucursalId,
                'created_by' => Auth::id(),
                'nombre_reporte' => 'Inventario General - ' . now()->format('d/m/Y H:i'),
                'tipo_reporte' => 'inventario_general',
                'categoria_id' => $categoriaId,
                'total_productos' => $totalProductos,
                'valor_total_costo' => $valorTotalCosto,
                'valor_total_venta' => $valorTotalVenta,
                'stock_total' => $stockTotal,
                'datos_reporte' => $datosReporte,
                'generated_at' => now(),
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Reporte generado exitosamente.',
                'data' => $reporte->load('creator', 'categoria')
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 422);
        }
    }

    /**
     * Generar reporte de productos con bajo stock
     */
    public function generateBajoStock(Request $request)
    {
        try {
            $sucursalId = session('sucursal_id');

            $repuestos = Repuesto::forSucursal($sucursalId)
                ->whereColumn('stock_actual', '<=', 'stock_minimo')
                ->get();

            $datosReporte = [
                'productos' => $repuestos->map(fn($r) => [
                    'id' => $r->id,
                    'codigo' => $r->codigo_interno,
                    'nombre' => $r->nombre,
                    'stock_actual' => $r->stock_actual,
                    'stock_minimo' => $r->stock_minimo,
                    'estado' => $r->stock_actual == 0 ? 'AGOTADO' : 'BAJO',
                ])->toArray(),
            ];

            $reporte = InventoryReport::create([
                'sucursal_id' => $sucursalId,
                'created_by' => Auth::id(),
                'nombre_reporte' => 'Bajo Stock - ' . now()->format('d/m/Y H:i'),
                'tipo_reporte' => 'bajo_stock',
                'total_productos' => $repuestos->count(),
                'valor_total_costo' => $repuestos->sum('precio_costo'),
                'valor_total_venta' => $repuestos->sum('precio_venta'),
                'stock_total' => $repuestos->sum('stock_actual'),
                'datos_reporte' => $datosReporte,
                'generated_at' => now(),
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Reporte de bajo stock generado.',
                'data' => $reporte->load('creator')
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 422);
        }
    }

    /**
     * Generar reporte por categoría
     */
    public function generatePorCategoria(Request $request)
    {
        try {
            $sucursalId = session('sucursal_id');

            $categorias = Categoria::forSucursal($sucursalId)
                ->with(['repuestos' => function ($q) {
                    $q->select('categoria_id', 'precio_costo', 'precio_venta', 'stock_actual');
                }])
                ->get();

            $datosReporte = [];
            $totalProductos = 0;
            $valorTotalCosto = 0;
            $valorTotalVenta = 0;
            $stockTotal = 0;

            foreach ($categorias as $categoria) {
                $costo = $categoria->repuestos->sum('precio_costo');
                $venta = $categoria->repuestos->sum('precio_venta');
                $stock = $categoria->repuestos->sum('stock_actual');
                $productos = $categoria->repuestos->count();

                $datosReporte[] = [
                    'categoria' => $categoria->nombre,
                    'total_productos' => $productos,
                    'valor_costo' => $costo,
                    'valor_venta' => $venta,
                    'ganancia' => $venta - $costo,
                    'margen' => $costo > 0 ? round((($venta - $costo) / $costo) * 100, 2) : 0,
                    'stock' => $stock,
                ];

                $totalProductos += $productos;
                $valorTotalCosto += $costo;
                $valorTotalVenta += $venta;
                $stockTotal += $stock;
            }

            $reporte = InventoryReport::create([
                'sucursal_id' => $sucursalId,
                'created_by' => Auth::id(),
                'nombre_reporte' => 'Reporte por Categoría - ' . now()->format('d/m/Y H:i'),
                'tipo_reporte' => 'por_categoria',
                'total_productos' => $totalProductos,
                'valor_total_costo' => $valorTotalCosto,
                'valor_total_venta' => $valorTotalVenta,
                'stock_total' => $stockTotal,
                'datos_reporte' => ['categorias' => $datosReporte],
                'generated_at' => now(),
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Reporte por categoría generado.',
                'data' => $reporte->load('creator')
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 422);
        }
    }

    /**
     * Ver detalle de un reporte
     */
    public function show($id)
    {
        try {
            $reporte = InventoryReport::forCurrentUser()
                ->with('creator', 'categoria')
                ->findOrFail($id);

            return response()->json([
                'success' => true,
                'data' => $reporte
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Reporte no encontrado'
            ], 404);
        }
    }

    /**
     * Descargar reporte en PDF
     */
    public function downloadPdf($id)
    {
        try {
            $reporte = InventoryReport::forCurrentUser()
                ->with('creator', 'categoria')
                ->findOrFail($id);

            // Aquí implementarías generación de PDF con Laravel-PDF o similar
            return response()->json([
                'success' => true,
                'message' => 'PDF en desarrollo',
                'data' => $reporte
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Reporte no encontrado'
            ], 404);
        }
    }

    /**
     * Eliminar un reporte
     */
    public function destroy($id)
    {
        try {
            $reporte = InventoryReport::forCurrentUser()->findOrFail($id);

            // Validar que el usuario creó el reporte o es admin
            if ($reporte->created_by !== Auth::id() && !Auth::user()->hasRole('admin')) {
                return response()->json([
                    'success' => false,
                    'message' => 'No tienes permiso para eliminar este reporte'
                ], 403);
            }

            $reporte->delete();

            return response()->json([
                'success' => true,
                'message' => 'Reporte eliminado exitosamente.'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 422);
        }
    }
}
