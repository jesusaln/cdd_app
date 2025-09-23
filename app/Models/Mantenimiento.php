<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Mantenimiento extends Model
{
    use HasFactory;

    protected $fillable = [
        'carro_id',
        'tipo',
        'fecha',
        'proximo_mantenimiento',
        'descripcion',
        'notas',
        'costo',
        'estado',
        'kilometraje_actual',
        'proximo_kilometraje',
        'prioridad',
        'alerta_enviada',
        'alerta_enviada_at',
        'dias_anticipacion_alerta',
        'observaciones_alerta',
        'requiere_aprobacion',
        'tipo_alerta',
        'recordatorios_enviados',
        'frecuencia_recordatorio_dias',
    ];

    protected $casts = [
        'fecha' => 'date',
        'proximo_mantenimiento' => 'date',
        'costo' => 'decimal:2',
        'alerta_enviada' => 'boolean',
        'alerta_enviada_at' => 'datetime',
        'requiere_aprobacion' => 'boolean',
        'recordatorios_enviados' => 'array',
        'dias_anticipacion_alerta' => 'integer',
        'frecuencia_recordatorio_dias' => 'integer',
    ];

    // Constantes para los estados
    const ESTADO_COMPLETADO = 'completado';
    const ESTADO_PENDIENTE = 'pendiente';
    const ESTADO_EN_PROCESO = 'en_proceso';

    // Constantes para prioridades
    const PRIORIDAD_BAJA = 'baja';
    const PRIORIDAD_MEDIA = 'media';
    const PRIORIDAD_ALTA = 'alta';
    const PRIORIDAD_CRITICA = 'critica';

    // Constantes para tipos de alerta
    const TIPO_ALERTA_AUTOMATICA = 'automatica';
    const TIPO_ALERTA_MANUAL = 'manual';

    // Constantes para tipos comunes de mantenimiento
    const TIPO_CAMBIO_ACEITE = 'cambio_aceite';
    const TIPO_REVISION_PERIODICA = 'revision_periodica';
    const TIPO_FRENOS = 'frenos';
    const TIPO_NEUMATICOS = 'neumaticos';
    const TIPO_FILTROS = 'filtros';
    const TIPO_BATERIA = 'bateria';
    const TIPO_TRANSMISION = 'transmision';
    const TIPO_REFRIGERANTE = 'refrigerante';
    const TIPO_SUSPENSION = 'suspension';
    const TIPO_OTRO = 'otro';

    /**
     * Relación con el modelo Carro
     */
    public function carro(): BelongsTo
    {
        return $this->belongsTo(Carro::class);
    }

    /**
     * Scope para obtener mantenimientos próximos a vencer
     */
    public function scopeProximosAVencer($query, $dias = 30)
    {
        return $query->where('proximo_mantenimiento', '<=', now()->addDays($dias))
            ->where('estado', '!=', self::ESTADO_COMPLETADO);
    }

    /**
     * Scope para obtener mantenimientos por estado
     */
    public function scopeByEstado($query, $estado)
    {
        return $query->where('estado', $estado);
    }

    /**
     * Scope para obtener mantenimientos por tipo
     */
    public function scopeByTipo($query, $tipo)
    {
        return $query->where('tipo', $tipo);
    }

    /**
     * Accessor para formatear el costo
     */
    public function getCostoFormateadoAttribute()
    {
        return '$' . number_format($this->costo, 2);
    }

    /**
     * Verificar si el mantenimiento está vencido
     */
    public function getEsVencidoAttribute()
    {
        return $this->proximo_mantenimiento &&
            $this->proximo_mantenimiento < now() &&
            $this->estado !== self::ESTADO_COMPLETADO;
    }

    /**
     * Obtener días restantes para el próximo mantenimiento
     */
    public function getDiasRestantesAttribute()
    {
        if (!$this->proximo_mantenimiento) {
            return null;
        }

        return round(now()->diffInDays($this->proximo_mantenimiento, false));
    }

    /**
     * Scope para obtener mantenimientos por prioridad
     */
    public function scopeByPrioridad($query, $prioridad)
    {
        return $query->where('prioridad', $prioridad);
    }

    /**
     * Scope para obtener mantenimientos con alertas pendientes
     */
    public function scopeConAlertasPendientes($query)
    {
        return $query->where('alerta_enviada', false)
            ->where('proximo_mantenimiento', '<=', now()->addDays($this->dias_anticipacion_alerta ?? 30));
    }

    /**
     * Scope para obtener mantenimientos críticos (alta prioridad y próximos a vencer)
     */
    public function scopeCriticos($query)
    {
        return $query->whereIn('prioridad', [self::PRIORIDAD_ALTA, self::PRIORIDAD_CRITICA])
            ->where('proximo_mantenimiento', '<=', now()->addDays(7));
    }

    /**
     * Scope para obtener mantenimientos que requieren aprobación
     */
    public function scopeRequierenAprobacion($query)
    {
        return $query->where('requiere_aprobacion', true);
    }

    /**
     * Verificar si el mantenimiento requiere alerta
     */
    public function getRequiereAlertaAttribute()
    {
        if ($this->alerta_enviada) {
            return false;
        }

        if (!$this->proximo_mantenimiento) {
            return false;
        }

        $diasRestantes = $this->dias_restantes;
        $diasAnticipacion = $this->dias_anticipacion_alerta ?? 30;

        return $diasRestantes !== null && $diasRestantes <= $diasAnticipacion;
    }

    /**
     * Obtener el nivel de urgencia basado en días restantes y prioridad
     */
    public function getNivelUrgenciaAttribute()
    {
        $diasRestantes = $this->dias_restantes;

        if ($diasRestantes === null) {
            return 'info';
        }

        if ($diasRestantes < 0) {
            return 'danger'; // Vencido
        }

        if ($diasRestantes <= 3) {
            return 'critical'; // Muy urgente
        }

        if ($diasRestantes <= 7) {
            return 'warning'; // Urgente
        }

        if ($diasRestantes <= 15) {
            return 'info'; // Moderado
        }

        return 'success'; // Todo bien
    }

    /**
     * Obtener clases CSS para el nivel de urgencia
     */
    public function getClasesUrgenciaAttribute()
    {
        $niveles = [
            'success' => 'bg-green-100 text-green-700 border-green-200',
            'info' => 'bg-blue-100 text-blue-700 border-blue-200',
            'warning' => 'bg-yellow-100 text-yellow-700 border-yellow-200',
            'critical' => 'bg-orange-100 text-orange-700 border-orange-200',
            'danger' => 'bg-red-100 text-red-700 border-red-200'
        ];

        return $niveles[$this->nivel_urgencia] ?? 'bg-gray-100 text-gray-700 border-gray-200';
    }

    /**
     * Marcar alerta como enviada
     */
    public function marcarAlertaEnviada($tipo = 'automatica')
    {
        $this->update([
            'alerta_enviada' => true,
            'alerta_enviada_at' => now(),
            'tipo_alerta' => $tipo
        ]);
    }

    /**
     * Agregar recordatorio enviado
     */
    public function agregarRecordatorioEnviado($tipo = 'email', $fecha = null)
    {
        $recordatorios = $this->recordatorios_enviados ?? [];
        $recordatorios[] = [
            'tipo' => $tipo,
            'fecha' => $fecha ?? now()->toISOString(),
            'timestamp' => now()->timestamp
        ];

        $this->update(['recordatorios_enviados' => $recordatorios]);
    }

    /**
     * Obtener estadísticas de alertas para dashboard
     */
    public static function getEstadisticasAlertas()
    {
        return [
            'total_con_alerta_pendiente' => self::conAlertasPendientes()->count(),
            'criticos' => self::criticos()->count(),
            'por_vencer_7_dias' => self::where('proximo_mantenimiento', '<=', now()->addDays(7))
                ->where('proximo_mantenimiento', '>=', now())
                ->count(),
            'vencidos' => self::where('proximo_mantenimiento', '<', now())
                ->where('estado', '!=', self::ESTADO_COMPLETADO)
                ->count(),
            'requieren_aprobacion' => self::requierenAprobacion()->count(),
        ];
    }

    /**
     * Generar mantenimientos recurrentes para los vencidos (método legacy para datos antiguos)
     * Nota: Esta funcionalidad ahora está integrada en el proceso de crear y completar mantenimientos
     */
    public static function generarMantenimientosRecurrentes()
    {
        $mantenimientosVencidos = self::where('proximo_mantenimiento', '<', now())
            ->where('estado', '!=', self::ESTADO_COMPLETADO)
            ->with('carro')
            ->get();

        $creados = 0;

        foreach ($mantenimientosVencidos as $mantenimiento) {
            // Calcular la fecha del próximo mantenimiento basado en el intervalo anterior
            if ($mantenimiento->fecha && $mantenimiento->proximo_mantenimiento) {
                $intervaloDias = $mantenimiento->fecha->diffInDays($mantenimiento->proximo_mantenimiento);

                // Crear nuevo mantenimiento
                $nuevoMantenimiento = self::create([
                    'carro_id' => $mantenimiento->carro_id,
                    'tipo' => $mantenimiento->tipo,
                    'fecha' => $mantenimiento->proximo_mantenimiento, // La fecha vencida se convierte en la fecha del servicio
                    'proximo_mantenimiento' => now()->addDays($intervaloDias), // Próximo mantenimiento
                    'descripcion' => $mantenimiento->descripcion,
                    'notas' => $mantenimiento->notas,
                    'costo' => $mantenimiento->costo,
                    'estado' => self::ESTADO_PENDIENTE, // Nuevo mantenimiento como pendiente
                    'kilometraje_actual' => $mantenimiento->carro->kilometraje ?? $mantenimiento->kilometraje_actual,
                    'proximo_kilometraje' => $mantenimiento->proximo_kilometraje,
                    'prioridad' => $mantenimiento->prioridad,
                    'dias_anticipacion_alerta' => $mantenimiento->dias_anticipacion_alerta,
                    'requiere_aprobacion' => $mantenimiento->requiere_aprobacion,
                    'observaciones_alerta' => $mantenimiento->observaciones_alerta,
                ]);

                // Marcar el mantenimiento anterior como completado
                $mantenimiento->update([
                    'estado' => self::ESTADO_COMPLETADO,
                    'notas' => ($mantenimiento->notas ? $mantenimiento->notas . ' | ' : '') . 'Mantenimiento completado automáticamente - generado nuevo registro ID: ' . $nuevoMantenimiento->id
                ]);

                $creados++;
            }
        }

        return $creados;
    }

    /**
     * Verificar si necesita generar mantenimiento recurrente
     */
    public function necesitaGenerarRecurrente()
    {
        return $this->proximo_mantenimiento &&
               $this->proximo_mantenimiento < now() &&
               $this->estado !== self::ESTADO_COMPLETADO;
    }
}
