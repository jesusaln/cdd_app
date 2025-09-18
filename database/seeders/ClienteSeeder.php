<?php

namespace Database\Seeders;

use App\Models\Cliente;
use Illuminate\Database\Seeder;
use Faker\Factory as Faker;
use Illuminate\Support\Facades\DB;
use Eclipxe\SepomexPhp\SepomexPhp;

class ClienteSeeder extends Seeder
{
    public function run()
    {
        // 1. Cliente genérico (para ventas sin cliente específico)
        Cliente::firstOrCreate(
            ['rfc' => 'XAXX010101000'],
            [
                'nombre_razon_social' => 'PUBLICO EN GENERAL',
                'tipo_persona' => 'fisica', // ¡minúsculas para coincidir con enum!
                'rfc' => 'XAXX010101000',
                'regimen_fiscal' => '616',
                'uso_cfdi' => 'G03',
                'email' => 'ventas@empresa.com',
                'telefono' => '0000000000',
                'calle' => 'CONSUMIDOR FINAL',
                'numero_exterior' => '0',
                'colonia' => 'CENTRO',
                'codigo_postal' => '00000',
                'municipio' => 'NO ESPECIFICADO',
                'estado' => 'DIF', // ⬅️ ¡Clave SAT, no nombre!
                'pais' => 'MX',    // ⬅️ ¡Debe coincidir con tu migración (char 2, default 'MX')!
                'activo' => true
            ]
        );

        // 2. Clientes de prueba con datos reales de SEPOMEX (solo en desarrollo)
        if (app()->environment('local', 'testing')) {
            $faker = Faker::create('es_MX');

            $regimenesFiscales = ['601', '603', '605', '606', '608', '610', '615', '616', '620'];
            $usosCFDI = ['G01', 'G02', 'G03', 'I01', 'I02', 'I03', 'I04', 'I05', 'I06', 'I07', 'I08'];

            // Obtener códigos postales reales de SEPOMEX
            $codigosPostalesReales = $this->obtenerCodigosPostalesReales();

            for ($i = 0; $i < min(15, count($codigosPostalesReales)); $i++) {
                $tipoPersona = $faker->randomElement(['fisica', 'moral']); // ¡minúsculas!

                // Usar datos reales de SEPOMEX
                $datosCP = $codigosPostalesReales[$i];
                $coloniaAleatoria = !empty($datosCP['colonias']) ?
                    $faker->randomElement($datosCP['colonias']) :
                    'CENTRO';

                Cliente::create([
                    'nombre_razon_social' => $tipoPersona === 'fisica' ?
                        $faker->name() :
                        $faker->company(),
                    'tipo_persona' => $tipoPersona,
                    'rfc' => $this->generarRFC($tipoPersona),
                    'regimen_fiscal' => $faker->randomElement($regimenesFiscales),
                    'uso_cfdi' => $faker->randomElement($usosCFDI),
                    'email' => $faker->unique()->safeEmail(),
                    'telefono' => $faker->numerify('55#######'),
                    'calle' => $faker->streetName(),
                    'numero_exterior' => $faker->buildingNumber(),
                    'numero_interior' => $faker->optional(0.3)->buildingNumber(),
                    'colonia' => $coloniaAleatoria,
                    'codigo_postal' => $datosCP['codigo_postal'],
                    'municipio' => $datosCP['municipio'],
                    'estado' => $datosCP['estado'], // ⬅️ ¡Nombre completo del estado!
                    'pais' => 'MX',
                    'activo' => $faker->boolean(90)
                ]);
            }
        }
    }

    private function obtenerCodigosPostalesReales(): array
    {
        try {
            // Códigos postales de ejemplo de diferentes estados
            $codigosEjemplo = [
                '01000', // Ciudad de México
                '83117', // Hermosillo, Sonora
                '45050', // Zapopan, Jalisco
                '22000', // Tijuana, Baja California
                '64000', // Monterrey, Nuevo León
                '44100', // Guadalajara, Jalisco
                '20000', // Aguascalientes, Aguascalientes
                '31000', // Chihuahua, Chihuahua
                '29000', // Tuxtla Gutiérrez, Chiapas
                '97000', // Mérida, Yucatán
                '80000', // Culiacán, Sinaloa
                '86000', // Villahermosa, Tabasco
                '24000', // Campeche, Campeche
                '85000', // Ciudad Obregón, Sonora
                '27000', // Torreón, Coahuila
            ];

            $resultados = [];

            foreach ($codigosEjemplo as $cp) {
                try {
                    $sepomex = SepomexPhp::createForDatabaseFile(storage_path('sepomex.sqlite'));
                    $zipCodeData = $sepomex->getZipCodeData($cp);

                    if ($zipCodeData) {
                        $colonias = [];
                        foreach ($zipCodeData->locations as $location) {
                            $colonias[] = $location->name;
                        }

                        $resultados[] = [
                            'codigo_postal' => $cp,
                            'estado' => $zipCodeData->state->name,
                            'municipio' => $zipCodeData->district->name,
                            'colonias' => $colonias
                        ];
                    }
                } catch (\Exception $e) {
                    // Si hay error con un CP específico, continuar con el siguiente
                    continue;
                }
            }

            // Si no se pudieron obtener datos reales, devolver datos de respaldo
            if (empty($resultados)) {
                return $this->datosRespaldo();
            }

            return $resultados;

        } catch (\Exception $e) {
            // En caso de error, usar datos de respaldo
            return $this->datosRespaldo();
        }
    }

    private function datosRespaldo(): array
    {
        return [
            [
                'codigo_postal' => '01000',
                'estado' => 'Ciudad de México',
                'municipio' => 'Álvaro Obregón',
                'colonias' => ['San Ángel', 'Centro']
            ],
            [
                'codigo_postal' => '83117',
                'estado' => 'Sonora',
                'municipio' => 'Hermosillo',
                'colonias' => ['Centro', 'Bicentenario Residencial']
            ],
            [
                'codigo_postal' => '45050',
                'estado' => 'Jalisco',
                'municipio' => 'Zapopan',
                'colonias' => ['Centro', 'Ciudad Del Sol']
            ]
        ];
    }

    private function generarRFC(string $tipoPersona): string
    {
        $faker = \Faker\Factory::create('es_MX');

        if ($tipoPersona === 'fisica') {
            return $faker->unique()->regexify('[A-Z]{4}[0-9]{6}[A-Z0-9]{3}');
        }

        return $faker->unique()->regexify('[A-Z]{3}[0-9]{6}[A-Z0-9]{3}');
    }
}
