<?php

namespace App\Http\Controllers\Panel;

use App\Http\Controllers\Controller;
use App\Models\Repuesto;
use App\Models\Categoria;
use App\Models\Sucursal;
use App\Models\MovimientoInventario;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Barryvdh\DomPDF\Facade\Pdf;

class ReporteController extends Controller
{
    /**
     * Reporte: Movimientos de Inventario (Kardex General)
     */
    public function movimientosInventario(Request $request)
    {
        $user = Auth::user();
        $sucursales = $user->sucursales;
        
        $fechaInicio = $request->get('fecha_inicio', now()->startOfMonth()->format('Y-m-d'));
        $fechaFin = $request->get('fecha_fin', now()->endOfMonth()->format('Y-m-d'));

        $query = MovimientoInventario::with(['repuesto', 'usuario', 'sucursal'])
            ->whereIn('sucursal_id', $sucursales->pluck('id'))
            ->whereBetween('created_at', [$fechaInicio . ' 00:00:00', $fechaFin . ' 23:59:59']);

        if ($request->tipo && $request->tipo !== 'todos') {
            $query->where('tipo', $request->tipo);
        }

        if ($request->motivo && $request->motivo !== 'todos') {
            $query->where('motivo', $request->motivo);
        }

        if ($request->sucursal_id) {
            $query->where('sucursal_id', $request->sucursal_id);
        }

        $movimientos = $query->orderBy('created_at', 'desc')->paginate(50);

        return view('panel.reportes.movimientos-inventario', [
            'movimientos' => $movimientos,
            'sucursales' => $sucursales,
            'fechaInicio' => $fechaInicio,
            'fechaFin' => $fechaFin,
            'filtros' => $request->all()
        ]);
    }

    /**
     * Exportar a PDF: Movimientos de Inventario
     */
    public function exportMovimientosPDF(Request $request)
    {
        $user = Auth::user();
        $sucursales = $user->sucursales;
        
        $fechaInicio = $request->get('fecha_inicio', now()->startOfMonth()->format('Y-m-d'));
        $fechaFin = $request->get('fecha_fin', now()->endOfMonth()->format('Y-m-d'));

        $query = MovimientoInventario::with(['repuesto', 'usuario', 'sucursal'])
            ->whereIn('sucursal_id', $sucursales->pluck('id'))
            ->whereBetween('created_at', [$fechaInicio . ' 00:00:00', $fechaFin . ' 23:59:59']);

        if ($request->tipo && $request->tipo !== 'todos') {
            $query->where('tipo', $request->tipo);
        }

        if ($request->sucursal_id) {
            $query->where('sucursal_id', $request->sucursal_id);
        }

        $movimientos = $query->orderBy('created_at', 'desc')->get();

        $pdf = Pdf::loadView('panel.reportes.pdf.movimientos', [
            'movimientos' => $movimientos,
            'fechaInicio' => $fechaInicio,
            'fechaFin' => $fechaFin,
            'sucursal' => $request->sucursal_id ? Sucursal::find($request->sucursal_id)?->nombre : 'Todas',
            'fechaGeneracion' => now()->format('d/m/Y H:i')
        ])->setPaper('a4', 'landscape');

        return $pdf->download('kardex_inventario_' . now()->format('Y-m-d') . '.pdf');
    }
    /**
     * Reporte: Inventario por Sucursal
     */
    public function inventarioPorSucursal(Request $request)
    {
        $user = Auth::user();
        $sucursales = $user->sucursales;

        $repuestos = Repuesto::forCurrentUser()
            ->with('categoria', 'categoria.attributeSchemas')
            ->when($request->sucursal_id, function ($query) {
                return $query->where('sucursal_id', $request->sucursal_id);
            })
            ->when($request->categoria_id, function ($query) {
                return $query->where('categoria_id', $request->categoria_id);
            })
            ->when($request->stock_bajo, function ($query) {
                return $query->whereColumn('stock_actual', '<=', 'stock_minimo');
            })
            ->orderBy('nombre')
            ->paginate(50);

        return view('panel.reportes.inventario-por-sucursal', [
            'repuestos' => $repuestos,
            'sucursales' => $sucursales,
            'categorias' => Categoria::forCurrentUser()->get(),
            'totalValorInventario' => $this->calcularValorInventario($repuestos),
        ]);
    }

    /**
     * Reporte: Stock Bajo (Productos que necesitan reorden)
     */
    public function stockBajo(Request $request)
    {
        $productosAlerta = Repuesto::forCurrentUser()
            ->with('categoria')
            ->whereColumn('stock_actual', '<=', 'stock_minimo')
            ->when($request->sucursal_id, function ($query) {
                return $query->where('sucursal_id', $request->sucursal_id);
            })
            ->orderBy('stock_actual', 'asc')
            ->get();

        return view('panel.reportes.stock-bajo', [
            'productos' => $productosAlerta,
            'sucursales' => Auth::user()->sucursales,
            'totalProductos' => $productosAlerta->count(),
            'valorTotalAlerta' => $productosAlerta->sum(function ($p) {
                return $p->stock_actual * $p->precio_costo;
            }),
        ]);
    }

    /**
     * Reporte: Comparativa de Precios
     */
    public function comparativaPreciosS(Request $request)
    {
        $repuestos = Repuesto::forCurrentUser()
            ->with('categoria')
            ->when($request->producto_id, function ($query) {
                return $query->where('id', $request->producto_id);
            })
            ->get()
            ->groupBy('nombre');

        $margenPromedio = Repuesto::forCurrentUser()
            ->get()
            ->average(function ($p) {
                return (($p->precio_venta - $p->precio_costo) / $p->precio_costo) * 100;
            });

        return view('panel.reportes.comparativa-precios', [
            'repuestos' => $repuestos,
            'margenPromedio' => round($margenPromedio, 2),
        ]);
    }

    /**
     * Reporte: Atributos Dinámicos (Auditoría)
     */
    public function auditAtributosdinamicos(Request $request)
    {
        $repuestosConAtributos = Repuesto::forCurrentUser()
            ->with('categoria')
            ->whereNotNull('atributos')
            ->when($request->categoria_id, function ($query) {
                return $query->where('categoria_id', $request->categoria_id);
            })
            ->get();

        return view('panel.reportes.audit-atributos', [
            'repuestos' => $repuestosConAtributos,
            'categorias' => Categoria::forCurrentUser()->get(),
            'total' => $repuestosConAtributos->count(),
        ]);
    }

    /**
     * Exportar a PDF: Inventario
     */
    public function exportInventarioPDF(Request $request)
    {
        $repuestos = Repuesto::forCurrentUser()
            ->with('categoria')
            ->when($request->sucursal_id, function ($query) {
                return $query->where('sucursal_id', $request->sucursal_id);
            })
            ->get();

        $pdf = Pdf::loadView('panel.reportes.pdf.inventario', [
            'repuestos' => $repuestos,
            'fecha' => now()->format('d/m/Y H:i'),
        ]);

        return $pdf->download('inventario_' . now()->format('Y-m-d') . '.pdf');
    }

    /**
     * Calcular valor total del inventario
     */
    private function calcularValorInventario($repuestos)
    {
        return $repuestos->sum(function ($p) {
            return $p->stock_actual * $p->precio_costo;
        });
    }
}
