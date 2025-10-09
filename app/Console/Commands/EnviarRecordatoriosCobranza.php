<?php

namespace App\Console\Commands;

use App\Models\CuentasPorCobrar;
use App\Models\RecordatorioCobranza;
use App\Models\EmpresaConfiguracion;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;

class EnviarRecordatoriosCobranza extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'cobranza:enviar-recordatorios {--dry-run : Ejecutar sin enviar emails}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Enviar recordatorios automáticos de pago para cuentas por cobrar vencidas';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('🚀 Iniciando envío de recordatorios de cobranza...');
        $dryRun = $this->option('dry-run');

        if ($dryRun) {
            $this->warn('⚠️ Modo de prueba activado - no se enviarán emails reales');
        }

        // Obtener cuentas que necesitan recordatorio
        $cuentasNecesitanRecordatorio = CuentasPorCobrar::with(['venta.cliente'])
            ->pendientes()
            ->whereHas('venta.cliente', function ($query) {
                $query->whereNotNull('email');
            })
            ->get()
            ->filter(function ($cuenta) {
                return $cuenta->necesitaRecordatorio();
            });

        $this->info("📋 Se encontraron {$cuentasNecesitanRecordatorio->count()} cuentas que necesitan recordatorio");

        if ($cuentasNecesitanRecordatorio->isEmpty()) {
            $this->info('✅ No hay cuentas que necesiten recordatorios en este momento');
            return 0;
        }

        $totalEnviados = 0;
        $totalErrores = 0;

        foreach ($cuentasNecesitanRecordatorio as $cuenta) {
            try {
                $this->processCuenta($cuenta, $dryRun);
                $totalEnviados++;
                $this->line("✅ Recordatorio enviado para venta #{$cuenta->venta->numero_venta}");
            } catch (\Exception $e) {
                $totalErrores++;
                $this->error("❌ Error procesando cuenta {$cuenta->id}: {$e->getMessage()}");
                Log::error('Error enviando recordatorio de cobranza', [
                    'cuenta_id' => $cuenta->id,
                    'venta_id' => $cuenta->venta_id,
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString()
                ]);
            }
        }

        $this->newLine();
        $this->info("📊 Resumen del proceso:");
        $this->line("   ✅ Recordatorios enviados: {$totalEnviados}");
        $this->line("   ❌ Errores: {$totalErrores}");
        $this->line("   📋 Total procesados: {$cuentasNecesitanRecordatorio->count()}");

        if ($totalErrores > 0) {
            return 1; // Código de error
        }

        return 0; // Éxito
    }

    /**
     * Procesar una cuenta individual
     */
    private function processCuenta(CuentasPorCobrar $cuenta, bool $dryRun): void
    {
        // Programar el recordatorio
        $recordatorio = $cuenta->programarRecordatorio();

        if (!$recordatorio) {
            throw new \Exception('No se pudo programar recordatorio para esta cuenta');
        }

        if ($dryRun) {
            $this->warn("   [DRY RUN] Se programaría recordatorio para venta #{$cuenta->venta->numero_venta}");
            $recordatorio->marcarComoEnviado('Simulado en dry-run');
            return;
        }

        // Verificar que el cliente tenga email
        if (!$cuenta->venta->cliente->email) {
            throw new \Exception('Cliente sin email configurado');
        }

        // Obtener configuración de empresa
        $configuracion = EmpresaConfiguracion::getConfig();

        // Generar PDF de la venta
        $pdf = Pdf::loadView('venta_pdf', [
            'venta' => $cuenta->venta,
            'configuracion' => $configuracion,
        ]);

        // Configurar opciones del PDF
        $pdf->setPaper('letter', 'portrait');
        $pdf->setOptions([
            'defaultFont' => 'sans-serif',
            'isHtml5ParserEnabled' => true,
            'isPhpEnabled' => true,
        ]);

        // Preparar datos del email
        $datosEmail = [
            'cuenta' => $cuenta,
            'cliente' => $cuenta->venta->cliente,
            'configuracion' => $configuracion,
            'recordatorio' => $recordatorio,
        ];

        // Configurar SMTP
        config([
            'mail.mailers.smtp.host' => $configuracion->smtp_host,
            'mail.mailers.smtp.port' => $configuracion->smtp_port,
            'mail.mailers.smtp.username' => $configuracion->smtp_username,
            'mail.mailers.smtp.password' => $configuracion->smtp_password,
            'mail.mailers.smtp.encryption' => $configuracion->smtp_encryption,
            'mail.from.address' => $configuracion->email_from_address,
            'mail.from.name' => $configuracion->email_from_name,
        ]);

        // Enviar email con PDF adjunto
        Mail::send('emails.recordatorio_pago', $datosEmail, function ($message) use ($cuenta, $pdf, $configuracion) {
            $message->to($cuenta->venta->cliente->email)
                ->subject("🚨 Recordatorio de Pago - Venta #{$cuenta->venta->numero_venta} - {$configuracion->nombre_empresa}")
                ->attachData($pdf->output(), "venta-{$cuenta->venta->numero_venta}.pdf", [
                    'mime' => 'application/pdf',
                ]);

            // Agregar reply-to si está configurado
            if ($configuracion->email_reply_to) {
                $message->replyTo($configuracion->email_reply_to);
            }
        });

        // Marcar como enviado
        $recordatorio->marcarComoEnviado();

        Log::info("Recordatorio de cobranza enviado exitosamente", [
            'cuenta_id' => $cuenta->id,
            'venta_id' => $cuenta->venta_id,
            'cliente_email' => $cuenta->venta->cliente->email,
            'tipo_recordatorio' => $recordatorio->tipo_recordatorio,
            'numero_intento' => $recordatorio->numero_intento,
        ]);
    }
}
