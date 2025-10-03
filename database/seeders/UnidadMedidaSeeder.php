<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class UnidadMedidaSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $unidades = [
            ['nombre' => 'Pieza', 'abreviatura' => 'pz', 'descripcion' => 'Unidad básica de conteo'],
            ['nombre' => 'Kilogramo', 'abreviatura' => 'kg', 'descripcion' => 'Unidad de masa'],
            ['nombre' => 'Gramo', 'abreviatura' => 'g', 'descripcion' => 'Unidad de masa pequeña'],
            ['nombre' => 'Litro', 'abreviatura' => 'L', 'descripcion' => 'Unidad de volumen'],
            ['nombre' => 'Mililitro', 'abreviatura' => 'ml', 'descripcion' => 'Unidad de volumen pequeña'],
            ['nombre' => 'Metro', 'abreviatura' => 'm', 'descripcion' => 'Unidad de longitud'],
            ['nombre' => 'Centímetro', 'abreviatura' => 'cm', 'descripcion' => 'Unidad de longitud pequeña'],
            ['nombre' => 'Milímetro', 'abreviatura' => 'mm', 'descripcion' => 'Unidad de longitud muy pequeña'],
            ['nombre' => 'Unidad', 'abreviatura' => 'u', 'descripcion' => 'Unidad genérica'],
            ['nombre' => 'Caja', 'abreviatura' => 'cj', 'descripcion' => 'Empaque en caja'],
            ['nombre' => 'Paquete', 'abreviatura' => 'pq', 'descripcion' => 'Empaque en paquete'],
        ];

        foreach ($unidades as $unidad) {
            \App\Models\UnidadMedida::firstOrCreate(
                ['nombre' => $unidad['nombre']],
                $unidad
            );
        }
    }
}
