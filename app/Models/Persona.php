<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Persona extends Model
{
    protected $fillable = [
        'user_id',
        'nombres',
        'apellidos',
        'edad',
        'sexo',
        'telefono',
        'correo',
        'direccion',
        'cursos',
        'otros_datos'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
