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
}