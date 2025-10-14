<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Producto;
use App\Models\Almacen;
use App\Models\Inventario;

class InventarioSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Obtener almacenes existentes
        $almacenes = Almacen::all();
        if ($almacenes->isEmpty()) {
            $this->command->info('No hay almacenes, saltando InventarioSeeder');
            return;
        }

        // Obtener productos existentes
        $productos = Producto::all();
        if ($productos->isEmpty()) {
            $this->command->info('No hay productos, saltando InventarioSeeder');
            return;
        }

        // Distribuir productos en almacenes según la solicitud:
        // 10 productos en un almacén, 20 en otro, 50 en otro
        $distribucion = [
            10, // primer almacén
            20, // segundo almacén
            50, // tercer almacén
        ];

        $productoIndex = 0;

        foreach ($almacenes as $index => $almacen) {
            $cantidadProductos = $distribucion[$index] ?? 10; // default 10 si hay más almacenes

            for ($i = 0; $i < $cantidadProductos && $productoIndex < $productos->count(); $i++) {
                $producto = $productos[$productoIndex];

                Inventario::firstOrCreate(
                    [
                        'producto_id' => $producto->id,
                        'almacen_id' => $almacen->id,
                    ],
                    [
                        'cantidad' => rand(5, 100), // cantidad aleatoria entre 5 y 100
                        'stock_minimo' => rand(1, 10), // stock mínimo aleatorio
                    ]
                );

                $productoIndex++;
            }

            // Si ya no hay más productos, salir
            if ($productoIndex >= $productos->count()) {
                break;
            }
        }

        // Actualizar stock total en productos
        foreach ($productos as $producto) {
            $totalStock = Inventario::where('producto_id', $producto->id)->sum('cantidad');
            $producto->update(['stock' => $totalStock]);
        }

        $this->command->info('Inventarios creados y stocks actualizados exitosamente');
    }
}
