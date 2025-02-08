<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ClienteController;
use App\Http\Controllers\CategoriaController;
use App\Http\Controllers\MarcaController;
use App\Http\Controllers\ProveedorController;
use App\Http\Controllers\AlmacenController;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::get('/check-email', [ClienteController::class, 'checkEmail']);
// Categorias
Route::get('/categorias', [CategoriaController::class, 'index'])->name('api.categorias.index');

// Marcas
Route::get('/marcas', [MarcaController::class, 'index'])->name('api.marcas.index');

// Proveedores
Route::get('/proveedores', [ProveedorController::class, 'index'])->name('api.proveedores.index');

// Almacenes
Route::get('/almacenes', [AlmacenController::class, 'index'])->name('api.almacenes.index');
