<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EntregaDinero extends Model
{
    protected $table = 'entregas_dinero';

    protected $fillable = [
        'user_id',
        'fecha_entrega',
        'monto_efectivo',
        'monto_cheques',
        'monto_tarjetas',
        'total',
        'estado',
        'notas',
        'tipo_origen',
        'id_origen',
        'recibido_por',
        'fecha_recibido',
        'notas_recibido',
        'entregado_responsable',
        'fecha_entregado_responsable',
        'responsable_organizacion',
        'notas_entrega_responsable',
    ];

    protected $casts = [
        'fecha_entrega' => 'date',
        'fecha_recibido' => 'datetime',
        'fecha_entregado_responsable' => 'datetime',
        'monto_efectivo' => 'decimal:2',
        'monto_cheques' => 'decimal:2',
        'monto_tarjetas' => 'decimal:2',
        'total' => 'decimal:2',
        'entregado_responsable' => 'boolean',
    ];

    /**
     * Relación con el usuario que trae el dinero.
     */
    public function usuario(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Relación con el usuario que recibió el dinero.
     */
    public function recibidoPor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'recibido_por');
    }

    /**
     * Scope para entregas pendientes.
     */
    public function scopePendientes($query)
    {
        return $query->where('estado', 'pendiente');
    }

    /**
     * Scope para entregas recibidas.
     */
    public function scopeRecibidas($query)
    {
        return $query->where('estado', 'recibido');
    }

    /**
     * Marcar como entregado al responsable de la organización.
     */
    public function marcarEntregadoResponsable(string $responsableNombre, string $notas = null): void
    {
        $this->update([
            'entregado_responsable' => true,
            'fecha_entregado_responsable' => now(),
            'responsable_organizacion' => $responsableNombre,
            'notas_entrega_responsable' => $notas,
        ]);
    }

    /**
     * Scope para entregas entregadas al responsable.
     */
    public function scopeEntregadasResponsable($query)
    {
        return $query->where('entregado_responsable', true);
    }

    /**
     * Scope para entregas pendientes de entregar al responsable.
     */
    public function scopePendientesResponsable($query)
    {
        return $query->where('estado', 'recibido')
                    ->where(function ($q) {
                        $q->where('entregado_responsable', false)
                          ->orWhereNull('entregado_responsable');
                    });
    }
}
