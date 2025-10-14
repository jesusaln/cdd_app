<?php

namespace Database\Seeders;

use App\Models\SatUsoCfdi;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class SatUsoCfdiSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $usos = [
            [
                'clave' => 'G01',
                'descripcion' => 'Adquisición de mercancías',
                'regimen_fiscal_receptor' => '601,603,605,606,607,608,610,611,612,614,615,616,620,621,622,623,624,625,626',
            ],
            [
                'clave' => 'G02',
                'descripcion' => 'Devoluciones, descuentos o bonificaciones',
                'regimen_fiscal_receptor' => '601,603,605,606,607,608,610,611,612,614,615,616,620,621,622,623,624,625,626',
            ],
            [
                'clave' => 'G03',
                'descripcion' => 'Gastos en general',
                'regimen_fiscal_receptor' => '601,603,605,606,607,608,610,611,612,614,615,616,620,621,622,623,624,625,626',
            ],
            [
                'clave' => 'I01',
                'descripcion' => 'Construcciones',
                'regimen_fiscal_receptor' => '601,603,606,607,608,610,611,612,614,615,616,620,621,622,623,624,625,626',
            ],
            [
                'clave' => 'I02',
                'descripcion' => 'Mobiliario y equipo de oficina por inversiones',
                'regimen_fiscal_receptor' => '601,603,606,607,608,610,611,612,614,615,616,620,621,622,623,624,625,626',
            ],
            [
                'clave' => 'I03',
                'descripcion' => 'Equipo de transporte',
                'regimen_fiscal_receptor' => '601,603,606,607,608,610,611,612,614,615,616,620,621,622,623,624,625,626',
            ],
            [
                'clave' => 'I04',
                'descripcion' => 'Equipo de computo y accesorios',
                'regimen_fiscal_receptor' => '601,603,606,607,608,610,611,612,614,615,616,620,621,622,623,624,625,626',
            ],
            [
                'clave' => 'I05',
                'descripcion' => 'Dados, troqueles, moldes, matrices y herramental',
                'regimen_fiscal_receptor' => '601,603,606,607,608,610,611,612,614,615,616,620,621,622,623,624,625,626',
            ],
            [
                'clave' => 'I06',
                'descripcion' => 'Comunicaciones telefónicas',
                'regimen_fiscal_receptor' => '601,603,606,607,608,610,611,612,614,615,616,620,621,622,623,624,625,626',
            ],
            [
                'clave' => 'I07',
                'descripcion' => 'Comunicaciones satelitales',
                'regimen_fiscal_receptor' => '601,603,606,607,608,610,611,612,614,615,616,620,621,622,623,624,625,626',
            ],
            [
                'clave' => 'I08',
                'descripcion' => 'Otra maquinaria y equipo',
                'regimen_fiscal_receptor' => '601,603,606,607,608,610,611,612,614,615,616,620,621,622,623,624,625,626',
            ],
            [
                'clave' => 'D01',
                'descripcion' => 'Honorarios médicos, dentales y gastos hospitalarios',
                'regimen_fiscal_receptor' => '605,606,607,608,611,612,614,615,616,625,626',
            ],
            [
                'clave' => 'D02',
                'descripcion' => 'Gastos médicos por incapacidad o discapacidad',
                'regimen_fiscal_receptor' => '605,606,607,608,611,612,614,615,616,625,626',
            ],
            [
                'clave' => 'D03',
                'descripcion' => 'Gastos funerales',
                'regimen_fiscal_receptor' => '605,606,607,608,611,612,614,615,616,625,626',
            ],
            [
                'clave' => 'D04',
                'descripcion' => 'Donativos',
                'regimen_fiscal_receptor' => '601,603,605,606,607,608,610,611,612,614,615,616,620,621,622,623,624,625,626',
            ],
            [
                'clave' => 'D05',
                'descripcion' => 'Intereses reales efectivamente pagados por créditos hipotecarios (casa habitación)',
                'regimen_fiscal_receptor' => '605,606,607,608,611,612,614,615,616,625,626',
            ],
            [
                'clave' => 'D06',
                'descripcion' => 'Aportaciones voluntarias al SAR',
                'regimen_fiscal_receptor' => '605,606,607,608,611,612,614,615,616,625,626',
            ],
            [
                'clave' => 'D07',
                'descripcion' => 'Primas por seguros de gastos médicos',
                'regimen_fiscal_receptor' => '605,606,607,608,611,612,614,615,616,625,626',
            ],
            [
                'clave' => 'D08',
                'descripcion' => 'Gastos de transportación escolar obligatoria',
                'regimen_fiscal_receptor' => '605,606,607,608,611,612,614,615,616,625,626',
            ],
            [
                'clave' => 'D09',
                'descripcion' => 'Depósitos en cuentas para el ahorro, primas que tengan como base planes de pensiones',
                'regimen_fiscal_receptor' => '605,606,607,608,611,612,614,615,616,625,626',
            ],
            [
                'clave' => 'D10',
                'descripcion' => 'Pagos por servicios educativos (colegiaturas)',
                'regimen_fiscal_receptor' => '605,606,607,608,611,612,614,615,616,625,626',
            ],
            [
                'clave' => 'P01',
                'descripcion' => 'Por definir',
                'regimen_fiscal_receptor' => '601,603,605,606,607,608,610,611,612,614,615,616,620,621,622,623,624,625,626',
            ],
            [
                'clave' => 'S01',
                'descripcion' => 'Sin efectos fiscales',
                'regimen_fiscal_receptor' => '616',
            ],
            [
                'clave' => 'CP01',
                'descripcion' => 'Pagos',
                'regimen_fiscal_receptor' => '601,603,605,606,607,608,610,611,612,614,615,616,620,621,622,623,624,625,626',
            ],
            [
                'clave' => 'CN01',
                'descripcion' => 'Nómina',
                'regimen_fiscal_receptor' => '605,606,607,608,611,612,614,615,616,625,626',
            ],
        ];

        foreach ($usos as $uso) {
            SatUsoCfdi::updateOrCreate(
                ['clave' => $uso['clave']],
                $uso
            );
        }
    }
}
