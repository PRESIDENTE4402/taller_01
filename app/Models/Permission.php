<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Permission extends Model
{
    use HasFactory;

    protected $table = 'permisos';

    protected $fillable = [
        'nombre',
        'slug',
        'descripcion'
    ];

    public function roles()
    {
        return $this->belongsToMany(Role::class, 'permiso_rol', 'permiso_id', 'role_id');
    }
}
