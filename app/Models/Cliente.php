<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Cliente extends Model
{
    use HasFactory;

    protected $fillable = [
        'nombre_razon_social',
        'rfc',
        'regimen_fiscal',
        'uso_cfdi',
        'email',
        'telefono',
        'calle',
        'numero_exterior',
        'numero_interior',
        'colonia',
        'codigo_postal',
        'municipio',
        'estado',
        'pais',
        'tipo_persona',
        'requiere_factura',
        'activo',
        'notas', // ✅ faltaba
        'acepta_marketing', // ✅ faltaba
    ];

    protected $casts = [
        'activo' => 'boolean',
        'acepta_marketing' => 'boolean',
    ];

    /**
     * Get the cotizaciones for the cliente.
     */
    public function cotizaciones(): HasMany
    {
        return $this->hasMany(Cotizacion::class);
    }
}
