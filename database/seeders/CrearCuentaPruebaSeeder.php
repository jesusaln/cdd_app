<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\CuentasPorCobrar;
use App\Models\Venta;
use App\Models\Cliente;
use Carbon\Carbon;

class CrearCuentaPruebaSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Buscar cliente existente o crear uno nuevo
        $cliente = Cliente::where('email', 'cliente.prueba@empresa.com')->first();

        if (!$cliente) {
            $cliente = Cliente::create([
                'nombre_razon_social' => 'Cliente de Prueba',
                'rfc' => 'XAXX010101001', // RFC diferente
                'tipo_persona' => 'fisica',
                'email' => 'cliente.prueba@empresa.com',
                'telefono' => '555-000-0000',
                'calle' => 'Calle de Prueba',
                'numero_exterior' => '123',
                'colonia' => 'Colonia Prueba',
                'codigo_postal' => '00000',
                'municipio' => 'Ciudad Prueba',
                'estado' => 'Estado Prueba',
                'pais' => 'México',
                'uso_cfdi' => 'G03',
                'regimen_fiscal' => '601',
                'activo' => true,
            ]);
        }

        // Crear venta de prueba si no existe
        $venta = Venta::firstOrCreate(
            ['numero_venta' => 'VEN-PRUEBA-001'],
            [
                'cliente_id' => $cliente->id,
                'subtotal' => 1000.00,
                'iva' => 160.00, // Nota: Esto se calcula dinámicamente en producción
                'total' => 1160.00,
                'fecha' => Carbon::now()->subDays(35), // 35 días atrás
                'estado' => 'aprobada',
                'pagado' => false,
            ]
        );

        // Crear cuenta por cobrar con fecha de vencimiento pasada
        CuentasPorCobrar::firstOrCreate(
            ['venta_id' => $venta->id],
            [
                'monto_total' => 1160.00,
                'monto_pagado' => 0.00,
                'monto_pendiente' => 1160.00,
                'fecha_vencimiento' => Carbon::now()->subDays(5), // Vencida hace 5 días
                'estado' => 'pendiente',
                'notas' => 'Cuenta de prueba para testing de recordatorios',
            ]
        );

        $this->command->info('✅ Cuenta de prueba creada exitosamente');
        $this->command->info("   Venta: {$venta->numero_venta}");
        $this->command->info("   Cliente: {$cliente->nombre_razon_social}");
        $this->command->info("   Email: {$cliente->email}");
        $this->command->info("   Fecha vencimiento: " . Carbon::now()->subDays(5)->format('Y-m-d'));
        $this->command->info("   Estado: pendiente");
    }
}
