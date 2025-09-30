<?php

namespace App\Services;

use App\Models\InventarioMovimiento;
use App\Models\Producto;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class InventarioService
{
    /**
     * Registra un movimiento de inventario y ajusta el stock del producto.
     *
     * @param Producto $producto
     * @param string $tipo 'entrada' o 'salida'
     * @param int $cantidad
     * @param string $motivo
     * @param string|null $referencia
     * @param int $userId
     * @param array|null $metadatos
     * @return InventarioMovimiento
     */
    public function registrarMovimiento(
        Producto $producto,
        string $tipo,
        int $cantidad,
        string $motivo,
        ?string $referencia = null,
        int $userId,
        ?array $metadatos = null
    ): InventarioMovimiento {
        return DB::transaction(function () use ($producto, $tipo, $cantidad, $motivo, $referencia, $userId, $metadatos) {
            // Crear el movimiento
            $movimiento = InventarioMovimiento::create([
                'producto_id' => $producto->id,
                'tipo' => $tipo,
                'cantidad' => $cantidad,
                'motivo' => $motivo,
                'referencia' => $referencia,
                'user_id' => $userId,
                'metadatos' => $metadatos,
            ]);

            // Ajustar el stock del producto
            if ($tipo === 'entrada') {
                $producto->increment('stock', $cantidad);
            } elseif ($tipo === 'salida') {
                $producto->decrement('stock', $cantidad);
            }

            return $movimiento;
        });
    }

    /**
     * Verifica si un producto tiene stock suficiente para una salida.
     *
     * @param Producto $producto
     * @param int $cantidad
     * @return bool
     */
    public function tieneStockSuficiente(Producto $producto, int $cantidad): bool
    {
        return $producto->stock >= $cantidad;
    }

    /**
     * Obtiene el historial de movimientos de un producto.
     *
     * @param Producto $producto
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function obtenerHistorial(Producto $producto)
    {
        return $producto->movimientos()->with('user')->orderBy('created_at', 'desc')->get();
    }

    /**
     * Obtiene estadísticas generales de movimientos de inventario.
     *
     * @return array
     */
    public function obtenerEstadisticasGenerales()
    {
        return [
            'total_movimientos' => InventarioMovimiento::count(),
            'total_entradas' => InventarioMovimiento::where('tipo', 'entrada')->sum('cantidad'),
            'total_salidas' => InventarioMovimiento::where('tipo', 'salida')->sum('cantidad'),
            'productos_con_movimientos' => InventarioMovimiento::distinct('producto_id')->count('producto_id'),
            'usuarios_que_registraron' => InventarioMovimiento::distinct('user_id')->count('user_id'),
            'movimientos_hoy' => InventarioMovimiento::whereDate('created_at', today())->count(),
            'movimientos_este_mes' => InventarioMovimiento::whereMonth('created_at', now()->month)
                ->whereYear('created_at', now()->year)->count(),
        ];
    }

    /**
     * Obtiene movimientos con filtros avanzados.
     *
     * @param array $filtros
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function obtenerMovimientosConFiltros($filtros = [])
    {
        $query = InventarioMovimiento::with(['producto', 'user']);

        if (!empty($filtros['producto_id'])) {
            $query->where('producto_id', $filtros['producto_id']);
        }

        if (!empty($filtros['tipo'])) {
            $query->where('tipo', $filtros['tipo']);
        }

        if (!empty($filtros['motivo'])) {
            $query->where('motivo', 'like', '%' . $filtros['motivo'] . '%');
        }

        if (!empty($filtros['fecha_desde'])) {
            $query->whereDate('created_at', '>=', $filtros['fecha_desde']);
        }

        if (!empty($filtros['fecha_hasta'])) {
            $query->whereDate('created_at', '<=', $filtros['fecha_hasta']);
        }

        if (!empty($filtros['user_id'])) {
            $query->where('user_id', $filtros['user_id']);
        }

        return $query;
    }

    /**
     * Obtiene productos con mayor movimiento de inventario.
     *
     * @param int $limite
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function obtenerProductosMasMovidos($limite = 10)
    {
        return Producto::select('productos.*', DB::raw('SUM(ABS(inventario_movimientos.cantidad)) as total_movido'))
            ->join('inventario_movimientos', 'productos.id', '=', 'inventario_movimientos.producto_id')
            ->groupBy('productos.id')
            ->orderBy('total_movido', 'desc')
            ->limit($limite)
            ->with('categoria', 'marca')
            ->get();
    }

    /**
     * Obtiene usuarios que más movimientos registran.
     *
     * @param int $limite
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function obtenerUsuariosMasActivos($limite = 10)
    {
        return User::select('users.*', DB::raw('COUNT(inventario_movimientos.id) as total_movimientos'))
            ->join('inventario_movimientos', 'users.id', '=', 'inventario_movimientos.user_id')
            ->groupBy('users.id')
            ->orderBy('total_movimientos', 'desc')
            ->limit($limite)
            ->get();
    }
}
