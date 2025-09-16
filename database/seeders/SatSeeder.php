<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\SatRegimenFiscal;
use App\Models\SatUsoCfdi;
use App\Models\SatEstado;

class SatSeeder extends Seeder
{
    public function run(): void
    {
        // Regímenes fiscales (clave, descripcion, persona_fisica, persona_moral)
        SatRegimenFiscal::create(['clave' => '601', 'descripcion' => 'Incorporación Fiscal', 'persona_fisica' => true, 'persona_moral' => false]);
        SatRegimenFiscal::create(['clave' => '612', 'descripcion' => 'Personas Físicas con Actividades Empresariales y Profesionales', 'persona_fisica' => true, 'persona_moral' => false]);
        SatRegimenFiscal::create(['clave' => '626', 'descripcion' => 'Régimen Simplificado de Confianza (RESICO)', 'persona_fisica' => true, 'persona_moral' => false]);

        // Usos CFDI (clave, descripcion, persona_fisica, persona_moral)
        SatUsoCfdi::create(['clave' => 'G03', 'descripcion' => 'Gastos en general', 'persona_fisica' => true, 'persona_moral' => true]);

        // Estados (clave, nombre)
        SatEstado::create(['clave' => 'SON', 'nombre' => 'Sonora']);
        SatEstado::create(['clave' => 'CDMX', 'nombre' => 'Ciudad de México']);
        SatEstado::create(['clave' => 'JAL', 'nombre' => 'Jalisco']);
        // Agrega todos los 32 estados SAT si necesitas completos
    }
}
