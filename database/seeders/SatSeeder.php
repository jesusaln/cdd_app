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

        // Usos CFDI (clave, descripcion, regimen_fiscal_receptor, activo)
        SatUsoCfdi::create(['clave' => 'G03', 'descripcion' => 'Gastos en general', 'regimen_fiscal_receptor' => '601,603,605,606,607,608,609,610,611,612,614,615,616,620,621,622,623,624,625,626', 'activo' => true]);

        // Estados (clave, nombre)
        SatEstado::create(['clave' => 'SON', 'nombre' => 'Sonora']);
        SatEstado::create(['clave' => 'CDMX', 'nombre' => 'Ciudad de México']);
        SatEstado::create(['clave' => 'JAL', 'nombre' => 'Jalisco']);
        // Agrega todos los 32 estados SAT si necesitas completos
    }
}
