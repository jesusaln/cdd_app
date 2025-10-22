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
        'problema_reportado',
        'prioridad',
        'estado',
        'evidencias',
        'foto_equipo',
        'foto_hoja_servicio',
        'foto_identificacion',
        'productos_utilizados',
        'productos_vendidos',
        'monto_productos_vendidos',
        'requiere_venta',
        'venta_id',
        'activo',
    ];

    protected $casts = [
        'fecha_hora' => 'datetime',
        'productos_utilizados' => 'array',
        'productos_vendidos' => 'array',
        'monto_productos_vendidos' => 'decimal:2',
        'requiere_venta' => 'boolean',
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

    /**
     * Productos utilizados durante la cita
     */
    public function productosUtilizados()
    {
        return $this->belongsToMany(Producto::class, 'cita_productos_utilizados')
            ->withPivot('cantidad', 'precio_unitario', 'tipo_uso', 'notas')
            ->withTimestamps();
    }

    /**
     * Productos que se vendieron durante la cita
     */
    public function productosVendidos()
    {
        return $this->belongsToMany(Producto::class, 'cita_productos_vendidos')
            ->withPivot('cantidad', 'precio_venta', 'subtotal', 'venta_id')
            ->withTimestamps();
    }

    /**
     * Servicios realizados durante la cita
     */
    public function serviciosRealizados()
    {
        return $this->belongsToMany(Servicio::class, 'cita_servicios')
            ->withPivot('cantidad', 'precio', 'subtotal', 'notas')
            ->withTimestamps();
    }

    /**
     * Venta generada desde la cita (si aplica)
     */
    public function venta()
    {
        return $this->belongsTo(Venta::class);
    }

    /**
     * Verificar si la cita puede ser modificada
     */
    public function puedeSerModificada(): bool
    {
        // No permitir modificar citas completadas con más de 7 días
        if ($this->estado === self::ESTADO_COMPLETADO) {
            return now()->diffInDays($this->updated_at) < 7;
        }

        // No permitir modificar citas canceladas
        if ($this->estado === self::ESTADO_CANCELADO) {
            return false;
        }

        return true;
    }

    /**
     * Verificar si la cita puede ser eliminada
     */
    public function puedeSerEliminada(): bool
    {
        // No permitir eliminar citas completadas con menos de 30 días
        if ($this->estado === self::ESTADO_COMPLETADO) {
            return now()->diffInDays($this->created_at) >= 30;
        }

        // No permitir eliminar citas en proceso
        if ($this->estado === self::ESTADO_EN_PROCESO) {
            return false;
        }

        return true;
    }

    /**
     * Obtener el siguiente estado válido
     */
    public function getSiguientesEstadosValidos(): array
    {
        return match($this->estado) {
            self::ESTADO_PENDIENTE => [self::ESTADO_EN_PROCESO, self::ESTADO_CANCELADO],
            self::ESTADO_EN_PROCESO => [self::ESTADO_COMPLETADO, self::ESTADO_CANCELADO],
            self::ESTADO_COMPLETADO => [], // No se puede cambiar de completado
            self::ESTADO_CANCELADO => [self::ESTADO_PENDIENTE], // Solo se puede reactivar
            default => []
        };
    }

    /**
     * Cambiar estado de la cita
     */
    public function cambiarEstado(string $nuevoEstado): bool
    {
        $estadosValidos = $this->getSiguientesEstadosValidos();

        if (!in_array($nuevoEstado, $estadosValidos)) {
            return false;
        }

        $this->estado = $nuevoEstado;
        $this->save();

        return true;
    }

    /**
     * Verificar si hay conflicto de horario
     */
    public static function hayConflictoHorario(int $tecnicoId, string $fechaHora, ?int $excludeId = null): bool
    {
        $query = self::where('tecnico_id', $tecnicoId)
            ->where('fecha_hora', $fechaHora)
            ->where('estado', '!=', self::ESTADO_CANCELADO);

        if ($excludeId) {
            $query->where('id', '!=', $excludeId);
        }

        return $query->exists();
    }
}
