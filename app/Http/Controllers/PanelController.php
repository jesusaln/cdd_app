<?php
// app/Http/Controllers/PanelController.php

namespace App\Http\Controllers;

use Inertia\Inertia;
use Illuminate\Support\Facades\Auth;
use App\Models\Cliente;
use App\Models\Producto;
use App\Models\Proveedor;
use App\Models\Cita;
use App\Models\Tecnico; // ¡IMPORTANTE: Asegúrate de importar el modelo Tecnico si no lo habías hecho!
use Carbon\Carbon;

class PanelController extends Controller
{
    public function index()
    {
        $user = Auth::user();

        // Asegúrate de que esta zona horaria sea correcta para tu servidor y usuarios
        $now = Carbon::now('America/Hermosillo');
        $startOfMonth = $now->copy()->startOfMonth();

        // --- Contadores de Registros ---
        $clientesCount = Cliente::count();
        $productosCount = Producto::count();
        $proveedoresCount = Proveedor::count();
        $citasCount = Cita::count();

        // --- Contadores Adicionales para el Dashboard ---

        // Clientes Nuevos este Mes
        $clientesNuevosCount = Cliente::where('created_at', '>=', $startOfMonth)->count();

        // Productos con Bajo Stock
        $productosBajoStock = Producto::select('nombre', 'stock', 'stock_minimo')
            ->whereColumn('stock', '<=', 'stock_minimo') // <-- ¡CAMBIO CLAVE AQUÍ!
            ->get();
        $productosBajoStockCount = $productosBajoStock->count();
        $productosBajoStockNombres = $productosBajoStock->pluck('nombre')->toArray();

        // Proveedores con Pedidos Pendientes (placeholder)
        $proveedoresPedidosPendientesCount = 0;

        // Citas para Hoy
        // ¡IMPORTANTE! Cargar las relaciones `cliente` y `tecnico` con Eager Loading.
        // También selecciona las columnas necesarias, incluyendo las foreign keys.
        $citasHoy = Cita::with(['cliente', 'tecnico']) // <-- ¡NUEVO O MODIFICADO!
            ->select('id', 'tipo_servicio', 'fecha_hora', 'cliente_id', 'tecnico_id') // <-- ¡NUEVO O MODIFICADO!
            ->whereDate('fecha_hora', $now->toDateString())
            ->orderBy('fecha_hora')
            ->get();

        $citasHoyCount = $citasHoy->count();

        // Mapeamos las citas para un formato más fácil de usar en el frontend
        $citasHoyDetalles = $citasHoy->map(function ($cita) {
            return [
                'id' => $cita->id, // Incluir el ID de la cita
                'cliente' => $cita->cliente ? $cita->cliente->nombre_razon_social : 'Desconocido', // Nombre del cliente (maneja nulos)
                'tecnico' => $cita->tecnico ? $cita->tecnico->nombre : 'Sin técnico asignado', // Nombre del técnico (maneja nulos)
                'titulo' => $cita->tipo_servicio, // Usamos 'tipo_servicio' como el título de la cita
                'hora' => Carbon::parse($cita->fecha_hora)->format('H:i'), // Extraer la hora de 'fecha_hora'
            ];
        })->toArray();




        //dd($citasHoyDetalles);
        // --- Pasar los datos al frontend ---
        return Inertia::render('Panel', [
            'user' => $user ? [
                'id' => $user->id,
                'nombre' => $user->name,
                'rol' => $user->rol ?? $user->roles->pluck('name')->first() ?? 'Usuario',
            ] : null,
            'clientesCount' => $clientesCount,
            'clientesNuevosCount' => $clientesNuevosCount,
            'productosCount' => $productosCount,
            'productosBajoStockCount' => $productosBajoStockCount,
            'productosBajoStockNombres' => $productosBajoStockNombres,
            'proveedoresCount' => $proveedoresCount,
            'proveedoresPedidosPendientesCount' => $proveedoresPedidosPendientesCount,
            'citasCount' => $citasCount,
            'citasHoyCount' => $citasHoyCount,
            'citasHoyDetalles' => $citasHoyDetalles,

        ]);
    }
}
