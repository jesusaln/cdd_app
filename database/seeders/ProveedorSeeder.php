<?php

namespace Database\Seeders;

use App\Models\Proveedor;
use Illuminate\Database\Seeder;
use Faker\Factory as Faker;

class ProveedorSeeder extends Seeder
{
    public function run()
    {
        // 1. Proveedor genérico (para compras sin proveedor específico)
        Proveedor::firstOrCreate(
            ['rfc' => 'XAXX010101000'],
            [
                'nombre_razon_social' => 'PROVEEDOR GENÉRICO',
                'tipo_persona' => 'Moral',
                'regimen_fiscal' => '616',
                'uso_cfdi' => 'G03',
                'email' => 'proveedores@empresa.com',
                'telefono' => '5555555555',
                'calle' => 'SIN ESPECIFICAR',
                'numero_exterior' => '0',
                'colonia' => 'CENTRO',
                'codigo_postal' => '00000',
                'municipio' => 'CIUDAD DE MÉXICO',
                'estado' => 'CIUDAD DE MÉXICO',
                'pais' => 'MÉXICO',
                'activo' => true
            ]
        );

        // 2. Proveedores de prueba (solo en desarrollo)
        if (app()->environment('local', 'testing')) {
            $faker = Faker::create('es_MX');

            $regimenesFiscales = [
                '601' => 'General de Ley Personas Morales',
                '603' => 'Personas Morales con Fines no Lucrativos',
                '605' => 'Sueldos y Salarios',
                '606' => 'Arrendamiento',
                '608' => 'Demás ingresos',
                '610' => 'Residentes en el Extranjero sin Establecimiento Permanente en México',
                '615' => 'Régimen de los ingresos por obtención de premios',
                '616' => 'Sin obligaciones fiscales',
                '620' => 'Sociedades Cooperativas de Producción'
            ];

            $usosCFDI = ['G01', 'G02', 'G03', 'I01', 'I02', 'I03', 'D01', 'D02', 'D03', 'P01'];

            $estadosMexicanos = [
                'AGUASCALIENTES',
                'BAJA CALIFORNIA',
                'BAJA CALIFORNIA SUR',
                'CAMPECHE',
                'CHIAPAS',
                'CHIHUAHUA',
                'CIUDAD DE MÉXICO',
                'COAHUILA',
                'COLIMA',
                'DURANGO',
                'ESTADO DE MÉXICO',
                'GUANAJUATO',
                'GUERRERO',
                'HIDALGO',
                'JALISCO',
                'MICHOACÁN',
                'MORELOS',
                'NAYARIT',
                'NUEVO LEÓN',
                'OAXACA',
                'PUEBLA',
                'QUERÉTARO',
                'QUINTANA ROO',
                'SAN LUIS POTOSÍ',
                'SINALOA',
                'SONORA',
                'TABASCO',
                'TAMAULIPAS',
                'TLAXCALA',
                'VERACRUZ',
                'YUCATÁN',
                'ZACATECAS'
            ];

            for ($i = 0; $i < 10; $i++) {
                $tipoPersona = $faker->randomElement(['Fisica', 'Moral']);
                $regimen = $faker->randomElement(array_keys($regimenesFiscales));

                Proveedor::create([
                    'nombre_razon_social' => $tipoPersona === 'Fisica' ?
                        $faker->name() :
                        $faker->company(),
                    'tipo_persona' => $tipoPersona,
                    'rfc' => $this->generarRFC($tipoPersona),
                    'regimen_fiscal' => $regimen,
                    'uso_cfdi' => $faker->randomElement($usosCFDI),
                    'email' => $faker->companyEmail(),
                    'telefono' => $faker->numerify('55########'),
                    'calle' => $faker->streetName(),
                    'numero_exterior' => $faker->buildingNumber(),
                    'numero_interior' => $faker->optional(0.3)->buildingNumber(),
                    'colonia' => $faker->citySuffix(),
                    'codigo_postal' => $faker->postcode(),
                    'municipio' => $faker->city(),
                    'estado' => $faker->randomElement($estadosMexicanos),
                    'pais' => 'MÉXICO',
                    'activo' => true
                ]);
            }
        }
    }

    /**
     * Genera un RFC válido para pruebas
     */
    private function generarRFC(string $tipoPersona): string
    {
        $faker = \Faker\Factory::create('es_MX');

        if ($tipoPersona === 'Fisica') {
            return $faker->unique()->regexify('[A-Z]{4}[0-9]{6}[A-Z0-9]{3}');
        }

        // Para persona moral
        return $faker->unique()->regexify('[A-Z]{3}[0-9]{6}[A-Z0-9]{3}');
    }
}
