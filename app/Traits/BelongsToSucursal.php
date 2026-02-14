<?php

namespace App\Traits;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

trait BelongsToSucursal {
    
    /**
     * Scope automático para filtrar por sucursal actual
     * Uso: Model::forCurrentUser()->get()
     */
    public function scopeForCurrentUser(Builder $query)
    {
        if (Auth::check()) {
            $sucursalIds = Auth::user()->sucursales->pluck('id');
            
            if ($sucursalIds->isNotEmpty()) {
                return $query->whereIn('sucursal_id', $sucursalIds);
            }
        }
        
        // Si no hay usuario o sucursales, devolver query vacía (seguridad)
        return $query->where('sucursal_id', null);
    }

    /**
     * Scope para filtrar por una sucursal específica
     * Uso: Model::forSucursal(1)->get()
     */
    public function scopeForSucursal(Builder $query, $sucursalId)
    {
        if (Auth::check()) {
            // Validar que el usuario tenga acceso a esta sucursal
            $userSucursals = Auth::user()->sucursales->pluck('id');
            
            if ($userSucursals->contains($sucursalId)) {
                return $query->where('sucursal_id', $sucursalId);
            }
        }
        
        // Si el usuario no tiene acceso, devolver query vacía
        return $query->where('sucursal_id', null);
    }
    
    /**
     * Relación: Pertenece a una Sucursal
     */
    public function sucursal()
    {
        return $this->belongsTo(\App\Models\Sucursal::class, 'sucursal_id');
    }
}
