<?php

namespace Database\Seeders;

use App\Models\SatRegimenFiscal;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class SatRegimenFiscalSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $regimenes = [
            // Régimenes más comunes para personas físicas
            [
                'clave' => '605',
                'descripcion' => 'Sueldos y Salarios e Ingresos Asimilados a Salarios',
                'persona_fisica' => true,
                'persona_moral' => false,
            ],
            [
                'clave' => '606',
                'descripcion' => 'Arrendamiento',
                'persona_fisica' => true,
                'persona_moral' => true,
            ],
            [
                'clave' => '607',
                'descripcion' => 'Régimen de Enajenación o Adquisición de Bienes',
                'persona_fisica' => true,
                'persona_moral' => true,
            ],
            [
                'clave' => '608',
                'descripcion' => 'Demás ingresos',
                'persona_fisica' => true,
                'persona_moral' => true,
            ],
            [
                'clave' => '611',
                'descripcion' => 'Ingresos por Dividendos (socios y accionistas)',
                'persona_fisica' => true,
                'persona_moral' => true,
            ],
            [
                'clave' => '612',
                'descripcion' => 'Personas Físicas con Actividades Empresariales y Profesionales',
                'persona_fisica' => true,
                'persona_moral' => false,
            ],
            [
                'clave' => '614',
                'descripcion' => 'Ingresos por intereses',
                'persona_fisica' => true,
                'persona_moral' => true,
            ],
            [
                'clave' => '615',
                'descripcion' => 'Régimen de los ingresos por obtención de premios',
                'persona_fisica' => true,
                'persona_moral' => true,
            ],
            [
                'clave' => '616',
                'descripcion' => 'Sin obligaciones fiscales',
                'persona_fisica' => true,
                'persona_moral' => true,
            ],

            // Régimenes para personas morales
            [
                'clave' => '601',
                'descripcion' => 'General de Ley Personas Morales',
                'persona_fisica' => false,
                'persona_moral' => true,
            ],
            [
                'clave' => '603',
                'descripcion' => 'Personas Morales con Fines no Lucrativos',
                'persona_fisica' => false,
                'persona_moral' => true,
            ],
            [
                'clave' => '604',
                'descripcion' => 'Sociedades Cooperativas de Producción que optan por diferir sus ingresos',
                'persona_fisica' => false,
                'persona_moral' => true,
            ],
            [
                'clave' => '609',
                'descripcion' => 'Consolidación',
                'persona_fisica' => false,
                'persona_moral' => true,
            ],
            [
                'clave' => '610',
                'descripcion' => 'Residentes en el Extranjero sin Establecimiento Permanente en México',
                'persona_fisica' => false,
                'persona_moral' => true,
            ],
            [
                'clave' => '611',
                'descripcion' => 'Ingresos por Dividendos (socios y accionistas)',
                'persona_fisica' => true,
                'persona_moral' => true,
            ],
            [
                'clave' => '612',
                'descripcion' => 'Personas Físicas con Actividades Empresariales y Profesionales',
                'persona_fisica' => true,
                'persona_moral' => false,
            ],
            [
                'clave' => '620',
                'descripcion' => 'Sociedades Cooperativas de Producción',
                'persona_fisica' => false,
                'persona_moral' => true,
            ],
            [
                'clave' => '621',
                'descripcion' => 'Incorporación Fiscal',
                'persona_fisica' => false,
                'persona_moral' => true,
            ],
            [
                'clave' => '622',
                'descripcion' => 'Actividades Agrícolas, Ganaderas, Silvícolas y Pesqueras',
                'persona_fisica' => false,
                'persona_moral' => true,
            ],
            [
                'clave' => '623',
                'descripcion' => 'Opcional para Grupos de Sociedades',
                'persona_fisica' => false,
                'persona_moral' => true,
            ],
            [
                'clave' => '624',
                'descripcion' => 'Coordinados',
                'persona_fisica' => false,
                'persona_moral' => true,
            ],
            [
                'clave' => '625',
                'descripcion' => 'Régimen de las Actividades Empresariales con ingresos a través de Plataformas Tecnológicas',
                'persona_fisica' => true,
                'persona_moral' => true,
            ],
            [
                'clave' => '626',
                'descripcion' => 'Régimen Simplificado de Confianza',
                'persona_fisica' => true,
                'persona_moral' => true,
            ],
        ];

        foreach ($regimenes as $regimen) {
            SatRegimenFiscal::updateOrCreate(
                ['clave' => $regimen['clave']],
                $regimen
            );
        }
    }
}
