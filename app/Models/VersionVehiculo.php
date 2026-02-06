<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VersionVehiculo extends Model
{
    use HasFactory;

    protected $table = 'versiones_vehiculos';

    protected $fillable = [
        'modelo_id',
        'nombre'
    ];

    public function modelo()
    {
        return $this->belongsTo(ModeloVehiculo::class, 'modelo_id');
    }
}
