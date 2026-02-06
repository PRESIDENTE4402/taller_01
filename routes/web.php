<?php

use App\Http\Controllers\Panel\SucursalController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\Panel\MarcaVehiculoController;
use App\Http\Controllers\Panel\RoleController;
use App\Http\Controllers\Panel\UsuarioController;
use App\Http\Controllers\Panel\VersionVehiculoController;
use App\Http\Controllers\Panel\ModeloVehiculoController;


// Public Routes
Route::get('/', function () {
    return view('welcome');
})->name('home');

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

            // Usuarios (Asignación Roles)
            Route::prefix('usuarios')->name('usuarios.')->group(function () {
                Route::get('/', [UsuarioController::class, 'index'])->name('index');
                Route::get('/list', [UsuarioController::class, 'list'])->name('list');
                Route::get('/roles-list', [UsuarioController::class, 'listRoles'])->name('listRoles');
                Route::post('/{id}/assign-role', [UsuarioController::class, 'assignRole'])->name('assignRole');
            });

        });
    });
});
