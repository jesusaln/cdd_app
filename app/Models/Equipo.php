<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Equipo extends Model
{
    use SoftDeletes;

    protected $table = 'equipos';

    protected $fillable = [
        'codigo',
        'nombre',
        'marca',
        'modelo',
        'numero_serie',
        'descripcion',
        'especificaciones',
        'imagen',
        'precio_renta_mensual',
        'precio_compra',
        'fecha_adquisicion',
        'estado',
        'condicion',
        'ubicacion_fisica',
        'notas',
        'accesorios',
        'fecha_garantia',
        'proveedor',
    ];

    protected $casts = [
        'especificaciones' => 'array',
        'accesorios' => 'array',
        'fecha_adquisicion' => 'date',
        'fecha_garantia' => 'date',
    ];

    /**
     * Relación muchos a muchos con Rentas
     */
    public function rentas(): BelongsToMany
    {
        return $this->belongsToMany(Renta::class, 'equipo_renta', 'equipo_id', 'renta_id')
            ->withPivot('precio_mensual')
            ->withTimestamps();
    }
}
