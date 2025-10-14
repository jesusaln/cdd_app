<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Auditable as AuditableTrait;
use OwenIt\Auditing\Contracts\Auditable as AuditableContract;

class PagoPrestamo extends Model implements AuditableContract
{
    use HasFactory, AuditableTrait, SoftDeletes;

    protected $table = 'pagos_prestamos';

    protected $fillable = [
        'prestamo_id',
        'numero_pago',
        'monto_programado',
        'monto_pagado',
        'fecha_programada',
        'fecha_pago',
        'fecha_registro',
        'estado',
        'dias_atraso',
        'notas',
        'metodo_pago',
        'referencia',
        'confirmado',
    ];

    protected $casts = [
        'monto_programado' => 'decimal:2',
        'monto_pagado' => 'decimal:2',
        'fecha_programada' => 'date',
        'fecha_pago' => 'date',
        'fecha_registro' => 'date',
        'confirmado' => 'boolean',
    ];

    protected $attributes = [
        'estado' => 'pendiente',
        'dias_atraso' => 0,
        'confirmado' => false,
    ];

    protected $auditExclude = [
        'updated_at',
    ];

    /**
     * Relaciones
     */
    public function prestamo(): BelongsTo
    {
        return $this->belongsTo(Prestamo::class);
    }

    public function historialPagos(): HasMany
    {
        return $this->hasMany(HistorialPagoPrestamo::class, 'pago_prestamo_id');
    }

    /**
     * Scopes
     */
    public function scopePendientes($query)
    {
        return $query->where('estado', 'pendiente');
    }

    public function scopePagados($query)
    {
        return $query->where('estado', 'pagado');
    }

    public function scopeAtrasados($query)
    {
        return $query->where('estado', 'atrasado');
    }

    public function scopeParciales($query)
    {
        return $query->where('estado', 'parcial');
    }

    public function scopePorPrestamo($query, $prestamoId)
    {
        return $query->where('prestamo_id', $prestamoId);
    }

    public function scopeVencidos($query)
    {
        return $query->where('fecha_programada', '<', now())
                    ->whereIn('estado', ['pendiente', 'parcial']);
    }

    /**
     * Accessors y Mutators
     */
    public function getEstadoTextoAttribute(): string
    {
        return match($this->estado) {
            'pendiente' => 'Pendiente',
            'pagado' => 'Pagado',
            'atrasado' => 'Atrasado',
            'parcial' => 'Pago Parcial',
            default => 'Desconocido'
        };
    }

    public function getEstaVencidoAttribute(): bool
    {
        return $this->fecha_programada < now()->toDateString() && !in_array($this->estado, ['pagado']);
    }

    public function getMontoPagadoAttribute(): float
    {
        // Calcular desde el historial si existe, sino usar el campo directo
        if ($this->historialPagos()->exists()) {
            return $this->historialPagos()->sum('monto_pagado');
        }
        return $this->attributes['monto_pagado'] ?? 0;
    }

    public function getMontoPendienteAttribute(): float
    {
        return max(0, $this->monto_programado - $this->monto_pagado);
    }

    public function setFechaRegistroAttribute($value): void
    {
        $this->attributes['fecha_registro'] = $value ?: now()->toDateString();
    }

    /**
     * Métodos útiles
     */
    public function agregarPago(float $monto, string $fechaPago = null, string $metodoPago = null, string $referencia = null): bool
    {
        if ($monto <= 0) {
            return false; // No se puede agregar pago con monto 0 o negativo
        }

        $fechaPago = $fechaPago ?: now()->toDateString();

        // Crear registro en el historial
        HistorialPagoPrestamo::create([
            'pago_prestamo_id' => $this->id,
            'prestamo_id' => $this->prestamo_id,
            'monto_pagado' => $monto,
            'fecha_pago' => $fechaPago,
            'fecha_registro' => now()->toDateString(),
            'metodo_pago' => $metodoPago,
            'referencia' => $referencia,
            'confirmado' => true,
        ]);

        // Recalcular el estado basado en el historial
        $this->recalcularEstado();

        // Actualizar el préstamo relacionado
        $this->prestamo->actualizarEstado();

        return true;
    }

    /**
     * Recalcular estado basado en el historial de pagos
     */
    public function recalcularEstado(): void
    {
        $totalPagado = $this->historialPagos()->sum('monto_pagado');
        $diasAtraso = 0;

        if ($totalPagado > 0) {
            // Calcular días de atraso basado en el último pago
            $ultimoPago = $this->historialPagos()->orderBy('fecha_pago', 'desc')->first();
            if ($ultimoPago) {
                $diasAtraso = max(0, (strtotime($ultimoPago->fecha_pago) - strtotime($this->fecha_programada)) / 86400);
            }
        }

        // Determinar estado
        if ($totalPagado >= $this->monto_programado) {
            $estado = 'pagado';
        } elseif ($totalPagado > 0) {
            $estado = 'parcial';
        } else {
            $estado = 'pendiente';
        }

        $this->update([
            'monto_pagado' => $totalPagado,
            'fecha_pago' => $totalPagado > 0 ? $ultimoPago->fecha_pago : null,
            'fecha_registro' => now()->toDateString(),
            'estado' => $estado,
            'dias_atraso' => intval($diasAtraso),
        ]);
    }

    /**
     * Marcar como pagado completamente (método anterior para compatibilidad)
     */
    public function marcarComoPagado(float $monto, string $fechaPago = null): bool
    {
        $fechaPago = $fechaPago ?: now()->toDateString();

        // Calcular días de atraso
        $diasAtraso = max(0, (strtotime($fechaPago) - strtotime($this->fecha_programada)) / 86400);

        // Determinar estado
        if ($monto >= $this->monto_programado) {
            $estado = 'pagado';
        } elseif ($monto > 0) {
            $estado = 'parcial';
        } else {
            return false; // No se puede marcar como pagado con monto 0 o negativo
        }

        $this->update([
            'monto_pagado' => $monto,
            'fecha_pago' => $fechaPago,
            'fecha_registro' => now()->toDateString(),
            'estado' => $estado,
            'dias_atraso' => intval($diasAtraso),
        ]);

        // Actualizar el préstamo relacionado
        $this->prestamo->actualizarEstado();

        return true;
    }

    public function calcularDiasAtraso(): int
    {
        if ($this->estado === 'pagado' || !$this->fecha_pago) {
            return 0;
        }

        return max(0, (strtotime($this->fecha_pago) - strtotime($this->fecha_programada)) / 86400);
    }
}
