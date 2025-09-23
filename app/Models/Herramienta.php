<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Herramienta extends Model
{
    use HasFactory;

    protected $fillable = [
        'nombre',
        'numero_serie',
        'foto',
        'tecnico_id',
        'estado',
        'vida_util_meses',
        'fecha_ultimo_mantenimiento',
        'costo_reemplazo',
        'categoria',
        'categoria_id',
        'descripcion',
        'requiere_mantenimiento',
        'dias_para_mantenimiento',
        'fecha_asignacion',
        'fecha_recepcion'
    ];

    protected $casts = [
        'fecha_ultimo_mantenimiento' => 'date',
        'fecha_asignacion' => 'datetime',
        'fecha_recepcion' => 'datetime',
        'costo_reemplazo' => 'decimal:2',
        'vida_util_meses' => 'integer',
        'dias_para_mantenimiento' => 'integer',
        'requiere_mantenimiento' => 'boolean',
    ];

    // Estados posibles
    const ESTADO_DISPONIBLE = 'disponible';
    const ESTADO_ASIGNADA = 'asignada';
    const ESTADO_MANTENIMIENTO = 'mantenimiento';
    const ESTADO_BAJA = 'baja';
    const ESTADO_PERDIDA = 'perdida';

    // Categorías comunes de herramientas
    const CATEGORIAS = [
        'electrica' => 'Eléctrica',
        'manual' => 'Manual',
        'medicion' => 'Medición',
        'seguridad' => 'Seguridad',
        'limpieza' => 'Limpieza',
        'jardineria' => 'Jardinería',
        'construccion' => 'Construcción',
        'electronica' => 'Electrónica',
        'otra' => 'Otra'
    ];

    // Relaciones
    public function tecnico()
    {
        return $this->belongsTo(Tecnico::class, 'tecnico_id');
    }

    public function categoriaHerramienta()
    {
        return $this->belongsTo(CategoriaHerramienta::class, 'categoria_id');
    }

    public function asignaciones()
    {
        return $this->hasMany(AsignacionHerramienta::class);
    }

    public function detallesAsignacionesMasivas()
    {
        return $this->hasMany(DetalleAsignacionMasiva::class);
    }

    public function asignacionesMasivas()
    {
        return $this->belongsToMany(AsignacionMasiva::class, 'detalle_asignaciones_masivas', 'herramienta_id', 'asignacion_masiva_id')
                    ->withPivot(['estado_individual', 'fecha_asignacion_individual', 'fecha_devolucion_individual'])
                    ->withTimestamps();
    }

    public function estados()
    {
        return $this->hasMany(EstadoHerramienta::class);
    }

    public function historial()
    {
        return $this->hasMany(HistorialHerramienta::class);
    }

    // Scopes
    public function scopeDisponibles($query)
    {
        return $query->where('estado', self::ESTADO_DISPONIBLE);
    }

    public function scopeAsignadas($query)
    {
        return $query->where('estado', self::ESTADO_ASIGNADA);
    }

    public function scopeEnMantenimiento($query)
    {
        return $query->where('estado', self::ESTADO_MANTENIMIENTO);
    }

    public function scopePorCategoria($query, $categoria)
    {
        return $query->where('categoria', $categoria);
    }

    public function scopeRequierenMantenimiento($query)
    {
        return $query->where('requiere_mantenimiento', true);
    }

    // Métodos auxiliares
    public function estaDisponible()
    {
        return $this->estado === self::ESTADO_DISPONIBLE;
    }

    public function estaAsignada()
    {
        return $this->estado === self::ESTADO_ASIGNADA;
    }

    public function estaEnMantenimiento()
    {
        return $this->estado === self::ESTADO_MANTENIMIENTO;
    }

    public function requiereMantenimiento()
    {
        return $this->requiere_mantenimiento;
    }

    public function getEstadoLabelAttribute()
    {
        return match($this->estado) {
            self::ESTADO_DISPONIBLE => 'Disponible',
            self::ESTADO_ASIGNADA => 'Asignada',
            self::ESTADO_MANTENIMIENTO => 'En Mantenimiento',
            self::ESTADO_BAJA => 'De Baja',
            self::ESTADO_PERDIDA => 'Perdida',
            default => 'Sin Estado'
        };
    }

    public function getEstadoColorAttribute()
    {
        return match($this->estado) {
            self::ESTADO_DISPONIBLE => 'green',
            self::ESTADO_ASIGNADA => 'blue',
            self::ESTADO_MANTENIMIENTO => 'yellow',
            self::ESTADO_BAJA => 'red',
            self::ESTADO_PERDIDA => 'red',
            default => 'gray'
        };
    }

    public function getCategoriaLabelAttribute()
    {
        // Primero intenta usar la relación con CategoriaHerramienta
        if ($this->categoriaHerramienta) {
            return $this->categoriaHerramienta->nombre;
        }

        // Si no hay relación, usa el campo categoria legacy
        return self::CATEGORIAS[$this->categoria] ?? 'Sin Categoría';
    }

    public function getUltimoEstadoAttribute()
    {
        return $this->estados()->latest('fecha_inspeccion')->first();
    }

    public function getAsignacionActivaAttribute()
    {
        return $this->asignaciones()->where('activo', true)->latest('fecha_asignacion')->first();
    }

    public function getAsignacionMasivaActivaAttribute()
    {
        return $this->detallesAsignacionesMasivas()
                    ->with('asignacionMasiva')
                    ->where('estado_individual', DetalleAsignacionMasiva::ESTADO_ASIGNADA)
                    ->whereHas('asignacionMasiva', function ($query) {
                        $query->where('estado', AsignacionMasiva::ESTADO_ACTIVA);
                    })
                    ->latest('fecha_asignacion_individual')
                    ->first();
    }

    public function getEstaEnAsignacionMasivaAttribute()
    {
        return $this->asignacion_masiva_activa !== null;
    }

    public function getDiasDesdeUltimoMantenimientoAttribute()
    {
        if (!$this->fecha_ultimo_mantenimiento) return null;
        return now()->diffInDays($this->fecha_ultimo_mantenimiento);
    }

    public function necesitaMantenimiento()
    {
        if (!$this->dias_para_mantenimiento || !$this->fecha_ultimo_mantenimiento) {
            return false;
        }

        return $this->dias_desde_ultimo_mantenimiento >= $this->dias_para_mantenimiento;
    }

    public function getDiasParaProximoMantenimientoAttribute()
    {
        if (!$this->dias_para_mantenimiento || !$this->fecha_ultimo_mantenimiento) {
            return null;
        }

        $diasTranscurridos = $this->dias_desde_ultimo_mantenimiento;
        return max(0, $this->dias_para_mantenimiento - $diasTranscurridos);
    }

    public function getPorcentajeVidaUtilAttribute()
    {
        if (!$this->vida_util_meses || !$this->fecha_ultimo_mantenimiento) {
            return null;
        }

        $diasTranscurridos = $this->dias_desde_ultimo_mantenimiento;
        $diasTotales = $this->vida_util_meses * 30; // Aproximado

        if ($diasTotales <= 0) return null;

        return min(100, ($diasTranscurridos / $diasTotales) * 100);
    }

    // Verificar si la herramienta está próxima a vencer su vida útil
    public function vidaUtilProximaAVencer()
    {
        $porcentaje = $this->porcentaje_vida_util;
        return $porcentaje !== null && $porcentaje >= 80;
    }

    // Obtener estadísticas de la herramienta
    public function getEstadisticasAttribute()
    {
        $historial = $this->historial;

        return [
            'total_asignaciones' => $historial->count(),
            'asignaciones_activas' => $historial->whereNull('fecha_devolucion')->count(),
            'promedio_dias_uso' => $historial->whereNotNull('fecha_devolucion')->avg('duracion_dias'),
            'devoluciones_por_desgaste' => $historial->where('motivo_devolucion', HistorialHerramienta::MOTIVO_DEVOLUCION_DESGASTE)->count(),
            'devoluciones_por_danio' => $historial->where('motivo_devolucion', HistorialHerramienta::MOTIVO_DEVOLUCION_DANIO)->count(),
            'devoluciones_por_perdida' => $historial->where('motivo_devolucion', HistorialHerramienta::MOTIVO_DEVOLUCION_PERDIDA)->count(),
        ];
    }

    // Verificar si está en una asignación masiva activa
    public function estaEnAsignacionMasiva()
    {
        return $this->esta_en_asignacion_masiva;
    }

    // Obtener información completa de asignación (individual o masiva)
    public function getInfoAsignacionCompletaAttribute()
    {
        if ($this->estaEnAsignacionMasiva()) {
            $detalle = $this->asignacion_masiva_activa;
            return [
                'tipo' => 'masiva',
                'codigo' => $detalle->asignacionMasiva->codigo_asignacion,
                'tecnico' => $detalle->asignacionMasiva->tecnico,
                'fecha_asignacion' => $detalle->fecha_asignacion_individual,
                'proyecto' => $detalle->asignacionMasiva->proyecto_trabajo,
                'observaciones' => $detalle->observaciones_asignacion,
                'asignacion_id' => $detalle->asignacion_masiva_id
            ];
        } elseif ($this->asignacion_activa) {
            return [
                'tipo' => 'individual',
                'codigo' => 'IND-' . $this->asignacion_activa->id,
                'tecnico' => $this->tecnico,
                'fecha_asignacion' => $this->asignacion_activa->fecha_asignacion,
                'proyecto' => null,
                'observaciones' => $this->asignacion_activa->observaciones_entrega,
                'asignacion_id' => $this->asignacion_activa->id
            ];
        }

        return null;
    }

    // Liberar herramienta de cualquier tipo de asignación
    public function liberar($observaciones = null, $motivo = 'normal')
    {
        if ($this->estaEnAsignacionMasiva()) {
            $detalle = $this->asignacion_masiva_activa;

            switch ($motivo) {
                case 'perdida':
                    $detalle->marcarComoPerdida($observaciones);
                    break;
                case 'dañada':
                    $detalle->marcarComoDañada($observaciones);
                    break;
                default:
                    $detalle->marcarComoDevuelta($observaciones);
            }
        } else {
            // Lógica para asignación individual existente
            $this->update([
                'estado' => self::ESTADO_DISPONIBLE,
                'tecnico_id' => null,
                'fecha_recepcion' => now()
            ]);
        }

        return true;
    }

    // Obtener historial completo (individual y masivo)
    public function getHistorialCompletoAttribute()
    {
        $historialIndividual = $this->historial()
                                   ->where('tipo_asignacion', 'individual')
                                   ->with(['tecnico', 'asignadoPor', 'recibidoPor'])
                                   ->get();

        $historialMasivo = $this->historial()
                               ->where('tipo_asignacion', 'masiva')
                               ->with(['tecnico', 'asignadoPor', 'recibidoPor', 'asignacionMasiva'])
                               ->get();

        return $historialIndividual->merge($historialMasivo)
                                  ->sortByDesc('fecha_asignacion')
                                  ->values();
    }
}
