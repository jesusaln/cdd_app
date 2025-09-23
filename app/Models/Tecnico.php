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
}
