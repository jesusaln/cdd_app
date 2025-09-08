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
        'tipo_persona',
        'tipo_identificacion',
        'identificacion',
        'curp',
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
        'activo',
        'notas',
    ];

    protected $casts = [
        'activo' => 'boolean',
    ];

    protected $attributes = [
        'activo' => true,
    ];

    protected $auditExclude = [
        'updated_at',
    ];

    /**
     * Boot model events
     */
    protected static function booted()
    {
        static::creating(function ($cliente) {
            if (is_null($cliente->activo)) {
                $cliente->activo = true;
            }
        });
    }

    /**
     * Relaciones
     */
    public function cotizaciones(): HasMany
    {
        return $this->hasMany(Cotizacion::class);
    }

    public function ventas(): HasMany
    {
        return $this->hasMany(Venta::class);
    }

    public function facturas(): HasMany
    {
        return $this->hasMany(Factura::class);
    }

    public function pedidos(): HasMany
    {
        return $this->hasMany(Pedido::class);
    }

    /**
     * Scopes
     */
    public function scopeActivos($query)
    {
        return $query->where('activo', true);
    }

    public function scopeInactivos($query)
    {
        return $query->where('activo', false);
    }

    /**
     * Accessors
     */
    protected $appends = ['direccion_completa'];

    public function getDireccionCompletaAttribute(): string
    {
        return trim(sprintf(
            '%s %s%s, %s, %s, %s, %s',
            $this->calle,
            $this->numero_exterior,
            $this->numero_interior ? " Int. {$this->numero_interior}" : '',
            $this->colonia,
            $this->codigo_postal,
            $this->municipio,
            $this->estado
        ));
    }
}
