<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Role extends Model
{
    use HasFactory;

    protected $fillable = ['nombre', 'slug', 'descripcion'];

    public function users()
    {
        return $this->belongsToMany(User::class, 'rol_usuario', 'role_id', 'user_id');
    }

    public function permissions()
    {
        return $this->belongsToMany(Permission::class, 'permiso_rol', 'role_id', 'permiso_id');
    }
}
