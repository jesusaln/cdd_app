<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CuentasPorCobrar extends Model
{
    use HasFactory;

    protected $table = 'cuentas_por_cobrar';

    protected $fillable = [
        'venta_id',
        'monto_total',
        'monto_pagado',
        'monto_pendiente',
        'fecha_vencimiento',
        'estado',
        'notas',
    ];

    protected $casts = [
        'monto_total' => 'decimal:2',
        'monto_pagado' => 'decimal:2',
        'monto_pendiente' => 'decimal:2',
        'fecha_vencimiento' => 'date',
    ];

    /**
     * @return BelongsTo<Venta, CuentasPorCobrar>
     */
    public function venta(): BelongsTo
    {
        return $this->belongsTo(Venta::class);
    }

    public function estaVencida(): bool
    {
        return $this->fecha_vencimiento && $this->fecha_vencimiento->isPast() && $this->estado !== 'pagado';
    }

    public function calcularPendiente(): float
    {
        return (float) ($this->monto_total - $this->monto_pagado);
    }

    public function actualizarEstado(): void
    {
        $pendiente = $this->calcularPendiente();

        if ($pendiente <= 0) {
            $this->estado = 'pagado';
        } elseif ($this->monto_pagado > 0) {
            $this->estado = 'parcial';
        } elseif ($this->estaVencida()) {
            $this->estado = 'vencido';
        } else {
            $this->estado = 'pendiente';
        }

        $this->monto_pendiente = max(0, $pendiente);
        $this->save();
    }

    public function registrarPago(float $monto, ?string $notas = null): void
    {
        $this->monto_pagado += $monto;
        if ($notas) {
            $this->notas = ($this->notas ? $this->notas . "\n" : '') . "Pago recibido: {$monto} - {$notas}";
        }
        $this->actualizarEstado();
    }

    public function scopePendientes($query)
    {
        return $query->whereIn('estado', ['pendiente', 'parcial', 'vencido']);
    }

    public function scopeVencidas($query)
    {
        return $query->where('estado', 'vencido')
            ->orWhere(function ($q) {
                $q->where('fecha_vencimiento', '<', now())
                  ->where('estado', '!=', 'pagado');
            });
    }

    /**
     * @return HasMany<RecordatorioCobranza, CuentasPorCobrar>
     */
    public function recordatorios()
    {
        return $this->hasMany(RecordatorioCobranza::class);
    }

    /**
     * Verificar si necesita enviar recordatorio
     */
    public function necesitaRecordatorio(): bool
    {
        if ($this->estado === 'pagado' || !$this->fecha_vencimiento) {
            return false;
        }

        $hoy = now();
        $diasDesdeVencimiento = $hoy->diffInDays($this->fecha_vencimiento, false);

        // Si no está vencida aún, no enviar recordatorio
        if ($diasDesdeVencimiento < 0) {
            return false;
        }

        // Si está vencida hoy (día 0), enviar recordatorio de vencimiento
        if ($diasDesdeVencimiento == 0) {
            return !RecordatorioCobranza::where('cuenta_por_cobrar_id', $this->id)
                ->where('tipo_recordatorio', 'vencimiento')
                ->whereDate('fecha_envio', $hoy->toDateString())
                ->exists();
        }

        // Si pasó 1 día desde el vencimiento, enviar recordatorio del día siguiente
        if ($diasDesdeVencimiento == 1) {
            return !RecordatorioCobranza::where('cuenta_por_cobrar_id', $this->id)
                ->where('tipo_recordatorio', 'dia_siguiente')
                ->whereDate('fecha_envio', $hoy->toDateString())
                ->exists();
        }

        // Si pasaron más días, verificar si necesita recordatorio cada 3 días
        if ($diasDesdeVencimiento >= 2) {
            $diasDesdeUltimoRecordatorio = RecordatorioCobranza::where('cuenta_por_cobrar_id', $this->id)
                ->where('tipo_recordatorio', 'cada_3_dias')
                ->where('enviado', true)
                ->latest('fecha_envio')
                ->first();

            if (!$diasDesdeUltimoRecordatorio) {
                // Nunca se envió recordatorio cada 3 días, enviar el primero
                return true;
            }

            $diasDesdeUltimo = $hoy->diffInDays($diasDesdeUltimoRecordatorio->fecha_envio, false);
            return $diasDesdeUltimo >= 3;
        }

        return false;
    }

    /**
     * Programar próximo recordatorio
     */
    public function programarRecordatorio(): ?RecordatorioCobranza
    {
        if (!$this->necesitaRecordatorio()) {
            return null;
        }

        $hoy = now();
        $diasDesdeVencimiento = $hoy->diffInDays($this->fecha_vencimiento, false);

        if ($diasDesdeVencimiento == 0) {
            $tipo = 'vencimiento';
            $proximo = $hoy->copy()->addDay(); // Mañana
        } elseif ($diasDesdeVencimiento == 1) {
            $tipo = 'dia_siguiente';
            $proximo = $hoy->copy()->addDays(3); // En 3 días
        } else {
            $tipo = 'cada_3_dias';
            $proximo = $hoy->copy()->addDays(3); // En 3 días
        }

        $intentosAnteriores = RecordatorioCobranza::where('cuenta_por_cobrar_id', $this->id)->count();

        return $this->recordatorios()->create([
            'tipo_recordatorio' => $tipo,
            'fecha_envio' => $hoy,
            'fecha_proximo_recordatorio' => $proximo,
            'enviado' => false,
            'numero_intento' => $intentosAnteriores + 1,
        ]);
    }
}
