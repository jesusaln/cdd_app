<?php
// app/Http/Controllers/PanelController.php

namespace App\Http\Controllers;

use Inertia\Inertia;
use Illuminate\Support\Facades\Auth;
use App\Models\Cliente;
use App\Models\Producto;
use App\Models\Proveedor;
use App\Models\Cita;
use App\Models\Servicio;
use App\Models\Categoria;
use App\Models\Marca;
use App\Models\Almacen;
use App\Models\OrdenCompra; // Asegúrate de importar el modelo

use App\Models\Tecnico; // ¡IMPORTANTE: Asegúrate de importar el modelo Tecnico si no lo habías hecho!
use Carbon\Carbon;

class PanelController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $now = Carbon::now('America/Hermosillo');
        $startOfMonth = $now->copy()->startOfMonth();

        // --- Contadores de Registros ---
        $clientesCount = Cliente::count();
        $productosCount = Producto::count();
        $proveedoresCount = Proveedor::count();
        $citasCount = Cita::count();

        // --- Contadores Adicionales ---
        $clientesNuevosCount = Cliente::where('created_at', '>=', $startOfMonth)->count();

        // Productos con Bajo Stock
        $productosBajoStock = Producto::select('nombre', 'stock', 'stock_minimo')
            ->whereColumn('stock', '<=', 'stock_minimo')
            ->get();
        $productosBajoStockCount = $productosBajoStock->count();
        $productosBajoStockNombres = $productosBajoStock->pluck('nombre')->toArray();

        // Órdenes de Compra Pendientes
        $ordenesPendientes = OrdenCompra::with('proveedor')
            ->where('estado', 'pendiente')
            ->orderBy('created_at', 'desc')
            ->get();

        $proveedoresPedidosPendientesCount = $ordenesPendientes->count();

        // Formatear las órdenes pendientes para el frontend
        $ordenesPendientesDetalles = $ordenesPendientes->map(function ($orden) {
            return [
                'id' => $orden->id,
                'proveedor' => $orden->proveedor ? $orden->proveedor->nombre_razon_social : 'Proveedor no especificado',
                'total' => number_format($orden->total, 2),
                'fecha_creacion' => Carbon::parse($orden->created_at)->format('d/m/Y'),
                'fecha_recepcion' => $orden->fecha_recepcion ? Carbon::parse($orden->fecha_recepcion)->format('d/m/Y') : 'No especificada',
            ];
        })->toArray();

        // Citas para Hoy (solo en proceso y pendientes)
        $citasHoy = Cita::with(['cliente', 'tecnico'])
            ->select('id', 'tipo_servicio', 'fecha_hora', 'cliente_id', 'tecnico_id', 'estado')
            ->whereDate('fecha_hora', $now->toDateString())
            ->whereIn('estado', ['en_proceso', 'pendiente'])
            ->orderByRaw("
                CASE
                    WHEN estado = 'en_proceso' THEN 1
                    WHEN estado = 'pendiente' THEN 2
                    ELSE 999
                END ASC
            ")
            ->orderBy('fecha_hora')
            ->get();

        $citasHoyCount = $citasHoy->count();
        $citasHoyDetalles = $citasHoy->map(function ($cita) {
            return [
                'id' => $cita->id,
                'cliente' => $cita->cliente ? $cita->cliente->nombre_razon_social : 'Desconocido',
                'tecnico' => $cita->tecnico ? $cita->tecnico->nombre : 'Sin técnico asignado',
                'titulo' => $cita->tipo_servicio,
                'hora' => Carbon::parse($cita->fecha_hora)->format('H:i'),
                'estado' => $cita->estado,
                'estado_label' => $cita->estado === 'en_proceso' ? 'En Proceso' : 'Pendiente',
            ];
        })->toArray();

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
            'ordenesPendientesDetalles' => $ordenesPendientesDetalles, // Nuevo campo
            'citasCount' => $citasCount,
            'citasHoyCount' => $citasHoyCount,
            'citasHoyDetalles' => $citasHoyDetalles,
        ]);
    }
}
