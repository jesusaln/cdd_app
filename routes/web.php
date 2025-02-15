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
use App\Http\Controllers\CotizacionController;

Route::get('/', function () {
    return Inertia::render('Welcome', [
        'canLogin' => Route::has('login'),
        'canRegister' => Route::has('register'),
        'laravelVersion' => Application::VERSION,
        'phpVersion' => PHP_VERSION,
    ]);
})->name('welcome');

Route::middleware([
    'auth:sanctum',
    config('jetstream.auth_session'),
    'verified',
])->group(function () {

    // Ruta principal para el panel
    Route::get('/panel', function () {
        return Inertia::render('Panel', [
            'clientesCount' => \App\Models\Cliente::count()
        ]); // Verifica que 'Panel.vue' exista en el frontend
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
});
