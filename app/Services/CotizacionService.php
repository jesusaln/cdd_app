<?php

namespace App\Services;

use App\Enums\EstadoCotizacion;
use App\Models\Cotizacion;
use App\Models\Pedido;
use App\Models\Producto;
use App\Models\Servicio;
use App\Models\Venta;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use InvalidArgumentException;

class CotizacionService
{
    /**
     * Obtiene todas las cotizaciones con sus relaciones.
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getAllCotizaciones()
    {
        return Cotizacion::with(['cliente', 'productos', 'servicios'])->get();
    }

    /**
     * Crea una nueva cotización con los datos proporcionados.
     *
     * @param array $data Datos validados de la solicitud
     * @return Cotizacion
     * @throws InvalidArgumentException
     */
    public function createCotizacion(array $data)
    {
        return DB::transaction(function () use ($data) {
            $totales = $this->calculateTotals($data['productos'], $data['descuento_general'] ?? 0);

            $cotizacion = Cotizacion::create([
                'cliente_id' => $data['cliente_id'],
                'subtotal' => $totales['subtotal'],
                'descuento_general' => $data['descuento_general'] ?? 0,
                'descuento_items' => $totales['descuento_items'],
                'iva' => $totales['iva'],
                'total' => $totales['total'],
                'estado' => EstadoCotizacion::Pendiente,
            ]);

            $this->attachItems($cotizacion, $data['productos']);

            return $cotizacion;
        });
    }

    /**
     * Actualiza una cotización existente con los datos proporcionados.
     *
     * @param Cotizacion $cotizacion
     * @param array $data Datos validados de la solicitud
     * @return Cotizacion
     * @throws InvalidArgumentException
     */
    public function updateCotizacion(Cotizacion $cotizacion, array $data)
    {
        return DB::transaction(function () use ($cotizacion, $data) {
            $totales = $this->calculateTotals($data['productos'], $data['descuento_general'] ?? 0);

            $cotizacion->update([
                'cliente_id' => $data['cliente_id'],
                'subtotal' => $totales['subtotal'],
                'descuento_general' => $data['descuento_general'] ?? 0,
                'descuento_items' => $totales['descuento_items'],
                'iva' => $totales['iva'],
                'total' => $totales['total'],
            ]);

            $cotizacion->productos()->sync([]);
            $cotizacion->servicios()->sync([]);

            $this->attachItems($cotizacion, $data['productos']);

            return $cotizacion;
        });
    }

    /**
     * Elimina una cotización y sus relaciones.
     *
     * @param Cotizacion $cotizacion
     * @return void
     */
    public function deleteCotizacion(Cotizacion $cotizacion)
    {
        DB::transaction(function () use ($cotizacion) {
            $cotizacion->productos()->detach();
            $cotizacion->servicios()->detach();
            $cotizacion->delete();
        });
    }

    /**
     * Convierte una cotización a un pedido.
     *
     * @param Cotizacion $cotizacion
     * @return Pedido
     */
    public function convertirAPedido(Cotizacion $cotizacion)
    {
        return DB::transaction(function () use ($cotizacion) {
            $pedido = Pedido::create([
                'cliente_id' => $cotizacion->cliente_id,
                'subtotal' => $cotizacion->subtotal,
                'descuento_general' => $cotizacion->descuento_general,
                'descuento_items' => $cotizacion->descuento_items,
                'iva' => $cotizacion->iva,
                'total' => $cotizacion->total,
            ]);

            foreach ($cotizacion->productos as $producto) {
                $pedido->productos()->attach($producto->id, [
                    'cantidad' => $producto->pivot->cantidad,
                    'precio' => $producto->pivot->precio,
                    'descuento' => $producto->pivot->descuento,
                ]);
            }

            foreach ($cotizacion->servicios as $servicio) {
                $pedido->servicios()->attach($servicio->id, [
                    'cantidad' => $servicio->pivot->cantidad,
                    'precio' => $servicio->pivot->precio,
                    'descuento' => $servicio->pivot->descuento,
                ]);
            }

            return $pedido;
        });
    }

    /**
     * Convierte una cotización a una venta.
     *
     * @param Cotizacion $cotizacion
     * @return Venta
     */
    public function convertirAVenta(Cotizacion $cotizacion)
    {
        return DB::transaction(function () use ($cotizacion) {
            $venta = Venta::create([
                'cliente_id' => $cotizacion->cliente_id,
                'subtotal' => $cotizacion->subtotal,
                'descuento_general' => $cotizacion->descuento_general,
                'descuento_items' => $cotizacion->descuento_items,
                'iva' => $cotizacion->iva,
                'total' => $cotizacion->total,
            ]);

            foreach ($cotizacion->productos as $producto) {
                $venta->productos()->attach($producto->id, [
                    'cantidad' => $producto->pivot->cantidad,
                    'precio' => $producto->pivot->precio,
                    'descuento' => $producto->pivot->descuento,
                ]);
            }

            foreach ($cotizacion->servicios as $servicio) {
                $venta->servicios()->attach($servicio->id, [
                    'cantidad' => $servicio->pivot->cantidad,
                    'precio' => $servicio->pivot->precio,
                    'descuento' => $servicio->pivot->descuento,
                ]);
            }

            return $venta;
        });
    }

    /**
     * Calcula los totales de la cotización, incluyendo descuentos e IVA.
     *
     * @param array $items Productos o servicios de la cotización
     * @param float $descuentoGeneral Descuento general en porcentaje
     * @return array
     */
    private function calculateTotals(array $items, float $descuentoGeneral = 0)
    {
        $subtotal = 0;
        $descuentoItems = 0;
        $ivaRate = 0.16; // Tasa de IVA (debe coincidir con el cliente)

        foreach ($items as $item) {
            // Validar existencia del ítem
            $this->validateItem($item);

            $itemSubtotal = $item['cantidad'] * $item['precio'];
            $itemDescuento = isset($item['descuento']) ? ($itemSubtotal * $item['descuento'] / 100) : 0;

            $subtotal += $itemSubtotal;
            $descuentoItems += $itemDescuento;
        }

        $subtotalConDescuentoItems = $subtotal - $descuentoItems;
        $descuentoGeneralMonto = $subtotalConDescuentoItems * ($descuentoGeneral / 100);
        $subtotalConDescuentos = $subtotalConDescuentoItems - $descuentoGeneralMonto;
        $iva = $subtotalConDescuentos * $ivaRate;
        $total = $subtotalConDescuentos + $iva;

        return [
            'subtotal' => $subtotal,
            'descuento_items' => $descuentoItems,
            'descuento_general' => $descuentoGeneralMonto,
            'iva' => $iva,
            'total' => $total,
        ];
    }

    /**
     * Asocia productos y servicios a la cotización en las tablas pivot.
     *
     * @param Cotizacion $cotizacion
     * @param array $items
     * @return void
     * @throws InvalidArgumentException
     */
    private function attachItems(Cotizacion $cotizacion, array $items)
    {
        foreach ($items as $item) {
            $this->validateItem($item);

            $pivotData = [
                'cantidad' => $item['cantidad'],
                'precio' => $item['precio'],
                'descuento' => $item['descuento'] ?? 0,
            ];

            if ($item['tipo'] === 'producto') {
                $cotizacion->productos()->attach($item['id'], $pivotData);
            } elseif ($item['tipo'] === 'servicio') {
                $cotizacion->servicios()->attach($item['id'], $pivotData);
            } else {
                throw new InvalidArgumentException("Tipo de ítem inválido: {$item['tipo']}");
            }
        }
    }

    /**
     * Valida la existencia de un producto o servicio.
     *
     * @param array $item
     * @return void
     * @throws InvalidArgumentException
     */
    private function validateItem(array $item)
    {
        if (!isset($item['id']) || !isset($item['tipo']) || !isset($item['cantidad']) || !isset($item['precio'])) {
            throw new InvalidArgumentException('Datos del ítem incompletos');
        }

        if ($item['tipo'] === 'producto') {
            if (!Producto::where('id', $item['id'])->exists()) {
                throw new InvalidArgumentException("El producto con ID {$item['id']} no existe");
            }
        } elseif ($item['tipo'] === 'servicio') {
            if (!Servicio::where('id', $item['id'])->exists()) {
                throw new InvalidArgumentException("El servicio con ID {$item['id']} no existe");
            }
        } else {
            throw new InvalidArgumentException("Tipo de ítem inválido: {$item['tipo']}");
        }
    }
}
