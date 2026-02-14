<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class VerifySucursalAccess
{
    /**
     * Middleware que verifica que el usuario solo acceda a sucursales que le pertenecen
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next)
    {
        // Si el request contiene un parámetro 'sucursal_id', validarlo
        if ($request->has('sucursal_id')) {
            $sucursalId = $request->input('sucursal_id');
            $userSucursals = Auth::user()->sucursales->pluck('id');

            // Si el usuario no tiene acceso a esta sucursal, denegar
            if (!$userSucursals->contains($sucursalId)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Acceso denegado a esta sucursal'
                ], 403);
            }
        }

        // También validar en request body (POST/PUT)
        if ($request->isMethod('post') || $request->isMethod('put')) {
            if ($request->has('sucursal_id')) {
                $sucursalId = $request->input('sucursal_id');
                $userSucursals = Auth::user()->sucursales->pluck('id');

                if (!$userSucursals->contains($sucursalId)) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Acceso denegado a esta sucursal'
                    ], 403);
                }
            }
        }

        return $next($request);
    }
}
