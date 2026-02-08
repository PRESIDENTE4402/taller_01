<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        if (str_contains(request()->header('host'), 'ngrok-free.app')) {
            \URL::forceScheme('https');
        }

        // 1. Los administradores pueden hacer todo
        \Illuminate\Support\Facades\Gate::before(function ($user, $ability) {
            return $user->hasRole('admin') ? true : null;
        });

        // 2. Vincular nuestro sistema de permisos con el sistema de Laravel
        try {
            if (app()->runningInConsole() || \Illuminate\Support\Facades\Schema::hasTable('permisos')) {
                // Usamos un cache simple o solo definimos si la clase existe
                if (class_exists(\App\Models\Permission::class)) {
                    \App\Models\Permission::all()->each(function ($permission) {
                        \Illuminate\Support\Facades\Gate::define($permission->slug, function ($user) use ($permission) {
                            return $user && $user->hasPermission($permission->slug);
                        });
                    });
                }
            }
        } catch (\Exception $e) {
            // Silenciar errores en migraciones o si la tabla no existe aún
        }
    }
}
