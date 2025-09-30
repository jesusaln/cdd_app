<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Venta;
use App\Models\VentaItem;
use App\Models\Cliente;
use App\Models\Producto;
use App\Models\User;
use App\Models\InventarioMovimiento;
use Carbon\Carbon;

class VentaSeeder extends Seeder
{
    public function run(): void
    {
        // Obtener datos necesarios
        $clientes = Cliente::all();
        $productos = Producto::where('stock', '>', 0)->get();
        $usuarios = User::all();

        if ($clientes->isEmpty() || $productos->isEmpty()) {
            return; // No hay clientes o productos para crear ventas
        }

        // Crear algunas ventas de ejemplo
        $ventas = [
            [
                'cliente_id' => $clientes->random()->id,
                'vendedor_type' => 'App\Models\User',
                'vendedor_id' => $usuarios->random()->id,
                'fecha' => Carbon::now()->subDays(rand(1, 15)),
                'estado' => 'pagado',
                'notas' => 'Venta realizada exitosamente',
                'productos' => $productos->random(min(3, $productos->count()))->map(function ($producto) {
                    return [
                        'producto_id' => $producto->id,
                        'cantidad' => rand(1, min(5, $producto->stock)),
                        'precio_unitario' => $producto->precio_venta,
                    ];
                })->toArray()
            ],
            [
                'cliente_id' => $clientes->random()->id,
                'vendedor_type' => 'App\Models\User',
                'vendedor_id' => $usuarios->random()->id,
                'fecha' => Carbon::now()->subDays(rand(1, 15)),
                'estado' => 'pagado',
                'notas' => 'Venta de productos varios',
                'productos' => $productos->random(min(2, $productos->count()))->map(function ($producto) {
                    return [
                        'producto_id' => $producto->id,
                        'cantidad' => rand(1, min(3, $producto->stock)),
                        'precio_unitario' => $producto->precio_venta,
                    ];
                })->toArray()
            ],
            [
                'cliente_id' => $clientes->random()->id,
                'vendedor_type' => 'App\Models\User',
                'vendedor_id' => $usuarios->random()->id,
                'fecha' => Carbon::now()->subDays(rand(1, 15)),
                'estado' => 'pendiente',
                'notas' => 'Venta pendiente de pago',
                'productos' => $productos->random(min(1, $productos->count()))->map(function ($producto) {
                    return [
                        'producto_id' => $producto->id,
                        'cantidad' => rand(1, min(2, $producto->stock)),
                        'precio_unitario' => $producto->precio_venta,
                    ];
                })->toArray()
            ],
        ];

        foreach ($ventas as $ventaData) {
            $productosVenta = $ventaData['productos'];
            unset($ventaData['productos']);

            // Calcular totales
            $total = 0;
            foreach ($productosVenta as $producto) {
                $total += $producto['cantidad'] * $producto['precio_unitario'];
            }
            $ventaData['subtotal'] = $total;
            $ventaData['total'] = $total;
            $ventaData['numero_venta'] = 'V' . str_pad(Venta::count() + 1, 4, '0', STR_PAD_LEFT);

            $venta = Venta::create($ventaData);

            // Crear items de venta y reducir stock
            foreach ($productosVenta as $productoData) {
                VentaItem::create([
                    'venta_id' => $venta->id,
                    'ventable_id' => $productoData['producto_id'],
                    'ventable_type' => 'App\Models\Producto',
                    'cantidad' => $productoData['cantidad'],
                    'precio' => $productoData['precio_unitario'],
                    'descuento' => 0,
                    'subtotal' => $productoData['cantidad'] * $productoData['precio_unitario'],
                    'descuento_monto' => 0,
                ]);

                // Registrar movimiento de inventario y reducir stock
                $producto = Producto::find($productoData['producto_id']);
                if ($producto) {
                    $stockAnterior = $producto->stock;
                    $producto->decrement('stock', $productoData['cantidad']);
                    $stockPosterior = $producto->stock;

                    InventarioMovimiento::create([
                        'producto_id' => $producto->id,
                        'tipo' => 'salida',
                        'cantidad' => $productoData['cantidad'],
                        'stock_anterior' => $stockAnterior,
                        'stock_posterior' => $stockPosterior,
                        'motivo' => 'Venta realizada',
                        'referencia_type' => 'App\Models\Venta',
                        'referencia_id' => $venta->id,
                        'user_id' => $venta->vendedor_id,
                        'detalles' => "Venta #{$venta->numero_venta} - {$producto->nombre}",
                    ]);
                }
            }
        }
    }
}
