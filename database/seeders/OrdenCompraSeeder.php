<?php

namespace Database\Seeders;

use App\Models\OrdenCompra;
use App\Models\Producto;
use App\Models\Proveedor;
use Illuminate\Database\Seeder;
use Faker\Factory as Faker;

class OrdenCompraSeeder extends Seeder
{
    public function run(): void
    {
        $faker = Faker::create('es_MX');

        // Obtener proveedores y productos existentes
        $proveedores = Proveedor::all();
        $productos = Producto::all();

        if ($proveedores->isEmpty() || $productos->isEmpty()) {
            $this->command->warn('No hay proveedores o productos suficientes para crear órdenes de compra. Ejecuta ProveedorSeeder y ProductoSeeder primero.');
            return;
        }

        // Órdenes de compra esenciales para desarrollo
        $ordenesEsenciales = [
            [
                'proveedor_id' => $proveedores->first()->id,
                'numero_orden' => 'OC-001',
                'fecha_orden' => now()->subDays(10),
                'fecha_entrega_esperada' => now()->addDays(15),
                'prioridad' => 'media',
                'direccion_entrega' => 'Sucursal Central, Calle Principal 123',
                'terminos_pago' => '30 días',
                'metodo_pago' => 'transferencia',
                'descuento_general' => 250.00,
                'observaciones' => 'Orden de prueba inicial',
                'estado' => 'pendiente',
            ]
        ];

        foreach ($ordenesEsenciales as $ordenData) {
            // Crear orden sin totales primero
            $orden = OrdenCompra::firstOrCreate(
                ['numero_orden' => $ordenData['numero_orden']],
                $ordenData
            );

            // Agregar productos a la orden y calcular totales
            if ($orden->productos()->count() === 0) {
                $productosSeleccionados = $productos->random(min(3, $productos->count()));
                $subtotal = 0;
                $descuentoItems = 0;

                foreach ($productosSeleccionados as $producto) {
                    $cantidad = $faker->numberBetween(1, 10);
                    $precio = $producto->precio_compra ?? $faker->randomFloat(2, 50, 200);
                    $descuento = $faker->randomFloat(2, 0, 50);

                    $orden->productos()->attach($producto->id, [
                        'cantidad' => $cantidad,
                        'precio' => $precio,
                        'descuento' => $descuento,
                        'unidad_medida' => $producto->unidad_medida ?? 'Pieza',
                    ]);

                    // Calcular subtotal y descuentos
                    $subtotalItem = $cantidad * $precio;
                    $descuentoItem = ($subtotalItem * $descuento) / 100;
                    $subtotal += $subtotalItem;
                    $descuentoItems += $descuentoItem;
                }

                // Calcular totales finales
                $descuentoGeneral = $ordenData['descuento_general'] ?? 0;
                $subtotalDespuesDescuentoGeneral = $subtotal - $descuentoItems - $descuentoGeneral;
                $iva = $subtotalDespuesDescuentoGeneral * 0.16;
                $total = $subtotalDespuesDescuentoGeneral + $iva;

                // Actualizar orden con totales calculados
                $orden->update([
                    'subtotal' => round($subtotal, 2),
                    'descuento_items' => round($descuentoItems, 2),
                    'iva' => round($iva, 2),
                    'total' => round($total, 2),
                ]);
            }
        }

        // Órdenes adicionales para desarrollo
        if (app()->environment('local', 'testing')) {
            for ($i = 0; $i < 10; $i++) {
                $proveedor = $proveedores->random();
                $fechaOrden = $faker->dateTimeBetween('-30 days', 'now');
                $subtotal = 0;
                $productosOrden = [];

                // Seleccionar productos aleatorios
                $numProductos = $faker->numberBetween(1, 5);
                $productosSeleccionados = $productos->random(min($numProductos, $productos->count()));

                foreach ($productosSeleccionados as $producto) {
                    $cantidad = $faker->numberBetween(1, 20);
                    $precio = $producto->precio_compra ?? $faker->randomFloat(2, 20, 500);
                    $descuento = $faker->randomFloat(2, 0, $precio * 0.1);

                    $productosOrden[] = [
                        'producto' => $producto,
                        'cantidad' => $cantidad,
                        'precio' => $precio,
                        'descuento' => $descuento,
                        'unidad_medida' => $producto->unidad_medida ?? 'Pieza',
                    ];

                    $subtotal += ($precio - $descuento) * $cantidad;
                }

                $descuentoGeneral = $faker->randomFloat(2, 0, $subtotal * 0.05);
                $iva = ($subtotal - $descuentoGeneral) * 0.16;
                $total = $subtotal - $descuentoGeneral + $iva;

                $orden = OrdenCompra::create([
                    'proveedor_id' => $proveedor->id,
                    'numero_orden' => 'OC-' . str_pad($i + 2, 3, '0', STR_PAD_LEFT),
                    'fecha_orden' => $fechaOrden,
                    'fecha_entrega_esperada' => $faker->dateTimeBetween($fechaOrden, '+30 days'),
                    'prioridad' => $faker->randomElement(['baja', 'media', 'alta', 'urgente']),
                    'direccion_entrega' => $faker->address(),
                    'terminos_pago' => $faker->randomElement(['contado', '7 días', '15 días', '30 días', '60 días']),
                    'metodo_pago' => $faker->randomElement(['efectivo', 'transferencia', 'cheque', 'tarjeta']),
                    'subtotal' => $subtotal,
                    'descuento_items' => 0, // Ya incluido en productos
                    'descuento_general' => $descuentoGeneral,
                    'iva' => $iva,
                    'total' => $total,
                    'observaciones' => $faker->optional(0.7)->sentence(),
                    'estado' => $faker->randomElement(['pendiente', 'aprobada', 'enviada', 'recibida', 'cancelada']),
                    'fecha_recepcion' => $faker->optional(0.3)->dateTimeBetween($fechaOrden, 'now'),
                ]);

                // Adjuntar productos
                foreach ($productosOrden as $item) {
                    $orden->productos()->attach($item['producto']->id, [
                        'cantidad' => $item['cantidad'],
                        'precio' => $item['precio'],
                        'descuento' => $item['descuento'],
                        'unidad_medida' => $item['unidad_medida'],
                    ]);
                }
            }
        }
    }
}
