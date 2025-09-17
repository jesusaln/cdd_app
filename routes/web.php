<?php

use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use Illuminate\Foundation\Application;
use App\Http\Controllers\ClienteController;
use App\Http\Controllers\ProductoController;
use App\Http\Controllers\CategoriaController;
use App\Http\Controllers\MarcaController;
use App\Http\Controllers\ProveedorController;
use App\Http\Controllers\AlmacenController;
use App\Http\Controllers\CotizacionController;
use App\Http\Controllers\PedidoController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\VentaController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\CompraController;
use App\Http\Controllers\ReporteController;
use App\Http\Controllers\HerramientaController;
use App\Http\Controllers\TecnicoController;
use App\Http\Controllers\CarroController;
use App\Http\Controllers\MantenimientoController;
use App\Http\Controllers\CitaController;
use App\Http\Controllers\PanelController;
use App\Http\Controllers\EmpresasController;
use App\Http\Controllers\ServicioController;
use App\Http\Controllers\InventarioController;
use App\Http\Controllers\OrdenCompraController;
use App\Http\Controllers\DatabaseBackupController;
use App\Http\Controllers\EquipoController;
use App\Http\Controllers\RentasController;
use App\Http\Controllers\BitacoraActividadController;



Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/clientes/export', [ClienteController::class, 'export'])
        ->name('clientes.export');
});


/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
| Aquí defines todas las rutas web de tu aplicación. Estas rutas están
| cargadas por el RouteServiceProvider y automáticamente tienen el
| middleware 'web' aplicado.
*/

// =====================================================
// RUTAS PÚBLICAS
// =====================================================
Route::get('/', function () {
    return Inertia::render('Welcome', [
        'canLogin' => Route::has('login'),
        'laravelVersion' => Application::VERSION,
        'phpVersion' => PHP_VERSION,
    ]);
})->name('welcome');

Route::get('/empresas', [EmpresasController::class, 'index'])->name('empresas.index');

// =====================================================
// RECURSOS PRINCIPALES (Fuera del middleware auth)
// =====================================================
Route::resource('ordenescompra', OrdenCompraController::class);
Route::post('ordenescompra/{id}/recibir', [OrdenCompraController::class, 'recibirOrden'])->name('ordenescompra.recibir');

Route::get('/productos/{id}/inventario', [ProductoController::class, 'showInventario'])->name('productos.inventario');
Route::resource('inventario', InventarioController::class);

// =====================================================
// RUTAS PROTEGIDAS POR AUTENTICACIÓN
// =====================================================
Route::middleware(['auth:sanctum', config('jetstream.auth_session'), 'verified'])->group(function () {

    // Dashboard y Panel
    Route::get('/panel', [PanelController::class, 'index'])->name('panel');
    Route::get('/dashboard', function () {
        return redirect()->route('panel');
    })->name('dashboard');

    // =====================================================
    // RECURSOS PRINCIPALES
    // =====================================================
    Route::resource('clientes', ClienteController::class)->names('clientes');
    Route::resource('productos', ProductoController::class)->names('productos');
    Route::resource('proveedores', ProveedorController::class)->names('proveedores');
    Route::resource('categorias', CategoriaController::class)->names('categorias');
    Route::resource('marcas', MarcaController::class)->names('marcas');
    Route::resource('almacenes', AlmacenController::class)->names('almacenes'); // ✅ DUPLICACIÓN ELIMINADA
    Route::resource('cotizaciones', CotizacionController::class)->names('cotizaciones');
    Route::resource('pedidos', PedidoController::class)->names('pedidos');
    Route::resource('ventas', VentaController::class)->names('ventas');
    Route::resource('servicios', ServicioController::class)->names('servicios');
    Route::resource('usuarios', UserController::class)->names('usuarios');
    Route::resource('compras', CompraController::class)->names('compras');
    Route::resource('reportes', ReporteController::class);
    Route::resource('herramientas', HerramientaController::class)->names('herramientas');
    Route::resource('tecnicos', TecnicoController::class)->names('tecnicos');
    Route::resource('citas', CitaController::class)->names('citas');
    Route::resource('carros', CarroController::class)->names('carros');
    Route::resource('mantenimientos', MantenimientoController::class)->names('mantenimientos');
    Route::resource('equipos', EquipoController::class);
    Route::resource('rentas', RentasController::class);
    Route::resource('bitacora-actividades', BitacoraActividadController::class)->names('bitacora-actividades');


    // =====================================================
    // RUTAS ESPECÍFICAS DE CLIENTES
    // =====================================================
    Route::post('/clientes/validar-rfc', [ClienteController::class, 'validarRfc'])->name('clientes.validarRfc');
    Route::get('/clientes/validar-email', [ClienteController::class, 'validarEmail'])->name('clientes.validarEmail');
    Route::get('/clientes/export', [ClienteController::class, 'export'])->name('clientes.export');
    Route::post('/clientes/search', [ClienteController::class, 'search'])->name('clientes.search');
    Route::get('/clientes/stats', [ClienteController::class, 'stats'])->name('clientes.stats');
    Route::post('/clientes/clear-cache', [ClienteController::class, 'clearCache'])->name('clientes.clearCache');
    Route::put('/clientes/{cliente}/toggle', [ClienteController::class, 'toggle'])->name('clientes.toggle');

    // =====================================================
    // RUTAS ESPECÍFICAS DE PRODUCTOS
    // =====================================================
    Route::post('/productos/validate-stock', [ProductoController::class, 'validateStock'])->name('productos.validateStock');

    // =====================================================
    // RUTAS ESPECÍFICAS DE COTIZACIONES
    // =====================================================
    Route::post('/cotizaciones/{id}/convertir-a-venta', [CotizacionController::class, 'convertirAVenta'])->name('cotizaciones.convertir-a-venta');
    Route::get('/cotizaciones/{id}/confirmar-pedido', [CotizacionController::class, 'mostrarConfirmacionPedido'])->name('cotizaciones.confirmar-pedido');
    Route::get('/cotizaciones/{id}/confirmar-venta', [CotizacionController::class, 'mostrarConfirmacionVenta'])->name('cotizaciones.confirmar-venta');
    Route::post('/cotizaciones/draft', [CotizacionController::class, 'guardarBorrador'])->name('cotizaciones.storeDraft');
    Route::post('/cotizaciones/{cotizacion}/duplicate', [CotizacionController::class, 'duplicate'])->name('cotizaciones.duplicate');
    Route::post('/cotizaciones/{id}/enviar-pedido', [CotizacionController::class, 'enviarAPedido'])->name('cotizaciones.enviar-pedido');
    Route::post('/cotizaciones/{id}/cancel', [CotizacionController::class, 'cancel'])->name('cotizaciones.cancel');

    // =====================================================
    // RUTAS ESPECÍFICAS DE PEDIDOS
    // =====================================================
    Route::post('/pedidos/{id}/enviar-a-venta', [PedidoController::class, 'enviarAVenta'])
        ->name('pedidos.enviar-a-venta');
    Route::post('/pedidos/{id}/cancel', [PedidoController::class, 'cancel'])->name('pedidos.cancel');
    Route::post('/pedidos/{pedido}/duplicate', [PedidoController::class, 'duplicate'])->name('pedidos.duplicate');
    Route::get('/pedidos', [PedidoController::class, 'index'])->name('pedidos.index');
    // =====================================================
    // RUTAS ESPECÍFICAS DE USUARIOS
    // =====================================================
    Route::get('/perfil', [UserController::class, 'profile'])->name('perfil');
    // ✅ CONFLICTO RESUELTO: Eliminada ruta duplicada - usa la del resource usuarios.show

    // =====================================================
    // RUTAS ESPECÍFICAS DE REPORTES
    // =====================================================
    Route::get('/reportes', [ReporteController::class, 'index'])->name('reportes.index'); // Nota: Esto podría conflictuar con el resource

    // =====================================================
    // RUTAS ESPECÍFICAS DE CITAS
    // =====================================================
    Route::put('/citas/{id}', [CitaController::class, 'update']); // Nota: Esto duplica el resource update
    Route::patch('/citas/{id}/update-index', [CitaController::class, 'updateIndex'])->name('citas.updateIndex');

    // =====================================================
    // RUTAS DE NOTIFICACIONES
    // =====================================================
    Route::get('/notifications', [NotificationController::class, 'index'])->name('notifications.index');
    Route::post('/notifications/mark-as-read', [NotificationController::class, 'markAsRead'])->name('notifications.markAsRead');



    // =====================================================
    // RUTAS BACKUP
    // =====================================================


    Route::middleware(['auth'])->group(function () {
        Route::prefix('admin/backup')->name('backup.')->group(function () {
            Route::get('/', [DatabaseBackupController::class, 'index'])->name('index');
            Route::post('/create', [DatabaseBackupController::class, 'create'])->name('create');
            Route::get('/download/{path}', [DatabaseBackupController::class, 'download'])->name('download');
            Route::delete('/delete/{path}', [DatabaseBackupController::class, 'delete'])->name('delete');
            Route::post('/restore/{path}', [DatabaseBackupController::class, 'restore'])->name('restore');
            Route::post('/clean', [DatabaseBackupController::class, 'clean'])->name('clean');
        });
    });




    Route::middleware(['auth', 'verified'])->group(function () {
        Route::resource('bitacora', BitacoraActividadController::class)
            ->parameters(['bitacora' => 'bitacora'])
            ->names('bitacora');
    });
});
