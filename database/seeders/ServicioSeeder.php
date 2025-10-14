<?php

namespace Database\Seeders;

use App\Models\Servicio;
use App\Models\Categoria;
use Illuminate\Database\Seeder;
use Faker\Factory as Faker;

class ServicioSeeder extends Seeder
{
    public function run(): void
    {
        $faker = Faker::create('es_MX');

        // Obtener categorías existentes o crear una por defecto
        $categoriaDefault = Categoria::firstOrCreate(
            ['nombre' => 'Servicios Generales'],
            ['descripcion' => 'Categoría para servicios diversos']
        );

        // Servicios básicos que siempre deben existir
        $serviciosEsenciales = [
            [
                'nombre' => 'Mantenimiento Básico',
                'descripcion' => 'Servicio de mantenimiento preventivo básico',
                'codigo' => 'SERV-001',
                'precio' => 500.00,
                'duracion' => 60,
                'estado' => 'activo', // Cambiado a string para coincidir con ENUM
                'categoria_id' => $categoriaDefault->id
            ],
            [
                'nombre' => 'Instalación Estándar',
                'descripcion' => 'Servicio de instalación de equipos básicos',
                'codigo' => 'SERV-002',
                'precio' => 800.00,
                'duracion' => 120,
                'estado' => 'activo', // Cambiado a string para coincidir con ENUM
                'categoria_id' => $categoriaDefault->id
            ]
        ];

        foreach ($serviciosEsenciales as $servicio) {
            Servicio::firstOrCreate(
                ['codigo' => $servicio['codigo']],
                $servicio
            );
        }

        // Servicios adicionales para desarrollo
        if (app()->environment('local', 'testing')) {
            $tiposServicios = [
                'Técnico' => ['Reparación', 'Diagnóstico', 'Configuración'],
                'Consultoría' => ['Asesoría', 'Capacitación', 'Implementación'],
                'Soporte' => ['Remoto', 'Presencial', 'Emergencia']
            ];

            foreach ($tiposServicios as $tipo => $servicios) {
                foreach ($servicios as $servicio) {
                    $nombre = "$tipo - $servicio";
                    Servicio::create([
                        'nombre' => $nombre,
                        'descripcion' => "Servicio de $servicio en el área de $tipo",
                        'codigo' => 'SERV-' . strtoupper(substr($tipo, 0, 3)) . '-' . $faker->unique()->numberBetween(100, 999),
                        'precio' => $faker->randomFloat(2, 300, 2000),
                        'duracion' => $faker->numberBetween(30, 240), // en minutos
                        'estado' => $faker->boolean(90) ? 'activo' : 'inactivo', // Convertido a valores ENUM
                        'categoria_id' => $categoriaDefault->id
                    ]);
                }
            }
        }
    }
}
