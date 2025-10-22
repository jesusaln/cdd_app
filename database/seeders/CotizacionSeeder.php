<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Cotizacion;
use App\Models\Cliente;
use App\Models\Producto;
use App\Models\Servicio;
use App\Models\CotizacionItem;

class CotizacionSeeder extends Seeder
{
    public function run(): void
    {
        $clientes = Cliente::all();
        $productos = Producto::all();
        $servicios = Servicio::all();

        foreach ($clientes as $cliente) {
            // Crear entre 1 y 3 cotizaciones por cliente
            for ($i = 0; $i < rand(1, 3); $i++) {
                $cotizacion = Cotizacion::create([
                    'cliente_id' => $cliente->id,
                    'subtotal' => 0,
                    'descuento_general' => 0,
                    'iva' => 0,
                    'total' => 0,
                    'estado' => 'pendiente',
                    'notas' => 'Cotización generada automáticamente',
                ]);

                // Crear items aleatorios para la cotización (productos o servicios)
                $itemsCount = rand(2, 5);
                for ($j = 0; $j < $itemsCount; $j++) {
                    $tipo = rand(0, 1); // 0=producto, 1=servicio

                    if ($tipo === 0 && $productos->isNotEmpty()) {
                        $cotizable = $productos->random();
                    } elseif ($tipo === 1 && $servicios->isNotEmpty()) {
                        $cotizable = $servicios->random();
                    } else {
                        continue;
                    }

                    $cantidad = rand(1, 10);
                    $precio = $cotizable->precio ?? 100;
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
                $ivaRate = \App\Services\EmpresaConfiguracionService::getIvaPorcentaje() / 100;
                $cotizacion->iva = ($cotizacion->subtotal - $cotizacion->descuento_general) * $ivaRate;
                $cotizacion->total = $cotizacion->subtotal - $cotizacion->descuento_general + $cotizacion->iva;
                $cotizacion->save();
            }
        }
    }
}
