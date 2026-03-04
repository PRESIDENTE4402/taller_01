<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DetalleOrden extends Model
{
    use HasFactory;

    protected $table = 'detalles_orden';

    protected $fillable = [
        'orden_trabajo_id',
        'repuesto_id',
        'descripcion_manual',
        'cantidad',
        'precio_unitario',
        'suministrado_por',
        'notas',
        'estado'
    ];

    public function orden()
    {
        return $this->belongsTo(OrdenTrabajo::class, 'orden_trabajo_id');
    }

    public function repuesto()
    {
        return $this->belongsTo(Repuesto::class);
    }
}
