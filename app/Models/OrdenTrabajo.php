<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrdenTrabajo extends Model
{
    use HasFactory;

    protected $table = 'ordenes_trabajo';

    protected $fillable = [
        'sucursal_id',
        'codigo_orden',
        'tipo_orden',
        'vehiculo_id',
        'cliente_id',
        'cita_id',
        'receptor_id',
        'fecha_recepcion',
        'fecha_finalizacion',
        'fecha_entrega',
        'color',
        'kilometraje_entrada',
        'nivel_combustible',
        'inventario_recepcion',
        'danos_reportados',
        'danos_imagen_url',
        'falla_cliente',
        'diagnostico',
        'diagnostico_final',
        'conteo_reprogramaciones',
        'estado',
        'total_estimado'
    ];

    protected $casts = [
        'fecha_recepcion' => 'datetime',
        'fecha_finalizacion' => 'datetime',
        'fecha_entrega' => 'datetime',
        'inventario_recepcion' => 'array',
        'danos_reportados' => 'array',
    ];

    public function archivos()
    {
        return $this->hasMany(OrdenTrabajoArchivo::class);
    }

    public function sucursal()
    {
        return $this->belongsTo(Sucursal::class);
    }

    public function vehiculo()
    {
        return $this->belongsTo(Vehiculo::class);
    }

    public function cliente()
    {
        return $this->belongsTo(Cliente::class);
    }

    public function cita()
    {
        return $this->belongsTo(Cita::class);
    }

    public function receptor()
    {
        return $this->belongsTo(User::class, 'receptor_id');
    }
}
