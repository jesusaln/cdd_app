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

Route::get('/', function () {
    return Inertia::render('Welcome', [
        'canLogin' => Route::has('login'),
        'canRegister' => Route::has('register'),
        'laravelVersion' => Application::VERSION,
        'phpVersion' => PHP_VERSION,
    ]);
})->name('welcome');

// Rutas protegidas por autenticación
Route::middleware([
    'auth:sanctum',
    config('jetstream.auth_session'),
    'verified',
])->group(function () {
    // Ruta principal para el panel
    Route::get('/panel', function () {
        return Inertia::render('Panel', [
            'clientesCount' => \App\Models\Cliente::count()
        ]);
    })->name('panel');

    // Redirigir dashboard a panel
    Route::get('/dashboard', function () {
        return redirect()->route('panel');
    })->name('dashboard');

    // Clientes
    Route::resource('clientes', ClienteController::class)->names('clientes');

    // Productos
    Route::resource('productos', ProductoController::class)->names('productos');

    // Proveedores
    Route::resource('proveedores', ProveedorController::class)->names('proveedores');

    // Categorías
    Route::resource('categorias', CategoriaController::class)->names('categorias');

    // Marcas
    Route::resource('marcas', MarcaController::class)->names('marcas');

    // Almacenes
    Route::resource('almacenes', AlmacenController::class)->names('almacenes');

    // Cotizaciones
    Route::resource('cotizaciones', CotizacionController::class)->names('cotizaciones');
    Route::post('/cotizaciones/{id}/convertir-a-pedido', [CotizacionController::class, 'convertirAPedido'])->name('cotizaciones.convertir-a-pedido');

    // Pedidos
    Route::resource('pedidos', PedidoController::class)->names('pedidos');
    Route::post('/pedidos/{id}/enviar-a-ventas', [PedidoController::class, 'enviarAVentas'])->name('pedidos.enviarAVentas');

    // Ventas
    Route::resource('ventas', VentaController::class)->names('ventas');

    // Usuarios
    Route::resource('usuarios', UserController::class)->names('usuarios');

    // Perfil de usuario autenticado
    Route::get('/perfil', [UserController::class, 'profile'])->name('perfil');

    // Ver detalle de un usuario (sólo admins pueden ver otros usuarios)
    Route::get('/usuario/{id}', [UserController::class, 'show'])->name('usuarios.show');

    // Compras
    Route::resource('compras', CompraController::class)->names('compras');

    // Notificaciones
    Route::get('/notifications', [NotificationController::class, 'index']);
    Route::post('/notifications/mark-as-read', [NotificationController::class, 'markAsRead']);
});
