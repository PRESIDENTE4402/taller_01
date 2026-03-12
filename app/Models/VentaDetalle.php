<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class VentaDetalle extends Model
{
    protected $table = 'venta_detalles';
    protected $fillable = [
        'venta_id', 'repuesto_id', 'cantidad',
        'precio_unitario', 'costo_unitario', 'subtotal'
    ];

    public function venta()
    {
        return $this->belongsTo(Venta::class);
    }

    public function repuesto()
    {
        return $this->belongsTo(Repuesto::class);
    }
}
