<?php

use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use App\Http\Controllers\ClienteController;
use App\Http\Controllers\ProductoController;


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

    // Productos
    Route::get('/productos', [ProductoController::class, 'index'])->name('productos');

    // Cotización

});
