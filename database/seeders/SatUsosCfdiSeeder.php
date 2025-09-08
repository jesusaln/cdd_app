<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SatUsosCfdiSeeder extends Seeder
{
    public function run(): void
    {
        $rows = [
            ['clave' => 'G01', 'descripcion' => 'Adquisición de mercancías', 'persona_fisica' => true, 'persona_moral' => true],
            ['clave' => 'G02', 'descripcion' => 'Devoluciones, descuentos o bonificaciones', 'persona_fisica' => true, 'persona_moral' => true],
            ['clave' => 'G03', 'descripcion' => 'Gastos en general', 'persona_fisica' => true, 'persona_moral' => true],
            ['clave' => 'I01', 'descripcion' => 'Construcciones', 'persona_fisica' => true, 'persona_moral' => true],
            ['clave' => 'I02', 'descripcion' => 'Mobiliario y equipo de oficina por inversiones', 'persona_fisica' => true, 'persona_moral' => true],
            ['clave' => 'I03', 'descripcion' => 'Equipo de transporte', 'persona_fisica' => true, 'persona_moral' => true],
            ['clave' => 'I04', 'descripcion' => 'Equipo de cómputo y accesorios', 'persona_fisica' => true, 'persona_moral' => true],
            ['clave' => 'I05', 'descripcion' => 'Dados, troqueles, moldes, matrices y herramental', 'persona_fisica' => true, 'persona_moral' => true],
            ['clave' => 'I06', 'descripcion' => 'Comunicaciones telefónicas', 'persona_fisica' => true, 'persona_moral' => true],
            ['clave' => 'I07', 'descripcion' => 'Comunicaciones satelitales', 'persona_fisica' => true, 'persona_moral' => true],
            ['clave' => 'I08', 'descripcion' => 'Otra maquinaria y equipo', 'persona_fisica' => true, 'persona_moral' => true],
            ['clave' => 'D01', 'descripcion' => 'Honorarios médicos, dentales y gastos hospitalarios', 'persona_fisica' => true, 'persona_moral' => false],
            ['clave' => 'D02', 'descripcion' => 'Gastos médicos por incapacidad o discapacidad', 'persona_fisica' => true, 'persona_moral' => false],
            ['clave' => 'D03', 'descripcion' => 'Gastos funerales', 'persona_fisica' => true, 'persona_moral' => false],
            ['clave' => 'D04', 'descripcion' => 'Donativos', 'persona_fisica' => true, 'persona_moral' => true],
            ['clave' => 'D05', 'descripcion' => 'Intereses reales efectivamente pagados por créditos hipotecarios', 'persona_fisica' => true, 'persona_moral' => false],
            ['clave' => 'D06', 'descripcion' => 'Aportaciones voluntarias al SAR', 'persona_fisica' => true, 'persona_moral' => false],
            ['clave' => 'D07', 'descripcion' => 'Primas por seguros de gastos médicos', 'persona_fisica' => true, 'persona_moral' => false],
            ['clave' => 'D08', 'descripcion' => 'Gastos de transportación escolar obligatoria', 'persona_fisica' => true, 'persona_moral' => false],
            ['clave' => 'D09', 'descripcion' => 'Depósitos en cuentas para el ahorro, primas que tengan como base planes de pensiones', 'persona_fisica' => true, 'persona_moral' => false],
            ['clave' => 'D10', 'descripcion' => 'Pagos por servicios educativos (colegiaturas)', 'persona_fisica' => true, 'persona_moral' => false],
            ['clave' => 'S01', 'descripcion' => 'Sin efectos fiscales', 'persona_fisica' => true, 'persona_moral' => true],
        ];

        DB::table('sat_usos_cfdi')->upsert($rows, ['clave']);
    }
}
