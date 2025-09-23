<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Api\ClienteController;
use App\Http\Controllers\Api\CategoriaController;
use App\Http\Controllers\Api\MarcaController;
use App\Http\Controllers\Api\ProveedorController;
use App\Http\Controllers\Api\AlmacenController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\ProductoController;
use App\Http\Controllers\Api\CotizacionController;
use App\Http\Controllers\Api\PedidoController;
use App\Http\Controllers\Api\VentaController;
use App\Http\Controllers\Api\CitaController;
use App\Http\Controllers\Api\TecnicoController;
use App\Http\Controllers\Api\ServicioController;
use App\Http\Controllers\Api\HerramientaController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
| Aquí defines todas las rutas API de tu aplicación. Estas rutas están
| automáticamente prefijadas con 'api' y tienen el middleware 'api' aplicado.
*/

// =====================================================
// RUTAS DE AUTENTICACIÓN
// =====================================================
Route::post('/login', [AuthController::class, 'login'])->name('api.login');
Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth:sanctum')->name('api.logout');
Route::get('/user', [AuthController::class, 'user'])->middleware('auth:sanctum')->name('api.user');
Route::get('/create-token', [AuthController::class, 'createToken'])->middleware('auth:sanctum')->name('api.create-token');

// =====================================================
// RUTAS PÚBLICAS (Sin autenticación)
// =====================================================
Route::get('/check-email', [ClienteController::class, 'checkEmail'])->name('api.check-email');
Route::get('/clientes/check-email', [ClienteController::class, 'checkEmail'])->name('api.clientes.check-email');
Route::post('/validar-rfc', [ClienteController::class, 'validarRfc'])->name('api.validar-rfc');

// Endpoint para autocompletado de direcciones por código postal
Route::get('/cp/{cp}', function (string $cp) {
    try {
        // Validar que el CP tenga 5 dígitos
        if (!preg_match('/^\d{5}$/', $cp)) {
            return response()->json(['error' => 'Código postal debe tener 5 dígitos'], 400);
        }

        $sepomex = \Eclipxe\SepomexPhp\SepomexPhp::createForDatabaseFile(storage_path('sepomex.sqlite'));
        $zipCodeData = $sepomex->getZipCodeData($cp);

        if (!$zipCodeData) {
            return response()->json(['error' => 'Código postal no encontrado'], 404);
        }

        // Obtener datos del código postal
        $colonias = [];
        foreach ($zipCodeData->locations as $location) {
            $colonias[] = $location->name;
        }

        return response()->json([
            'estado' => $zipCodeData->state->name,
            'municipio' => $zipCodeData->district->name,
            'colonias' => $colonias,
            'pais' => 'México',
        ]);
    } catch (\Exception $e) {
        Log::error('Error consultando código postal: ' . $e->getMessage(), ['cp' => $cp]);
        return response()->json(['error' => 'Error interno del servidor'], 500);
    }
})->name('api.cp');

// =====================================================
// RECURSOS API (Con nombres únicos para evitar conflictos)
// =====================================================

// Clientes - Definición manual para mayor control
Route::prefix('clientes')->name('api.clientes.')->group(function () {
    Route::get('/', [ClienteController::class, 'index'])->name('index');
    Route::post('/', [ClienteController::class, 'store'])->name('store');
    Route::get('/{cliente}', [ClienteController::class, 'show'])->name('show');
    Route::put('/{cliente}', [ClienteController::class, 'update'])->name('update');
    Route::delete('/{cliente}', [ClienteController::class, 'destroy'])->name('destroy');
});

// Productos - Definición manual para mayor control
Route::prefix('productos')->name('api.productos.')->group(function () {
    Route::get('/', [ProductoController::class, 'index'])->name('index');
    Route::post('/', [ProductoController::class, 'store'])->name('store');
    Route::get('/{producto}', [ProductoController::class, 'show'])->name('show');
    Route::put('/{producto}', [ProductoController::class, 'update'])->name('update');
    Route::delete('/{producto}', [ProductoController::class, 'destroy'])->name('destroy');
});

// Cotizaciones - Definición manual para mayor control
Route::prefix('cotizaciones')->name('api.cotizaciones.')->group(function () {
    Route::get('/', [CotizacionController::class, 'index'])->name('index');
    Route::post('/', [CotizacionController::class, 'store'])->name('store');
    Route::get('/{cotizacion}', [CotizacionController::class, 'show'])->name('show');
    Route::put('/{cotizacion}', [CotizacionController::class, 'update'])->name('update');
    Route::delete('/{cotizacion}', [CotizacionController::class, 'destroy'])->name('destroy');
});

// Recursos usando apiResource con nombres únicos
Route::apiResource('categorias', CategoriaController::class)->names('api.categorias');
Route::apiResource('marcas', MarcaController::class)->names('api.marcas');
Route::apiResource('proveedores', ProveedorController::class)->names('api.proveedores'); // ✅ CONFLICTO RESUELTO
Route::apiResource('almacenes', AlmacenController::class)->names('api.almacenes');
Route::apiResource('pedidos', PedidoController::class)->names('api.pedidos');
Route::apiResource('ventas', VentaController::class)->names('api.ventas');
Route::apiResource('citas', CitaController::class)->names('api.citas');
Route::apiResource('tecnicos', TecnicoController::class)->names('api.tecnicos');
Route::apiResource('servicios', ServicioController::class)->names('api.servicios');

// Herramientas API
Route::apiResource('herramientas', HerramientaController::class)->names('api.herramientas');

// API para asignaciones de herramientas
Route::prefix('herramientas')->name('api.herramientas.')->group(function () {
    Route::get('{herramienta}/asignaciones', [HerramientaController::class, 'asignaciones'])->name('asignaciones');
    Route::get('{herramienta}/historial', [HerramientaController::class, 'historial'])->name('historial');
    Route::get('{herramienta}/estados', [HerramientaController::class, 'estados'])->name('estados');
    Route::get('{herramienta}/estadisticas', [HerramientaController::class, 'estadisticas'])->name('estadisticas');
    Route::post('{herramienta}/asignar', [HerramientaController::class, 'asignar'])->name('asignar');
    Route::post('{herramienta}/recibir', [HerramientaController::class, 'recibir'])->name('recibir');
    Route::post('{herramienta}/inspeccionar', [HerramientaController::class, 'inspeccionar'])->name('inspeccionar');
});

// API para alertas de mantenimiento
Route::prefix('alertas')->name('api.alertas.')->group(function () {
    Route::get('mantenimiento', [HerramientaController::class, 'alertasMantenimiento'])->name('mantenimiento');
    Route::get('desgaste', [HerramientaController::class, 'alertasDesgaste'])->name('desgaste');
    Route::get('vencimiento', [HerramientaController::class, 'alertasVencimiento'])->name('vencimiento');
    Route::post('marcar-leida/{tipo}/{herramienta}', [HerramientaController::class, 'marcarAlertaLeida'])->name('marcar-leida');
});

// =====================================================
// RUTAS PROTEGIDAS (Opcional - descomenta si necesitas autenticación)
// =====================================================
/*
Route::middleware('auth:sanctum')->group(function () {
    // Aquí puedes poner rutas que requieran autenticación
    // Ejemplo: Route::apiResource('admin/usuarios', AdminUsuarioController::class);
});
*/
