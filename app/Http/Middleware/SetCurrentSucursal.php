<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SetCurrentSucursal
{
    /**
     * Middleware que establece la sucursal actual en la sesión
     * Usa: sucursal_id del query string → sucursal_por_defecto → primera sucursal del usuario
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next)
    {
        if (Auth::check()) {
            $user = Auth::user();
            $sucursalId = null;

            // 1. Prioridad: sucursal_id en query string
            if ($request->has('sucursal_id')) {
                $querySucursal = $request->input('sucursal_id');
                $userSucursals = $user->sucursales->pluck('id');

                if ($userSucursals->contains($querySucursal)) {
                    $sucursalId = $querySucursal;
                }
            }

            // 2. Si no hay sucursal_id en query, usar sucursal por defecto
            if (!$sucursalId && $user->sucursal_por_defecto_id) {
                $sucursalId = $user->sucursal_por_defecto_id;
            }

            // 3. Si no hay sucursal por defecto, usar la primera sucursal del usuario
            if (!$sucursalId) {
                $firstSucursal = $user->sucursales->first();
                $sucursalId = $firstSucursal ? $firstSucursal->id : null;
            }

            // Establecer en sesión
            if ($sucursalId) {
                session(['sucursal_id' => $sucursalId]);
                $request->merge(['current_sucursal_id' => $sucursalId]);
            }
        }

        return $next($request);
    }
}
