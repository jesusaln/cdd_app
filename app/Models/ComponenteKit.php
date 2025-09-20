<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class ComponenteKit extends Model
{
    use SoftDeletes;

    protected $table = 'componentes_kit';

    protected $fillable = [
        'tipo',
        'nombre',
        'descripcion',
        'codigo',
        'numero_serie',
        'marca',
        'modelo',
        'precio_renta_mensual',
        'precio_compra',
        'estado',
        'condicion',
        'fecha_adquisicion',
        'ubicacion_fisica',
        'notas',
        'fecha_garantia',
        'proveedor',
    ];

    protected $casts = [
        'fecha_adquisicion' => 'date',
        'fecha_garantia' => 'date',
        'precio_renta_mensual' => 'decimal:2',
        'precio_compra' => 'decimal:2',
    ];

    /**
     * Relación con rentas a través de la tabla pivote
     */
    public function rentas(): BelongsToMany
    {
        return $this->belongsToMany(Renta::class, 'renta_componentes_kit')
            ->withPivot('precio_mensual', 'notas')
            ->withTimestamps();
    }
}
