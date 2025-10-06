<?php

namespace App\Services;

use App\Models\InventarioMovimiento;
use App\Models\Producto;
use App\Models\Almacen;
use App\Models\User;
use App\Models\Lote;
use Illuminate\Database\DatabaseManager;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;
use RuntimeException;

class InventarioService
{
    public function __construct(private readonly DatabaseManager $db)
    {
    }

    public function entrada(Producto $producto, int $cantidad, array $contexto = []): void
    {
        if ($producto->expires) {
            $this->entradaConLote($producto, $cantidad, $contexto);
        } else {
            $this->ajustar($producto, 'entrada', $cantidad, $contexto);
        }
    }

    public function salida(Producto $producto, int $cantidad, array $contexto = []): void
    {
        if ($producto->expires) {
            $this->salidaConLote($producto, $cantidad, $contexto);
        } else {
            $this->ajustar($producto, 'salida', $cantidad, $contexto);
        }
    }

    protected function ajustar(Producto $producto, string $tipo, int $cantidad, array $contexto = []): void
    {
        if (!in_array($tipo, ['entrada', 'salida'], true)) {
            throw new InvalidArgumentException('Tipo de movimiento inválido.');
        }

        if ($cantidad <= 0) {
            throw new InvalidArgumentException('La cantidad del movimiento debe ser mayor que cero.');
        }

        $this->db->transaction(function () use ($producto, $tipo, $cantidad, $contexto) {
            $almacenId = Arr::get($contexto, 'almacen_id');
            if (!$almacenId) {
                // Si no se especifica almacén, usar el primer almacén activo como fallback
                $almacenId = Almacen::where('estado', 'activo')->first()?->id;
                if (!$almacenId) {
                    throw new RuntimeException('No hay almacenes activos disponibles.');
                }
            }

            // Verificar si el almacén existe y está activo
            $almacen = Almacen::find($almacenId);
            if (!$almacen || $almacen->estado !== 'activo') {
                throw new RuntimeException('El almacén especificado no existe o no está activo.');
            }

            // Obtener o crear registro de inventario para este producto en este almacén
            $inventario = \App\Models\Inventario::firstOrCreate(
                [
                    'producto_id' => $producto->id,
                    'almacen_id' => $almacenId,
                ],
                [
                    'cantidad' => 0,
                    'stock_minimo' => 0,
                ]
            );

            $stockAnterior = $inventario->cantidad;

            $nuevoStock = $stockAnterior;
            if ($tipo === 'entrada') {
                $nuevoStock = $stockAnterior + $cantidad;
            } else {
                $nuevoStock = $stockAnterior - $cantidad;
                if ($nuevoStock < 0) {
                    throw new RuntimeException("Stock insuficiente para el producto '{$producto->nombre}' en el almacén '{$almacen->nombre}'.");
                }
            }

            // Actualizar inventario específico
            $inventario->update(['cantidad' => $nuevoStock]);

            // Actualizar stock total del producto
            $totalStock = \App\Models\Inventario::where('producto_id', $producto->id)->sum('cantidad');
            $producto->update(['stock' => $totalStock]);

            // Registrar movimiento en inventario_movimientos
            $userId = Arr::get($contexto, 'user_id');
            if (!$userId && $this->usuarioAutenticado()) {
                $userId = Auth::id();
            }

            $referencia = Arr::get($contexto, 'referencia');
            $referenciaType = $referencia ? get_class($referencia) : null;
            $referenciaId = $referencia ? $referencia->id : null;

            \App\Models\InventarioMovimiento::create([
                'producto_id' => $producto->id,
                'almacen_id' => $almacenId,
                'tipo' => $tipo,
                'cantidad' => $cantidad,
                'stock_anterior' => $stockAnterior,
                'stock_posterior' => $nuevoStock,
                'motivo' => Arr::get($contexto, 'motivo', 'Movimiento de inventario'),
                'referencia_type' => $referenciaType,
                'referencia_id' => $referenciaId,
                'user_id' => $userId,
                'detalles' => Arr::get($contexto, 'detalles', []),
            ]);
        });
    }

    protected function usuarioAutenticado(): bool
    {
        try {
            return Auth::check();
        } catch (\Throwable $e) {
            return false;
        }
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

    /**
     * Entrada de productos con manejo de lotes
     */
    protected function entradaConLote(Producto $producto, int $cantidad, array $contexto = []): void
    {
        $numeroLote = Arr::get($contexto, 'numero_lote');
        $fechaCaducidad = Arr::get($contexto, 'fecha_caducidad');
        $costoUnitario = Arr::get($contexto, 'costo_unitario');

        if (!$numeroLote) {
            throw new InvalidArgumentException('Número de lote requerido para productos que vencen');
        }

        $this->db->transaction(function () use ($producto, $cantidad, $contexto, $numeroLote, $fechaCaducidad, $costoUnitario) {
            $almacenId = Arr::get($contexto, 'almacen_id');
            if (!$almacenId) {
                $almacenId = Almacen::where('estado', 'activo')->first()?->id;
                if (!$almacenId) {
                    throw new RuntimeException('No hay almacenes activos disponibles.');
                }
            }

            // Crear o actualizar lote
            $lote = Lote::firstOrCreate(
                [
                    'producto_id' => $producto->id,
                    'numero_lote' => $numeroLote,
                ],
                [
                    'fecha_caducidad' => $fechaCaducidad,
                    'cantidad_inicial' => 0,
                    'cantidad_actual' => 0,
                    'costo_unitario' => $costoUnitario,
                ]
            );

            $lote->increment('cantidad_inicial', $cantidad);
            $lote->increment('cantidad_actual', $cantidad);

            // Actualizar inventario general
            $inventario = \App\Models\Inventario::firstOrCreate(
                [
                    'producto_id' => $producto->id,
                    'almacen_id' => $almacenId,
                ],
                [
                    'cantidad' => 0,
                    'stock_minimo' => 0,
                ]
            );

            $stockAnterior = $inventario->cantidad;
            $inventario->increment('cantidad', $cantidad);

            // Actualizar stock total del producto
            $totalStock = \App\Models\Inventario::where('producto_id', $producto->id)->sum('cantidad');
            $producto->update(['stock' => $totalStock]);

            // Registrar movimiento
            $userId = Arr::get($contexto, 'user_id');
            if (!$userId && $this->usuarioAutenticado()) {
                $userId = Auth::id();
            }

            $referencia = Arr::get($contexto, 'referencia');
            $referenciaType = $referencia ? get_class($referencia) : null;
            $referenciaId = $referencia ? $referencia->id : null;

            \App\Models\InventarioMovimiento::create([
                'producto_id' => $producto->id,
                'almacen_id' => $almacenId,
                'lote_id' => $lote->id,
                'tipo' => 'entrada',
                'cantidad' => $cantidad,
                'stock_anterior' => $stockAnterior,
                'stock_posterior' => $stockAnterior + $cantidad,
                'motivo' => Arr::get($contexto, 'motivo', 'Entrada con lote'),
                'referencia_type' => $referenciaType,
                'referencia_id' => $referenciaId,
                'user_id' => $userId,
                'detalles' => Arr::get($contexto, 'detalles', []),
            ]);
        });
    }

    /**
     * Salida de productos con manejo de lotes (FIFO)
     */
    protected function salidaConLote(Producto $producto, int $cantidad, array $contexto = []): void
    {
        $this->db->transaction(function () use ($producto, $cantidad, $contexto) {
            $almacenId = Arr::get($contexto, 'almacen_id');
            if (!$almacenId) {
                $almacenId = Almacen::where('estado', 'activo')->first()?->id;
                if (!$almacenId) {
                    throw new RuntimeException('No hay almacenes activos disponibles.');
                }
            }

            // Obtener lotes disponibles ordenados por fecha de caducidad (FIFO)
            $lotes = $producto->lotes()
                ->where('cantidad_actual', '>', 0)
                ->where(function ($q) {
                    $q->whereNull('fecha_caducidad')
                      ->orWhere('fecha_caducidad', '>', now());
                })
                ->orderBy('fecha_caducidad', 'asc')
                ->get();

            $cantidadRestante = $cantidad;
            $lotesUsados = [];

            foreach ($lotes as $lote) {
                if ($cantidadRestante <= 0) break;

                $cantidadLote = min($cantidadRestante, $lote->cantidad_actual);
                $lote->decrement('cantidad_actual', $cantidadLote);
                $cantidadRestante -= $cantidadLote;
                $lotesUsados[] = ['lote' => $lote, 'cantidad' => $cantidadLote];
            }

            if ($cantidadRestante > 0) {
                throw new RuntimeException("Stock insuficiente. Faltan {$cantidadRestante} unidades del producto '{$producto->nombre}'.");
            }

            // Actualizar inventario general
            $inventario = \App\Models\Inventario::where('producto_id', $producto->id)
                ->where('almacen_id', $almacenId)
                ->first();

            if (!$inventario || $inventario->cantidad < $cantidad) {
                throw new RuntimeException("Stock insuficiente en almacén para el producto '{$producto->nombre}'.");
            }

            $stockAnterior = $inventario->cantidad;
            $inventario->decrement('cantidad', $cantidad);

            // Actualizar stock total del producto
            $totalStock = \App\Models\Inventario::where('producto_id', $producto->id)->sum('cantidad');
            $producto->update(['stock' => $totalStock]);

            // Registrar movimientos por lote
            $userId = Arr::get($contexto, 'user_id');
            if (!$userId && $this->usuarioAutenticado()) {
                $userId = Auth::id();
            }

            $referencia = Arr::get($contexto, 'referencia');
            $referenciaType = $referencia ? get_class($referencia) : null;
            $referenciaId = $referencia ? $referencia->id : null;

            foreach ($lotesUsados as $loteData) {
                \App\Models\InventarioMovimiento::create([
                    'producto_id' => $producto->id,
                    'almacen_id' => $almacenId,
                    'lote_id' => $loteData['lote']->id,
                    'tipo' => 'salida',
                    'cantidad' => $loteData['cantidad'],
                    'stock_anterior' => $stockAnterior,
                    'stock_posterior' => $stockAnterior - $cantidad,
                    'motivo' => Arr::get($contexto, 'motivo', 'Salida con lote'),
                    'referencia_type' => $referenciaType,
                    'referencia_id' => $referenciaId,
                    'user_id' => $userId,
                    'detalles' => Arr::get($contexto, 'detalles', []),
                ]);
            }
        });
    }
}
