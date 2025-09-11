<?php

namespace Database\Seeders;

use App\Models\Cliente;
use Illuminate\Database\Seeder;
use Faker\Factory as Faker;

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

        // 2. Clientes de prueba (solo en desarrollo)
        if (app()->environment('local', 'testing')) {
            $faker = Faker::create('es_MX');

            $regimenesFiscales = ['601', '603', '605', '606', '608', '610', '615', '616', '620'];
            $usosCFDI = ['G01', 'G02', 'G03', 'I01', 'I02', 'I03', 'I04', 'I05', 'I06', 'I07', 'I08'];

            // ⬇️ Ahora usamos CLAVES de estados, no nombres
            $clavesEstados = [
                'AGU',
                'BCN',
                'BCS',
                'CAM',
                'CHP',
                'CHH',
                'DIF',
                'DUR',
                'GUA',
                'GRO',
                'HID',
                'JAL',
                'MEX',
                'MIC',
                'MOR',
                'NAY',
                'NLE',
                'OAX',
                'PUE',
                'QUE',
                'ROO',
                'SIN',
                'SLP',
                'SON',
                'TAB',
                'TAM',
                'TLA',
                'VER',
                'YUC',
                'ZAC'
            ];

            for ($i = 0; $i < 15; $i++) {
                $tipoPersona = $faker->randomElement(['fisica', 'moral']); // ¡minúsculas!

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
                    'colonia' => $faker->citySuffix(),
                    'codigo_postal' => $faker->postcode(),
                    'municipio' => $faker->city(),
                    'estado' => $faker->randomElement($clavesEstados), // ⬅️ ¡Clave!
                    'pais' => 'MX', // ⬅️ ¡Importante!
                    'activo' => $faker->boolean(90)
                ]);
            }
        }
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
