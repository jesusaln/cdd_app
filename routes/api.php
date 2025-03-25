<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\ClienteController;
use App\Http\Controllers\CategoriaController;
use App\Http\Controllers\MarcaController;
use App\Http\Controllers\ProveedorController;
use App\Http\Controllers\AlmacenController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\ProductoController;




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
