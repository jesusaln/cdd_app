<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ClienteController;
use App\Http\Controllers\CategoriaController;
use App\Http\Controllers\MarcaController;
use App\Http\Controllers\ProveedorController;
use App\Http\Controllers\AlmacenController;
use App\Http\Controllers\ProductoController;

// Ruta protegida por Sanctum
Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

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




Route::get('/productos/{id}', [ProductoController::class, 'show']);
