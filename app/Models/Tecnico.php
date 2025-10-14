<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Herramienta;

class Tecnico extends Model
{
    use HasFactory;

    protected $casts = [
        'margen_venta_productos' => 'decimal:2',
        'margen_venta_servicios' => 'decimal:2',
        'comision_instalacion' => 'decimal:2',
    ];

    protected $fillable = [
        'nombre',
        'apellido',
        'email',
        'telefono',
        'direccion',
        'activo',
        'user_id',
        'margen_venta_productos',
        'margen_venta_servicios',
        'comision_instalacion',
    ];

    // Relación con la tabla herramientas
    public function herramientas()
    {
        return $this->hasMany(Herramienta::class, 'tecnico_id');
    }

    // Relación con asignaciones de herramientas
    public function asignacionesHerramientas()
    {
        return $this->hasMany(AsignacionHerramienta::class);
    }

    // Relación con asignaciones masivas
    public function asignacionesMasivas()
    {
        return $this->hasMany(AsignacionMasiva::class);
    }

    // Relación con responsabilidad de herramientas
    public function responsabilidadHerramientas()
    {
        return $this->hasOne(ResponsabilidadHerramienta::class);
    }

    // Relación con historial de herramientas
    public function historialHerramientas()
    {
        return $this->hasMany(HistorialHerramienta::class);
    }

    // Herramientas actualmente asignadas (activas)
    public function herramientasAsignadas()
    {
        return $this->herramientas()->where('estado', Herramienta::ESTADO_ASIGNADA);
    }

    // Historial de herramientas activas (sin devolver)
    public function historialActivo()
    {
        return $this->historialHerramientas()->whereNull('fecha_devolucion');
    }

    // Historial de herramientas completado (devueltas)
    public function historialCompletado()
    {
        return $this->historialHerramientas()->whereNotNull('fecha_devolucion');
    }

    // Asignaciones masivas activas
    public function asignacionesMasivasActivas()
    {
        return $this->asignacionesMasivas()->where('estado', AsignacionMasiva::ESTADO_ACTIVA);
    }

    // Asignaciones masivas pendientes
    public function asignacionesMasivasPendientes()
    {
        return $this->asignacionesMasivas()->where('estado', AsignacionMasiva::ESTADO_PENDIENTE);
    }

    // Relación con usuario
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // Ventas realizadas por este técnico
    public function ventas()
    {
        return $this->morphMany(Venta::class, 'vendedor');
    }

    // Ganancia total de todas las ventas realizadas por este técnico
    public function getGananciaTotalAttribute()
    {
        return $this->ventas->sum('ganancia_total');
    }

    // Obtener resumen de herramientas del técnico
    public function getResumenHerramientasAttribute()
    {
        $herramientasAsignadas = $this->herramientasAsignadas;
        $responsabilidad = $this->responsabilidadHerramientas;

        return [
            'total_herramientas' => $herramientasAsignadas->count(),
            'valor_total' => $herramientasAsignadas->sum('costo_reemplazo') ?? 0,
            'herramientas_vencidas' => $herramientasAsignadas->filter(function ($h) {
                return $h->necesitaMantenimiento() || $h->vidaUtilProximaAVencer();
            })->count(),
            'asignaciones_masivas_activas' => $this->asignacionesMasivasActivas->count(),
            'dias_promedio_uso' => $responsabilidad->dias_promedio_uso ?? 0,
            'tiene_alertas' => $responsabilidad && count($responsabilidad->alertas) > 0
        ];
    }

    // Verificar si el técnico tiene herramientas vencidas
    public function tieneHerramientasVencidas()
    {
        return $this->herramientasAsignadas->filter(function ($herramienta) {
            return $herramienta->necesitaMantenimiento() || $herramienta->vidaUtilProximaAVencer();
        })->count() > 0;
    }

    // Obtener valor total de herramientas asignadas
    public function getValorTotalHerramientasAttribute()
    {
        return $this->herramientasAsignadas->sum('costo_reemplazo') ?? 0;
    }

    // Obtener herramientas que necesitan atención
    public function getHerramientasQueNecesitanAtencionAttribute()
    {
        return $this->herramientasAsignadas->filter(function ($herramienta) {
            return $herramienta->necesitaMantenimiento() || $herramienta->vidaUtilProximaAVencer();
        });
    }

    // Actualizar responsabilidades automáticamente
    public function actualizarResponsabilidades()
    {
        return ResponsabilidadHerramienta::actualizarParaTecnico($this->id);
    }
}
