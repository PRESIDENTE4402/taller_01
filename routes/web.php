<?php

use App\Http\Controllers\Panel\SucursalController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\Panel\MarcaVehiculoController;
use App\Http\Controllers\Panel\RoleController;
use App\Http\Controllers\Panel\UsuarioController;
use App\Http\Controllers\Panel\VersionVehiculoController;
use App\Http\Controllers\Panel\ModeloVehiculoController;


use App\Http\Controllers\Landing\CitaController;

// Public Routes
Route::get('/', function () {
    return view('welcome');
})->name('home');

Route::post('/api/landing/citas', [CitaController::class, 'store'])->name('landing.citas.store');

// Auth Routes
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login']);

    // Password Reset Routes
    Route::get('/forgot-password', [App\Http\Controllers\PasswordResetController::class, 'create'])->name('password.request');
    Route::post('/forgot-password', [App\Http\Controllers\PasswordResetController::class, 'store'])->name('password.email');
    Route::get('/reset-password/{token}', [App\Http\Controllers\PasswordResetController::class, 'edit'])->name('password.reset');
    Route::post('/reset-password', [App\Http\Controllers\PasswordResetController::class, 'update'])->name('password.update');
});

Route::middleware('auth')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

    // Rutas del PANEL (Aplicación Interna)
    Route::prefix('panel')->name('panel.')->group(function () {
        Route::get('/', function () {
            return view('dashboard');
        })->name('dashboard');


        // Módulos de Operaciones (Nuevo Grupo)
        Route::prefix('operaciones')->name('operaciones.')->group(function () {
            // Citas
            Route::prefix('citas')->name('citas.')->group(function () {
                Route::get('/', [App\Http\Controllers\Panel\CitaController::class, 'index'])->name('index');
                Route::get('/list', [App\Http\Controllers\Panel\CitaController::class, 'list'])->name('list');
                Route::post('/', [App\Http\Controllers\Panel\CitaController::class, 'store'])->name('store'); // Manual
                Route::put('/{id}', [App\Http\Controllers\Panel\CitaController::class, 'update'])->name('update');
                Route::delete('/{id}', [App\Http\Controllers\Panel\CitaController::class, 'destroy'])->name('destroy');

                // APIs auxiliares para creación manual
                Route::get('/api/search-clients', [App\Http\Controllers\Panel\CitaController::class, 'searchClients'])->name('searchClients');
                Route::get('/api/get-client-vehicles/{clienteId}', [App\Http\Controllers\Panel\CitaController::class, 'getClientVehicles'])->name('getClientVehicles');
                Route::get('/api/get-brands', [App\Http\Controllers\Panel\CitaController::class, 'getBrands'])->name('getBrands');
                Route::get('/api/calendar-counts', [App\Http\Controllers\Panel\CitaController::class, 'getCalendarCounts'])->name('getCalendarCounts'); // New
            });

            // Órdenes de Trabajo (New)
            Route::prefix('ordenes-trabajo')->name('ordenes_trabajo.')->group(function () {
                Route::get('/', [App\Http\Controllers\Panel\OrdenTrabajoController::class, 'index'])->name('index');
                Route::get('/dashboard', [App\Http\Controllers\Panel\OrdenTrabajoController::class, 'dashboard'])->name('dashboard'); // New Dashboard
                Route::get('/list', [App\Http\Controllers\Panel\OrdenTrabajoController::class, 'list'])->name('list');
                Route::get('/create', [App\Http\Controllers\Panel\OrdenTrabajoController::class, 'create'])->name('create');
                Route::post('/', [App\Http\Controllers\Panel\OrdenTrabajoController::class, 'store'])->name('store');
            });
        });

        // Módulos de Mantenimiento
        Route::prefix('mantenimientos')->name('mantenimientos.')->group(function () {

            // Marcas
            Route::prefix('marcas')->name('marcas.')->group(function () {
                Route::get('/', [MarcaVehiculoController::class, 'index'])->name('index');
                Route::get('/list', [MarcaVehiculoController::class, 'list'])->name('list');
                Route::post('/', [MarcaVehiculoController::class, 'store'])->name('store');
                Route::put('/{id}', [MarcaVehiculoController::class, 'update'])->name('update');
                Route::delete('/{id}', [MarcaVehiculoController::class, 'destroy'])->name('destroy');
            });

            // Versiones
            Route::prefix('versiones')->name('versiones.')->group(function () {
                Route::get('/', [VersionVehiculoController::class, 'index'])->name('index');
                Route::get('/list', [VersionVehiculoController::class, 'list'])->name('list');
                Route::get('/by-modelo/{modeloId}', [VersionVehiculoController::class, 'listByModelo'])->name('listByModelo');
                Route::get('/modelos-list', [VersionVehiculoController::class, 'listModelos'])->name('listModelos'); // Dropdown population
                Route::post('/', [VersionVehiculoController::class, 'store'])->name('store');
                Route::put('/{id}', [VersionVehiculoController::class, 'update'])->name('update');
                Route::delete('/{id}', [VersionVehiculoController::class, 'destroy'])->name('destroy');
            });
            // Modelos (API para Modal)
            Route::prefix('modelos')->name('modelos.')->group(function () {
                Route::get('/by-marca/{marcaId}', [ModeloVehiculoController::class, 'listByMarca'])->name('listByMarca');
                Route::post('/', [ModeloVehiculoController::class, 'store'])->name('store');
                Route::put('/{id}', [ModeloVehiculoController::class, 'update'])->name('update');
                Route::delete('/{id}', [ModeloVehiculoController::class, 'destroy'])->name('destroy');
            });
            // Sucursales
            Route::prefix('sucursales')->name('sucursales.')->group(function () {
                Route::get('/', [SucursalController::class, 'index'])->name('index');
                Route::get('/list', [SucursalController::class, 'list'])->name('list');
                Route::post('/', [SucursalController::class, 'store'])->name('store');
                Route::put('/{id}', [SucursalController::class, 'update'])->name('update');
                Route::delete('/{id}', [SucursalController::class, 'destroy'])->name('destroy');
            });
        });

        // Seguridad (Roles y Usuarios)
        Route::prefix('seguridad')->name('seguridad.')->group(function () {

            // Roles
            Route::prefix('roles')->name('roles.')->group(function () {
                Route::get('/', [RoleController::class, 'index'])->name('index');
                Route::get('/list', [RoleController::class, 'list'])->name('list');
                Route::post('/', [RoleController::class, 'store'])->name('store');
                Route::put('/{id}', [RoleController::class, 'update'])->name('update');
                Route::delete('/{id}', [RoleController::class, 'destroy'])->name('destroy');
            });

            // Permisos
            Route::prefix('permisos')->name('permisos.')->group(function () {
                Route::get('/', [\App\Http\Controllers\Panel\PermissionController::class, 'index'])->name('index');
                Route::get('/list', [\App\Http\Controllers\Panel\PermissionController::class, 'list'])->name('list');
                Route::post('/', [\App\Http\Controllers\Panel\PermissionController::class, 'store'])->name('store');
                Route::put('/{id}', [\App\Http\Controllers\Panel\PermissionController::class, 'update'])->name('update');
                Route::delete('/{id}', [\App\Http\Controllers\Panel\PermissionController::class, 'destroy'])->name('destroy');
            });

            // Usuarios (Asignación Roles)
            Route::prefix('usuarios')->name('usuarios.')->group(function () {
                Route::get('/', [UsuarioController::class, 'index'])->name('index');
                Route::get('/list', [UsuarioController::class, 'list'])->name('list');
                Route::get('/sucursales-list', [UsuarioController::class, 'listSucursales'])->name('listSucursales');
                Route::post('/', [UsuarioController::class, 'store'])->name('store');
                Route::get('/roles-list', [UsuarioController::class, 'listRoles'])->name('listRoles');
                Route::post('/{id}/assign-role', [UsuarioController::class, 'assignRole'])->name('assignRole');
                Route::put('/{id}', [UsuarioController::class, 'update'])->name('update');
            });
        });

        // Recursos Humanos (Asistencias)
        Route::prefix('rrhh')->name('rrhh.')->group(function () {
            // Asistencias
            Route::prefix('asistencias')->name('asistencias.')->group(function () {
                Route::get('/mi-qr', function () {
                    return view('panel.rrhh.mi-qr');
                })->name('mi-qr');

                // Solo usuarios con permiso 'ver_asistencias' pueden entrar al listado
                Route::middleware('can_do:ver_asistencias')->group(function () {
                    Route::get('/', [App\Http\Controllers\Panel\AsistenciaController::class, 'index'])->name('index');
                    Route::get('/list', [App\Http\Controllers\Panel\AsistenciaController::class, 'list'])->name('list');
                });

                Route::get('/check-status/{userId}', [App\Http\Controllers\Panel\AsistenciaController::class, 'verifyUser'])->name('verifyUser');
                Route::get('/search-users', [App\Http\Controllers\Panel\AsistenciaController::class, 'searchUsers'])->name('searchUsers');
                Route::post('/register', [App\Http\Controllers\Panel\AsistenciaController::class, 'registerAttendance'])->name('register');
                Route::delete('/{id}', [App\Http\Controllers\Panel\AsistenciaController::class, 'destroy'])->name('destroy');
            });
        });
    });
});
