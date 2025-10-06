<?php

namespace App\Console\Commands;

use App\Models\CuentasPorCobrar;
use App\Models\Venta;
use Illuminate\Console\Command;

class GenerarCuentasPorCobrar extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:generar-cuentas-por-cobrar {--force : Forzar recreación de cuentas existentes}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Genera cuentas por cobrar para ventas no pagadas que no tienen cuenta asociada';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $force = $this->option('force');

        $this->info('Buscando ventas sin cuentas por cobrar...');

        $ventasSinCuenta = Venta::whereDoesntHave('cuentaPorCobrar')
            ->where('pagado', false)
            ->whereIn('estado', ['borrador', 'pendiente', 'aprobada'])
            ->get();

        if ($ventasSinCuenta->isEmpty()) {
            $this->info('No hay ventas sin cuentas por cobrar.');
            return;
        }

        $this->info("Encontradas {$ventasSinCuenta->count()} ventas sin cuentas por cobrar.");

        if (!$force) {
            if (!$this->confirm('¿Deseas continuar con la creación de cuentas por cobrar?')) {
                return;
            }
        }

        $creadas = 0;
        $errores = 0;

        foreach ($ventasSinCuenta as $venta) {
            try {
                CuentasPorCobrar::create([
                    'venta_id' => $venta->id,
                    'monto_total' => $venta->total,
                    'monto_pagado' => 0,
                    'monto_pendiente' => $venta->total,
                    'fecha_vencimiento' => $venta->fecha->addDays(30),
                    'estado' => 'pendiente',
                    'notas' => 'Cuenta por cobrar generada por comando',
                ]);

                $creadas++;
                $this->line("Creada cuenta para venta {$venta->numero_venta}");
            } catch (\Exception $e) {
                $errores++;
                $this->error("Error creando cuenta para venta {$venta->numero_venta}: {$e->getMessage()}");
            }
        }

        $this->info("Proceso completado. Creadas: {$creadas}, Errores: {$errores}");
    }
}
