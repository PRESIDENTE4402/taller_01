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

// Public API Routes for Landing Page
Route::get('/api/landing/brands', [CitaController::class, 'getBrands']);
Route::get('/api/landing/branches', [CitaController::class, 'getBranches']);
Route::get('/api/landing/models/{marcaId}', [CitaController::class, 'getModels']);
Route::get('/api/landing/versions/{modeloId}', [CitaController::class, 'getVersions']);
Route::get('/api/landing/client-lookup', [CitaController::class, 'clientLookup']);
Route::post('/api/landing/citas', [CitaController::class, 'store'])->name('landing.citas.store');
Route::get('/api/landing/images', [\App\Http\Controllers\Panel\LandingImageController::class, 'getPublicImages']);
Route::get('/api/landing/historial', [App\Http\Controllers\Landing\HistorialController::class, 'search']);

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
        Route::get('/', [\App\Http\Controllers\Panel\DashboardController::class, 'index'])->name('dashboard');


        // Gestión de Clientes (Directorio Principal)
        Route::prefix('clientes')->name('clientes.')->middleware('can_do:gestionar_clientes')->group(function () {
            Route::get('/', [App\Http\Controllers\Panel\ClienteController::class, 'index'])->name('index');
            Route::get('/list', [App\Http\Controllers\Panel\ClienteController::class, 'list'])->name('list');
            Route::get('/{id}', [App\Http\Controllers\Panel\ClienteController::class, 'show'])->name('show'); // New Profile View
            Route::post('/', [App\Http\Controllers\Panel\ClienteController::class, 'store'])->name('store');
            Route::put('/{id}', [App\Http\Controllers\Panel\ClienteController::class, 'update'])->name('update');
            Route::delete('/{id}', [App\Http\Controllers\Panel\ClienteController::class, 'destroy'])->name('destroy');
        });

        Route::prefix('vehiculos')->name('vehiculos.')->middleware('can_do:gestionar_vehiculos')->group(function () {
            Route::get('/', [VehiculoController::class, 'index'])->name('index');
            Route::get('/{id}', [VehiculoController::class, 'show'])->name('show');
            Route::post('/', [VehiculoController::class, 'store'])->name('store');
            Route::put('/{id}', [VehiculoController::class, 'update'])->name('update');
            Route::delete('/{id}', [VehiculoController::class, 'destroy'])->name('destroy');
        });

        // Módulos de Operaciones (Nuevo Grupo)
        Route::prefix('operaciones')->name('operaciones.')->group(function () {
            // Citas
            Route::prefix('citas')->name('citas.')->middleware('can_do:gestionar_citas')->group(function () {
                Route::get('/', [App\Http\Controllers\Panel\CitaController::class, 'index'])->name('index');
                Route::get('/list', [App\Http\Controllers\Panel\CitaController::class, 'list'])->name('list');
                Route::post('/', [App\Http\Controllers\Panel\CitaController::class, 'store'])->name('store'); // Manual
                Route::put('/{id}', [App\Http\Controllers\Panel\CitaController::class, 'update'])->name('update');
                Route::delete('/{id}', [App\Http\Controllers\Panel\CitaController::class, 'destroy'])->name('destroy');

                // APIs auxiliares para creación manual
                Route::get('/api/search-clients', [App\Http\Controllers\Panel\CitaController::class, 'searchClients'])->name('searchClients');
                Route::get('/api/check-client-exists', [App\Http\Controllers\Panel\CitaController::class, 'checkClientExists'])->name('checkClientExists');
                Route::get('/api/search-vehicles', [App\Http\Controllers\Panel\CitaController::class, 'searchVehicles'])->name('searchVehicles');
                Route::get('/api/get-client-vehicles/{clienteId}', [App\Http\Controllers\Panel\CitaController::class, 'getClientVehicles'])->name('getClientVehicles');
                Route::get('/api/get-brands', [App\Http\Controllers\Panel\CitaController::class, 'getBrands'])->name('getBrands');

                Route::get('/api/calendar-counts', [App\Http\Controllers\Panel\CitaController::class, 'getCalendarCounts'])->name('getCalendarCounts'); // New
                Route::post('/{id}/notify', [App\Http\Controllers\Panel\CitaController::class, 'sendNotification'])->name('notify');
            });





            // Órdenes de Trabajo (New)


            Route::prefix('ordenes-trabajo')->name('ordenes_trabajo.')->middleware('can_do:gestionar_ordenes_trabajo')->group(function () {
                Route::get('/api/search-repuestos', [App\Http\Controllers\Panel\OrdenTrabajoController::class, 'searchRepuestos'])->name('searchRepuestos');
                Route::post('/api/fast-repuesto', [App\Http\Controllers\Panel\OrdenTrabajoController::class, 'storeFastRepuesto'])->name('fastRepuesto'); // NUEVO REPUESTO EXPRÉS
                Route::get('/', [App\Http\Controllers\Panel\OrdenTrabajoController::class, 'index'])->name('index');
                Route::get('/dashboard', [App\Http\Controllers\Panel\OrdenTrabajoController::class, 'dashboard'])->name('dashboard')->middleware('can_do:gestionar_recepcion'); // New Dashboard
                Route::get('/list', [App\Http\Controllers\Panel\OrdenTrabajoController::class, 'list'])->name('list');
                Route::get('/create', [App\Http\Controllers\Panel\OrdenTrabajoController::class, 'create'])->name('create');
                Route::post('/', [App\Http\Controllers\Panel\OrdenTrabajoController::class, 'store'])->name('store');
                Route::get('/{id}/edit', [App\Http\Controllers\Panel\OrdenTrabajoController::class, 'edit'])->name('edit');
                Route::put('/{id}', [App\Http\Controllers\Panel\OrdenTrabajoController::class, 'update'])->name('update');
                Route::get('/{id}', [App\Http\Controllers\Panel\OrdenTrabajoController::class, 'show'])->name('show');
                Route::get('/{id}/details', [App\Http\Controllers\Panel\OrdenTrabajoController::class, 'getDetails'])->name('details');
                Route::get('/{id}/print', [App\Http\Controllers\Panel\OrdenTrabajoController::class, 'print'])->name('print');
                Route::put('/{id}/status', [App\Http\Controllers\Panel\OrdenTrabajoController::class, 'updateStatus'])->name('status.update');
                Route::put('/{id}/diagnostico', [App\Http\Controllers\Panel\OrdenTrabajoController::class, 'updateDiagnostico'])->name('diagnostico.update');
                Route::post('/{id}/details', [App\Http\Controllers\Panel\OrdenTrabajoController::class, 'addDetail'])->name('details.store');
                Route::put('/{id}/details/{detail_id}', [App\Http\Controllers\Panel\OrdenTrabajoController::class, 'updateDetail'])->name('details.update');
                Route::delete('/{id}/details/{detail_id}', [App\Http\Controllers\Panel\OrdenTrabajoController::class, 'deleteDetail'])->name('details.destroy');
                Route::put('/{id}/details/{detail_id}/status', [App\Http\Controllers\Panel\OrdenTrabajoController::class, 'updateDetailStatus'])->name('details.status.update');
                Route::post('/{id}/tasks', [App\Http\Controllers\Panel\OrdenTrabajoController::class, 'addTask'])->name('tasks.store');
                Route::put('/{id}/tasks/{task_id}', [App\Http\Controllers\Panel\OrdenTrabajoController::class, 'updateTask'])->name('tasks.update');
                Route::delete('/{id}/tasks/{task_id}', [App\Http\Controllers\Panel\OrdenTrabajoController::class, 'deleteTask'])->name('tasks.destroy');
                Route::post('/{id}/pagos', [App\Http\Controllers\Panel\OrdenTrabajoController::class, 'storePago'])->name('pagos.store');
                Route::put('/{id}/pagos/{pago_id}', [App\Http\Controllers\Panel\OrdenTrabajoController::class, 'updatePago'])->name('pagos.update');
                Route::delete('/{id}/pagos/{pago_id}', [App\Http\Controllers\Panel\OrdenTrabajoController::class, 'deletePago'])->name('pagos.destroy');
                Route::post('/{id}/notify', [App\Http\Controllers\Panel\OrdenTrabajoController::class, 'sendNotification'])->name('notify');
                Route::put('/citas/{id}/cancel', [App\Http\Controllers\Panel\OrdenTrabajoController::class, 'cancelCita'])->name('citas.cancel');
            });
        });

        // Tablero de Colaboradores
        Route::prefix('colaboradores')->name('colaboradores.')->group(function () {
            Route::get('/', [App\Http\Controllers\Panel\ColaboradorController::class, 'index'])->name('index');
            Route::get('/list', [App\Http\Controllers\Panel\ColaboradorController::class, 'list'])->name('list');
            Route::post('/assign', [App\Http\Controllers\Panel\ColaboradorController::class, 'assignTask'])->name('assign');
            Route::get('/{id}', [App\Http\Controllers\Panel\ColaboradorController::class, 'show'])->name('show');
            Route::get('/{id}/print', [App\Http\Controllers\Panel\ColaboradorController::class, 'print'])->name('print');
        });

        // Tablero de Mecánicos (Mis Tareas)
        Route::prefix('mis-tareas')->name('mis_tareas.')->group(function () {
            Route::get('/', [\App\Http\Controllers\Panel\MisTareasController::class, 'index'])->name('index');
            Route::post('/{id}/status', [\App\Http\Controllers\Panel\MisTareasController::class, 'updateStatus'])->name('status');
            Route::post('/{id}/notes', [\App\Http\Controllers\Panel\MisTareasController::class, 'addNotes'])->name('notes');
            Route::delete('/{id}', [\App\Http\Controllers\Panel\MisTareasController::class, 'destroy'])->name('destroy');
        });


        // Notificaciones
        Route::get('/notifications/{id}/read', function ($id) {
            $notification = Auth::user()->notifications()->findOrFail($id);
            $notification->markAsRead();
            return redirect($notification->data['url'] ?? route('panel.dashboard'));
        })->name('notifications.read');


        // Módulos de Mantenimiento
        Route::prefix('mantenimientos')->name('mantenimientos.')->group(function () {

            // Plantillas de Mensajes
            Route::prefix('plantillas-mensajes')->name('plantillas_mensajes.')->group(function () {
                Route::get('/', [App\Http\Controllers\Panel\PlantillaMensajeController::class, 'index'])->name('index');
                Route::get('/list', [App\Http\Controllers\Panel\PlantillaMensajeController::class, 'list'])->name('list');
                Route::post('/', [App\Http\Controllers\Panel\PlantillaMensajeController::class, 'store'])->name('store');
                Route::put('/{id}', [App\Http\Controllers\Panel\PlantillaMensajeController::class, 'update'])->name('update');
                Route::delete('/{id}', [App\Http\Controllers\Panel\PlantillaMensajeController::class, 'destroy'])->name('destroy');
            });

            // Marcas
            Route::prefix('marcas')->name('marcas.')->middleware('can_do:gestionar_marcas')->group(function () {
                Route::get('/', [MarcaVehiculoController::class, 'index'])->name('index');
                Route::get('/list', [MarcaVehiculoController::class, 'list'])->name('list');
                Route::post('/', [MarcaVehiculoController::class, 'store'])->name('store');
                Route::put('/{id}', [MarcaVehiculoController::class, 'update'])->name('update');
                Route::delete('/{id}', [MarcaVehiculoController::class, 'destroy'])->name('destroy');
            });

            // Versiones
            Route::prefix('versiones')->name('versiones.')->middleware('can_do:gestionar_versiones')->group(function () {
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
            Route::prefix('sucursales')->name('sucursales.')->middleware('can_do:gestionar_sucursales')->group(function () {
                Route::get('/', [SucursalController::class, 'index'])->name('index');
                Route::get('/list', [SucursalController::class, 'list'])->name('list');
                Route::post('/', [SucursalController::class, 'store'])->name('store');
                Route::put('/{id}', [SucursalController::class, 'update'])->name('update');
                Route::delete('/{id}', [SucursalController::class, 'destroy'])->name('destroy');
            });


            // Categorias (SaaS)
            Route::prefix('categorias')->name('categorias.')->middleware('can_do:gestionar_inventario')->group(function () {
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
            Route::prefix('reportes')->name('reportes.')->middleware('can_do:ver_reportes')->group(function () {
                Route::get('/inventario-por-sucursal', [\App\Http\Controllers\Panel\ReporteController::class, 'inventarioPorSucursal'])->name('inventario-por-sucursal');
                Route::get('/movimientos-inventario', [\App\Http\Controllers\Panel\ReporteController::class, 'movimientosInventario'])->name('movimientos-inventario');
                Route::get('/movimientos-pdf', [\App\Http\Controllers\Panel\ReporteController::class, 'exportMovimientosPDF'])->name('movimientos-pdf');
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
            Route::prefix('repuestos')->name('repuestos.')->middleware('can_do:gestionar_inventario')->group(function () {
                Route::get('/', [\App\Http\Controllers\Panel\RepuestoController::class, 'index'])->name('index');
                Route::get('/list', [\App\Http\Controllers\Panel\RepuestoController::class, 'list'])->name('list');
                Route::post('/', [\App\Http\Controllers\Panel\RepuestoController::class, 'store'])->name('store');
                Route::put('/{id}', [\App\Http\Controllers\Panel\RepuestoController::class, 'update'])->name('update');
                Route::delete('/{id}', [\App\Http\Controllers\Panel\RepuestoController::class, 'destroy'])->name('destroy');
                // Movimientos de Stock
                Route::get('/history/{id}', [\App\Http\Controllers\Panel\RepuestoController::class, 'getHistory'])->name('history');
                Route::post('/movement/{id}', [\App\Http\Controllers\Panel\RepuestoController::class, 'storeMovement'])->name('storeMovement');
                Route::post('/bulk-movement', [\App\Http\Controllers\Panel\RepuestoController::class, 'bulkMovement'])->name('bulkMovement');
            });

            // Imágenes para Landing Page
            Route::prefix('imagenes-landing')->name('imagenes_landing.')->middleware('can_do:gestionar_imagenes_landing')->group(function () {
                Route::get('/', [\App\Http\Controllers\Panel\LandingImageController::class, 'index'])->name('index');
                Route::get('/list', [\App\Http\Controllers\Panel\LandingImageController::class, 'list'])->name('list');
                Route::post('/', [\App\Http\Controllers\Panel\LandingImageController::class, 'store'])->name('store');
                Route::put('/{id}', [\App\Http\Controllers\Panel\LandingImageController::class, 'update'])->name('update');
                Route::delete('/{id}', [\App\Http\Controllers\Panel\LandingImageController::class, 'destroy'])->name('destroy');
                Route::post('/upload', [\App\Http\Controllers\Panel\LandingImageController::class, 'upload'])->name('upload');
            });

            // Inventario Recepción (Items Checklist)
            Route::prefix('inventario-recepcion')->name('inventario_recepcion.')->middleware('can_do:gestionar_items_recepcion')->group(function () {
                Route::get('/', [\App\Http\Controllers\Panel\InventarioRecepcionItemController::class, 'index'])->name('index');
                Route::get('/list', [\App\Http\Controllers\Panel\InventarioRecepcionItemController::class, 'list'])->name('list');
                Route::post('/', [\App\Http\Controllers\Panel\InventarioRecepcionItemController::class, 'store'])->name('store');
                Route::put('/{id}', [\App\Http\Controllers\Panel\InventarioRecepcionItemController::class, 'update'])->name('update');
                Route::delete('/{id}', [\App\Http\Controllers\Panel\InventarioRecepcionItemController::class, 'destroy'])->name('destroy');
            });
        });

        // Ventas
        Route::prefix('ventas')->name('ventas.')->group(function () {
            Route::get('/', [\App\Http\Controllers\Panel\VentaController::class, 'index'])->name('index');
            Route::get('/list', [\App\Http\Controllers\Panel\VentaController::class, 'list'])->name('list');
            Route::get('/{id}', [\App\Http\Controllers\Panel\VentaController::class, 'show'])->name('show');
            Route::post('/', [\App\Http\Controllers\Panel\VentaController::class, 'store'])->name('store');
            Route::post('/{id}/return', [\App\Http\Controllers\Panel\VentaController::class, 'processReturn'])->name('return');
        });

        // Seguridad (Roles y Usuarios)
        Route::prefix('seguridad')->name('seguridad.')->group(function () {

            // Roles
            Route::prefix('roles')->name('roles.')->middleware('can_do:gestionar_roles')->group(function () {
                Route::get('/', [RoleController::class, 'index'])->name('index');
                Route::get('/list', [RoleController::class, 'list'])->name('list');
                Route::post('/', [RoleController::class, 'store'])->name('store');
                Route::put('/{id}', [RoleController::class, 'update'])->name('update');
                Route::delete('/{id}', [RoleController::class, 'destroy'])->name('destroy');
            });

            // Permisos
            Route::prefix('permisos')->name('permisos.')->middleware('can_do:gestionar_permisos')->group(function () {
                Route::get('/', [\App\Http\Controllers\Panel\PermissionController::class, 'index'])->name('index');
                Route::get('/list', [\App\Http\Controllers\Panel\PermissionController::class, 'list'])->name('list');
                Route::post('/', [\App\Http\Controllers\Panel\PermissionController::class, 'store'])->name('store');
                Route::put('/{id}', [\App\Http\Controllers\Panel\PermissionController::class, 'update'])->name('update');
                Route::delete('/{id}', [\App\Http\Controllers\Panel\PermissionController::class, 'destroy'])->name('destroy');
            });

            // Usuarios (Asignación Roles)
            Route::prefix('usuarios')->name('usuarios.')->middleware('can_do:gestionar_usuarios')->group(function () {
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
        // Planilla (Pagos)
        Route::prefix('planilla')->name('planilla.')->middleware('can_do:gestionar_planilla')->group(function () {
            Route::prefix('pagos')->name('pagos.')->group(function () {
                Route::get('/', [App\Http\Controllers\Panel\Planilla\PagoTrabajadorController::class, 'index'])->name('index');
                Route::get('/list', [App\Http\Controllers\Panel\Planilla\PagoTrabajadorController::class, 'list'])->name('list');
                Route::get('/trabajador-data/{userId}', [App\Http\Controllers\Panel\Planilla\PagoTrabajadorController::class, 'getTrabajadorData'])->name('trabajadorData');
                Route::get('/trabajador-data-edit/{pagoId}', [App\Http\Controllers\Panel\Planilla\PagoTrabajadorController::class, 'getTrabajadorDataEdit'])->name('trabajadorDataEdit');
                Route::post('/', [App\Http\Controllers\Panel\Planilla\PagoTrabajadorController::class, 'store'])->name('store');
                Route::put('/{id}', [App\Http\Controllers\Panel\Planilla\PagoTrabajadorController::class, 'update'])->name('update');
                Route::delete('/{id}', [App\Http\Controllers\Panel\Planilla\PagoTrabajadorController::class, 'destroy'])->name('destroy');
                Route::get('/{id}/detalles', [App\Http\Controllers\Panel\Planilla\PagoTrabajadorController::class, 'detalles'])->name('detalles');
            });
        });
    });
});

// Test Routes
require base_path('routes/test.php');
