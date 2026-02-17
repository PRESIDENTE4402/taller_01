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
use App\Http\Controllers\Panel\VehiculoController; // Import

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
    Route::prefix('panel')->name('panel.')->middleware(['verify_sucursal', 'set_current_sucursal'])->group(function () {
        Route::get('/', function () {
            return view('dashboard');
        })->name('dashboard');


        // Gestión de Clientes (Directorio Principal)
        Route::prefix('clientes')->name('clientes.')->group(function () {
            Route::get('/', [App\Http\Controllers\Panel\ClienteController::class, 'index'])->name('index');
            Route::get('/list', [App\Http\Controllers\Panel\ClienteController::class, 'list'])->name('list');
            Route::get('/{id}', [App\Http\Controllers\Panel\ClienteController::class, 'show'])->name('show'); // New Profile View
            Route::post('/', [App\Http\Controllers\Panel\ClienteController::class, 'store'])->name('store');
            Route::put('/{id}', [App\Http\Controllers\Panel\ClienteController::class, 'update'])->name('update');
            Route::delete('/{id}', [App\Http\Controllers\Panel\ClienteController::class, 'destroy'])->name('destroy');
        });

        Route::prefix('vehiculos')->name('vehiculos.')->group(function () {
            Route::post('/', [VehiculoController::class, 'store'])->name('store');
            Route::put('/{id}', [VehiculoController::class, 'update'])->name('update');
            Route::delete('/{id}', [VehiculoController::class, 'destroy'])->name('destroy');
        });

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
                Route::get('/api/search-vehicles', [App\Http\Controllers\Panel\CitaController::class, 'searchVehicles'])->name('searchVehicles');
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
                Route::get('/{id}/print', [App\Http\Controllers\Panel\OrdenTrabajoController::class, 'print'])->name('print');
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


            // Categorias (SaaS)
            Route::prefix('categorias')->name('categorias.')->group(function () {
                Route::get('/', [\App\Http\Controllers\Panel\CategoriaController::class, 'index'])->name('index');
                Route::get('/list', [\App\Http\Controllers\Panel\CategoriaController::class, 'list'])->name('list');
                Route::post('/', [\App\Http\Controllers\Panel\CategoriaController::class, 'store'])->name('store');
                Route::put('/{id}', [\App\Http\Controllers\Panel\CategoriaController::class, 'update'])->name('update');
                Route::delete('/{id}', [\App\Http\Controllers\Panel\CategoriaController::class, 'destroy'])->name('destroy');
            });

            // Atributos Dinámicos (Configuración por Categoría)
            Route::prefix('atributos-dinamicos')->name('atributos_dinamicos.')->group(function () {
                Route::get('/categoria/{categoriaId}', [\App\Http\Controllers\Panel\DynamicAttributeSchemaController::class, 'listByCategoria'])->name('listByCategoria');
                Route::post('/', [\App\Http\Controllers\Panel\DynamicAttributeSchemaController::class, 'store'])->name('store');
                Route::put('/{id}', [\App\Http\Controllers\Panel\DynamicAttributeSchemaController::class, 'update'])->name('update');
                Route::delete('/{id}', [\App\Http\Controllers\Panel\DynamicAttributeSchemaController::class, 'destroy'])->name('destroy');
                Route::post('/reorder', [\App\Http\Controllers\Panel\DynamicAttributeSchemaController::class, 'reorder'])->name('reorder');
            });

            // Reportes (Análisis y Auditoría)
            Route::prefix('reportes')->name('reportes.')->group(function () {
                Route::get('/inventario-por-sucursal', [\App\Http\Controllers\Panel\ReporteController::class, 'inventarioPorSucursal'])->name('inventario-por-sucursal');
                Route::get('/stock-bajo', [\App\Http\Controllers\Panel\ReporteController::class, 'stockBajo'])->name('stock-bajo');
                Route::get('/comparativa-precios', [\App\Http\Controllers\Panel\ReporteController::class, 'comparativaPreciosS'])->name('comparativa-precios');
                Route::get('/audit-atributos', [\App\Http\Controllers\Panel\ReporteController::class, 'auditAtributosdinamicos'])->name('audit-atributos');
                Route::get('/inventario-pdf', [\App\Http\Controllers\Panel\ReporteController::class, 'exportInventarioPDF'])->name('inventario-pdf');
            });

            // Configuración de Atributos Dinámicos (Interfaz de Usuario)
            Route::get('/configurar-atributos', function () {
                return view('panel.mantenimientos.atributos_dinamicos.index');
            })->name('configurar-atributos');

            // Repuestos (SaaS)
            Route::prefix('repuestos')->name('repuestos.')->group(function () {
                Route::get('/', [\App\Http\Controllers\Panel\RepuestoController::class, 'index'])->name('index');
                Route::get('/list', [\App\Http\Controllers\Panel\RepuestoController::class, 'list'])->name('list');
                Route::post('/', [\App\Http\Controllers\Panel\RepuestoController::class, 'store'])->name('store');
                Route::put('/{id}', [\App\Http\Controllers\Panel\RepuestoController::class, 'update'])->name('update');
                Route::delete('/{id}', [\App\Http\Controllers\Panel\RepuestoController::class, 'destroy'])->name('destroy');
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

// Test Routes
require base_path('routes/test.php');
