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
                'numero_interior' => null,
                'colonia' => 'CENTRO',
                'codigo_postal' => '00000',
                'municipio' => 'CIUDAD DE MÉXICO',
                'estado' => 'CIUDAD DE MÉXICO',
                'pais' => 'MÉXICO',
                'activo' => true
            ]
        );

        // 2. Proveedores de prueba con emails reales para testing
        $proveedoresPrueba = [
            [
                'rfc' => 'OISP890101HA1',
                'nombre_razon_social' => 'OFICINA Y SUMINISTROS PROFESIONALES S.A. DE C.V.',
                'tipo_persona' => 'Moral',
                'regimen_fiscal' => '601',
                'uso_cfdi' => 'G03',
                'email' => 'ventas@oficinaysuministros.com',
                'telefono' => '5555678901',
                'calle' => 'AVENIDA INSURGENTES',
                'numero_exterior' => '1234',
                'numero_interior' => 'OF 201',
                'colonia' => 'NAPOLES',
                'codigo_postal' => '03810',
                'municipio' => 'BENITO JUÁREZ',
                'estado' => 'CIUDAD DE MÉXICO',
                'pais' => 'MÉXICO',
                'activo' => true
            ],
            [
                'rfc' => 'TECM850101HA2',
                'nombre_razon_social' => 'TECNOLOGÍA Y COMPUTACIÓN MÉXICO S. DE R.L.',
                'tipo_persona' => 'Moral',
                'regimen_fiscal' => '601',
                'uso_cfdi' => 'G03',
                'email' => 'contacto@tecnologiacomputacion.com',
                'telefono' => '5555678902',
                'calle' => 'CALLE REFORMA',
                'numero_exterior' => '567',
                'numero_interior' => 'PISO 8',
                'colonia' => 'CENTRO',
                'codigo_postal' => '06000',
                'municipio' => 'CUAUHTÉMOC',
                'estado' => 'CIUDAD DE MÉXICO',
                'pais' => 'MÉXICO',
                'activo' => true
            ],
            [
                'rfc' => 'MAPR780101HA3',
                'nombre_razon_social' => 'MATERIALES PARA OFICINA Y PAPELERÍA S.A.',
                'tipo_persona' => 'Moral',
                'regimen_fiscal' => '601',
                'uso_cfdi' => 'G03',
                'email' => 'pedidos@materialesoficina.com',
                'telefono' => '5555678903',
                'calle' => 'BOULEVARD PUERTO AÉREO',
                'numero_exterior' => '890',
                'numero_interior' => 'BODEGA 5',
                'colonia' => 'PEÑÓN DE LOS BAÑOS',
                'codigo_postal' => '15520',
                'municipio' => 'VENUSTIANO CARRANZA',
                'estado' => 'CIUDAD DE MÉXICO',
                'pais' => 'MÉXICO',
                'activo' => true
            ],
            [
                'rfc' => 'HEQU900101HA4',
                'nombre_razon_social' => 'HERRAMIENTAS Y EQUIPOS INDUSTRIALES S. DE R.L.',
                'tipo_persona' => 'Moral',
                'regimen_fiscal' => '601',
                'uso_cfdi' => 'G03',
                'email' => 'ventas@herramientasequipos.com',
                'telefono' => '5555678904',
                'calle' => 'CALLE SUR 123',
                'numero_exterior' => '456',
                'numero_interior' => 'NAVE INDUSTRIAL',
                'colonia' => 'INDUSTRIAL VALLEJO',
                'codigo_postal' => '02300',
                'municipio' => 'AZCAPOTZALCO',
                'estado' => 'CIUDAD DE MÉXICO',
                'pais' => 'MÉXICO',
                'activo' => true
            ],
            [
                'rfc' => 'ELCO850101HA5',
                'nombre_razon_social' => 'ELECTRÓNICA Y COMPUTADORAS DEL CENTRO S.A.',
                'tipo_persona' => 'Moral',
                'regimen_fiscal' => '601',
                'uso_cfdi' => 'G03',
                'email' => 'contacto@electronicacomputadoras.com',
                'telefono' => '5555678905',
                'calle' => 'AVENIDA TLALPAN',
                'numero_exterior' => '2345',
                'numero_interior' => 'LOCAL 12',
                'colonia' => 'CENTRO',
                'codigo_postal' => '06000',
                'municipio' => 'CUAUHTÉMOC',
                'estado' => 'CIUDAD DE MÉXICO',
                'pais' => 'MÉXICO',
                'activo' => true
            ]
        ];

        foreach ($proveedoresPrueba as $proveedorData) {
            Proveedor::firstOrCreate(
                ['rfc' => $proveedorData['rfc']],
                $proveedorData
            );
        }

        // 2. Proveedores adicionales para testing (solo en desarrollo)
        if (app()->environment('local', 'testing')) {
            $proveedoresAdicionales = [
                [
                    'rfc' => 'SUME800101HA6',
                    'nombre_razon_social' => 'SUMINISTROS ELECTRÓNICOS MÉXICO S.A. DE C.V.',
                    'tipo_persona' => 'Moral',
                    'regimen_fiscal' => '601',
                    'uso_cfdi' => 'G03',
                    'email' => 'info@suministronicos.com',
                    'telefono' => '5555678906',
                    'calle' => 'CALLE MONTEVIDEO',
                    'numero_exterior' => '789',
                    'numero_interior' => 'SUITE 300',
                    'colonia' => 'NAPOLES',
                    'codigo_postal' => '03810',
                    'municipio' => 'BENITO JUÁREZ',
                    'estado' => 'CIUDAD DE MÉXICO',
                    'pais' => 'MÉXICO',
                    'activo' => true
                ],
                [
                    'rfc' => 'LIMC850101HA7',
                    'nombre_razon_social' => 'LIMPIEZA Y MANTENIMIENTO COMERCIAL S. DE R.L.',
                    'tipo_persona' => 'Moral',
                    'regimen_fiscal' => '601',
                    'uso_cfdi' => 'G03',
                    'email' => 'contacto@limpiezacomercial.com',
                    'telefono' => '5555678907',
                    'calle' => 'AVENIDA CONSTITUYENTES',
                    'numero_exterior' => '1234',
                    'numero_interior' => 'BODEGA A',
                    'colonia' => 'CONSTITUYENTES',
                    'codigo_postal' => '11830',
                    'municipio' => 'MIGUEL HIDALGO',
                    'estado' => 'CIUDAD DE MÉXICO',
                    'pais' => 'MÉXICO',
                    'activo' => true
                ],
                [
                    'rfc' => 'PAPR900101HA8',
                    'nombre_razon_social' => 'PAPELERÍA Y MATERIALES DE OFICINA RÁPIDO S.A.',
                    'tipo_persona' => 'Moral',
                    'regimen_fiscal' => '601',
                    'uso_cfdi' => 'G03',
                    'email' => 'ventas@papeleriarapido.com',
                    'telefono' => '5555678908',
                    'calle' => 'CALLE TLAXCALA',
                    'numero_exterior' => '567',
                    'numero_interior' => 'TIENDA 3',
                    'colonia' => 'CENTRO',
                    'codigo_postal' => '06000',
                    'municipio' => 'CUAUHTÉMOC',
                    'estado' => 'CIUDAD DE MÉXICO',
                    'pais' => 'MÉXICO',
                    'activo' => true
                ]
            ];

            foreach ($proveedoresAdicionales as $proveedorData) {
                Proveedor::firstOrCreate(
                    ['rfc' => $proveedorData['rfc']],
                    $proveedorData
                );
            }
        }
    }

}
