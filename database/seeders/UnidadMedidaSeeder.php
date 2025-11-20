<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\UnidadMedida;

class UnidadMedidaSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $unidades = [
            [
                'nombre' => 'Pieza',
                'abreviatura' => 'pz',
                'descripcion' => 'Unidad individual o pieza (predeterminada)',
                'estado' => 'activo',
            ],
            [
                'nombre' => 'Kilogramos',
                'abreviatura' => 'kg',
                'descripcion' => 'Unidad de peso en kilogramos',
                'estado' => 'activo',
            ],
            [
                'nombre' => 'Gramos',
                'abreviatura' => 'g',
                'descripcion' => 'Unidad de peso en gramos',
                'estado' => 'activo',
            ],
            [
                'nombre' => 'Litros',
                'abreviatura' => 'l',
                'descripcion' => 'Unidad de volumen en litros',
                'estado' => 'activo',
            ],
            [
                'nombre' => 'Mililitros',
                'abreviatura' => 'ml',
                'descripcion' => 'Unidad de volumen en mililitros',
                'estado' => 'activo',
            ],
            [
                'nombre' => 'Metro(s)',
                'abreviatura' => 'm',
                'descripcion' => 'Unidad de longitud en metros',
                'estado' => 'activo',
            ],
            [
                'nombre' => 'Centimetros',
                'abreviatura' => 'cm',
                'descripcion' => 'Unidad de longitud en Centimetros',
                'estado' => 'activo',
            ],
            [
                'nombre' => 'Milimetros',
                'abreviatura' => 'mm',
                'descripcion' => 'Unidad de longitud en Milimetros',
                'estado' => 'activo',
            ],
            [
                'nombre' => 'Unidad',
                'abreviatura' => 'u',
                'descripcion' => 'Unidad genérica',
                'estado' => 'activo',
            ],
            [
                'nombre' => 'Caja',
                'abreviatura' => 'cja',
                'descripcion' => 'Unidad de empaque en caja',
                'estado' => 'activo',
            ],
            [
                'nombre' => 'Paquete',
                'abreviatura' => 'paq',
                'descripcion' => 'Unidad de empaque en paquete',
                'estado' => 'activo',
            ],
            [
                'nombre' => 'Juego',
                'abreviatura' => 'jgo',
                'descripcion' => 'Conjunto de piezas',
                'estado' => 'activo',
            ],
            [
                'nombre' => 'Resma',
                'abreviatura' => 'res',
                'descripcion' => 'Resma de papel (500 hojas)',
                'estado' => 'activo',
            ],
        ];

        foreach ($unidades as $unidad) {
            $record = UnidadMedida::withTrashed()->updateOrCreate(
                ['nombre' => $unidad['nombre']],
                $unidad
            );

            if ($record->trashed()) {
                $record->restore();
            }
        }
    }
}



