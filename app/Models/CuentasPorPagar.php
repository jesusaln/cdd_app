<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CuentasPorPagar extends Model
{
    use HasFactory;

    protected $table = 'cuentas_por_pagar';

    protected $fillable = [
        'compra_id',
        'monto_total',
        'monto_pagado',
        'monto_pendiente',
        'fecha_vencimiento',
        'estado',
        'notas',
        'pagado',
        'metodo_pago',
        'fecha_pago',
        'pagado_por',
        'notas_pago',
    ];

    protected $casts = [
        'monto_total' => 'decimal:2',
        'monto_pagado' => 'decimal:2',
        'monto_pendiente' => 'decimal:2',
        'fecha_vencimiento' => 'date',
        'fecha_pago' => 'datetime',
        'pagado' => 'boolean',
    ];

    /**
     * @return BelongsTo<Compra, CuentasPorPagar>
     */
    public function compra(): BelongsTo
    {
        return $this->belongsTo(Compra::class);
    }

    /**
     * Verifica si la cuenta está vencida
     */
    public function estaVencida(): bool
    {
        return $this->fecha_vencimiento && $this->fecha_vencimiento->isPast() && $this->estado !== 'pagado';
    }

    /**
     * Calcula el monto pendiente
     */
    public function calcularPendiente(): float
    {
        return $this->monto_total - $this->monto_pagado;
    }

    /**
     * Actualiza el estado basado en los pagos
     */
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

        $this->monto_pendiente = $pendiente;
        $this->save();
    }

    /**
     * Registra un pago parcial
     */
    public function registrarPago(float $monto, string $notas = null): void
    {
        $this->monto_pagado += $monto;
        if ($notas) {
            $this->notas = ($this->notas ? $this->notas . "\n" : '') . "Pago: {$monto} - {$notas}";
        }
        $this->actualizarEstado();
    }

    /**
     * Marca la cuenta como pagada con información detallada
     */
    public function marcarPagado(string $metodoPago, string $notas = null): void
    {
        $this->update([
            'pagado' => true,
            'estado' => 'pagado',
            'metodo_pago' => $metodoPago,
            'fecha_pago' => now(),
            'pagado_por' => auth()->id(),
            'notas_pago' => $notas,
            'monto_pagado' => $this->monto_total,
            'monto_pendiente' => 0,
        ]);
    }

    /**
     * Relación con el usuario que pagó
     */
    public function pagadoPor()
    {
        return $this->belongsTo(User::class, 'pagado_por');
    }

    /**
     * Scope para cuentas pendientes
     */
    public function scopePendientes($query)
    {
        return $query->whereIn('estado', ['pendiente', 'parcial', 'vencido']);
    }

    /**
     * Scope para cuentas vencidas
     */
    public function scopeVencidas($query)
    {
        return $query->where('estado', 'vencido')
                    ->orWhere(function ($q) {
                        $q->where('fecha_vencimiento', '<', now())
                          ->where('estado', '!=', 'pagado');
                    });
    }
}
