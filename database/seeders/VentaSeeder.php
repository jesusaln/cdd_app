<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Venta;
use App\Models\VentaItem;
use App\Models\Cliente;
use App\Models\Producto;
use App\Models\User;
use App\Models\InventarioMovimiento;
use App\Services\InventarioService;
use Carbon\Carbon;

class VentaSeeder extends Seeder
{
    public function __construct(private readonly InventarioService $inventarioService)
    {
    }

    public function run(): void
    {
        // Obtener datos necesarios
        $clientes = Cliente::all();
        $productos = Producto::where('estado', 'activo')->get(); // Obtener productos activos (el stock se valida por almacén)
        $usuarios = User::all();
        $almacenes = \App\Models\Almacen::where('estado', 'activo')->get();

        if ($clientes->isEmpty() || $productos->isEmpty()) {
            $this->command->info('No hay clientes o productos para crear ventas. VentaSeeder completado.');
            return; // No hay clientes o productos para crear ventas
        }

        // Verificar si hay stock disponible en almacenes
        $productosConStockIds = [];
        foreach ($productos as $producto) {
            $inventario = \App\Models\Inventario::where('producto_id', $producto->id)
                ->where('almacen_id', $almacenes->first()->id)
                ->where('cantidad', '>', 0)
                ->first();

            if ($inventario) {
                $productosConStockIds[] = $producto->id;
            }
        }

        if (empty($productosConStockIds)) {
            $this->command->info('No hay productos con stock disponible para ventas. Ejecuta primero CompraSeeder.');
            return; // No hay productos con stock para vender
        }

        // Obtener productos con stock como colección
        $productosConStock = Producto::whereIn('id', $productosConStockIds)->get();
        $this->command->info('Productos con stock disponible: ' . $productosConStock->count());

        // Crear algunas ventas de ejemplo
        $ventas = [
            [
                'cliente_id' => $clientes->random()->id,
                'vendedor_type' => 'App\Models\User',
                'vendedor_id' => $usuarios->random()->id,
                'fecha' => Carbon::now()->subDays(rand(1, 15)),
                'estado' => 'pagado',
                'notas' => 'Venta realizada exitosamente',
                'productos' => $productosConStock->random(min(3, $productosConStock->count()))->map(function ($producto) use ($almacenes) {
                    $almacen = $almacenes->first();
                    $inventario = \App\Models\Inventario::where('producto_id', $producto->id)
                        ->where('almacen_id', $almacen->id)
                        ->first();

                    $stockDisponible = $inventario ? $inventario->cantidad : 0;
                    $cantidadMaxima = min(5, max(1, $stockDisponible));

                    return [
                        'producto_id' => $producto->id,
                        'cantidad' => rand(1, max(1, $cantidadMaxima)),
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
                'productos' => $productosConStock->random(min(2, $productosConStock->count()))->map(function ($producto) use ($almacenes) {
                    $almacen = $almacenes->first();
                    $inventario = \App\Models\Inventario::where('producto_id', $producto->id)
                        ->where('almacen_id', $almacen->id)
                        ->first();

                    $stockDisponible = $inventario ? $inventario->cantidad : 0;
                    $cantidadMaxima = min(3, max(1, $stockDisponible));

                    return [
                        'producto_id' => $producto->id,
                        'cantidad' => rand(1, max(1, $cantidadMaxima)),
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
                'productos' => $productosConStock->random(min(1, $productosConStock->count()))->map(function ($producto) use ($almacenes) {
                    $almacen = $almacenes->first();
                    $inventario = \App\Models\Inventario::where('producto_id', $producto->id)
                        ->where('almacen_id', $almacen->id)
                        ->first();

                    $stockDisponible = $inventario ? $inventario->cantidad : 0;
                    $cantidadMaxima = min(2, max(1, $stockDisponible));

                    return [
                        'producto_id' => $producto->id,
                        'cantidad' => rand(1, max(1, $cantidadMaxima)),
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

                // Usar servicio de inventario para salida correcta
                $producto = Producto::find($productoData['producto_id']);
                if ($producto) {
                    $this->inventarioService->salida($producto, $productoData['cantidad'], [
                        'almacen_id' => $almacenes->first()->id,
                        'motivo' => 'Venta realizada',
                        'referencia_type' => 'App\Models\Venta',
                        'referencia_id' => $venta->id,
                        'user_id' => $venta->vendedor_id,
                        'detalles' => [
                            'venta_numero' => $venta->numero_venta,
                            'producto_nombre' => $producto->nombre,
                            'precio_unitario' => $productoData['precio_unitario']
                        ],
                    ]);
                }
            }
        }
    }
}
