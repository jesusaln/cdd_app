<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\ClienteController;
use App\Http\Controllers\Api\CategoriaController;
use App\Http\Controllers\Api\MarcaController;
use App\Http\Controllers\Api\ProveedorController; // Ensure this class exists in the specified namespace
use App\Http\Controllers\Api\AlmacenController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\ProductoController;
use App\Http\Controllers\Api\CotizacionController;
use App\Models\Pedido;
use App\Http\Controllers\Api\PedidoController;
use App\Http\Controllers\Api\VentaController;

// Ruta protegida por Sanctum
Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');


Route::post('/login', [AuthController::class, 'login']);
Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth:sanctum');
Route::get('/user', [AuthController::class, 'user'])->middleware('auth:sanctum');

Route::get('/create-token', [AuthController::class, 'createToken'])->middleware('auth:sanctum');

// Verificar correo electrónico
Route::get('/check-email', [ClienteController::class, 'checkEmail']);

// Cotizaciones


Route::get('/cotizaciones', [CotizacionController::class, 'index']);       // GET /api/cotizaciones
Route::get('/cotizaciones/{id}', [CotizacionController::class, 'show']);  // GET /api/cotizaciones/{id}
Route::post('/cotizaciones', [CotizacionController::class, 'store']);     // POST /api/cotizaciones
Route::put('/cotizaciones/{id}', [CotizacionController::class, 'update']); // PUT /api/cotizaciones/{id}
Route::delete('/cotizaciones/{id}', [CotizacionController::class, 'destroy']); // DELETE /api/cotizaciones/{id}

Route::apiResource('pedidos', PedidoController::class);
Route::apiResource('ventas', VentaController::class);



Route::apiResource('categorias', CategoriaController::class);
Route::apiResource('marcas', MarcaController::class);
Route::apiResource('proveedores', ProveedorController::class);
Route::apiResource('almacenes', AlmacenController::class);

// Productos
Route::get('/productos', [ProductoController::class, 'index'])->name('api.productos.index'); // Obtener todos los productos
Route::post('/productos', [ProductoController::class, 'store'])->name('api.productos.store'); // Crear un nuevo producto
Route::get('/productos/{id}', [ProductoController::class, 'show'])->name('api.productos.show'); // Obtener un producto específico
Route::put('/productos/{id}', [ProductoController::class, 'update'])->name('api.productos.update'); // Actualizar un producto
Route::delete('/productos/{id}', [ProductoController::class, 'destroy'])->name('api.productos.destroy'); // Eliminar un producto

// Clientes
Route::get('/clientes', [ClienteController::class, 'index'])->name('api.clientes.index'); // Obtener todos los clientes
Route::post('/clientes', [ClienteController::class, 'store'])->name('api.clientes.store'); // Crear un nuevo cliente
Route::get('/clientes/{id}', [ClienteController::class, 'show'])->name('api.clientes.show'); // Obtener un cliente específico
Route::put('/clientes/{id}', [ClienteController::class, 'update'])->name('api.clientes.update'); // Actualizar un cliente
Route::delete('/clientes/{id}', [ClienteController::class, 'destroy'])->name('api.clientes.destroy'); // Eliminar un cliente

// Verificar si un email ya existe
Route::get('/clientes/check-email', [ClienteController::class, 'checkEmail'])->name('api.clientes.check-email');
