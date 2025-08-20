<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Cotizacion;
use App\Models\Producto;
use App\Models\Servicio;
use App\Models\CotizacionItem;

class CotizacionItemSeeder extends Seeder
{
    public function run(): void
    {
        $cotizaciones = Cotizacion::all();
        $productos = Producto::all();
        $servicios = Servicio::all();

        foreach ($cotizaciones as $cotizacion) {
            // Cada cotización tendrá entre 2 y 5 items
            $itemsCount = rand(2, 5);

            for ($i = 0; $i < $itemsCount; $i++) {
                $tipo = rand(0, 1); // 0=producto, 1=servicio

                if ($tipo === 0 && $productos->isNotEmpty()) {
                    $cotizable = $productos->random();
                } elseif ($tipo === 1 && $servicios->isNotEmpty()) {
                    $cotizable = $servicios->random();
                } else {
                    continue;
                }

                $cantidad = rand(1, 10);
                $precio = $cotizable->precio_venta ?? $cotizable->precio ?? 100;
                $subtotal = $cantidad * $precio;
                $descuento = rand(0, 20);
                $descuento_monto = $subtotal * ($descuento / 100);

                CotizacionItem::create([
                    'cotizacion_id' => $cotizacion->id,
                    'cotizable_id' => $cotizable->id,
                    'cotizable_type' => get_class($cotizable),
                    'cantidad' => $cantidad,
                    'precio' => $precio,
                    'descuento' => $descuento,
                    'subtotal' => $subtotal,
                    'descuento_monto' => $descuento_monto,
                ]);
            }

            // Recalcular totales de la cotización
            $cotizacion->subtotal = $cotizacion->items->sum('subtotal');
            $cotizacion->descuento_general = $cotizacion->items->sum('descuento_monto');
            $cotizacion->iva = ($cotizacion->subtotal - $cotizacion->descuento_general) * 0.16;
            $cotizacion->total = $cotizacion->subtotal - $cotizacion->descuento_general + $cotizacion->iva;
            $cotizacion->save();
        }
    }
}
