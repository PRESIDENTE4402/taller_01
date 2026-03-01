<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'phone',
        'sucursal_por_defecto_id',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    // En app/Models/User.php
    public function roles()
    {
        return $this->belongsToMany(Role::class, 'rol_usuario');
    }

    // Método útil para verificar permisos
    public function hasRole($roleSlug)
    {
        return $this->roles()->where('slug', $roleSlug)->exists();
    }

    public function hasPermission($permissionSlug)
    {
        return $this->roles()->whereHas('permissions', function ($q) use ($permissionSlug) {
            $q->where('slug', $permissionSlug);
        })->exists();
    }

    public function persona()
    {
        return $this->hasOne(Persona::class);
    }

    public function sucursales()
    {
        return $this->belongsToMany(Sucursal::class, 'sucursal_usuario');
    }

    public function bitacoras()
    {
        return $this->hasMany(BitacoraTrabajo::class, 'user_id');
    }

    /**
     * Relación: Sucursal por defecto del usuario
     */
    public function sucursalPorDefecto()
    {
        return $this->belongsTo(Sucursal::class, 'sucursal_por_defecto_id');
    }

    /**
     * Send the password reset notification.
     *
     * @param  string  $token
     * @return void
     */
    public function sendPasswordResetNotification($token)
    {
        $this->notify(new \App\Notifications\ResetPasswordNotification($token));
    }
}
