<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\CategoriaHerramienta;
use Illuminate\Support\Str;

class CategoriaHerramientaSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categorias = [
            [
                'nombre' => 'Eléctrica',
                'slug' => 'electrica',
                'descripcion' => 'Herramientas eléctricas como taladros, sierras, lijadoras, etc.',
                'activo' => true,
            ],
            [
                'nombre' => 'Manual',
                'slug' => 'manual',
                'descripcion' => 'Herramientas manuales como martillos, destornilladores, llaves, etc.',
                'activo' => true,
            ],
            [
                'nombre' => 'Medición',
                'slug' => 'medicion',
                'descripcion' => 'Herramientas de medición como cintas métricas, niveles, calibres, etc.',
                'activo' => true,
            ],
            [
                'nombre' => 'Seguridad',
                'slug' => 'seguridad',
                'descripcion' => 'Equipos de protección personal y herramientas de seguridad.',
                'activo' => true,
            ],
            [
                'nombre' => 'Limpieza',
                'slug' => 'limpieza',
                'descripcion' => 'Herramientas y equipos de limpieza.',
                'activo' => true,
            ],
            [
                'nombre' => 'Jardinería',
                'slug' => 'jardineria',
                'descripcion' => 'Herramientas para trabajos de jardinería y exteriores.',
                'activo' => true,
            ],
            [
                'nombre' => 'Construcción',
                'slug' => 'construccion',
                'descripcion' => 'Herramientas para construcción y obra.',
                'activo' => true,
            ],
            [
                'nombre' => 'Electrónica',
                'slug' => 'electronica',
                'descripcion' => 'Herramientas especializadas para trabajo electrónico.',
                'activo' => true,
            ],
            [
                'nombre' => 'Otra',
                'slug' => 'otra',
                'descripcion' => 'Categoría para herramientas que no encajan en las demás categorías.',
                'activo' => true,
            ],
        ];

        foreach ($categorias as $categoria) {
            CategoriaHerramienta::create($categoria);
        }
    }
}
