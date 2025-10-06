<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Compra;
use App\Models\CompraItem;
use App\Models\Proveedor;
use App\Models\Producto;
use App\Models\InventarioMovimiento;
use App\Services\InventarioService;
use Carbon\Carbon;

class CompraSeeder extends Seeder
{
    public function __construct(private readonly InventarioService $inventarioService)
    {
    }

    public function run(): void
    {
        // Obtener proveedores existentes
        $proveedores = Proveedor::all();

        if ($proveedores->isEmpty()) {
            return; // No hay proveedores, no podemos crear compras
        }

        // Obtener productos existentes con stock suficiente para compras
        $productos = Producto::where('estado', 'activo')->get();

        if ($productos->isEmpty()) {
            return; // No hay productos disponibles, no podemos crear compras
        }

        // Obtener almacenes existentes
        $almacenes = \App\Models\Almacen::all();

        if ($almacenes->isEmpty()) {
            return; // No hay almacenes, no podemos crear compras
        }

        // Crear algunas compras de ejemplo usando productos existentes
        $compras = [
            [
                'proveedor_id' => $proveedores->random()->id,
                'estado' => 'procesada',
                'notas' => 'Compra inicial de productos básicos',
                'productos' => $productos->random(min(3, $productos->count()))->map(function ($producto) {
                    return [
                        'producto_id' => $producto->id,
                        'cantidad' => rand(10, 50),
                        'precio_unitario' => $producto->precio_compra ?: rand(10, 100),
                    ];
                })->toArray()
            ],
            [
                'proveedor_id' => $proveedores->random()->id,
                'estado' => 'procesada',
                'notas' => 'Compra de repuestos',
                'productos' => $productos->random(min(2, $productos->count()))->map(function ($producto) {
                    return [
                        'producto_id' => $producto->id,
                        'cantidad' => rand(5, 30),
                        'precio_unitario' => $producto->precio_compra ?: rand(8, 50),
                    ];
                })->toArray()
            ],
            [
                'proveedor_id' => $proveedores->random()->id,
                'estado' => 'procesada',
                'notas' => 'Compra de materiales',
                'productos' => $productos->random(min(3, $productos->count()))->map(function ($producto) {
                    return [
                        'producto_id' => $producto->id,
                        'cantidad' => rand(20, 100),
                        'precio_unitario' => $producto->precio_compra ?: rand(5, 25),
                    ];
                })->toArray()
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

            // Elegir el almacén principal para esta compra
            $almacenCompra = $almacenes->first();

            $compraData['almacen_id'] = $almacenCompra->id;

            $compra = Compra::create($compraData);

            // Asociar productos existentes a la compra
            foreach ($productos as $productoData) {
                $producto = Producto::find($productoData['producto_id']);

                if (!$producto) {
                    continue; // Saltar si el producto no existe
                }

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

                // Usar servicio de inventario para entrada correcta
                $this->inventarioService->entrada($producto, $productoData['cantidad'], [
                    'almacen_id' => $almacenCompra->id,
                    'motivo' => 'Compra procesada',
                    'referencia_type' => 'App\Models\Compra',
                    'referencia_id' => $compra->id,
                    'user_id' => $this->obtenerUsuarioAdminId(),
                    'detalles' => [
                        'compra_numero' => $compra->numero_compra,
                        'producto_nombre' => $producto->nombre,
                        'precio_unitario' => $productoData['precio_unitario']
                    ],
                ]);
            }
        }
    }

    /**
     * Obtener el ID del usuario administrador existente
     */
    private function obtenerUsuarioAdminId(): int
    {
        // Buscar usuario administrador por email (como se crea en RolesAndPermissionsSeeder)
        $adminUser = \App\Models\User::where('email', 'jesuslopeznoriega@hotmail.com')->first();

        if ($adminUser) {
            return $adminUser->id;
        }

        // Si no existe el usuario administrador, buscar cualquier usuario existente
        $anyUser = \App\Models\User::first();

        if ($anyUser) {
            return $anyUser->id;
        }

        // Si no hay usuarios, crear uno temporal para evitar errores
        $tempUser = \App\Models\User::create([
            'name' => 'Usuario Temporal',
            'email' => 'temp-' . time() . '@temp.com',
            'password' => bcrypt('temp123'),
        ]);

        return $tempUser->id;
    }
}
