<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Carbon\Carbon;

class Cita extends Model
{
    use HasFactory, SoftDeletes;

    // Constantes para estados
    const ESTADO_PENDIENTE = 'pendiente';
    const ESTADO_EN_PROCESO = 'en_proceso';
    const ESTADO_COMPLETADO = 'completado';
    const ESTADO_CANCELADO = 'cancelado';

    // Constantes para prioridades
    const PRIORIDAD_BAJA = 'baja';
    const PRIORIDAD_MEDIA = 'media';
    const PRIORIDAD_ALTA = 'alta';
    const PRIORIDAD_URGENTE = 'urgente';

    protected $fillable = [
        'tecnico_id',
        'cliente_id',
        'tipo_servicio',
        'fecha_hora',
        'descripcion',
        'tipo_equipo',
        'marca_equipo',
        'modelo_equipo',
        'problema_reportado',
        'prioridad',
        'estado',
        'evidencias',
        'foto_equipo',
        'foto_hoja_servicio',
        'foto_identificacion',
    ];

    protected $casts = [
        'fecha_hora' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    // Scopes útiles
    public function scopePendientes($query)
    {
        return $query->where('estado', self::ESTADO_PENDIENTE);
    }

    public function scopeEnProceso($query)
    {
        return $query->where('estado', self::ESTADO_EN_PROCESO);
    }

    public function scopeCompletadas($query)
    {
        return $query->where('estado', self::ESTADO_COMPLETADO);
    }

    public function scopeCanceladas($query)
    {
        return $query->where('estado', self::ESTADO_CANCELADO);
    }

    public function scopePorTecnico($query, $tecnicoId)
    {
        return $query->where('tecnico_id', $tecnicoId);
    }

    public function scopePorCliente($query, $clienteId)
    {
        return $query->where('cliente_id', $clienteId);
    }

    public function scopeProximas($query)
    {
        return $query->where('fecha_hora', '>', now())->orderBy('fecha_hora');
    }

    public function scopeHoy($query)
    {
        return $query->whereDate('fecha_hora', today());
    }

    public function scopeEstaSemana($query)
    {
        return $query->whereBetween('fecha_hora', [
            now()->startOfWeek(),
            now()->endOfWeek()
        ]);
    }

    // Métodos de acceso
    public function getEstadoColorAttribute()
    {
        return match($this->estado) {
            self::ESTADO_PENDIENTE => 'yellow',
            self::ESTADO_EN_PROCESO => 'blue',
            self::ESTADO_COMPLETADO => 'green',
            self::ESTADO_CANCELADO => 'red',
            default => 'gray',
        };
    }

    public function getPrioridadColorAttribute()
    {
        return match($this->prioridad) {
            self::PRIORIDAD_BAJA => 'green',
            self::PRIORIDAD_MEDIA => 'yellow',
            self::PRIORIDAD_ALTA => 'orange',
            self::PRIORIDAD_URGENTE => 'red',
            default => 'gray',
        };
    }

    public function getEsPasadaAttribute()
    {
        return $this->fecha_hora->isPast();
    }

    public function getEsHoyAttribute()
    {
        return $this->fecha_hora->isToday();
    }

    public function cliente()
    {
        return $this->belongsTo(Cliente::class);
    }

    public function tecnico()
    {
        return $this->belongsTo(Tecnico::class);
    }
}
