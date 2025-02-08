<?php

use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use App\Http\Controllers\ClienteController;
use App\Http\Controllers\ProductoController;
use App\Http\Controllers\CategoriaController;
use App\Http\Controllers\MarcaController;
use App\Http\Controllers\ProveedorController;
use App\Http\Controllers\AlmacenController;


Route::get('/', function () {
    return Inertia::render('Welcome', [
        'canLogin' => Route::has('login'),
        'canRegister' => Route::has('register'),
        'laravelVersion' => Application::VERSION,
        'phpVersion' => PHP_VERSION,
    ]);
});

Route::middleware([
    'auth:sanctum',
    config('jetstream.auth_session'),
    'verified',
])->group(function () {
    Route::get('/dashboard', function () {
        return Inertia::render('Dashboard');
    })->name('dashboard');

    // Clientes
    Route::get('/clientes', [ClienteController::class, 'index'])->name('clientes.index');

    // Ruta para mostrar el formulario de creación de clientes
    Route::get('/clientes/create', [ClienteController::class, 'create'])->name('clientes.create');
    // Ruta para guardar un nuevo cliente
    Route::post('/clientes', [ClienteController::class, 'store'])->name('clientes.store');

    // Formulario de edición de clientes
    Route::get('/clientes/{cliente}/edit', [ClienteController::class, 'edit'])->name('clientes.edit');

    // Actualizar un cliente
    Route::put('/clientes/{cliente}', [ClienteController::class, 'update'])->name('clientes.update');

    // Eliminar un cliente
    Route::delete('/clientes/{cliente}', [ClienteController::class, 'destroy'])->name('clientes.destroy');

    // Productos
    Route::get('/productos', [ProductoController::class, 'index'])->name('productos.index');

    // Ruta para mostrar el formulario de creación de productos
    Route::get('/productos/create', [ProductoController::class, 'create'])->name('productos.create');

    // Ruta para guardar un nuevo producto
    Route::post('/productos', [ProductoController::class, 'store'])->name('productos.store');

    // Formulario de edición de productos
    Route::get('/productos/{producto}/edit', [ProductoController::class, 'edit'])->name('productos.edit');

    // Actualizar un producto
    Route::put('/productos/{producto}', [ProductoController::class, 'update'])->name('productos.update');

    // Eliminar un producto
    Route::delete('/productos/{producto}', [ProductoController::class, 'destroy'])->name('productos.destroy');




    // Proveedores
    Route::get('/proveedores', [ProveedorController::class, 'index'])->name('proveedores.index');

    // Ruta para mostrar el formulario de creación de proveedores
    Route::get('/proveedores/create', [ProveedorController::class, 'create'])->name('proveedores.create');

    // Ruta para guardar un nuevo proveedor
    Route::post('/proveedores', [ProveedorController::class, 'store'])->name('proveedores.store');

    // Formulario de edición de proveedores
    Route::get('/proveedores/{proveedor}/edit', [ProveedorController::class, 'edit'])->name('proveedores.edit');

    // Actualizar un proveedor
    Route::put('/proveedores/{proveedor}', [ProveedorController::class, 'update'])->name('proveedores.update');

    // Eliminar un proveedor
    Route::delete('/proveedores/{proveedor}', [ProveedorController::class, 'destroy'])->name('proveedores.destroy');

    Route::get('/panel', function () {
        return Inertia::render('Panel'); // Aquí debe coincidir con el nombre exacto del archivo Panel.vue
    })->name('panel');







    // Categorias
    Route::resource('categorias', CategoriaController::class)->names('categorias');

    // Marcas
    Route::resource('marcas', MarcaController::class)->names('marcas');

    // Ruta para mostrar la lista de almacenes
    Route::get('/almacenes', [AlmacenController::class, 'index'])->name('almacenes.index');

    // Ruta para mostrar el formulario de creación de almacenes
    Route::get('/almacenes/create', [AlmacenController::class, 'create'])->name('almacenes.create');

    // Ruta para guardar un nuevo almacén
    Route::post('/almacenes', [AlmacenController::class, 'store'])->name('almacenes.store');

    // Ruta para mostrar el formulario de edición de un almacén
    Route::get('/almacenes/{almacen}/edit', [AlmacenController::class, 'edit'])->name('almacenes.edit');

    // Ruta para actualizar un almacén existente
    Route::put('/almacenes/{almacen}', [AlmacenController::class, 'update'])->name('almacenes.update');

    // Ruta para eliminar un almacén
    Route::delete('/almacenes/{almacen}', [AlmacenController::class, 'destroy'])->name('almacenes.destroy');
});
