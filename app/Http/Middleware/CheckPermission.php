<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckPermission
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, string $permission): Response
    {
        if (!$request->user()) {
            return redirect()->route('login');
        }

        // Bypassear si es admin (slug 'admin')
        if ($request->user()->hasRole('admin')) {
            return $next($request);
        }

        // Verificar el permiso específico
        if ($request->user()->hasPermission($permission)) {
            return $next($request);
        }

        abort(403, "No tienes el permiso necesario: " . str_replace('_', ' ', $permission));
    }
}
