<?php
//mejoras
namespace Database\Seeders;

use App\Models\Almacen;
use Illuminate\Database\Seeder;
use Faker\Factory as Faker;

class AlmacenSeeder extends Seeder
{
    public function run()
    {
        // 1. Datos básicos de almacenes (siempre existirán)
        $almacenesEsenciales = [
            [
                'nombre' => 'Almacén Principal',
                'descripcion' => 'Almacén central de la empresa',
                'ubicacion' => 'Edificio A, Nivel 1'
            ],
            [
                'nombre' => 'Bodega Secundaria',
                'descripcion' => 'Almacén para sobrestock',
                'ubicacion' => 'Edificio B, Nivel 2'
            ]
        ];

        foreach ($almacenesEsenciales as $almacen) {
            Almacen::firstOrCreate(
                ['nombre' => $almacen['nombre']], // Buscar por nombre para evitar duplicados
                $almacen
            );
        }

        // 2. Generación dinámica con Faker (opcional)
        $faker = Faker::create('es_ES'); // Configuración regional en español

        $ubicaciones = [
            'Sótano 1',
            'Planta Baja',
            'Nivel 3, Ala Oeste',
            'Nave Industrial 5'
        ];

        for ($i = 0; $i < 3; $i++) {
            Almacen::firstOrCreate(
                ['nombre' => $faker->unique()->company . ' Storage'],
                [
                    'descripcion' => $faker->sentence(10),
                    'ubicacion' => $faker->randomElement($ubicaciones)
                ]
            );
        }
    }
}
