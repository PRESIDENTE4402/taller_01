<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\BelongsToSucursal;

class InventoryReport extends Model
{
    use HasFactory, BelongsToSucursal;

    protected $table = 'inventory_reports';

    protected $fillable = [
        'sucursal_id',
        'created_by',
        'nombre_reporte',
        'descripcion',
        'tipo_reporte',
        'categoria_id',
        'fecha_inicio',
        'fecha_fin',
        'filtros_adicionales',
        'total_productos',
        'valor_total_costo',
        'valor_total_venta',
        'stock_total',
        'datos_reporte',
        'generated_at',
    ];

    protected $casts = [
        'filtros_adicionales' => 'array',
        'datos_reporte' => 'array',
        'fecha_inicio' => 'date',
        'fecha_fin' => 'date',
        'generated_at' => 'datetime',
        'valor_total_costo' => 'decimal:2',
        'valor_total_venta' => 'decimal:2',
    ];

    /**
     * Relación: Usuario que creó el reporte
     */
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Relación: Categoría (si aplica)
     */
    public function categoria()
    {
        return $this->belongsTo(Categoria::class);
    }

    /**
     * Margen de ganancia en porcentaje
     */
    public function getMargenGanancia()
    {
        if ($this->valor_total_costo == 0) {
            return 0;
        }
        
        $ganancia = $this->valor_total_venta - $this->valor_total_costo;
        return round(($ganancia / $this->valor_total_costo) * 100, 2);
    }

    /**
     * Ganancia en monto
     */
    public function getGananciaMonto()
    {
        return $this->valor_total_venta - $this->valor_total_costo;
    }

    /**
     * Scope: Filtrar por tipo de reporte
     */
    public function scopeByType($query, $tipo)
    {
        return $query->where('tipo_reporte', $tipo);
    }

    /**
     * Scope: Filtrar por sucursal actual
     */
    public function scopeRecent($query)
    {
        return $query->orderBy('generated_at', 'desc');
    }
}
