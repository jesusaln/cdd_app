<?php

namespace Database\Seeders;

use App\Models\Categoria;
use Illuminate\Database\Seeder;
use Faker\Factory as Faker;

class CategoriaSeeder extends Seeder
{
    public function run()
    {
        // Datos básicos de categorías (siempre se crearán)
        $categoriasBasicas = [
            [
                'nombre' => 'Sin Categoría',
                'descripcion' => 'Productos sin categoría específica'
            ],
            [
                'nombre' => 'Electrónicos',
                'descripcion' => 'Dispositivos electrónicos y accesorios'
            ],
            [
                'nombre' => 'Oficina',
                'descripcion' => 'Artículos de papelería y oficina'
            ]
        ];

        foreach ($categoriasBasicas as $categoria) {
            Categoria::firstOrCreate(
                ['nombre' => $categoria['nombre']],
                $categoria
            );
        }

        // Opcional: Generar categorías aleatorias con Faker
        $faker = Faker::create();

        for ($i = 0; $i < 5; $i++) {
            Categoria::firstOrCreate(
                ['nombre' => $faker->unique()->word],
                [
                    'descripcion' => $faker->sentence(6)
                ]
            );
        }
    }
}
