<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ComponenteKitSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $componentes = [
            // Computadoras
            [
                'tipo' => 'computadora',
                'nombre' => 'Computadora POS Dell Optiplex',
                'codigo' => 'COMP-001',
                'numero_serie' => 'DELL123456',
                'marca' => 'Dell',
                'modelo' => 'Optiplex 3080',
                'precio_renta_mensual' => 1500.00,
                'estado' => 'disponible',
                'condicion' => 'excelente',
            ],
            [
                'tipo' => 'computadora',
                'nombre' => 'Computadora POS HP ProDesk',
                'codigo' => 'COMP-002',
                'numero_serie' => 'HP789012',
                'marca' => 'HP',
                'modelo' => 'ProDesk 400',
                'precio_renta_mensual' => 1400.00,
                'estado' => 'disponible',
                'condicion' => 'bueno',
            ],

            // Básculas
            [
                'tipo' => 'bascula',
                'nombre' => 'Báscula Digital CAS PD-II',
                'codigo' => 'BASC-001',
                'numero_serie' => 'CAS001234',
                'marca' => 'CAS',
                'modelo' => 'PD-II',
                'precio_renta_mensual' => 800.00,
                'estado' => 'disponible',
                'condicion' => 'excelente',
            ],
            [
                'tipo' => 'bascula',
                'nombre' => 'Báscula Digital Toledo 8217',
                'codigo' => 'BASC-002',
                'numero_serie' => 'TOLEDO567890',
                'marca' => 'Toledo',
                'modelo' => '8217',
                'precio_renta_mensual' => 750.00,
                'estado' => 'disponible',
                'condicion' => 'bueno',
            ],

            // Lectores de código de barras
            [
                'tipo' => 'lector_codigo_barras',
                'nombre' => 'Lector Código Barras Honeywell 1470',
                'codigo' => 'LECT-001',
                'numero_serie' => 'HONEYWELL001',
                'marca' => 'Honeywell',
                'modelo' => '1470',
                'precio_renta_mensual' => 400.00,
                'estado' => 'disponible',
                'condicion' => 'excelente',
            ],
            [
                'tipo' => 'lector_codigo_barras',
                'nombre' => 'Lector Código Barras Zebra DS2208',
                'codigo' => 'LECT-002',
                'numero_serie' => 'ZEBRA002',
                'marca' => 'Zebra',
                'modelo' => 'DS2208',
                'precio_renta_mensual' => 450.00,
                'estado' => 'disponible',
                'condicion' => 'bueno',
            ],

            // Cajones de dinero
            [
                'tipo' => 'cajon_dinero',
                'nombre' => 'Cajón Dinero Epson TM-T88V',
                'codigo' => 'CAJON-001',
                'numero_serie' => 'EPSON001',
                'marca' => 'Epson',
                'modelo' => 'TM-T88V',
                'precio_renta_mensual' => 300.00,
                'estado' => 'disponible',
                'condicion' => 'excelente',
            ],
            [
                'tipo' => 'cajon_dinero',
                'nombre' => 'Cajón Dinero Star Micronics',
                'codigo' => 'CAJON-002',
                'numero_serie' => 'STAR002',
                'marca' => 'Star Micronics',
                'modelo' => 'CD-815',
                'precio_renta_mensual' => 350.00,
                'estado' => 'disponible',
                'condicion' => 'bueno',
            ],

            // Sistemas
            [
                'tipo' => 'sistema',
                'nombre' => 'Sistema POS Completo - Licencia Anual',
                'codigo' => 'SIS-001',
                'numero_serie' => 'POSSOFT001',
                'marca' => 'POS Software',
                'modelo' => 'Professional v2.1',
                'precio_renta_mensual' => 2000.00,
                'estado' => 'disponible',
                'condicion' => 'nuevo',
            ],

            // Impresoras de ticket
            [
                'tipo' => 'impresora_ticket',
                'nombre' => 'Impresora Ticket Epson TM-T20',
                'codigo' => 'IMP-001',
                'numero_serie' => 'EPSONTM001',
                'marca' => 'Epson',
                'modelo' => 'TM-T20',
                'precio_renta_mensual' => 250.00,
                'estado' => 'disponible',
                'condicion' => 'excelente',
            ],
            [
                'tipo' => 'impresora_ticket',
                'nombre' => 'Impresora Ticket Star Micronics TSP143',
                'codigo' => 'IMP-002',
                'numero_serie' => 'STARTSP002',
                'marca' => 'Star Micronics',
                'modelo' => 'TSP143',
                'precio_renta_mensual' => 280.00,
                'estado' => 'disponible',
                'condicion' => 'bueno',
            ],
        ];

        foreach ($componentes as $componente) {
            \App\Models\ComponenteKit::create($componente);
        }
    }
}
