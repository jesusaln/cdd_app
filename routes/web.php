<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
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
use App\Http\Controllers\UserNotificationController;
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
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\CfdiController;


Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/clientes/export', [ClienteController::class, 'export'])
        ->name('clientes.export');
    Route::get('/productos/export', [ProductoController::class, 'export'])
        ->name('productos.export');
    Route::get('/servicios/export', [ServicioController::class, 'export'])
        ->name('servicios.export');
    Route::get('/carros/export', [CarroController::class, 'export'])
        ->name('carros.export');
    Route::get('/mantenimientos/export', [MantenimientoController::class, 'export'])
        ->name('mantenimientos.export');
    Route::get('/bitacora/export', [BitacoraActividadController::class, 'export'])
        ->name('bitacora.export');
});


/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
| Aquí defines todas las rutas web de tu aplicación. Estas rutas están
| cargadas por el RouteServiceProvider y automáticamente tienen el
| middleware 'web' aplicado.
*/

// Opción 1: Usando PATCH
Route::patch('/notifications/mark-all-read', [NotificationController::class, 'markAllAsRead']);

// Opción 2: Usando POST (más común)
Route::post('/notifications/mark-all-read', [NotificationController::class, 'markAllAsRead']);

// Opción 3: Usando PUT
Route::put('/notifications/mark-all-read', [NotificationController::class, 'markAllAsRead']);

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
Route::get('/empresas', [EmpresasController::class, 'index'])->name('empresas.index');

// =====================================================
// RUTA PARA PLACEHOLDERS SVG
// =====================================================
Route::get('/placeholder/{w}x{h}/{bg?}/{fg?}', function (int $w, int $h, $bg = 'e5e7eb', $fg = '6b7280') {
    $text = \Illuminate\Support\Str::of(request('text', 'Sin imagen'))->limit(40);
    $fontSize = max(12, min($w / 12, 24)); // Tamaño de fuente adaptativo

    $svg = <<<SVG
<svg xmlns="http://www.w3.org/2000/svg" width="{$w}" height="{$h}" viewBox="0 0 {$w} {$h}">
  <rect width="100%" height="100%" fill="#{$bg}"/>
  <text x="50%" y="50%" dominant-baseline="middle" text-anchor="middle"
        font-family="system-ui, -apple-system, sans-serif" font-size="{$fontSize}" fill="#{$fg}"
        font-weight="500">
    {$text}
  </text>
</svg>
SVG;

    return response($svg, 200, [
        'Content-Type' => 'image/svg+xml',
        'Cache-Control' => 'public, max-age=3600', // Cache por 1 hora
    ]);
})->name('placeholder');

// =====================================================
// RECURSOS PRINCIPALES
// =====================================================

// =====================================================
// RECURSOS PRINCIPALES
// =====================================================

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
    Route::resource('ordenescompra', OrdenCompraController::class);
    Route::post('ordenescompra/{id}/recibir', [OrdenCompraController::class, 'recibirOrden'])->name('ordenescompra.recibir');
    Route::post('ordenescompra/{id}/duplicate', [OrdenCompraController::class, 'duplicate'])->name('ordenescompra.duplicate');
    Route::post('ordenescompra/{id}/urgente', [OrdenCompraController::class, 'marcarUrgente'])->name('ordenescompra.urgente');
    Route::post('ordenescompra/{id}/convertir-compra', [OrdenCompraController::class, 'convertirACompra'])->name('ordenescompra.convertir-compra');

    Route::resource('clientes', ClienteController::class)->names('clientes');
    Route::resource('productos', ProductoController::class)->names('productos');
    Route::resource('proveedores', ProveedorController::class)->names('proveedores');
    Route::resource('categorias', CategoriaController::class)->names('categorias');
    Route::put('/categorias/{categoria}/toggle', [CategoriaController::class, 'toggle'])->name('categorias.toggle');
    Route::get('/categorias/export', [CategoriaController::class, 'export'])->name('categorias.export');
    Route::resource('marcas', MarcaController::class)->names('marcas');
    Route::put('/marcas/{marca}/toggle', [MarcaController::class, 'toggle'])->name('marcas.toggle');
    Route::get('/marcas/export', [MarcaController::class, 'export'])->name('marcas.export');
    Route::resource('almacenes', AlmacenController::class)->names('almacenes'); // ✅ DUPLICACIÓN ELIMINADA
    Route::put('/almacenes/{almacen}/toggle', [AlmacenController::class, 'toggle'])->name('almacenes.toggle');
    Route::get('/almacenes/export', [AlmacenController::class, 'export'])->name('almacenes.export');
    Route::resource('cotizaciones', CotizacionController::class)->names('cotizaciones');
    Route::resource('pedidos', PedidoController::class)->names('pedidos');
    Route::resource('ventas', VentaController::class)->names('ventas');
    Route::resource('servicios', ServicioController::class)->names('servicios');
    Route::put('/servicios/{servicio}/toggle', [ServicioController::class, 'toggle'])->name('servicios.toggle');
    Route::resource('usuarios', UserController::class)->names('usuarios');
    Route::resource('compras', CompraController::class)->names('compras');
    Route::post('/compras/{compra}/duplicate', [CompraController::class, 'duplicate'])->name('compras.duplicate');
    Route::post('/compras/{id}/cancel', [CompraController::class, 'cancel'])->name('compras.cancel');
    Route::resource('reportes', ReporteController::class);
    Route::resource('herramientas', HerramientaController::class)->names('herramientas');
    Route::resource('tecnicos', TecnicoController::class)->names('tecnicos');
    Route::resource('citas', CitaController::class)->names('citas');
    Route::resource('carros', CarroController::class)->names('carros');
    Route::resource('mantenimientos', MantenimientoController::class)->names('mantenimientos');
    Route::resource('equipos', EquipoController::class);
    Route::put('/equipos/{equipo}/toggle', [EquipoController::class, 'toggle'])->name('equipos.toggle');
    Route::get('/equipos/export', [EquipoController::class, 'export'])->name('equipos.export');
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
    // RUTAS ESPECÍFICAS DE PROVEEDORES
    // =====================================================
    Route::get('/proveedores/export', [ProveedorController::class, 'export'])->name('proveedores.export');
    Route::put('/proveedores/{proveedor}/toggle', [ProveedorController::class, 'toggle'])->name('proveedores.toggle');

    // =====================================================
    // RUTAS ESPECÍFICAS DE TÉCNICOS
    // =====================================================
    Route::get('/tecnicos/export', [TecnicoController::class, 'export'])->name('tecnicos.export');
    Route::put('/tecnicos/{tecnico}/toggle', [TecnicoController::class, 'toggle'])->name('tecnicos.toggle');

    // =====================================================
    // RUTAS ESPECÍFICAS DE USUARIOS
    // =====================================================
    Route::get('/usuarios/export', [UserController::class, 'export'])->name('usuarios.export');
    Route::put('/usuarios/{user}/toggle', [UserController::class, 'toggle'])->name('usuarios.toggle');

    // =====================================================
    // RUTAS ESPECÍFICAS DE PRODUCTOS
    // =====================================================
    Route::post('/productos/validate-stock', [ProductoController::class, 'validateStock'])->name('productos.validateStock');
    Route::put('/productos/{producto}/toggle', [ProductoController::class, 'toggle'])->name('productos.toggle');

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
    Route::post('/pedidos/{id}/confirmar', [PedidoController::class, 'confirmar'])->name('pedidos.confirmar');
    Route::post('/pedidos/{id}/enviar-a-venta', [PedidoController::class, 'enviarAVenta'])
        ->name('pedidos.enviar-a-venta');
    Route::post('/pedidos/{id}/cancel', [PedidoController::class, 'cancel'])->name('pedidos.cancel');
    Route::post('/pedidos/{pedido}/duplicate', [PedidoController::class, 'duplicate'])->name('pedidos.duplicate');

    // =====================================================
    // RUTAS ESPECÍFICAS DE VENTAS
    // =====================================================
    Route::post('/ventas/{venta}/duplicate', [VentaController::class, 'duplicate'])->name('ventas.duplicate');
    Route::post('/ventas/{id}/cancel', [VentaController::class, 'cancel'])->name('ventas.cancel');
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
    Route::get('/notifications', [UserNotificationController::class, 'index'])->name('notifications.index');
    Route::get('/notifications/unread-count', [UserNotificationController::class, 'unreadCount'])->name('notifications.unreadCount');
    Route::post('/notifications/mark-as-read', [UserNotificationController::class, 'markAsRead'])->name('notifications.markAsRead');
    Route::post('/notifications/mark-all-as-read', [UserNotificationController::class, 'markAllAsRead'])->name('notifications.markAllAsRead');
    Route::delete('/notifications/{id}', [UserNotificationController::class, 'destroy'])->name('notifications.destroy');
    Route::post('/notifications/delete-multiple', [UserNotificationController::class, 'destroyMultiple'])->name('notifications.destroyMultiple');

    // Ruta temporal para pruebas
    Route::post('/test-notification', [UserNotificationController::class, 'createTest'])->name('test.notification');



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

    // =====================================================
    // RUTAS CFDI (FacturaLO Plus)
    // =====================================================
    Route::get('/cfdi/timbrar-demo', [CfdiController::class, 'timbrarDemo']);
    Route::post('/cfdi/cancelar-demo', [CfdiController::class, 'cancelarDemo']);
    Route::post('/cfdi/estado-sat', [CfdiController::class, 'estadoSat']);
});
