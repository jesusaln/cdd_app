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

Route::resource('ordenescompra', OrdenCompraController::class);
// Ruta específica para marcar como recibida
Route::post('ordenescompra/{id}/recibir', [OrdenCompraController::class, 'recibirOrden'])->name('ordenescompra.recibir');

Route::get('/productos/{id}/inventario', [ProductoController::class, 'showInventario'])->name('productos.inventario');
Route::resource('inventario', InventarioController::class);

Route::get('/empresas', [EmpresasController::class, 'index'])->name('empresas.index');

Route::get('/', function () {
    return Inertia::render('Welcome', [
        'canLogin' => Route::has('login'),
        'laravelVersion' => Application::VERSION,
        'phpVersion' => PHP_VERSION,
    ]);
})->name('welcome');

// Rutas protegidas por autenticación
Route::middleware(['auth:sanctum', config('jetstream.auth_session'), 'verified'])->group(function () {
    Route::get('/panel', [PanelController::class, 'index'])->name('panel');
    //Route::post('/api/validar-rfc', [ClienteController::class, 'validarRfc']);

    Route::get('/dashboard', function () {
        return redirect()->route('panel');
    })->name('dashboard');

    Route::resource('clientes', ClienteController::class)->names('clientes');
    

    Route::post('/validar-rfc', [ClienteController::class, 'validarRfc']);
    Route::get('/clientes/check-email', [ClienteController::class, 'checkEmail'])->name('clientes.checkEmail');

    Route::put('/clientes/{cliente}/toggle', [ClienteController::class, 'toggle'])->name('clientes.toggle');
    Route::resource('productos', ProductoController::class)->names('productos');

    // Proveedores corregidos
    Route::resource('proveedores', ProveedorController::class)
        ->names('proveedores')
        ->parameters(['proveedores' => 'proveedor']);
    // Elimina la línea duplicada: Route::put('/proveedores/{proveedor}', ...)

    Route::resource('categorias', CategoriaController::class)->names('categorias');
    Route::resource('marcas', MarcaController::class)->names('marcas');
    Route::resource('almacenes', AlmacenController::class)->names('almacenes');
    Route::delete('/almacenes/{id}', [AlmacenController::class, 'destroy'])->name('almacenes.destroy');
    Route::resource('cotizaciones', CotizacionController::class)->names('cotizaciones');
    Route::post('/cotizaciones/{id}/convertir-a-pedido', [CotizacionController::class, 'convertirAPedido'])->name('cotizaciones.convertir-a-pedido');
    Route::resource('pedidos', PedidoController::class)->names('pedidos');
    Route::post('/pedidos/{id}/enviar-a-ventas', [PedidoController::class, 'enviarAVentas'])->name('pedidos.enviarAVentas');
    Route::resource('ventas', VentaController::class)->names('ventas');
    Route::resource('servicios', ServicioController::class)->names('servicios');
    Route::resource('usuarios', UserController::class)->names('usuarios');
    Route::get('/perfil', [UserController::class, 'profile'])->name('perfil');
    Route::get('/usuario/{id}', [UserController::class, 'show'])->name('usuarios.show');
    Route::resource('compras', CompraController::class)->names('compras');
    Route::get('/reportes', [ReporteController::class, 'index'])->name('reportes.index');
    Route::resource('reportes', ReporteController::class);
    Route::resource('herramientas', HerramientaController::class)->names('herramientas');
    Route::resource('tecnicos', TecnicoController::class)->names('tecnicos');
    Route::resource('citas', CitaController::class)->names('citas');
    Route::put('/citas/{id}', [CitaController::class, 'update']);
    Route::patch('/citas/{id}/update-index', [CitaController::class, 'updateIndex'])->name('citas.updateIndex');
    Route::resource('carros', CarroController::class)->names('carros');
    Route::resource('mantenimientos', MantenimientoController::class)->names('mantenimientos');
    Route::get('/notifications', [NotificationController::class, 'index']);
    Route::post('/notifications/mark-as-read', [NotificationController::class, 'markAsRead']);
});
