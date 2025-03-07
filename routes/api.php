<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\ClienteController;
use App\Http\Controllers\CategoriaController;
use App\Http\Controllers\MarcaController;
use App\Http\Controllers\ProveedorController;
use App\Http\Controllers\AlmacenController;
use App\Http\Controllers\ProductoController;
use App\Http\Controllers\Api\AuthController;


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

// Categorías
Route::get('/categorias', [CategoriaController::class, 'index'])->name('api.categorias.index');
Route::get('/categorias/{id}', [CategoriaController::class, 'show'])->name('api.categorias.show');

// Marcas
Route::get('/marcas', [MarcaController::class, 'index'])->name('api.marcas.index');
Route::get('/marcas/{id}', [MarcaController::class, 'show'])->name('api.marcas.show');

// Proveedores
Route::get('/proveedores', [ProveedorController::class, 'index'])->name('api.proveedores.index');
Route::get('/proveedores/{id}', [ProveedorController::class, 'show'])->name('api.proveedores.show');

// Almacenes
Route::get('/almacenes', [AlmacenController::class, 'index'])->name('api.almacenes.index');
Route::get('/almacenes/{id}', [AlmacenController::class, 'show'])->name('api.almacenes.show');

// Productos
Route::get('/productos/{id}', [ProductoController::class, 'show'])->name('api.productos.show');

// Clientes
Route::get('/clientes', [ClienteController::class, 'index'])->name('api.clientes.index'); // Obtener todos los clientes
Route::post('/clientes', [ClienteController::class, 'store'])->name('api.clientes.store'); // Crear un nuevo cliente
Route::get('/clientes/{id}', [ClienteController::class, 'show'])->name('api.clientes.show'); // Obtener un cliente específico
Route::put('/clientes/{id}', [ClienteController::class, 'update'])->name('api.clientes.update'); // Actualizar un cliente
Route::delete('/clientes/{id}', [ClienteController::class, 'destroy'])->name('api.clientes.destroy'); // Eliminar un cliente

// Verificar si un email ya existe
Route::get('/clientes/check-email', [ClienteController::class, 'checkEmail'])->name('api.clientes.check-email');
