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
use App\Http\Controllers\CobranzaController;
use App\Http\Controllers\EntregaDineroController;
use App\Http\Controllers\BitacoraActividadController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\CfdiController;
use App\Http\Controllers\ReporteTecnicoController;
use App\Http\Controllers\ReportesDashboardController;
use App\Http\Controllers\AsignacionHerramientaController;
use App\Http\Controllers\EstadoHerramientaController;
use App\Http\Controllers\AsignacionMasivaController;
use App\Http\Controllers\TecnicoHerramientasController;


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
    Route::post('ordenescompra/{id}/enviar-compra', [OrdenCompraController::class, 'enviarACompra'])->name('ordenescompra.enviar-compra');
    Route::post('ordenescompra/{id}/recibir-mercancia', [OrdenCompraController::class, 'recibirMercancia'])->name('ordenescompra.recibir-mercancia');
    Route::post('ordenescompra/{id}/duplicate', [OrdenCompraController::class, 'duplicate'])->name('ordenescompra.duplicate');
    Route::post('ordenescompra/{id}/urgente', [OrdenCompraController::class, 'marcarUrgente'])->name('ordenescompra.urgente');
    Route::post('ordenescompra/{id}/cancelar', [OrdenCompraController::class, 'cancelar'])->name('ordenescompra.cancelar');
    Route::post('ordenescompra/{id}/cambiar-estado', [OrdenCompraController::class, 'cambiarEstado'])->name('ordenescompra.cambiar-estado');
    Route::get('ordenescompra/{id}/estado', [OrdenCompraController::class, 'getEstado'])->name('ordenescompra.get-estado');

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
// =====================================================
// RUTAS DE REPORTES ORGANIZADAS
// =====================================================

// Dashboard principal de reportes
Route::get('/reportes', [ReportesDashboardController::class, 'index'])->name('reportes.index');

// CRUD de reportes personalizados (debe ir primero para evitar conflictos)
Route::resource('reportes', ReporteController::class)->except(['index', 'show']);

// Reportes específicos (después del resource para tener prioridad)
Route::get('/reportes/ventas', [ReporteController::class, 'index'])->name('reportes.ventas');
Route::get('/reportes/corte-diario', [ReporteController::class, 'corteDiario'])->name('reportes.corte-diario');
Route::get('/reportes/export', [ReporteController::class, 'exportarCorteDiario'])->name('reportes.export');
Route::get('/reportes/tecnicos', [ReporteTecnicoController::class, 'index'])->name('reportes.tecnicos.index');
Route::get('/reportes/tecnicos/datos', [ReporteTecnicoController::class, 'datos'])->name('reportes.tecnicos.datos');

// Nuevos reportes
Route::get('/reportes/ventas-pendientes', [ReporteController::class, 'ventasPendientes'])->name('reportes.ventas-pendientes');
Route::get('/reportes/clientes', [ReporteController::class, 'clientes'])->name('reportes.clientes');
Route::get('/reportes/inventario', [ReporteController::class, 'inventario'])->name('reportes.inventario');
Route::get('/reportes/servicios', [ReporteController::class, 'servicios'])->name('reportes.servicios');
Route::get('/reportes/citas', [ReporteController::class, 'citas'])->name('reportes.citas');
Route::get('/reportes/mantenimientos', [ReporteController::class, 'mantenimientos'])->name('reportes.mantenimientos');
Route::get('/reportes/rentas', [ReporteController::class, 'rentas'])->name('reportes.rentas');
Route::get('/reportes/cobranzas', [ReporteController::class, 'cobranzas'])->name('reportes.cobranzas');
Route::get('/reportes/ganancias', [ReporteController::class, 'ganancias'])->name('reportes.ganancias');
Route::get('/reportes/productos', [ReporteController::class, 'productos'])->name('reportes.productos');
Route::get('/reportes/proveedores', [ReporteController::class, 'proveedores'])->name('reportes.proveedores');
Route::get('/reportes/empleados', [ReporteController::class, 'empleados'])->name('reportes.empleados');
Route::get('/reportes/auditoria', [ReporteController::class, 'auditoria'])->name('reportes.auditoria');

// Exportaciones
Route::get('/reportes/clientes/export', [ReporteController::class, 'exportarClientes'])->name('reportes.clientes.export');
Route::get('/reportes/inventario/export', [ReporteController::class, 'exportarInventario'])->name('reportes.inventario.export');
Route::get('/reportes/productos/export', [ReporteController::class, 'exportarProductos'])->name('reportes.productos.export');
    Route::resource('herramientas', HerramientaController::class)->names('herramientas');
    Route::post('herramientas/{herramienta}/asignar', [HerramientaController::class, 'asignar'])->name('herramientas.asignar');
    Route::post('herramientas/{herramienta}/recibir', [HerramientaController::class, 'recibir'])->name('herramientas.recibir');
    Route::resource('tecnicos', TecnicoController::class)->names('tecnicos');

    // Rutas para asignaciones de herramientas
    Route::resource('herramientas/asignaciones', AsignacionHerramientaController::class)->names('herramientas.asignaciones');
    Route::post('herramientas/asignaciones/{asignacion}/agregar-firma', [AsignacionHerramientaController::class, 'agregarFirma'])->name('herramientas.asignaciones.agregar-firma');
    Route::get('herramientas/{herramienta}/asignaciones-activas', [AsignacionHerramientaController::class, 'getAsignacionesActivas'])->name('herramientas.asignaciones-activas');

    // Rutas para estados de herramientas
    Route::resource('herramientas/estados', EstadoHerramientaController::class)->names('herramientas.estados');
    Route::get('herramientas/{herramienta}/estadisticas-desgaste', [EstadoHerramientaController::class, 'getEstadisticasDesgaste'])->name('herramientas.estadisticas-desgaste');
    Route::get('herramientas/estados/reporte-atencion', [EstadoHerramientaController::class, 'reporteAtencion'])->name('herramientas.estados.reporte-atencion');

    // Rutas para asignaciones masivas de herramientas
    Route::resource('herramientas/asignaciones-masivas', AsignacionMasivaController::class)->names('herramientas.asignaciones-masivas');
    Route::post('herramientas/asignaciones-masivas/{asignacionMasiva}/autorizar', [AsignacionMasivaController::class, 'autorizar'])->name('herramientas.asignaciones-masivas.autorizar');
    Route::post('herramientas/asignaciones-masivas/{asignacionMasiva}/completar', [AsignacionMasivaController::class, 'completar'])->name('herramientas.asignaciones-masivas.completar');
    Route::post('herramientas/asignaciones-masivas/{asignacionMasiva}/cancelar', [AsignacionMasivaController::class, 'cancelar'])->name('herramientas.asignaciones-masivas.cancelar');
    Route::post('herramientas/asignaciones-masivas/{asignacionMasiva}/devolver-herramienta/{herramienta}', [AsignacionMasivaController::class, 'devolverHerramienta'])->name('herramientas.asignaciones-masivas.devolver-herramienta');

    // Rutas para control de herramientas por técnico
    Route::get('herramientas/tecnicos-herramientas', [TecnicoHerramientasController::class, 'index'])->name('herramientas.tecnicos-herramientas.index');
    Route::get('herramientas/tecnicos-herramientas/{tecnico}', [TecnicoHerramientasController::class, 'show'])->name('herramientas.tecnicos-herramientas.show');
    Route::post('herramientas/tecnicos-herramientas/{tecnico}/actualizar-responsabilidad', [TecnicoHerramientasController::class, 'actualizarResponsabilidad'])->name('herramientas.tecnicos-herramientas.actualizar-responsabilidad');
    Route::get('herramientas/tecnicos-herramientas/{tecnico}/reporte', [TecnicoHerramientasController::class, 'reporte'])->name('herramientas.tecnicos-herramientas.reporte');
    Route::get('herramientas/tecnicos-herramientas/alertas', [TecnicoHerramientasController::class, 'alertas'])->name('herramientas.tecnicos-herramientas.alertas');
    Route::resource('citas', CitaController::class)->names('citas');
    Route::resource('carros', CarroController::class)->names('carros');
    Route::resource('mantenimientos', MantenimientoController::class)->names('mantenimientos');
    Route::post('mantenimientos/{mantenimiento}/completar', [MantenimientoController::class, 'completar'])->name('mantenimientos.completar');
    Route::post('mantenimientos/{mantenimiento}/marcar-realizado-hoy', [MantenimientoController::class, 'marcarRealizadoHoy'])->name('mantenimientos.marcar-realizado-hoy');
    Route::post('mantenimientos/generar-recurrentes', [MantenimientoController::class, 'generarRecurrentes'])->name('mantenimientos.generar-recurrentes');
    Route::resource('equipos', EquipoController::class);
    Route::put('/equipos/{equipo}/toggle', [EquipoController::class, 'toggle'])->name('equipos.toggle');
    Route::get('/equipos/export', [EquipoController::class, 'export'])->name('equipos.export');
    Route::resource('rentas', RentasController::class);
    Route::post('/rentas/{renta}/duplicate', [RentasController::class, 'duplicate'])->name('rentas.duplicate');
    Route::post('/rentas/{renta}/suspender', [RentasController::class, 'suspender'])->name('rentas.suspender');
    Route::post('/rentas/{renta}/reactivar', [RentasController::class, 'reactivar'])->name('rentas.reactivar');
    Route::post('/rentas/{renta}/finalizar', [RentasController::class, 'finalizar'])->name('rentas.finalizar');
    Route::post('/rentas/{renta}/renovar', [RentasController::class, 'renovar'])->name('rentas.renovar');

    Route::resource('cobranza', CobranzaController::class);
    Route::post('/cobranza/{id}/marcar-pagada', [CobranzaController::class, 'marcarPagada'])->name('cobranza.marcar-pagada');
    Route::post('/cobranza/venta/{id}/marcar-pagada', [CobranzaController::class, 'marcarVentaPagada'])->name('cobranza.venta.marcar-pagada');
    Route::post('/cobranza/generar-automaticas', [CobranzaController::class, 'generarCobranzas'])->name('cobranza.generar-automaticas');

    Route::get('/entregas-dinero/pendientes-por-usuario', [EntregaDineroController::class, 'pendientesPorUsuario'])->name('entregas-dinero.pendientes-por-usuario');
    Route::post('/entregas-dinero/marcar-automatico/{tipo_origen}/{id_origen}', [EntregaDineroController::class, 'marcarAutomaticoRecibido'])->name('entregas-dinero.marcar-automatico');
    Route::resource('entregas-dinero', EntregaDineroController::class);

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
    // RUTAS ESPECÍFICAS DE CITAS
    // =====================================================
    Route::get('/citas/export', [CitaController::class, 'export'])->name('citas.export');

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
    Route::post('/ventas/{id}/marcar-pagado', [VentaController::class, 'marcarPagado'])->name('ventas.marcar-pagado');
    // =====================================================
    // RUTAS ESPECÍFICAS DE USUARIOS
    // =====================================================
    Route::get('/perfil', [UserController::class, 'profile'])->name('perfil');
    // ✅ CONFLICTO RESUELTO: Eliminada ruta duplicada - usa la del resource usuarios.show

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
