<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Auditable as AuditableTrait;
use OwenIt\Auditing\Contracts\Auditable as AuditableContract;

class Prestamo extends Model implements AuditableContract
{
    use HasFactory, AuditableTrait, SoftDeletes;

    protected $table = 'prestamos';

    protected $fillable = [
        'cliente_id',
        'monto_prestado',
        'tasa_interes_mensual',
        'numero_pagos',
        'frecuencia_pago',
        'fecha_inicio',
        'fecha_primer_pago',
        'monto_interes_total',
        'monto_total_pagar',
        'pago_periodico',
        'estado',
        'pagos_realizados',
        'pagos_pendientes',
        'monto_pagado',
        'monto_pendiente',
        'descripcion',
        'notas',
        'activo',
    ];

    protected $casts = [
        'monto_prestado' => 'decimal:2',
        'tasa_interes_mensual' => 'decimal:2',
        'monto_interes_total' => 'decimal:2',
        'monto_total_pagar' => 'decimal:2',
        'pago_periodico' => 'decimal:2',
        'monto_pagado' => 'decimal:2',
        'monto_pendiente' => 'decimal:2',
        'fecha_inicio' => 'date',
        'fecha_primer_pago' => 'date',
        'activo' => 'boolean',
    ];

    protected $attributes = [
        'estado' => 'activo',
        'pagos_realizados' => 0,
        'pagos_pendientes' => 0,
        'monto_pagado' => 0,
        'monto_pendiente' => 0,
        'activo' => true,
    ];

    protected $auditExclude = [
        'updated_at',
    ];

    /**
     * Relaciones
     */
    public function cliente(): BelongsTo
    {
        return $this->belongsTo(Cliente::class);
    }

    /**
     * Scopes
     */
    public function scopeActivos($query)
    {
        return $query->where('estado', 'activo');
    }

    public function scopeCompletados($query)
    {
        return $query->where('estado', 'completado');
    }

    public function scopeCancelados($query)
    {
        return $query->where('estado', 'cancelado');
    }

    public function scopePorCliente($query, $clienteId)
    {
        return $query->where('cliente_id', $clienteId);
    }

    /**
     * Accessors y Mutators
     */
    public function getEstadoTextoAttribute(): string
    {
        return match($this->estado) {
            'activo' => 'Activo',
            'completado' => 'Completado',
            'cancelado' => 'Cancelado',
            default => 'Desconocido'
        };
    }

    public function getFrecuenciaTextoAttribute(): string
    {
        return match($this->frecuencia_pago) {
            'semanal' => 'Semanal',
            'quincenal' => 'Quincenal',
            'mensual' => 'Mensual',
            default => 'Desconocido'
        };
    }

    public function getProgresoAttribute(): float
    {
        if ($this->numero_pagos == 0) return 0;
        return round(($this->pagos_realizados / $this->numero_pagos) * 100, 2);
    }

    public function setTasaInteresMensualAttribute($value): void
    {
        $this->attributes['tasa_interes_mensual'] = max(0, min(100, (float) $value));
    }

    /**
     * Métodos útiles
     */
    public function calcularPagos(): array
    {
        $capital = $this->monto_prestado;
        $tasaMensual = $this->tasa_interes_mensual / 100; // Ahora es mensual directamente
        $periodos = $this->numero_pagos;

        if ($tasaMensual == 0) {
            // Sin interés - pago fijo
            $pago = $capital / $periodos;
            return [
                'pago_periodico' => round($pago, 2),
                'interes_total' => 0,
                'total_pagar' => round($capital, 2),
                'tasa_mensual' => 0,
                'tipo_calculo' => 'sin_interes',
            ];
        }

        // Fórmula de amortización francesa con tasa mensual directa
        if ($tasaMensual > 0) {
            // Usar precisión alta para evitar errores de redondeo
            $factor = pow(1 + $tasaMensual, $periodos);
            $pago = $capital * ($tasaMensual * $factor) / ($factor - 1);
            $interesTotal = ($pago * $periodos) - $capital;

            return [
                'pago_periodico' => round($pago, 2),
                'interes_total' => round($interesTotal, 2),
                'total_pagar' => round($pago * $periodos, 2),
                'tasa_mensual' => round($tasaMensual * 100, 4),
                'factor_compuesto' => round($factor, 6),
                'tipo_calculo' => 'amortizacion_francesa_mensual',
                'detalles_calculo' => [
                    'capital' => $capital,
                    'tasa_mensual' => $tasaMensual,
                    'periodos' => $periodos,
                    'frecuencia' => $this->frecuencia_pago,
                ]
            ];
        }

        return [
            'pago_periodico' => 0,
            'interes_total' => 0,
            'total_pagar' => $capital,
            'tasa_mensual' => 0,
            'tipo_calculo' => 'error',
        ];
    }

    /**
     * Calcular pagos con interés simple (opción alternativa)
     */
    public function calcularPagosInteresSimple(): array
    {
        $capital = $this->monto_prestado;
        $tasaMensual = $this->tasa_interes_mensual / 100;
        $periodos = $this->numero_pagos;

        if ($tasaMensual == 0) {
            $pago = $capital / $periodos;
            return [
                'pago_periodico' => round($pago, 2),
                'interes_total' => 0,
                'total_pagar' => round($capital, 2),
                'tipo_calculo' => 'sin_interes',
            ];
        }

        // Cálculo con interés simple mensual
        $interesTotal = $capital * $tasaMensual * $periodos;
        $totalPagar = $capital + $interesTotal;

        // Pago fijo: amortización de capital + interés del período
        $amortizacionCapital = $capital / $periodos;
        $interesPeriodo = $interesTotal / $periodos;
        $pago = $amortizacionCapital + $interesPeriodo;

        return [
            'pago_periodico' => round($pago, 2),
            'interes_total' => round($interesTotal, 2),
            'total_pagar' => round($totalPagar, 2),
            'tipo_calculo' => 'interes_simple_mensual',
        ];
    }

    public function actualizarEstado(): void
    {
        if ($this->pagos_realizados >= $this->numero_pagos && $this->monto_pagado >= $this->monto_total_pagar) {
            $this->estado = 'completado';
        } elseif ($this->monto_pendiente <= 0) {
            $this->estado = 'completado';
        }

        $this->pagos_pendientes = max(0, $this->numero_pagos - $this->pagos_realizados);
        $this->monto_pendiente = max(0, $this->monto_total_pagar - $this->monto_pagado);

        $this->save();
    }

    public function puedeSerEliminado(): bool
    {
        return $this->estado === 'cancelado' || ($this->pagos_realizados === 0 && $this->monto_pagado === 0);
    }
}
