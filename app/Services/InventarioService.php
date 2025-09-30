<?php

namespace App\Services;

use App\Models\InventarioMovimiento;
use App\Models\Producto;
use Illuminate\Database\DatabaseManager;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;
use InvalidArgumentException;
use RuntimeException;

class InventarioService
{
    public function __construct(private readonly DatabaseManager $db)
    {
    }

    public function entrada(Producto $producto, int $cantidad, array $contexto = []): InventarioMovimiento
    {
        return $this->ajustar($producto, InventarioMovimiento::TIPO_ENTRADA, $cantidad, $contexto);
    }

    public function salida(Producto $producto, int $cantidad, array $contexto = []): InventarioMovimiento
    {
        return $this->ajustar($producto, InventarioMovimiento::TIPO_SALIDA, $cantidad, $contexto);
    }

    protected function ajustar(Producto $producto, string $tipo, int $cantidad, array $contexto = []): InventarioMovimiento
    {
        if (!in_array($tipo, [InventarioMovimiento::TIPO_ENTRADA, InventarioMovimiento::TIPO_SALIDA], true)) {
            throw new InvalidArgumentException('Tipo de movimiento inválido.');
        }

        if ($cantidad <= 0) {
            throw new InvalidArgumentException('La cantidad del movimiento debe ser mayor que cero.');
        }

        return $this->db->transaction(function () use ($producto, $tipo, $cantidad, $contexto) {
            $producto->refresh();
            $stockAnterior = (int) $producto->stock;

            $nuevoStock = $stockAnterior;
            if ($tipo === InventarioMovimiento::TIPO_ENTRADA) {
                $nuevoStock = $stockAnterior + $cantidad;
            } else {
                $nuevoStock = $stockAnterior - $cantidad;
                if ($nuevoStock < 0) {
                    throw new RuntimeException("Stock insuficiente para el producto '{$producto->nombre}'.");
                }
            }

            $producto->forceFill(['stock' => $nuevoStock])->save();

            $referencia = Arr::get($contexto, 'referencia');
            $userId = Arr::get($contexto, 'user_id');
            if (!$userId && $this->usuarioAutenticado()) {
                $userId = Auth::id();
            }

            $movimientoData = [
                'producto_id' => $producto->id,
                'tipo' => $tipo,
                'cantidad' => $cantidad,
                'stock_anterior' => $stockAnterior,
                'stock_posterior' => $nuevoStock,
                'motivo' => Arr::get($contexto, 'motivo'),
                'user_id' => $userId,
                'detalles' => Arr::get($contexto, 'detalles'),
            ];

            if ($referencia) {
                $movimientoData['referencia_type'] = get_class($referencia);
                $movimientoData['referencia_id'] = $referencia->getKey();
            }

            return InventarioMovimiento::create($movimientoData);
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
}
