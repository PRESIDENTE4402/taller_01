<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BitacoraTrabajo extends Model
{
    use HasFactory;

    protected $table = 'bitacoras_trabajo';

    protected $fillable = [
        'user_id',
        'sucursal_id',
        'orden_trabajo_id',
        'tipo_actividad',
        'descripcion',
        'inicio',
        'fin',
        'minutos_totales',
        'meta_minutos',
        'estado',
        'notas_adicionales',
        'estado_pago',
        'pago_trabajador_id',
        'monto_pago'
    ];

    protected $casts = [
        'inicio' => 'datetime',
        'fin' => 'datetime',
    ];

    public function mecanico()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function orden()
    {
        return $this->belongsTo(OrdenTrabajo::class, 'orden_trabajo_id');
    }

    public function sucursal()
    {
        return $this->belongsTo(Sucursal::class);
    }

    public function pagoTrabajador()
    {
        return $this->belongsTo(PagoTrabajador::class, 'pago_trabajador_id');
    }
}
