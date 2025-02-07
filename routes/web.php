<?php

use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use App\Http\Controllers\ClienteController;
use App\Http\Controllers\ProductoController;
use App\Http\Controllers\CategoriaController;
use App\Http\Controllers\MarcaController;
use App\Http\Controllers\ProveedorController;


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
    Route::get('/productos', [ProductoController::class, 'index'])->name('productos');



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
});
