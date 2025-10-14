<?php

namespace App\Console\Commands;

use App\Models\Empresa;
use App\Models\CuentasPorCobrar;
use App\Jobs\SendWhatsAppTemplate;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class EnviarRecordatoriosWhatsApp extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'whatsapp:enviar-recordatorios
                            {--dias=3 : Días antes del vencimiento para enviar recordatorio}
                            {--empresa_id= : ID específico de empresa (opcional)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Enviar recordatorios de pago por WhatsApp a clientes con cuentas por cobrar próximas a vencer';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $diasAnticipacion = (int) $this->option('dias');
        $empresaId = $this->option('empresa_id');

        $this->info("Enviando recordatorios de WhatsApp {$diasAnticipacion} días antes del vencimiento...");

        // Obtener empresas con WhatsApp habilitado
        $empresasQuery = Empresa::where('whatsapp_enabled', true)
                               ->whereNotNull('whatsapp_template_payment_reminder');

        if ($empresaId) {
            $empresasQuery->where('id', $empresaId);
        }

        $empresas = $empresasQuery->get();

        if ($empresas->isEmpty()) {
            $this->warn('No se encontraron empresas con WhatsApp habilitado');
            return 0;
        }

        $totalEnviados = 0;

        foreach ($empresas as $empresa) {
            $this->info("Procesando empresa: {$empresa->nombre_razon_social}");

            try {
                // Obtener cuentas por cobrar próximas a vencer
                $fechaRecordatorio = now()->addDays($diasAnticipacion);

                $cuentasPorCobrar = CuentasPorCobrar::where('empresa_id', $empresa->id)
                    ->where('fecha_vencimiento', '<=', $fechaRecordatorio)
                    ->where('fecha_vencimiento', '>', now())
                    ->where('estado', 'pendiente')
                    ->whereHas('cliente', function ($query) {
                        // Solo clientes que hayan dado opt-in para WhatsApp
                        $query->where('whatsapp_optin', true);
                    })
                    ->with(['cliente'])
                    ->get();

                if ($cuentasPorCobrar->isEmpty()) {
                    $this->line("  No hay cuentas por cobrar próximas a vencer para esta empresa");
                    continue;
                }

                $this->line("  Encontradas {$cuentasPorCobrar->count()} cuentas por cobrar");

                foreach ($cuentasPorCobrar as $cuenta) {
                    // Verificar límite de mensajes por día para este cliente
                    $mensajesHoy = \App\Models\WhatsAppMessage::where('empresa_id', $empresa->id)
                        ->where('to', $cuenta->cliente->telefono)
                        ->whereDate('created_at', today())
                        ->count();

                    $limiteMensajes = 3; // Máximo 3 mensajes por día por cliente

                    if ($mensajesHoy >= $limiteMensajes) {
                        $this->line("  Saltando cliente {$cuenta->cliente->nombre_razon_social} - límite de mensajes alcanzado ({$mensajesHoy}/{$limiteMensajes})");
                        continue;
                    }

                    // Preparar parámetros para la plantilla
                    $templateParams = [
                        $cuenta->cliente->nombre_razon_social,
                        '$' . number_format($cuenta->monto_total, 2),
                        $cuenta->fecha_vencimiento->format('d/m/Y'),
                    ];

                    // Despachar job para envío
                    SendWhatsAppTemplate::dispatch(
                        $empresa->id,
                        $cuenta->cliente->telefono,
                        $empresa->whatsapp_template_payment_reminder,
                        $empresa->whatsapp_default_language,
                        $templateParams,
                        [
                            'tipo' => 'recordatorio_pago',
                            'cuenta_id' => $cuenta->id,
                            'dias_anticipacion' => $diasAnticipacion,
                        ]
                    );

                    $totalEnviados++;
                    $this->line("  Recordatorio programado para {$cuenta->cliente->nombre_razon_social} - {$cuenta->cliente->telefono}");
                }

            } catch (\Exception $e) {
                $this->error("Error procesando empresa {$empresa->nombre_razon_social}: " . $e->getMessage());
                Log::error('Error en envío de recordatorios WhatsApp', [
                    'empresa_id' => $empresa->id,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        $this->info("Proceso completado. Total de recordatorios programados: {$totalEnviados}");

        return 0;
    }
}
