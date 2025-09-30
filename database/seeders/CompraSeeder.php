<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Compra;
use App\Models\CompraItem;
use App\Models\Proveedor;
use App\Models\Producto;
use App\Models\InventarioMovimiento;
use Carbon\Carbon;

class CompraSeeder extends Seeder
{
    public function run(): void
    {
        // Obtener proveedores existentes
        $proveedores = Proveedor::all();

        if ($proveedores->isEmpty()) {
            return; // No hay proveedores, no podemos crear compras
        }

        // Crear algunas compras de ejemplo
        $compras = [
            [
                'proveedor_id' => $proveedores->random()->id,
                'estado' => 'procesada',
                'notas' => 'Compra inicial de productos básicos',
                'productos' => [
                    ['nombre' => 'Producto A', 'cantidad' => 50, 'precio_unitario' => 10.00, 'categoria' => 'Electrónicos'],
                    ['nombre' => 'Producto B', 'cantidad' => 30, 'precio_unitario' => 25.00, 'categoria' => 'Herramientas'],
                    ['nombre' => 'Producto C', 'cantidad' => 20, 'precio_unitario' => 15.00, 'categoria' => 'Accesorios'],
                ]
            ],
            [
                'proveedor_id' => $proveedores->random()->id,

                'estado' => 'procesada',
                'notas' => 'Compra de repuestos',
                'productos' => [
                    ['nombre' => 'Repuesto X', 'cantidad' => 40, 'precio_unitario' => 8.00, 'categoria' => 'Repuestos'],
                    ['nombre' => 'Repuesto Y', 'cantidad' => 25, 'precio_unitario' => 12.00, 'categoria' => 'Repuestos'],
                ]
            ],
            [
                'proveedor_id' => $proveedores->random()->id,

                'estado' => 'procesada',
                'notas' => 'Compra de materiales',
                'productos' => [
                    ['nombre' => 'Material P', 'cantidad' => 100, 'precio_unitario' => 5.00, 'categoria' => 'Materiales'],
                    ['nombre' => 'Material Q', 'cantidad' => 75, 'precio_unitario' => 7.50, 'categoria' => 'Materiales'],
                    ['nombre' => 'Material R', 'cantidad' => 60, 'precio_unitario' => 9.00, 'categoria' => 'Materiales'],
                ]
            ],
        ];

        foreach ($compras as $compraData) {
            $productos = $compraData['productos'];
            unset($compraData['productos']);

            $total = 0;
            foreach ($productos as $producto) {
                $total += $producto['cantidad'] * $producto['precio_unitario'];
            }
            $compraData['total'] = $total;

            $compra = Compra::create($compraData);

            // Crear productos si no existen y asociarlos a la compra
            foreach ($productos as $productoData) {
                $producto = Producto::firstOrCreate(
                    ['nombre' => $productoData['nombre']],
                    [
                        'descripcion' => 'Producto creado desde compra',
                        'codigo' => 'COMP-' . strtoupper(substr(md5($productoData['nombre']), 0, 8)),
                        'codigo_barras' => 'CB' . rand(100000000000, 999999999999),
                        'precio_compra' => $productoData['precio_unitario'],
                        'precio_venta' => $productoData['precio_unitario'] * 1.5, // Precio de venta = costo * 1.5
                        'stock' => $productoData['cantidad'],
                        'stock_minimo' => 10,
                        'impuesto' => 16.00,
                        'unidad_medida' => 'Pieza',
                        'tipo_producto' => 'fisico',
                        'estado' => 'activo',
                        'categoria_id' => \App\Models\Categoria::where('nombre', $productoData['categoria'])->first()?->id ?? 1,
                        'marca_id' => \App\Models\Marca::first()?->id ?? 1,
                        'proveedor_id' => $compra->proveedor_id,
                    ]
                );

                // Asociar producto a la compra
                CompraItem::create([
                    'compra_id' => $compra->id,
                    'comprable_id' => $producto->id,
                    'comprable_type' => 'App\Models\Producto',
                    'cantidad' => $productoData['cantidad'],
                    'precio' => $productoData['precio_unitario'],
                    'descuento' => 0,
                    'subtotal' => $productoData['cantidad'] * $productoData['precio_unitario'],
                    'descuento_monto' => 0,
                ]);

                // Registrar movimiento de inventario
                $stockAnterior = $producto->stock;
                $producto->increment('stock', $productoData['cantidad']);
                $stockPosterior = $producto->stock;

                InventarioMovimiento::create([
                    'producto_id' => $producto->id,
                    'tipo' => 'entrada',
                    'cantidad' => $productoData['cantidad'],
                    'stock_anterior' => $stockAnterior,
                    'stock_posterior' => $stockPosterior,
                    'motivo' => 'Compra procesada',
                    'referencia_type' => 'App\Models\Compra',
                    'referencia_id' => $compra->id,
                    'user_id' => 1, // Usuario por defecto
                    'detalles' => "Compra #{$compra->numero_compra} - {$productoData['nombre']}",
                ]);
            }
        }
    }
}
