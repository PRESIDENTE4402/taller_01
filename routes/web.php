<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\Panel\MarcaVehiculoController;
use App\Http\Controllers\Panel\VersionVehiculoController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

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
                Route::get('/modelos-list', [VersionVehiculoController::class, 'listModelos'])->name('listModelos'); // Dropdown population
                Route::post('/', [VersionVehiculoController::class, 'store'])->name('store');
                Route::put('/{id}', [VersionVehiculoController::class, 'update'])->name('update');
                Route::delete('/{id}', [VersionVehiculoController::class, 'destroy'])->name('destroy');
            });
        });
    });
});
