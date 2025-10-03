<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class UnidadMedida extends Model
{
    use HasFactory;

    protected $table = 'unidades_medida';

    protected $fillable = [
        'nombre',
        'abreviatura',
        'descripcion',
        'activo',
    ];

    protected $casts = [
        'activo' => 'boolean',
    ];

    /**
     * Relación con productos
     */
    public function productos(): HasMany
    {
        return $this->hasMany(Producto::class);
    }

    /**
     * Scope para unidades activas
     */
    public function scopeActivas($query)
    {
        return $query->where('activo', true);
    }
}
