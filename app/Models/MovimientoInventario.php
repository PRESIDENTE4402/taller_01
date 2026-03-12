<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MovimientoInventario extends Model
{
    protected $table = 'movimientos_inventario';

    protected $fillable = [
        'repuesto_id',
        'user_id',
        'sucursal_id',
        'cantidad',
        'tipo',
        'stock_anterior',
        'stock_nuevo',
        'motivo',
        'referencia_id',
        'notas'
    ];

    public function repuesto()
    {
        return $this->belongsTo(Repuesto::class);
    }

    public function usuario()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function sucursal()
    {
        return $this->belongsTo(Sucursal::class);
    }
}
