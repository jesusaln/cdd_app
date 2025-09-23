<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Obtener las categorías disponibles
        $categorias = DB::table('categoria_herramientas')->pluck('id', 'slug')->toArray();

        // Asignar categorías basadas en palabras clave en el nombre
        $asignaciones = [
            // Eléctrica
            'electrica' => [
                'taladro', 'sierra', 'amoladora', 'destornillador', 'lijadora', 'pulidora',
                'llave de impacto', 'sierra de calar', 'taladro de columna'
            ],
            // Manual
            'manual' => [
                'kit de herramientas', 'llave de tubo', 'alicates', 'martillo'
            ],
            // Medición
            'medicion' => [
                'multímetro', 'nivel láser', 'medidor de distancia', 'detector de voltaje',
                'herramienta de torque'
            ],
            // Seguridad
            'seguridad' => [
                'arnés', 'gafas'
            ],
            // Limpieza
            'limpieza' => [
                'aspiradora'
            ],
            // Construcción
            'construccion' => [
                'martillo neumático', 'elevador', 'prensa hidráulica', 'corte hidráulico'
            ],
            // Electrónica
            'electronica' => [
                'soldador', 'cortadora de plasma', 'máquina de soldar'
            ],
            // Otra
            'otra' => [
                'generador', 'compresor'
            ]
        ];

        foreach ($asignaciones as $categoriaSlug => $palabrasClave) {
            if (isset($categorias[$categoriaSlug])) {
                $categoriaId = $categorias[$categoriaSlug];

                foreach ($palabrasClave as $palabra) {
                    DB::table('herramientas')
                        ->where('categoria_id', null)
                        ->where('nombre', 'like', '%' . $palabra . '%')
                        ->update(['categoria_id' => $categoriaId]);
                }
            }
        }

        // Asignar categoría "otra" a las herramientas que aún no tienen categoría
        if (isset($categorias['otra'])) {
            DB::table('herramientas')
                ->where('categoria_id', null)
                ->update(['categoria_id' => $categorias['otra']]);
        }

        // Agregar campos adicionales a las herramientas existentes
        DB::table('herramientas')->where('estado', null)->update(['estado' => 'disponible']);
        DB::table('herramientas')->where('requiere_mantenimiento', null)->update(['requiere_mantenimiento' => false]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revertir los cambios
        DB::table('herramientas')->update([
            'categoria_id' => null,
            'estado' => null,
            'requiere_mantenimiento' => null
        ]);
    }
};
