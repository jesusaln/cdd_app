<?php

namespace Database\Seeders;

use App\Models\Tecnico;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class TecnicoSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $tecnicos = [
            [
                'nombre' => 'Juan',
                'apellido' => 'Pérez',
                'email' => 'juan.perez@empresa.com',
                'telefono' => '6621234567',
                'direccion' => 'Calle Principal 123, Hermosillo, Sonora',
                'activo' => true
            ],
            [
                'nombre' => 'María',
                'apellido' => 'González',
                'email' => 'maria.gonzalez@empresa.com',
                'telefono' => '6622345678',
                'direccion' => 'Avenida Secundaria 456, Hermosillo, Sonora',
                'activo' => true
            ],
            [
                'nombre' => 'Carlos',
                'apellido' => 'López',
                'email' => 'carlos.lopez@empresa.com',
                'telefono' => '6623456789',
                'direccion' => 'Boulevard Industrial 789, Hermosillo, Sonora',
                'activo' => true
            ],
            [
                'nombre' => 'Ana',
                'apellido' => 'Martínez',
                'email' => 'ana.martinez@empresa.com',
                'telefono' => '6624567890',
                'direccion' => 'Callejón Técnico 321, Hermosillo, Sonora',
                'activo' => true
            ],
            [
                'nombre' => 'Luis',
                'apellido' => 'Rodríguez',
                'email' => 'luis.rodriguez@empresa.com',
                'telefono' => '6625678901',
                'direccion' => 'Paseo del Técnico 654, Hermosillo, Sonora',
                'activo' => true
            ]
        ];

        foreach ($tecnicos as $tecnico) {
            Tecnico::firstOrCreate(
                ['email' => $tecnico['email']],
                $tecnico
            );
        }
    }
}
