<?php

namespace Database\Seeders;

use App\Models\Producto;
use App\Models\Categoria;
use App\Models\Marca;
use App\Models\Proveedor;
use App\Models\Almacen;
use Illuminate\Database\Seeder;
use Faker\Factory as Faker;

class ProductoSeeder extends Seeder
{
    public function run(): void
    {
        $faker = Faker::create('es_MX');

        // Obtener relaciones necesarias
        $categoriaDefault = Categoria::firstOrCreate(
            ['nombre' => 'General'],
            ['descripcion' => 'Categoría general para productos']
        );

        $marcaDefault = Marca::firstOrCreate(
            ['nombre' => 'Genérica'],
            ['descripcion' => 'Marca genérica para productos']
        );

        $proveedorDefault = Proveedor::firstOrCreate(
            ['rfc' => 'XAXX010101000'],
            [
                'nombre_razon_social' => 'PROVEEDOR GENERAL',
                'tipo_persona' => 'Moral',
                'regimen_fiscal' => '616'
            ]
        );

        $almacenDefault = Almacen::firstOrCreate(
            ['nombre' => 'Almacén Principal'],
            ['ubicacion' => 'Sucursal Central']
        );

        // Productos básicos que siempre deben existir
        $productosEsenciales = [
            [
                'nombre' => 'Producto Genérico',
                'descripcion' => 'Producto básico de referencia',
                'codigo' => 'PROD-001',
                'codigo_barras' => '7501000000017',
                'categoria_id' => $categoriaDefault->id,
                'marca_id' => $marcaDefault->id,
                'proveedor_id' => $proveedorDefault->id,
                'almacen_id' => $almacenDefault->id,
                'stock' => 100,
                'stock_minimo' => 10,
                'precio_compra' => 50.00,
                'precio_venta' => 80.00,
                'impuesto' => 16.00,
                'unidad_medida' => 'Pieza',
                'tipo_producto' => 'fisico',
                'estado' => 'activo'
            ]
        ];

        foreach ($productosEsenciales as $producto) {
            Producto::firstOrCreate(
                ['codigo' => $producto['codigo']],
                $producto
            );
        }

        // Productos adicionales para desarrollo
        if (app()->environment('local', 'testing')) {
            $unidadesMedida = ['Pieza', 'Kilogramo', 'Litro', 'Metro', 'Caja', 'Paquete'];
            $tiposProducto = ['fisico', 'digital'];

            for ($i = 0; $i < 20; $i++) {
                $tipo = $faker->randomElement($tiposProducto);

                // Generación segura de fecha de vencimiento
                $vencimiento = null;
                if ($tipo === 'fisico' && $faker->boolean(70)) {
                    $vencimiento = $faker->dateTimeBetween('now', '+2 years')->format('Y-m-d');
                }

                Producto::create([
                    'nombre' => $faker->unique()->words(3, true),
                    'descripcion' => $faker->paragraph(2),
                    'codigo' => 'PROD-' . str_pad($i + 2, 3, '0', STR_PAD_LEFT),
                    'codigo_barras' => $faker->unique()->ean13(),
                    'numero_serie' => $faker->optional(0.3)->bothify('SN#####'),
                    'categoria_id' => $categoriaDefault->id,
                    'marca_id' => $marcaDefault->id,
                    'proveedor_id' => $faker->optional(0.8)->randomElement([$proveedorDefault->id]),
                    'almacen_id' => $faker->optional(0.9)->randomElement([$almacenDefault->id]),
                    'stock' => $faker->numberBetween(0, 500),
                    'stock_minimo' => $faker->numberBetween(5, 50),
                    'precio_compra' => $faker->randomFloat(2, 10, 500),
                    'precio_venta' => $faker->randomFloat(2, 15, 750),
                    'impuesto' => $faker->randomElement([0, 8, 16]),
                    'unidad_medida' => $faker->randomElement($unidadesMedida),
                    'fecha_vencimiento' => $vencimiento,
                    'tipo_producto' => $tipo,
                    'imagen' => $faker->optional(0.6)->imageUrl(400, 400, 'product'),
                    'estado' => $faker->randomElement(['activo', 'inactivo'])
                ]);
            }
        }
    }
}
