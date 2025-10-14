<?php

namespace Database\Seeders;

use App\Models\Marca;
use Illuminate\Database\Seeder;
use Faker\Factory as Faker;

class MarcaSeeder extends Seeder
{
    public function run()
    {
        // 1. Marcas básicas esenciales
        $marcasBasicas = [
            [
                'nombre' => 'Genérica',
                'descripcion' => 'Productos sin marca específica'
            ],
            [
                'nombre' => 'Propia',
                'descripcion' => 'Marca propia de la tienda'
            ],
            [
                'nombre' => 'Sin Marca',
                'descripcion' => 'Productos que no pertenecen a una marca'
            ]
        ];

        foreach ($marcasBasicas as $marca) {
            Marca::firstOrCreate(
                ['nombre' => $marca['nombre']],
                $marca
            );
        }

        // 2. Marcas populares (siempre creadas)
        $marcasPopulares = [
            'Sony',
            'Samsung',
            'LG',
            'HP',
            'Dell',
            'Nike',
            'Adidas',
            'Apple',
            'Microsoft',
            'Canon'
        ];

        foreach ($marcasPopulares as $nombreMarca) {
            Marca::firstOrCreate(
                ['nombre' => $nombreMarca],
                ['descripcion' => "Productos de la marca $nombreMarca"]
            );
        }

        // 3. Marcas aleatorias (solo en desarrollo)
        if (app()->environment('local', 'testing')) {
            $faker = Faker::create();

            $tiposMarcas = [
                'Tecnología' => ['Electrónica', 'Computación', 'Telecomunicaciones'],
                'Hogar' => ['Electrodomésticos', 'Muebles', 'Decoración'],
                'Deportes' => ['Fitness', 'Outdoor', 'Ropa deportiva']
            ];

            foreach ($tiposMarcas as $categoria => $subcategorias) {
                foreach ($subcategorias as $subcategoria) {
                    $nombreMarca = $faker->unique()->company . ' ' . $subcategoria;
                    Marca::create([
                        'nombre' => $nombreMarca,
                        'descripcion' => "Marca especializada en $subcategoria para $categoria"
                    ]);
                }
            }
        }
    }
}
