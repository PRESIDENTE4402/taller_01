<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Venta extends Model
{
    protected $table = 'ventas';
    protected $fillable = [
        'folio', 'sucursal_id', 'user_id', 'cliente_id', 'cliente_nombre',
        'total', 'costo_total', 'notas', 'estado'
    ];

    public function detalles()
    {
        return $this->hasMany(VentaDetalle::class);
    }

    public function sucursal()
    {
        return $this->belongsTo(Sucursal::class);
    }

    public function usuario()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function cliente()
    {
        return $this->belongsTo(Cliente::class);
    }
}
