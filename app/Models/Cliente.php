<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use OwenIt\Auditing\Auditable as AuditableTrait;
use OwenIt\Auditing\Contracts\Auditable as AuditableContract;

class Cliente extends Model implements AuditableContract
{
    use HasFactory, AuditableTrait;

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
        'activo',
        'notas',
        'acepta_marketing',
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

    /**
     * Get the ventas for the cliente.
     */
    public function ventas(): HasMany
    {
        return $this->hasMany(Venta::class);
    }

    /**
     * Get the facturas for the cliente.
     */
    public function facturas(): HasMany
    {
        return $this->hasMany(Factura::class);
    }


    public function pedidos(): HasMany
    {
        return $this->hasMany(Pedido::class);
    }
}
