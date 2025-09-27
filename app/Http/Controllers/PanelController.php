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
use App\Models\Mantenimiento; // Importar modelo de Mantenimiento

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
        $mantenimientosCount = Mantenimiento::count();

        // --- Contadores Adicionales ---
        $clientesNuevosCount = Cliente::where('created_at', '>=', $startOfMonth)->count();

        // Productos con Bajo Stock
        $productosBajoStock = Producto::select('nombre', 'stock', 'stock_minimo')
            ->whereColumn('stock', '<=', 'stock_minimo')
            ->get();
        $productosBajoStockCount = $productosBajoStock->count();
        $productosBajoStockNombres = $productosBajoStock->pluck('nombre')->toArray();

        // Órdenes de Compra Pendientes
        $ordenesPendientes = OrdenCompra::with(['proveedor', 'productos'])
            ->where('estado', 'pendiente')
            ->orderBy('created_at', 'desc')
            ->get();

        $proveedoresPedidosPendientesCount = $ordenesPendientes->count();

        // Formatear las órdenes pendientes para el frontend
        $ordenesPendientesDetalles = $ordenesPendientes->map(function ($orden) {
            // Intentar calcular total basado en productos
            $totalCalculado = $this->calcularTotalOrden($orden);
            $total = $totalCalculado['total'] > 0 ? $totalCalculado['total'] : ($orden->total ?? 0);

            // Calcular días de retraso
            $diasRetraso = null;
            if ($orden->fecha_entrega_esperada) {
                $fechaEsperada = Carbon::parse($orden->fecha_entrega_esperada);
                $hoy = Carbon::now('America/Hermosillo');
                if ($hoy->greaterThan($fechaEsperada)) {
                    $diasRetraso = (int) ceil($fechaEsperada->diffInDays($hoy));
                } else {
                    $diasRetraso = 0; // No retrasada
                }
            }

            return [
                'id' => $orden->id,
                'proveedor' => $orden->proveedor ? $orden->proveedor->nombre_razon_social : 'Proveedor no especificado',
                'total' => number_format($total, 2),
                'prioridad' => $orden->prioridad,
                'dias_retraso' => $diasRetraso,
                'fecha_creacion' => Carbon::parse($orden->created_at)->format('d/m/Y'),
                'fecha_esperada' => $orden->fecha_entrega_esperada ? Carbon::parse($orden->fecha_entrega_esperada)->format('d/m/Y') : 'No especificada',
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

        // Mantenimientos Vencidos y Críticos
        $mantenimientosVencidos = Mantenimiento::with('carro')
            ->where('proximo_mantenimiento', '<', $now)
            ->where('estado', '!=', Mantenimiento::ESTADO_COMPLETADO)
            ->orderBy('proximo_mantenimiento', 'asc')
            ->get();

        $mantenimientosCriticos = Mantenimiento::with('carro')
            ->whereIn('prioridad', [Mantenimiento::PRIORIDAD_ALTA, Mantenimiento::PRIORIDAD_CRITICA])
            ->where('proximo_mantenimiento', '<=', $now->copy()->addDays(7))
            ->where('proximo_mantenimiento', '>=', $now)
            ->orderBy('prioridad', 'desc')
            ->orderBy('proximo_mantenimiento', 'asc')
            ->get();

        $mantenimientosVencidosCount = $mantenimientosVencidos->count();
        $mantenimientosCriticosCount = $mantenimientosCriticos->count();

        // Combinar y formatear mantenimientos críticos para mostrar en el panel
        $mantenimientosCriticosDetalles = $mantenimientosVencidos->concat($mantenimientosCriticos)->map(function ($mantenimiento) {
            return [
                'id' => $mantenimiento->id,
                'tipo' => $mantenimiento->tipo,
                'carro' => $mantenimiento->carro ? [
                    'marca' => $mantenimiento->carro->marca,
                    'modelo' => $mantenimiento->carro->modelo,
                    'placa' => $mantenimiento->carro->placa
                ] : null,
                'proximo_mantenimiento' => $mantenimiento->proximo_mantenimiento,
                'dias_restantes' => $mantenimiento->dias_restantes,
                'prioridad' => $mantenimiento->prioridad,
                'estado' => $mantenimiento->estado,
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
            // Nuevas métricas de mantenimiento
            'mantenimientosCount' => $mantenimientosCount,
            'mantenimientosVencidosCount' => $mantenimientosVencidosCount,
            'mantenimientosCriticosCount' => $mantenimientosCriticosCount,
            'mantenimientosCriticosDetalles' => $mantenimientosCriticosDetalles,
        ]);
    }

    /**
     * Calcula los totales de una orden de compra basada en sus productos
     */
    private function calcularTotalOrden($orden)
    {
        $subtotal = 0;
        $descuentoItems = 0;

        foreach ($orden->productos as $producto) {
            $cantidad = (float) ($producto->pivot->cantidad ?? 0);
            $precio = (float) ($producto->pivot->precio ?? 0);
            $descuento = (float) ($producto->pivot->descuento ?? 0);

            $subtotalItem = $cantidad * $precio;
            $descuentoItem = ($subtotalItem * $descuento) / 100;

            $subtotal += $subtotalItem;
            $descuentoItems += $descuentoItem;
        }

        // Aplicar descuento general
        $descuentoGeneral = (float) ($orden->descuento_general ?? 0);
        $subtotalDespuesDescuentoGeneral = $subtotal - $descuentoItems - $descuentoGeneral;

        // Calcular IVA (16%)
        $iva = $subtotalDespuesDescuentoGeneral * 0.16;

        // Total final
        $total = $subtotalDespuesDescuentoGeneral + $iva;

        return [
            'subtotal' => round($subtotal, 2),
            'descuento_items' => round($descuentoItems, 2),
            'iva' => round($iva, 2),
            'total' => round($total, 2),
        ];
    }
}
