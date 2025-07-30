<?php

namespace App\Services;

use App\Models\Cotizacion;
use App\Models\Pedido;
use App\Models\Venta;
use Illuminate\Support\Facades\DB;

class CotizacionService
{
    public function getAllCotizaciones()
    {
        return Cotizacion::with(['cliente', 'productos', 'servicios'])->get();
    }

    public function createCotizacion(array $data)
    {
        return DB::transaction(function () use ($data) {
            $cotizacion = Cotizacion::create([
                'cliente_id' => $data['cliente_id'],
                'total' => $this->calculateTotal($data['productos']),
            ]);

            $this->attachItems($cotizacion, $data['productos']);

            return $cotizacion;
        });
    }

    public function updateCotizacion(Cotizacion $cotizacion, array $data)
    {
        return DB::transaction(function () use ($cotizacion, $data) {
            $cotizacion->update([
                'cliente_id' => $data['cliente_id'],
                'total' => $this->calculateTotal($data['productos']),
            ]);

            $cotizacion->productos()->sync([]);
            $cotizacion->servicios()->sync([]);

            $this->attachItems($cotizacion, $data['productos']);

            return $cotizacion;
        });
    }

    public function deleteCotizacion(Cotizacion $cotizacion)
    {
        return DB::transaction(function () use ($cotizacion) {
            $cotizacion->productos()->detach();
            $cotizacion->servicios()->detach();
            $cotizacion->delete();
        });
    }

    public function convertirAPedido(Cotizacion $cotizacion)
    {
        return DB::transaction(function () use ($cotizacion) {
            $pedido = Pedido::create([
                'cliente_id' => $cotizacion->cliente_id,
                'total' => $cotizacion->total,
            ]);

            foreach ($cotizacion->productos as $producto) {
                $pedido->productos()->attach($producto->id, [
                    'cantidad' => $producto->pivot->cantidad,
                    'precio' => $producto->pivot->precio,
                ]);
            }

            foreach ($cotizacion->servicios as $servicio) {
                $pedido->servicios()->attach($servicio->id, [
                    'cantidad' => $servicio->pivot->cantidad,
                    'precio' => $servicio->pivot->precio,
                ]);
            }

            return $pedido;
        });
    }

    public function convertirAVenta(Cotizacion $cotizacion)
    {
        return DB::transaction(function () use ($cotizacion) {
            $venta = Venta::create([
                'cliente_id' => $cotizacion->cliente_id,
                'total' => $cotizacion->total,
            ]);

            foreach ($cotizacion->productos as $producto) {
                $venta->productos()->attach($producto->id, [
                    'cantidad' => $producto->pivot->cantidad,
                    'precio' => $producto->pivot->precio,
                ]);
            }

            foreach ($cotizacion->servicios as $servicio) {
                $venta->servicios()->attach($servicio->id, [
                    'cantidad' => $servicio->pivot->cantidad,
                    'precio' => $servicio->pivot->precio,
                ]);
            }

            return $venta;
        });
    }

    private function calculateTotal(array $items)
    {
        return array_sum(array_map(function ($item) {
            return $item['cantidad'] * $item['precio'];
        }, $items));
    }

    private function attachItems(Cotizacion $cotizacion, array $items)
    {
        foreach ($items as $itemData) {
            if ($itemData['tipo'] === 'producto') {
                $cotizacion->productos()->attach($itemData['id'], [
                    'cantidad' => $itemData['cantidad'],
                    'precio' => $itemData['precio'],
                ]);
            } elseif ($itemData['tipo'] === 'servicio') {
                $cotizacion->servicios()->attach($itemData['id'], [
                    'cantidad' => $itemData['cantidad'],
                    'precio' => $itemData['precio'],
                ]);
            }
        }
    }
}
