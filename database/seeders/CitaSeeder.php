<?php

namespace Database\Seeders;

use App\Models\Cita;
use App\Models\Cliente;
use App\Models\Tecnico;
use Illuminate\Database\Seeder;
use Faker\Factory as Faker;

class CitaSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Verificar que existan clientes y técnicos
        if (Cliente::count() === 0) {
            $this->command->info('No hay clientes. Ejecuta ClienteSeeder primero.');
            return;
        }

        if (Tecnico::count() === 0) {
            $this->command->info('No hay técnicos. Creando técnicos de prueba...');
            $this->crearTecnicosDePrueba();
        }

        $faker = Faker::create('es_MX');

        $clientes = Cliente::all();
        $tecnicos = Tecnico::all();

        $tiposServicio = ['Instalación', 'Mantenimiento', 'Reparación', 'Diagnóstico', 'Soporte remoto', 'Configuración'];
        $tiposEquipo = ['Laptop', 'PC de escritorio', 'Impresora', 'Servidor', 'Router', 'Monitor', 'Proyector'];
        $marcas = ['HP', 'Dell', 'Lenovo', 'Asus', 'Acer', 'Toshiba', 'Canon', 'Epson', 'Cisco', 'Apple', 'Samsung'];
        $modelos = ['XPS 13', 'ThinkPad T480', 'LaserJet Pro MFP', 'RT-AX88U', 'MacBook Air', 'Galaxy Book', 'PowerEdge T30'];
        $estados = ['pendiente', 'confirmada', 'en_progreso', 'completada', 'cancelada'];

        foreach (range(1, 20) as $index) {
            $tipoEquipo = $tiposEquipo[array_rand($tiposEquipo)];
            $marca = $marcas[array_rand($marcas)];
            $modelo = $modelos[array_rand($modelos)];

            Cita::create([
                'tecnico_id' => $tecnicos->random()->id,
                'cliente_id' => $clientes->random()->id,
                'tipo_servicio' => $faker->randomElement($tiposServicio),
                'fecha_hora' => $faker->dateTimeBetween('+1 day', '+30 days'),
                'descripcion' => $faker->sentence(10),
                'tipo_equipo' => $tipoEquipo,
                'marca_equipo' => $marca,
                'modelo_equipo' => $modelo,
                'problema_reportado' => $faker->randomElement([
                    'No enciende',
                    'Se apaga solo',
                    'Lentitud extrema',
                    'No conecta a internet',
                    'Pantalla azul',
                    'Ruido extraño',
                    'Sobrecalentamiento'
                ]),
                'estado' => $faker->randomElement($estados),
                'evidencias' => json_encode([
                    'notas' => $faker->optional()->sentence(),
                    'repuestos' => $faker->boolean(30) ? [
                        $faker->randomElement(['Fuente de poder', 'Memoria RAM', 'Disco SSD', 'Ventilador']),
                        $faker->randomElement(['Cable de alimentación', 'Batería', 'Placa base'])
                    ] : [],
                ], JSON_UNESCAPED_SLASHES),
                'foto_equipo' => $this->crearImagenTemporal($faker, 'equipo'),
                'foto_hoja_servicio' => $this->crearImagenTemporal($faker, 'hoja_servicio'),
                'foto_identificacion' => $this->crearImagenTemporal($faker, 'identificacion'),
            ]);
        }

        $this->command->info('20 citas creadas con éxito.');
    }

    /**
     * Crea técnicos de prueba si no existen.
     */
    private function crearTecnicosDePrueba(): void
    {
        $faker = Faker::create('es_MX');

        $especialidades = ['Hardware', 'Redes', 'Software', 'Soporte Técnico', 'Sistemas', 'Infraestructura'];

        foreach (range(1, 3) as $i) {
            Tecnico::create([
                'nombre' => $faker->name(),
                'email' => $faker->unique()->safeEmail(),
                'telefono' => $faker->numerify('55########'),
                'especialidad' => $faker->randomElement($especialidades),
                'activo' => true,
            ]);
        }

        $this->command->info('3 técnicos de prueba creados.');
    }

    /**
     * Genera una imagen simulada y devuelve la ruta.
     */
    private function crearImagenTemporal($faker, $prefix): string
    {
        $path = "citas/{$prefix}_" . time() . '_' . $faker->unique()->randomNumber() . '.jpg';
        $fullPath = storage_path('app/public/' . $path);

        // Crear imagen simple
        $image = imagecreate(200, 150);
        $bgColor = imagecolorallocate($image, rand(100, 200), rand(100, 200), rand(100, 200));
        $textColor = imagecolorallocate($image, 255, 255, 255);
        imagestring($image, 5, 10, 60, strtoupper($prefix), $textColor);
        imagestring($image, 3, 10, 90, date('Y-m-d'), $textColor);

        // Asegurarse de que el directorio exista
        if (!is_dir(dirname($fullPath))) {
            mkdir(dirname($fullPath), 0755, true);
        }

        imagejpeg($image, $fullPath);
        imagedestroy($image);

        return $path;
    }
}
