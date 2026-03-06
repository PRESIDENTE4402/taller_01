<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PagoTrabajador extends Model
{
    use \Illuminate\Database\Eloquent\Factories\HasFactory;

    protected $fillable = [
        'user_id',
        'monto_total',
        'fecha_pago',
        'fecha_inicio_periodo',
        'fecha_fin_periodo',
        'sueldo_base',
        'descuentos',
        'metodo_pago',
        'observaciones',
        'modificado'
    ];

    protected $casts = [
        'fecha_pago' => 'date',
        'fecha_inicio_periodo' => 'date',
        'fecha_fin_periodo' => 'date',
    ];

    public function trabajador()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function bitacoras()
    {
        return $this->hasMany(BitacoraTrabajo::class, 'pago_trabajador_id');
    }
}
