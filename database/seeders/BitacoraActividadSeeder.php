<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Cliente;
use App\Models\BitacoraActividad;
use Illuminate\Support\Carbon;
use Faker\Factory as Faker;

class BitacoraActividadSeeder extends Seeder
{
    public function run(): void
    {
        $faker = Faker::create('es_MX');

        // Usuario y cliente base
        $usuario = User::first() ?? User::factory()->create(['name' => 'Administrador']);
        $cliente = Cliente::first() ?? Cliente::factory()->create(['nombre_razon_social' => 'Cliente Demo']);

        // 1. Actividad fija de ejemplo (soporte)
        BitacoraActividad::firstOrCreate(
            ['titulo' => 'Soporte remoto - impresora'],
            [
                'user_id'       => $usuario->id,
                'cliente_id'    => $cliente->id,
                'descripcion'   => 'Resolución de error de conexión de impresora en sucursal principal.',
                'fecha'         => Carbon::now()->toDateString(),
                'hora'          => '10:30',
                'inicio_at'     => Carbon::now()->subHour(),
                'fin_at'        => Carbon::now(),
                'tipo'          => 'soporte',
                'estado'        => 'completado',
                'prioridad'     => 2,
                'ubicacion'     => 'Sucursal Centro',
                'adjuntos'      => ['capturas' => ['error1.png']],
                'es_facturable' => true,
                'costo_mxn'     => 850.00,
            ]
        );

        // 2. Actividad fija de instalación
        BitacoraActividad::firstOrCreate(
            ['titulo' => 'Instalación de cámaras de seguridad'],
            [
                'user_id'       => $usuario->id,
                'cliente_id'    => $cliente->id,
                'descripcion'   => 'Montaje y configuración de 4 cámaras IP con grabador NVR.',
                'fecha'         => Carbon::yesterday()->toDateString(),
                'hora'          => '15:00',
                'inicio_at'     => Carbon::yesterday()->setTime(15, 0),
                'fin_at'        => Carbon::yesterday()->setTime(18, 30),
                'tipo'          => 'instalacion',
                'estado'        => 'completado',
                'prioridad'     => 3,
                'ubicacion'     => 'Sucursal Norte',
                'adjuntos'      => ['fotos' => ['cam1.jpg', 'cam2.jpg']],
                'es_facturable' => true,
                'costo_mxn'     => 4500.00,
            ]
        );

        // 3. Actividades de prueba con Faker (solo en local/testing)
        if (app()->environment('local', 'testing')) {
            $tipos = ['soporte', 'instalacion', 'mantenimiento'];
            $estados = ['pendiente', 'en_proceso', 'completado', 'cancelado'];
            $prioridades = [1, 2, 3];

            for ($i = 0; $i < 20; $i++) {
                $inicio = $faker->dateTimeBetween('-1 month', 'now');
                $fin    = (clone $inicio)->modify('+' . $faker->numberBetween(15, 240) . ' minutes');

                BitacoraActividad::create([
                    'user_id'       => User::inRandomOrder()->first()->id ?? $usuario->id,
                    'cliente_id'    => Cliente::inRandomOrder()->first()->id ?? $cliente->id,
                    'titulo'        => $faker->sentence(4),
                    'descripcion'   => $faker->paragraph(),
                    'fecha'         => $inicio->format('Y-m-d'),
                    'hora'          => $inicio->format('H:i'),
                    'inicio_at'     => $inicio,
                    'fin_at'        => $fin,
                    'tipo'          => $faker->randomElement($tipos),
                    'estado'        => $faker->randomElement($estados),
                    'prioridad'     => $faker->randomElement($prioridades),
                    'ubicacion'     => $faker->address(),
                    'adjuntos'      => ['docs' => [$faker->word . '.pdf']],
                    'es_facturable' => $faker->boolean(),
                    'costo_mxn'     => $faker->randomFloat(2, 100, 5000),
                ]);
            }
        }
    }
}
