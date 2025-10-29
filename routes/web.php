<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Foundation\Application;
use App\Http\Controllers\ClienteController;
use App\Http\Controllers\PrestamoController;
use App\Http\Controllers\PagoPrestamoController;
use App\Http\Controllers\ProductoController;
use App\Http\Controllers\CategoriaController;
use App\Http\Controllers\MarcaController;
use App\Http\Controllers\ProveedorController;
use App\Http\Controllers\AlmacenController;
use App\Http\Controllers\CotizacionController;
use App\Http\Controllers\PedidoController;
use App\Http\Controllers\UserNotificationController;
use App\Http\Controllers\VentaController;
use App\Http\Controllers\CuentasPorCobrarController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\CompraController;
use App\Http\Controllers\ReporteController;
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
use App\Http\Controllers\RentasContratoController;
use App\Http\Controllers\CobranzaController;
use App\Http\Controllers\EntregaDineroController;
use App\Http\Controllers\BitacoraActividadController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\CfdiController;
use App\Http\Controllers\ReporteTecnicoController;
use App\Http\Controllers\ReportesDashboardController;
use App\Http\Controllers\ReporteMovimientosController;
// Controladores del módulo de herramientas
use App\Http\Controllers\HerramientaController;
// use App\Http\Controllers\AsignacionHerramientaController;
// use App\Http\Controllers\EstadoHerramientaController;
// use App\Http\Controllers\AsignacionMasivaController;
// use App\Http\Controllers\TecnicoHerramientasController;
use App\Http\Controllers\GestionHerramientasController;
use App\Http\Controllers\MovimientoInventarioController;
use App\Http\Controllers\TraspasoController;
use App\Http\Controllers\AjusteInventarioController;
use App\Http\Controllers\MovimientoManualController;
use App\Http\Controllers\ReportesInventarioController;
use App\Http\Controllers\ReportesController;
use App\Http\Controllers\EmpresaConfiguracionController;
use App\Http\Controllers\CategoriaHerramientaController;
use App\Http\Controllers\VacacionController;
use App\Http\Controllers\RegistroVacacionesController;
use App\Http\Controllers\EmpresaWhatsAppController;
use App\Http\Controllers\ImageController;
use App\Models\Empresa;

// Forzar patrón numérico para {herramienta} y evitar colisiones con rutas estáticas
Route::pattern('herramienta', '[0-9]+');


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
| AquÃƒÂ­ defines todas las rutas web de tu aplicaciÃƒÂ³n. Estas rutas estÃƒÂ¡n
| cargadas por el RouteServiceProvider y automÃƒÂ¡ticamente tienen el
| middleware 'web' aplicado.
*/


// Ruta para marcar todas las notificaciones como leÃƒÂ­das
Route::post('/notifications/mark-all-read', [NotificationController::class, 'markAllAsRead']);


// =====================================================
// RUTAS PÃƒÅ¡BLICAS
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
    $fontSize = max(12, min($w / 12, 24)); // TamaÃƒÂ±o de fuente adaptativo

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
// RUTAS PROTEGIDAS POR AUTENTICACIÃƒâ€œN
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
    Route::post('ordenescompra/{id}/convertir-directo', [OrdenCompraController::class, 'convertirDirecto'])->name('ordenescompra.convertir-directo');
    Route::post('ordenescompra/{id}/duplicate', [OrdenCompraController::class, 'duplicate'])->name('ordenescompra.duplicate');
    Route::post('ordenescompra/{id}/urgente', [OrdenCompraController::class, 'marcarUrgente'])->name('ordenescompra.urgente');
    Route::post('ordenescompra/{id}/cancelar', [OrdenCompraController::class, 'cancelar'])->name('ordenescompra.cancelar');
    Route::post('ordenescompra/{id}/cambiar-estado', [OrdenCompraController::class, 'cambiarEstado'])->name('ordenescompra.cambiar-estado');
    Route::get('ordenescompra/{id}/estado', [OrdenCompraController::class, 'getEstado'])->name('ordenescompra.get-estado');
    Route::post('ordenescompra/{id}/enviar-email', [OrdenCompraController::class, 'enviarEmail'])->name('ordenescompra.enviar-email');
    Route::post('ordenescompra/{ordenId}/productos/{productoId}/editar-precio', [OrdenCompraController::class, 'editarPrecioProducto'])->name('ordenescompra.editar-precio-producto');
    Route::get('productos/{productoId}/historial-precios', [OrdenCompraController::class, 'obtenerHistorialPrecios'])->name('productos.historial-precios');
    Route::get('reportes/historial-precios/{productoId}', [OrdenCompraController::class, 'mostrarHistorialPrecios'])->name('reportes.historial-precios');

    Route::resource('clientes', ClienteController::class)->names('clientes');
    Route::resource('prestamos', PrestamoController::class)->names('prestamos');
    Route::post('/prestamos/calcular-pagos', [PrestamoController::class, 'calcularPagos'])->name('prestamos.calcular-pagos');
    Route::patch('/prestamos/{prestamo}/cambiar-estado', [PrestamoController::class, 'cambiarEstado'])->name('prestamos.cambiar-estado');
    Route::get('/prestamos/{prestamo}/pagare', [PrestamoController::class, 'generarPagare'])->name('prestamos.pagare');
    Route::post('/prestamos/{prestamo}/enviar-recordatorio-whatsapp', [PrestamoController::class, 'enviarRecordatorioWhatsApp'])
        ->withoutMiddleware([\App\Http\Middleware\CustomVerifyCsrfToken::class, \Illuminate\Foundation\Http\Middleware\VerifyCsrfToken::class, 'auth:sanctum', config('jetstream.auth_session'), 'verified'])
        ->name('prestamos.enviar-recordatorio-whatsapp');
    Route::get('/prestamos/{prestamo}/pagos', [PagoPrestamoController::class, 'pagosPorPrestamo'])->name('prestamos.pagos');
    Route::resource('pagos', PagoPrestamoController::class)->names('pagos');
    Route::prefix('productos')->name('productos.')->group(function () {
        Route::get('/', [ProductoController::class, 'index'])->name('index');
        Route::get('/create', [ProductoController::class, 'create'])->name('create');
        Route::post('/', [ProductoController::class, 'store'])->name('store');
        Route::get('/export', [ProductoController::class, 'export'])->name('export');

        // Rutas de series - ANTES de las rutas con {producto}
        Route::get('/{producto}/series', [ProductoController::class, 'series'])->name('series');
        Route::put('/{producto}/series/{serie}', [ProductoController::class, 'updateSerie'])->name('series.update');
        Route::get('/{producto}/stock-detalle', [ProductoController::class, 'getStockDetalle'])->name('stock-detalle');

        // Rutas con parámetro {producto} - AL FINAL
        Route::get('/{producto}', [ProductoController::class, 'show'])->name('show');
        Route::get('/{producto}/edit', [ProductoController::class, 'edit'])->name('edit');
        Route::put('/{producto}', [ProductoController::class, 'update'])->name('update');
        Route::delete('/{producto}', [ProductoController::class, 'destroy'])->name('destroy');
        Route::put('/{producto}/toggle', [ProductoController::class, 'toggle'])->name('toggle');
    });
    Route::get('productos/{id}/stock-detalle', [ProductoController::class, 'getStockDetalle'])->name('productos.stock-detalle');
    Route::resource('proveedores', ProveedorController::class)->names('proveedores');
    Route::resource('categorias', CategoriaController::class)->names('categorias');
    Route::put('/categorias/{categoria}/toggle', [CategoriaController::class, 'toggle'])->name('categorias.toggle');
    Route::get('/categorias/export', [CategoriaController::class, 'export'])->name('categorias.export');
    Route::resource('marcas', MarcaController::class)->names('marcas');
    Route::put('/marcas/{marca}/toggle', [MarcaController::class, 'toggle'])->name('marcas.toggle');
    Route::get('/marcas/export', [MarcaController::class, 'export'])->name('marcas.export');
    Route::resource('almacenes', AlmacenController::class)->names('almacenes'); // Ã¢Å“â€¦ DUPLICACIÃƒâ€œN ELIMINADA
    Route::put('/almacenes/{almacen}/toggle', [AlmacenController::class, 'toggle'])->name('almacenes.toggle');
    Route::get('/almacenes/export', [AlmacenController::class, 'export'])->name('almacenes.export');
    Route::resource('traspasos', TraspasoController::class)->names('traspasos');
    Route::resource('movimientos-inventario', MovimientoInventarioController::class)->names('movimientos-inventario');
    Route::resource('ajustes-inventario', AjusteInventarioController::class)->names('ajustes-inventario');
    Route::resource('movimientos-manuales', MovimientoManualController::class)->names('movimientos-manuales');

    // Rutas específicas de cotizaciones antes del resource para evitar conflictos
    Route::get('/cotizaciones/siguiente-numero', [CotizacionController::class, 'obtenerSiguienteNumero'])->name('cotizaciones.siguiente-numero');

    Route::resource('cotizaciones', CotizacionController::class)->names('cotizaciones')->where(['cotizaciones' => '[0-9]+']);
    Route::resource('pedidos', PedidoController::class)->names('pedidos');
    Route::resource('ventas', VentaController::class)->names('ventas');
    Route::resource('servicios', ServicioController::class)->names('servicios');
    Route::put('/servicios/{servicio}/toggle', [ServicioController::class, 'toggle'])->name('servicios.toggle');
    Route::resource('usuarios', UserController::class)->names('usuarios');
    Route::resource('compras', CompraController::class)->names('compras');
    Route::post('/compras/{id}/cancel', [CompraController::class, 'cancel'])->name('compras.cancel');
    Route::delete('/compras/{id}/force', [CompraController::class, 'forceDestroy'])->name('compras.force-destroy');
    Route::resource('cuentas-por-pagar', \App\Http\Controllers\CuentasPorPagarController::class)->names('cuentas-por-pagar');
    Route::post('/cuentas-por-pagar/{id}/registrar-pago', [\App\Http\Controllers\CuentasPorPagarController::class, 'registrarPago'])->name('cuentas-por-pagar.registrar-pago');
    Route::post('/cuentas-por-pagar/{id}/marcar-pagado', [\App\Http\Controllers\CuentasPorPagarController::class, 'marcarPagado'])->name('cuentas-por-pagar.marcar-pagado');
    Route::resource('cuentas-por-cobrar', CuentasPorCobrarController::class)->names('cuentas-por-cobrar');
    Route::post('/cuentas-por-cobrar/{id}/registrar-pago', [CuentasPorCobrarController::class, 'registrarPago'])->name('cuentas-por-cobrar.registrar-pago');
    // =====================================================
    // RUTAS DE REPORTES ORGANIZADAS
    // =====================================================

    // Centro de reportes con tabs
    Route::get('/reportes', [ReportesDashboardController::class, 'indexTabs'])->name('reportes.index');

    // Dashboard de categorÃƒÂ­as de reportes
    Route::get('/reportes/dashboard', [ReportesController::class, 'index'])->name('reportes.dashboard');

    // CRUD de reportes personalizados (debe ir primero para evitar conflictos)
    Route::resource('reportes', ReporteController::class)->except(['index', 'show']);

    // Reportes especÃƒÂ­ficos redirigidos al centro de reportes con tabs
    Route::get('/reportes/ventas', function () {
        return redirect('/reportes?tab=ventas');
    })->name('reportes.ventas');
    Route::get('/reportes/corte-diario', function () {
        return redirect('/reportes?tab=corte');
    })->name('reportes.corte-diario');
    // Registrar entrega directa desde Corte Diario
    Route::post('/reportes/corte/entrega', [EntregaDineroController::class, 'storeDesdeCorte'])
        ->name('reportes.corte.entrega');
    Route::get('/reportes/export', [ReporteController::class, 'exportarCorteDiario'])->name('reportes.export');
    Route::get('/reportes/tecnicos', function () {
        return redirect('/reportes?tab=personal');
    })->name('reportes.tecnicos.index');
    Route::get('/reportes/tecnicos/datos', [ReporteTecnicoController::class, 'datos'])->name('reportes.tecnicos.datos');

    // Nuevos reportes redirigidos
    Route::get('/reportes/ventas-pendientes', function () {
        return redirect('/reportes?tab=ventas');
    })->name('reportes.ventas-pendientes');
    Route::get('/reportes/clientes', function () {
        return redirect('/reportes?tab=clientes');
    })->name('reportes.clientes');
    Route::get('/reportes/inventario', function () {
        return redirect('/reportes?tab=inventario');
    })->name('reportes.inventario');
    Route::get('/reportes/servicios', function () {
        return redirect('/reportes?tab=servicios');
    })->name('reportes.servicios');
    Route::get('/reportes/citas', function () {
        return redirect('/reportes?tab=citas');
    })->name('reportes.citas');
    Route::get('/reportes/mantenimientos', function () {
        return redirect('/reportes?tab=mantenimientos');
    })->name('reportes.mantenimientos');
    Route::get('/reportes/rentas', function () {
        return redirect('/reportes?tab=rentas');
    })->name('reportes.rentas');
    Route::get('/reportes/cobranzas', function () {
        return redirect('/reportes?tab=cobranzas');
    })->name('reportes.cobranzas');
    Route::get('/reportes/ganancias', function () {
        return redirect('/reportes?tab=ganancias');
    })->name('reportes.ganancias');
    Route::get('/reportes/productos', function () {
        return redirect('/reportes?tab=productos');
    })->name('reportes.productos');
    Route::get('/reportes/proveedores', function () {
        return redirect('/reportes?tab=proveedores');
    })->name('reportes.proveedores');
    Route::get('/reportes/empleados', function () {
        return redirect('/reportes?tab=empleados');
    })->name('reportes.empleados');
    Route::get('/reportes/auditoria', function () {
        return redirect('/reportes?tab=auditoria');
    })->name('reportes.auditoria');

    // Reporte de préstamos por cliente
    Route::get('/reportes/prestamos-por-cliente', [ReporteController::class, 'prestamosPorCliente'])->name('reportes.prestamos-por-cliente');
    Route::get('/reportes/prestamos-por-cliente/export', [ReporteController::class, 'exportarPrestamosPorCliente'])->name('reportes.prestamos-por-cliente.export');

    // Reporte de movimientos de inventario redirigido
    Route::get('/reportes/movimientos-inventario', function () {
        return redirect('/reportes?tab=movimientos');
    })->name('reportes.movimientos-inventario');
    Route::get('/reportes/movimientos-inventario/{id}', [ReporteMovimientosController::class, 'show'])->name('reportes.movimientos-inventario.show');
    Route::get('/reportes/movimientos-inventario-export', [ReporteMovimientosController::class, 'export'])->name('reportes.movimientos-inventario.export');

    // Reportes especÃƒÂ­ficos de inventario
    Route::get('/reportes/inventario/dashboard', [ReportesInventarioController::class, 'index'])->name('reportes.inventario.dashboard');
    Route::get('/reportes/inventario/stock-por-almacen', [ReportesInventarioController::class, 'stockPorAlmacen'])->name('reportes.inventario.stock-por-almacen');
    Route::get('/reportes/inventario/productos-bajo-stock', [ReportesInventarioController::class, 'productosBajoStock'])->name('reportes.inventario.productos-bajo-stock');
    Route::get('/reportes/inventario/movimientos-por-periodo', [ReportesInventarioController::class, 'movimientosPorPeriodo'])->name('reportes.inventario.movimientos-por-periodo');
    Route::get('/reportes/inventario/costos', [ReportesInventarioController::class, 'costosInventario'])->name('reportes.inventario.costos');

    // Exportaciones
    Route::get('/reportes/clientes/export', [ReporteController::class, 'exportarClientes'])->name('reportes.clientes.export');
    Route::get('/reportes/inventario/export', [ReporteController::class, 'exportarInventario'])->name('reportes.inventario.export');
    Route::get('/reportes/productos/export', [ReporteController::class, 'exportarProductos'])->name('reportes.productos.export');
    Route::resource('tecnicos', TecnicoController::class)->names('tecnicos');
    // Catálogo de herramientas (módulo mejorado)
    Route::resource('herramientas', HerramientaController::class)->names('herramientas');
    Route::get('herramientas-dashboard', [HerramientaController::class, 'dashboard'])->name('herramientas.dashboard');
    Route::get('herramientas-mantenimiento', [HerramientaController::class, 'mantenimiento'])->name('herramientas.mantenimiento');
    Route::post('herramientas/{herramienta}/mantenimiento', [HerramientaController::class, 'registrarMantenimiento'])->name('herramientas.registrar-mantenimiento');
    Route::get('herramientas/{herramienta}/estadisticas', [HerramientaController::class, 'estadisticas'])->name('herramientas.estadisticas');
    Route::post('herramientas/{herramienta}/cambiar-estado', [HerramientaController::class, 'cambiarEstado'])->name('herramientas.cambiar-estado');
    Route::get('herramientas-alertas', function () {
        return redirect()->route('herramientas.index');
    })->name('herramientas-alertas');
    // Alias con nombre con punto para coincidir con Ziggy 'herramientas*'
    Route::get('herramientas-alertas', function () {
        return redirect()->route('herramientas.index');
    })->name('herramientas.alertas');
    Route::get('herramientas-mantenimiento', [HerramientaController::class, 'mantenimiento'])->name('herramientas-mantenimiento');
    Route::get('herramientas-reportes', [HerramientaController::class, 'reportes'])->name('herramientas.reportes');
    Route::post('herramientas/reasignar', [GestionHerramientasController::class, 'reasignar'])->name('herramientas.reasignar');
    // Gestión de Herramientas - módulo independiente (index, create, edit)
    Route::get('herramientas/gestion', [GestionHerramientasController::class, 'index'])->name('herramientas.gestion.index');
    Route::get('herramientas/gestion/create', [GestionHerramientasController::class, 'create'])->name('herramientas.gestion.create');
    Route::get('herramientas/gestion/{tecnico}/edit', [GestionHerramientasController::class, 'edit'])->name('herramientas.gestion.edit');
    Route::post('herramientas/gestion/asignar', [GestionHerramientasController::class, 'asignar'])->name('herramientas.gestion.asignar');
    Route::put('herramientas/gestion/{tecnico}', [GestionHerramientasController::class, 'update'])->name('herramientas.gestion.update');
    Route::get('herramientas/gestion/{tecnico}/exportar', [GestionHerramientasController::class, 'exportarPorTecnico'])->name('herramientas.gestion.exportar');
    Route::get('herramientas/gestion/{tecnico}/descargar', [GestionHerramientasController::class, 'descargarReporteTecnico'])->name('herramientas.gestion.descargar');

    // Alias para enlaces antiguos del sidebar
    Route::get('herramientas/tecnicos-herramientas', function () {
        return redirect()->to('/herramientas/gestion');
    })->name('herramientas.tecnicos-herramientas.index');
    Route::get('herramientas/tecnicos-herramientas/{tecnico}', function ($tecnico) {
        return redirect()->to("/herramientas/gestion/{$tecnico}/edit");
    })->name('herramientas.tecnicos-herramientas.show');

    // (Eliminado previamente) Ruta obsoleta de reporte de estados de herramientas

    // Eliminado: asignaciones masivas de herramientas

    // Rutas para control de herramientas por tÃƒÂ©cnico
    Route::resource('citas', CitaController::class)->names('citas');
    Route::post('/citas/{id}/convertir-a-pedido', [CitaController::class, 'convertirAPedido'])->name('citas.convertir-a-pedido');
    Route::post('/citas/{id}/convertir-a-venta', [CitaController::class, 'convertirAVenta'])->name('citas.convertir-a-venta');
    Route::resource('carros', CarroController::class)->names('carros');
    Route::resource('mantenimientos', MantenimientoController::class)->names('mantenimientos');
    Route::post('mantenimientos/{mantenimiento}/marcar-realizado-hoy', [MantenimientoController::class, 'marcarRealizadoHoy'])->name('mantenimientos.marcar-realizado-hoy');
    Route::post('mantenimientos/generar-recurrentes', [MantenimientoController::class, 'generarRecurrentes'])->name('mantenimientos.generar-recurrentes');

    // Rutas API para validaciones de mantenimiento
    Route::get('mantenimientos/api/{carroId}/servicios/{tipoServicio}', [MantenimientoController::class, 'getServiciosPorTipo'])->name('mantenimientos.api.servicios-por-tipo');
    Route::post('mantenimientos/api/validar-servicio', [MantenimientoController::class, 'validarServicio'])->name('mantenimientos.api.validar-servicio');
    Route::get('mantenimientos/api/estadisticas', [MantenimientoController::class, 'getEstadisticasMantenimientos'])->name('mantenimientos.api.estadisticas');

    // Rutas PATCH para acciones rápidas de mantenimiento
    Route::patch('mantenimientos/{mantenimiento}/completar', [MantenimientoController::class, 'completar'])->name('mantenimientos.completar');
    Route::patch('mantenimientos/{mantenimiento}/posponer', [MantenimientoController::class, 'posponer'])->name('mantenimientos.posponer');
    Route::patch('mantenimientos/{mantenimiento}/reprogramar', [MantenimientoController::class, 'reprogramar'])->name('mantenimientos.reprogramar');
    Route::resource('equipos', EquipoController::class);
    Route::put('/equipos/{equipo}/toggle', [EquipoController::class, 'toggle'])->name('equipos.toggle');
    Route::get('/equipos/export', [EquipoController::class, 'export'])->name('equipos.export');
    Route::resource('rentas', RentasController::class);
    // Contrato de Renta (PDF)
    Route::get('/rentas/{renta}/contrato', [RentasContratoController::class, 'contratoPDF'])->name('rentas.contrato');
    Route::post('/rentas/{renta}/duplicate', [RentasController::class, 'duplicate'])->name('rentas.duplicate');
    Route::post('/rentas/{renta}/suspender', [RentasController::class, 'suspender'])->name('rentas.suspender');
    Route::post('/rentas/{renta}/reactivar', [RentasController::class, 'reactivar'])->name('rentas.reactivar');
    Route::post('/rentas/{renta}/finalizar', [RentasController::class, 'finalizar'])->name('rentas.finalizar');
    Route::post('/rentas/{renta}/renovar', [RentasController::class, 'renovar'])->name('rentas.renovar');

    // Categorías de herramientas (gestión completa)
    Route::get('/herramientas/categorias', [CategoriaHerramientaController::class, 'index'])->name('herramientas.categorias.index');
    Route::post('/herramientas/categorias', [CategoriaHerramientaController::class, 'store'])->name('herramientas.categorias.store');
    Route::get('/herramientas/categorias/{categoria}', [CategoriaHerramientaController::class, 'show'])->name('herramientas.categorias.show');
    Route::put('/herramientas/categorias/{categoria}', [CategoriaHerramientaController::class, 'update'])->name('herramientas.categorias.update');
    Route::delete('/herramientas/categorias/{categoria}', [CategoriaHerramientaController::class, 'destroy'])->name('herramientas.categorias.destroy');
    Route::patch('/herramientas/categorias/{categoria}/toggle', [CategoriaHerramientaController::class, 'toggle'])->name('herramientas.categorias.toggle');

    Route::resource('cobranza', CobranzaController::class);
    Route::post('/cobranza/{id}/marcar-pagada', [CobranzaController::class, 'marcarPagada'])->name('cobranza.marcar-pagada');
    Route::post('/cobranza/venta/{id}/marcar-pagada', [CobranzaController::class, 'marcarVentaPagada'])->name('cobranza.venta.marcar-pagada');
    Route::post('/cobranza/generar-automaticas', [CobranzaController::class, 'generarCobranzas'])->name('cobranza.generar-automaticas');

    Route::get('/entregas-dinero/pendientes-por-usuario', [EntregaDineroController::class, 'pendientesPorUsuario'])->name('entregas-dinero.pendientes-por-usuario');
    Route::get('/entregas-dinero/reporte-pagos', [EntregaDineroController::class, 'reportePagosRecibidos'])->name('entregas-dinero.reporte-pagos');
    Route::post('/entregas-dinero/marcar-automatico/{tipo_origen}/{id_origen}', [EntregaDineroController::class, 'marcarAutomaticoRecibido'])->name('entregas-dinero.marcar-automatico');
    Route::post('/entregas-dinero/{id}/marcar-entregado-responsable', [EntregaDineroController::class, 'marcarEntregadoResponsable'])->name('entregas-dinero.marcar-entregado-responsable');
    Route::resource('entregas-dinero', EntregaDineroController::class);

    Route::resource('bitacora-actividades', BitacoraActividadController::class)->names('bitacora-actividades');

    // =====================================================
    // RUTAS DE CONFIGURACIÓN DE EMPRESA
    // =====================================================
    Route::prefix('empresa')->name('empresa-configuracion.')->group(function () {
        Route::get('/configuracion', [EmpresaConfiguracionController::class, 'index'])->name('index');
        Route::match(['post', 'put'], '/configuracion', [EmpresaConfiguracionController::class, 'update'])->name('update');
        Route::post('/configuracion/logo', [EmpresaConfiguracionController::class, 'subirLogo'])->name('subir-logo');
        Route::post('/configuracion/favicon', [EmpresaConfiguracionController::class, 'subirFavicon'])->name('subir-favicon');
        Route::post('/configuracion/logo-reportes', [EmpresaConfiguracionController::class, 'subirLogoReportes'])->name('subir-logo-reportes');
        Route::delete('/configuracion/logo', [EmpresaConfiguracionController::class, 'eliminarLogo'])->name('eliminar-logo');
        Route::delete('/configuracion/favicon', [EmpresaConfiguracionController::class, 'eliminarFavicon'])->name('eliminar-favicon');
        Route::delete('/configuracion/logo-reportes', [EmpresaConfiguracionController::class, 'eliminarLogoReportes'])->name('eliminar-logo-reportes');
        Route::get('/configuracion/api', [EmpresaConfiguracionController::class, 'getConfig'])->name('api');
        Route::post('/configuracion/preview-colores', [EmpresaConfiguracionController::class, 'previewColores'])->name('preview-colores');
        Route::post('/configuracion/test-email', [EmpresaConfiguracionController::class, 'testEmail'])->name('test-email');
    });


    // =====================================================
    // RUTAS ESPECÃƒÂFICAS DE CLIENTES
    // =====================================================
    Route::post('/clientes/validar-rfc', [ClienteController::class, 'validarRfc'])->name('clientes.validarRfc');
    Route::get('/clientes/validar-email', [ClienteController::class, 'validarEmail'])->name('clientes.validarEmail');
    Route::get('/clientes/export', [ClienteController::class, 'export'])->name('clientes.export');
    Route::post('/clientes/search', [ClienteController::class, 'search'])->name('clientes.search');
    Route::get('/clientes/stats', [ClienteController::class, 'stats'])->name('clientes.stats');
    Route::post('/clientes/clear-cache', [ClienteController::class, 'clearCache'])->name('clientes.clearCache');
    Route::put('/clientes/{cliente}/toggle', [ClienteController::class, 'toggle'])->name('clientes.toggle');
    Route::get('/clientes/{cliente}/can-delete', [ClienteController::class, 'canDelete'])->name('clientes.canDelete');
    Route::get('/clientes/{cliente}/has-prestamos', [ClienteController::class, 'hasPrestamos'])->name('clientes.hasPrestamos');

    // =====================================================
    // RUTAS ESPECÃƒÂFICAS DE PROVEEDORES
    // =====================================================
    Route::get('/proveedores/export', [ProveedorController::class, 'export'])->name('proveedores.export');
    Route::put('/proveedores/{proveedor}/toggle', [ProveedorController::class, 'toggle'])->name('proveedores.toggle');

    // =====================================================
    // RUTAS ESPECÃƒÂFICAS DE TÃƒâ€°CNICOS
    // =====================================================
    Route::get('/tecnicos/export', [TecnicoController::class, 'export'])->name('tecnicos.export');
    Route::put('/tecnicos/{tecnico}/toggle', [TecnicoController::class, 'toggle'])->name('tecnicos.toggle');

    // =====================================================
    // RUTAS ESPECÃƒÂFICAS DE USUARIOS
    // =====================================================
    Route::get('/usuarios/export', [UserController::class, 'export'])->name('usuarios.export');
    Route::put('/usuarios/{user}/toggle', [UserController::class, 'toggle'])->name('usuarios.toggle');

    // =====================================================
    // RUTAS DE VACACIONES
    // =====================================================
    Route::resource('vacaciones', VacacionController::class)->names('vacaciones');
    Route::post('/vacaciones/{vacacion}/aprobar', [VacacionController::class, 'aprobar'])->name('vacaciones.aprobar');
    Route::post('/vacaciones/{vacacion}/rechazar', [VacacionController::class, 'rechazar'])->name('vacaciones.rechazar');
    Route::get('/mis-vacaciones', [VacacionController::class, 'misVacaciones'])->name('vacaciones.mis-vacaciones');
    Route::get('/vacaciones/create-para-empleado/{empleado}', [VacacionController::class, 'create'])->name('vacaciones.create-para-empleado');

    // =====================================================
    // RUTAS DE REGISTRO DE VACACIONES
    // =====================================================
    Route::resource('registro-vacaciones', RegistroVacacionesController::class)->names('registro-vacaciones');
    Route::post('/registro-vacaciones/actualizar/{empleado}', [RegistroVacacionesController::class, 'actualizar'])->name('registro-vacaciones.actualizar');
    Route::get('/registro-vacaciones/empleado/{empleado}', [RegistroVacacionesController::class, 'porEmpleado'])->name('registro-vacaciones.por-empleado');

    // =====================================================
    // RUTAS ESPECÃƒÂFICAS DE CITAS
    // =====================================================
    Route::get('/citas/export', [CitaController::class, 'export'])->name('citas.export');

    // =====================================================
    // RUTAS ESPECÃƒÂFICAS DE PRODUCTOS
    // =====================================================
    Route::post('/productos/validate-stock', [ProductoController::class, 'validateStock'])->name('productos.validateStock');
    Route::put('/productos/{producto}/toggle', [ProductoController::class, 'toggle'])->name('productos.toggle');

    // =====================================================
    // RUTAS ESPECÃƒÂFICAS DE COTIZACIONES
    // =====================================================
    Route::post('/cotizaciones/{id}/convertir-a-venta', [CotizacionController::class, 'convertirAVenta'])->name('cotizaciones.convertir-a-venta');
    Route::get('/cotizaciones/{id}/confirmar-pedido', [CotizacionController::class, 'mostrarConfirmacionPedido'])->name('cotizaciones.confirmar-pedido');
    Route::get('/cotizaciones/{id}/confirmar-venta', [CotizacionController::class, 'mostrarConfirmacionVenta'])->name('cotizaciones.confirmar-venta');
    Route::post('/cotizaciones/draft', [CotizacionController::class, 'guardarBorrador'])->name('cotizaciones.storeDraft');
    Route::post('/cotizaciones/{id}/enviar-email', [CotizacionController::class, 'enviarEmail'])->name('cotizaciones.enviar-email');
    Route::get('/cotizaciones/{id}/pdf', [CotizacionController::class, 'generarPDF'])->name('cotizaciones.pdf');
    Route::post('/cotizaciones/{cotizacion}/duplicate', [CotizacionController::class, 'duplicate'])->name('cotizaciones.duplicate');
    Route::post('/cotizaciones/{id}/enviar-pedido', [CotizacionController::class, 'enviarAPedido'])->name('cotizaciones.enviar-pedido');
    Route::post('/cotizaciones/{id}/cancel', [CotizacionController::class, 'cancel'])->name('cotizaciones.cancel');

    // =====================================================
    // RUTAS ESPECÃƒÂFICAS DE PEDIDOS
    // =====================================================
    Route::post('/pedidos/{id}/confirmar', [PedidoController::class, 'confirmar'])->name('pedidos.confirmar');
    Route::post('/pedidos/{id}/enviar-a-venta', [PedidoController::class, 'enviarAVenta'])
        ->name('pedidos.enviar-a-venta');
    Route::post('/pedidos/{id}/enviar-email', [PedidoController::class, 'enviarEmail'])->name('pedidos.enviar-email');
    Route::get('/pedidos/{id}/pdf', [PedidoController::class, 'generarPDF'])->name('pedidos.pdf');
    Route::post('/pedidos/{id}/cancel', [PedidoController::class, 'cancel'])->name('pedidos.cancel');
    Route::post('/pedidos/{pedido}/duplicate', [PedidoController::class, 'duplicate'])->name('pedidos.duplicate');

    // =====================================================
    // RUTAS ESPECÃƒÂFICAS DE VENTAS
    // =====================================================
    Route::post('/ventas/{venta}/duplicate', [VentaController::class, 'duplicate'])->name('ventas.duplicate');
    Route::post('/ventas/{id}/enviar-email', [VentaController::class, 'enviarEmail'])->name('ventas.enviar-email');
    Route::get('/ventas/{id}/pdf', [VentaController::class, 'generarPDF'])->name('ventas.pdf');
    Route::post('/ventas/{id}/cancel', [VentaController::class, 'cancel'])->name('ventas.cancel');
    Route::post('/ventas/{id}/marcar-pagado', [VentaController::class, 'marcarPagado'])->name('ventas.marcar-pagado');
    // =====================================================
    // RUTAS ESPECÃƒÂFICAS DE USUARIOS
    // =====================================================
    Route::get('/perfil', [UserController::class, 'profile'])->name('perfil');
    // Ã¢Å“â€¦ CONFLICTO RESUELTO: Eliminada ruta duplicada - usa la del resource usuarios.show

    // =====================================================
    // RUTAS ESPECÃƒÂFICAS DE CITAS
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

    // Ruta de prueba para imágenes
    Route::get('/test-images', function () {
        return Inertia::render('TestImages');
    })->name('test.images');

    // =====================================================
    // RUTAS BACKUP
    // =====================================================
    Route::middleware(['auth', 'can:manage-backups'])
        ->prefix('admin/backup')
        ->name('backup.')
        ->group(function () {
            Route::get('/', [DatabaseBackupController::class, 'index'])->name('index');
            Route::post('/create', [DatabaseBackupController::class, 'create'])->name('create');
            Route::get('/download/{filename}', [DatabaseBackupController::class, 'download'])->name('download');
            Route::delete('/delete/{filename}', [DatabaseBackupController::class, 'delete'])->name('delete');
            Route::post('/delete-multiple', [DatabaseBackupController::class, 'deleteMultiple'])->name('deleteMultiple');
            Route::post('/restore/{filename}', [DatabaseBackupController::class, 'restore'])->name('restore');
            Route::post('/clean', [DatabaseBackupController::class, 'clean'])->name('clean');
            Route::get('/monitoring', [DatabaseBackupController::class, 'monitoring'])->name('monitoring');
            Route::get('/stats', [DatabaseBackupController::class, 'stats'])->name('stats');
            Route::get('/security-stats', [DatabaseBackupController::class, 'securityStats'])->name('security.stats');
        });

    // =====================================================
    // RUTAS DE CONFIGURACIÓN WHATSAPP
    // =====================================================
    Route::middleware(['auth'])
        ->prefix('admin/whatsapp')
        ->name('admin.whatsapp.')
        ->group(function () {
            Route::get('/configuracion', [EmpresaWhatsAppController::class, 'index'])->name('configuracion');
            Route::post('/test', [EmpresaWhatsAppController::class, 'test'])
                ->withoutMiddleware([\App\Http\Middleware\CustomVerifyCsrfToken::class])
                ->name('test');
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

    // =====================================================
    // RUTA PARA REFRESCAR TOKEN CSRF
    // =====================================================
    Route::get('/csrf-token', function () {
        return response()->json(['token' => csrf_token()]);
    })->middleware(['auth:sanctum', config('jetstream.auth_session'), 'verified']);
});

// Alias de rutas antiguas a la nueva gestión de herramientas (evita 404 en vistas existentes)
// Eliminado: alias antiguos relacionados con gestión de herramientas

// =====================================================
// RUTA DE PRUEBA PARA VERIFICAR CARGA DE IMÁGENES
// =====================================================
Route::get('/test-images', function () {
    $profilePhotos = \Illuminate\Support\Facades\Storage::disk('public')->files('profile-photos');
    $images = [];

    foreach ($profilePhotos as $photo) {
        $images[] = [
            'filename' => basename($photo),
            'url' => asset('storage/' . $photo),
            'size' => \Illuminate\Support\Facades\Storage::disk('public')->size($photo),
            'exists' => \Illuminate\Support\Facades\Storage::disk('public')->exists($photo),
        ];
    }

    return response()->json([
        'server_info' => [
            'host' => request()->getHost(),
            'port' => request()->getPort(),
            'scheme' => request()->getScheme(),
            'origin' => request()->header('Origin'),
        ],
        'images' => $images,
        'storage_path' => storage_path('app/public'),
        'public_storage_exists' => is_link(public_path('storage')),
        'cors_headers' => [
            'Access-Control-Allow-Origin' => request()->header('Origin') ?: '*',
            'Access-Control-Allow-Credentials' => 'true',
        ]
    ]);
})->name('test.images');

// =====================================================
// SERVIDOR ESPECÍFICO PARA IMÁGENES CON HEADERS PERMISIVOS
// =====================================================
Route::get('/profile-photo/{filename}', [ImageController::class, 'serveProfilePhoto'])->name('serve-profile-photo');
Route::get('/api/profile-photos', [ImageController::class, 'listProfilePhotos'])->name('list-profile-photos');

// =====================================================
// RUTAS ALTERNATIVAS PARA IMÁGENES (INDEPENDIENTES DE APP_URL)
// =====================================================
Route::get('/img/profile-photos/{filename}', function ($filename) {
    $path = 'profile-photos/' . $filename;
    $fullPath = storage_path('app/public/' . $path);

    // Verificar que el archivo existe
    if (!file_exists($fullPath)) {
        return response('Imagen no encontrada', 404);
    }

    $mimeType = mime_content_type($fullPath) ?: 'image/png';

    return response()->file($fullPath, [
        'Content-Type' => $mimeType,
        'Cache-Control' => 'public, max-age=3600',
        'Access-Control-Allow-Origin' => request()->header('Origin') ?: '*',
        'Access-Control-Allow-Credentials' => 'true',
        'Access-Control-Allow-Methods' => 'GET, OPTIONS',
        'Access-Control-Allow-Headers' => 'Content-Type, Authorization, X-Requested-With, Accept, Origin',
    ]);
})->name('image.profile-photo');

// Ruta de debug para verificar configuración de URLs
Route::get('/debug-urls', function () {
    return response()->json([
        'app_url' => config('app.url'),
        'current_host' => request()->getHost(),
        'current_port' => request()->getPort(),
        'current_scheme' => request()->getScheme(),
        'is_secure' => request()->isSecure(),
        'full_url' => request()->fullUrl(),
        'storage_url_example' => \App\Helpers\UrlHelper::storageUrl('profile-photos/test.png'),
        'asset_url_example' => \App\Helpers\UrlHelper::assetUrl('images/logo.png'),
        'is_misconfigured' => \App\Helpers\UrlHelper::isAppUrlMisconfigured(),
    ]);
})->name('debug.urls');
