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
    ];

    protected $casts = [
        'fecha' => 'date',
        'proximo_mantenimiento' => 'date',
        'costo' => 'decimal:2',
    ];

    // Constantes para los estados
    const ESTADO_COMPLETADO = 'completado';
    const ESTADO_PENDIENTE = 'pendiente';
    const ESTADO_EN_PROCESO = 'en_proceso';

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

        return now()->diffInDays($this->proximo_mantenimiento, false);
    }
}
