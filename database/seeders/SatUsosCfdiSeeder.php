<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SatUsosCfdiSeeder extends Seeder
{
    public function run(): void
    {
        $rows = [
            ['clave' => 'G01', 'descripcion' => 'Adquisición de mercancías', 'regimen_fiscal_receptor' => '601,603,605,606,607,608,609,610,611,612,614,615,616,620,621,622,623,624,625,626', 'activo' => true],
            ['clave' => 'G02', 'descripcion' => 'Devoluciones, descuentos o bonificaciones', 'regimen_fiscal_receptor' => '601,603,605,606,607,608,609,610,611,612,614,615,616,620,621,622,623,624,625,626', 'activo' => true],
            ['clave' => 'G03', 'descripcion' => 'Gastos en general', 'regimen_fiscal_receptor' => '601,603,605,606,607,608,609,610,611,612,614,615,616,620,621,622,623,624,625,626', 'activo' => true],
            ['clave' => 'I01', 'descripcion' => 'Construcciones', 'regimen_fiscal_receptor' => '601,603,605,606,607,608,609,610,611,612,614,615,616,620,621,622,623,624,625,626', 'activo' => true],
            ['clave' => 'I02', 'descripcion' => 'Mobiliario y equipo de oficina por inversiones', 'regimen_fiscal_receptor' => '601,603,605,606,607,608,609,610,611,612,614,615,616,620,621,622,623,624,625,626', 'activo' => true],
            ['clave' => 'I03', 'descripcion' => 'Equipo de transporte', 'regimen_fiscal_receptor' => '601,603,605,606,607,608,609,610,611,612,614,615,616,620,621,622,623,624,625,626', 'activo' => true],
            ['clave' => 'I04', 'descripcion' => 'Equipo de cómputo y accesorios', 'regimen_fiscal_receptor' => '601,603,605,606,607,608,609,610,611,612,614,615,616,620,621,622,623,624,625,626', 'activo' => true],
            ['clave' => 'I05', 'descripcion' => 'Dados, troqueles, moldes, matrices y herramental', 'regimen_fiscal_receptor' => '601,603,605,606,607,608,609,610,611,612,614,615,616,620,621,622,623,624,625,626', 'activo' => true],
            ['clave' => 'I06', 'descripcion' => 'Comunicaciones telefónicas', 'regimen_fiscal_receptor' => '601,603,605,606,607,608,609,610,611,612,614,615,616,620,621,622,623,624,625,626', 'activo' => true],
            ['clave' => 'I07', 'descripcion' => 'Comunicaciones satelitales', 'regimen_fiscal_receptor' => '601,603,605,606,607,608,609,610,611,612,614,615,616,620,621,622,623,624,625,626', 'activo' => true],
            ['clave' => 'I08', 'descripcion' => 'Otra maquinaria y equipo', 'regimen_fiscal_receptor' => '601,603,605,606,607,608,609,610,611,612,614,615,616,620,621,622,623,624,625,626', 'activo' => true],
            ['clave' => 'D01', 'descripcion' => 'Honorarios médicos, dentales y gastos hospitalarios', 'regimen_fiscal_receptor' => '601,603,605,606,607,608,609,610,611,612,614,615,616,620,621,622,623,624,625,626', 'activo' => true],
            ['clave' => 'D02', 'descripcion' => 'Gastos médicos por incapacidad o discapacidad', 'regimen_fiscal_receptor' => '601,603,605,606,607,608,609,610,611,612,614,615,616,620,621,622,623,624,625,626', 'activo' => true],
            ['clave' => 'D03', 'descripcion' => 'Gastos funerales', 'regimen_fiscal_receptor' => '601,603,605,606,607,608,609,610,611,612,614,615,616,620,621,622,623,624,625,626', 'activo' => true],
            ['clave' => 'D04', 'descripcion' => 'Donativos', 'regimen_fiscal_receptor' => '601,603,605,606,607,608,609,610,611,612,614,615,616,620,621,622,623,624,625,626', 'activo' => true],
            ['clave' => 'D05', 'descripcion' => 'Intereses reales efectivamente pagados por créditos hipotecarios', 'regimen_fiscal_receptor' => '601,603,605,606,607,608,609,610,611,612,614,615,616,620,621,622,623,624,625,626', 'activo' => true],
            ['clave' => 'D06', 'descripcion' => 'Aportaciones voluntarias al SAR', 'regimen_fiscal_receptor' => '601,603,605,606,607,608,609,610,611,612,614,615,616,620,621,622,623,624,625,626', 'activo' => true],
            ['clave' => 'D07', 'descripcion' => 'Primas por seguros de gastos médicos', 'regimen_fiscal_receptor' => '601,603,605,606,607,608,609,610,611,612,614,615,616,620,621,622,623,624,625,626', 'activo' => true],
            ['clave' => 'D08', 'descripcion' => 'Gastos de transportación escolar obligatoria', 'regimen_fiscal_receptor' => '601,603,605,606,607,608,609,610,611,612,614,615,616,620,621,622,623,624,625,626', 'activo' => true],
            ['clave' => 'D09', 'descripcion' => 'Depósitos en cuentas para el ahorro, primas que tengan como base planes de pensiones', 'regimen_fiscal_receptor' => '601,603,605,606,607,608,609,610,611,612,614,615,616,620,621,622,623,624,625,626', 'activo' => true],
            ['clave' => 'D10', 'descripcion' => 'Pagos por servicios educativos (colegiaturas)', 'regimen_fiscal_receptor' => '601,603,605,606,607,608,609,610,611,612,614,615,616,620,621,622,623,624,625,626', 'activo' => true],
            ['clave' => 'S01', 'descripcion' => 'Sin efectos fiscales', 'regimen_fiscal_receptor' => '601,603,605,606,607,608,609,610,611,612,614,615,616,620,621,622,623,624,625,626', 'activo' => true],
        ];

        DB::table('sat_usos_cfdi')->upsert($rows, ['clave'], ['descripcion', 'regimen_fiscal_receptor', 'activo']);
    }
}
