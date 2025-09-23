<?php

namespace App\Services;

use App\Models\Mantenimiento;
use App\Models\Carro;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Notification;
use Carbon\Carbon;

class AlertaMantenimientoService
{
    /**
     * Verificar y enviar alertas para mantenimientos próximos a vencer
     */
    public function verificarYEnviarAlertas()
    {
        $mantenimientosConAlertaPendiente = Mantenimiento::conAlertasPendientes()->get();

        $alertasEnviadas = 0;
        $errores = [];

        foreach ($mantenimientosConAlertaPendiente as $mantenimiento) {
            try {
                // Verificar si realmente requiere alerta
                if (!$mantenimiento->requiere_alerta) {
                    continue;
                }

                // Enviar alerta
                $this->enviarAlertaMantenimiento($mantenimiento);

                // Marcar como enviada
                $mantenimiento->marcarAlertaEnviada();

                $alertasEnviadas++;

                Log::info("Alerta de mantenimiento enviada", [
                    'mantenimiento_id' => $mantenimiento->id,
                    'carro' => $mantenimiento->carro->marca . ' ' . $mantenimiento->carro->modelo,
                    'tipo' => $mantenimiento->tipo,
                    'dias_restantes' => $mantenimiento->dias_restantes
                ]);

            } catch (\Exception $e) {
                $errores[] = [
                    'mantenimiento_id' => $mantenimiento->id,
                    'error' => $e->getMessage()
                ];

                Log::error("Error al enviar alerta de mantenimiento", [
                    'mantenimiento_id' => $mantenimiento->id,
                    'error' => $e->getMessage()
                ]);
            }
        }

        return [
            'alertas_enviadas' => $alertasEnviadas,
            'errores' => $errores,
            'total_procesados' => $mantenimientosConAlertaPendiente->count()
        ];
    }

    /**
     * Enviar alerta de mantenimiento
     */
    private function enviarAlertaMantenimiento(Mantenimiento $mantenimiento)
    {
        $carro = $mantenimiento->carro;
        $diasRestantes = $mantenimiento->dias_restantes;

        // Determinar el tipo de alerta basado en la urgencia
        $tipoAlerta = $this->determinarTipoAlerta($diasRestantes, $mantenimiento->prioridad);

        $datosAlerta = [
            'carro' => $carro,
            'mantenimiento' => $mantenimiento,
            'dias_restantes' => $diasRestantes,
            'tipo_alerta' => $tipoAlerta,
            'fecha_proximo' => $mantenimiento->proximo_mantenimiento,
            'prioridad' => $mantenimiento->prioridad
        ];

        // Enviar notificación por email
        $this->enviarNotificacionEmail($datosAlerta);

        // Agregar recordatorio al historial
        $mantenimiento->agregarRecordatorioEnviado('email');
    }

    /**
     * Determinar el tipo de alerta basado en días restantes y prioridad
     */
    private function determinarTipoAlerta($diasRestantes, $prioridad)
    {
        if ($diasRestantes < 0) {
            return 'vencido';
        }

        if ($diasRestantes <= 3 || $prioridad === Mantenimiento::PRIORIDAD_CRITICA) {
            return 'critica';
        }

        if ($diasRestantes <= 7 || $prioridad === Mantenimiento::PRIORIDAD_ALTA) {
            return 'urgente';
        }

        if ($diasRestantes <= 15) {
            return 'moderada';
        }

        return 'informativa';
    }

    /**
     * Enviar notificación por email
     */
    private function enviarNotificacionEmail($datosAlerta)
    {
        // Aquí se implementaría el envío de email
        // Por ahora solo logueamos
        Log::info("Enviando email de alerta de mantenimiento", [
            'carro' => $datosAlerta['carro']->marca . ' ' . $datosAlerta['carro']->modelo,
            'tipo' => $datosAlerta['mantenimiento']->tipo,
            'dias_restantes' => $datosAlerta['dias_restantes'],
            'tipo_alerta' => $datosAlerta['tipo_alerta']
        ]);

        // TODO: Implementar envío real de email
        // Mail::to('admin@taller.com')->send(new AlertaMantenimiento($datosAlerta));
    }

    /**
     * Obtener mantenimientos que requieren atención inmediata
     */
    public function getMantenimientosCriticos()
    {
        return [
            'criticos' => Mantenimiento::criticos()->with('carro')->get(),
            'vencidos' => Mantenimiento::where('proximo_mantenimiento', '<', now())
                ->where('estado', '!=', Mantenimiento::ESTADO_COMPLETADO)
                ->with('carro')
                ->get(),
            'proximos_3_dias' => Mantenimiento::where('proximo_mantenimiento', '<=', now()->addDays(3))
                ->where('proximo_mantenimiento', '>=', now())
                ->with('carro')
                ->get()
        ];
    }

    /**
     * Generar reporte de alertas para dashboard
     */
    public function generarReporteAlertas()
    {
        $estadisticas = Mantenimiento::getEstadisticasAlertas();

        $reporte = [
            'estadisticas' => $estadisticas,
            'alertas_por_prioridad' => Mantenimiento::select('prioridad')
                ->selectRaw('COUNT(*) as total')
                ->where('alerta_enviada', false)
                ->where('proximo_mantenimiento', '<=', now()->addDays(30))
                ->groupBy('prioridad')
                ->get(),
            'alertas_por_tipo' => Mantenimiento::select('tipo')
                ->selectRaw('COUNT(*) as total')
                ->where('alerta_enviada', false)
                ->where('proximo_mantenimiento', '<=', now()->addDays(30))
                ->groupBy('tipo')
                ->get(),
            'proximos_mantenimientos' => Mantenimiento::with('carro')
                ->where('proximo_mantenimiento', '>=', now())
                ->where('proximo_mantenimiento', '<=', now()->addDays(30))
                ->orderBy('proximo_mantenimiento', 'asc')
                ->limit(10)
                ->get()
        ];

        return $reporte;
    }

    /**
     * Programar recordatorios automáticos
     */
    public function programarRecordatoriosAutomaticos()
    {
        $mantenimientosParaRecordatorio = Mantenimiento::where('alerta_enviada', true)
            ->where('estado', '!=', Mantenimiento::ESTADO_COMPLETADO)
            ->where('proximo_mantenimiento', '>', now())
            ->get();

        $recordatoriosEnviados = 0;

        foreach ($mantenimientosParaRecordatorio as $mantenimiento) {
            // Verificar si necesita recordatorio (máximo 3 recordatorios)
            $recordatoriosEnviadosCount = $this->contarRecordatoriosEnviados($mantenimiento);
            if ($recordatoriosEnviadosCount >= 3) {
                continue;
            }

            $diasDesdeUltimoRecordatorio = $this->getDiasDesdeUltimoRecordatorio($mantenimiento);

            if ($diasDesdeUltimoRecordatorio >= $mantenimiento->frecuencia_recordatorio_dias) {
                $this->enviarRecordatorio($mantenimiento);
                $mantenimiento->agregarRecordatorioEnviado('recordatorio_automatico');
                $recordatoriosEnviados++;
            }
        }

        return $recordatoriosEnviados;
    }

    /**
     * Contar recordatorios enviados (compatible con SQLite)
     */
    private function contarRecordatoriosEnviados(Mantenimiento $mantenimiento)
    {
        $recordatorios = $mantenimiento->recordatorios_enviados ?? [];
        return is_array($recordatorios) ? count($recordatorios) : 0;
    }

    /**
     * Obtener días desde el último recordatorio
     */
    private function getDiasDesdeUltimoRecordatorio(Mantenimiento $mantenimiento)
    {
        $recordatorios = $mantenimiento->recordatorios_enviados ?? [];

        if (empty($recordatorios)) {
            return PHP_INT_MAX; // Nunca se ha enviado recordatorio
        }

        $ultimoRecordatorio = collect($recordatorios)->sortByDesc('timestamp')->first();
        $fechaUltimoRecordatorio = Carbon::parse($ultimoRecordatorio['fecha']);

        return $fechaUltimoRecordatorio->diffInDays(now());
    }

    /**
     * Enviar recordatorio de mantenimiento
     */
    private function enviarRecordatorio(Mantenimiento $mantenimiento)
    {
        Log::info("Enviando recordatorio de mantenimiento", [
            'mantenimiento_id' => $mantenimiento->id,
            'carro' => $mantenimiento->carro->marca . ' ' . $mantenimiento->carro->modelo,
            'tipo' => $mantenimiento->tipo,
            'dias_restantes' => $mantenimiento->dias_restantes
        ]);

        // TODO: Implementar envío real de recordatorio
        // Mail::to('admin@taller.com')->send(new RecordatorioMantenimiento($mantenimiento));
    }
}
