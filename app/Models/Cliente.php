<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use OwenIt\Auditing\Auditable as AuditableTrait;
use OwenIt\Auditing\Contracts\Auditable as AuditableContract;

class Cliente extends Model implements AuditableContract
{
    use HasFactory, AuditableTrait;

    protected $table = 'clientes';

    protected $fillable = [
        'nombre_razon_social',
        'tipo_persona',
        'tipo_identificacion',
        'identificacion',
        'curp',
        'rfc',
        'regimen_fiscal',  // clave SAT
        'uso_cfdi',        // clave SAT
        'email',
        'telefono',
        'calle',
        'numero_exterior',
        'numero_interior',
        'colonia',
        'codigo_postal',
        'municipio',
        'estado',          // clave SAT de 3 letras (AGU, SON, etc.)
        'pais',            // 'MX'
        'activo',
        'notas',
    ];

    protected $casts = [
        'activo' => 'boolean',
    ];

    protected $attributes = [
        'activo' => true,
        'pais'   => 'MX',
    ];

    protected $auditExclude = [
        'updated_at',
    ];

    /**
     * Atributos calculados que se anexan al JSON
     */
    protected $appends = [
        'direccion_completa',
        'estado_nombre',
        'regimen_descripcion',
        'uso_cfdi_descripcion',
    ];

    /**
     * Boot model events
     */
    protected static function booted()
    {
        static::creating(function (Cliente $cliente) {
            if (is_null($cliente->activo)) {
                $cliente->activo = true;
            }
            if (empty($cliente->pais)) {
                $cliente->pais = 'MX';
            }
        });
    }

    // ------------------------------------------------------------------
    // Relaciones propias
    // ------------------------------------------------------------------

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

    // ------------------------------------------------------------------
    // Relaciones a catálogos SAT (por clave)
    // ------------------------------------------------------------------

    public function regimen(): BelongsTo
    {
        // clave local 'regimen_fiscal' -> clave primaria 'clave'
        return $this->belongsTo(SatRegimenFiscal::class, 'regimen_fiscal', 'clave');
    }

    public function uso(): BelongsTo
    {
        // clave local 'uso_cfdi' -> 'clave'
        return $this->belongsTo(SatUsoCfdi::class, 'uso_cfdi', 'clave');
    }

    public function estadoSat(): BelongsTo
    {
        // requiere un modelo App\Models\SatEstado con PK 'clave'
        return $this->belongsTo(SatEstado::class, 'estado', 'clave');
    }

    // ------------------------------------------------------------------
    // Scopes
    // ------------------------------------------------------------------

    public function scopeActivos($query)
    {
        return $query->where('activo', true);
    }

    public function scopeInactivos($query)
    {
        return $query->where('activo', false);
    }

    public function scopeBuscar($query, ?string $q)
    {
        $q = trim((string) $q);
        if ($q === '') return $query;

        return $query->where(function ($w) use ($q) {
            $w->where('nombre_razon_social', 'like', "%{$q}%")
                ->orWhere('rfc', 'like', "%{$q}%")
                ->orWhere('email', 'like', "%{$q}%");
        });
    }

    // ------------------------------------------------------------------
    // Mutadores / Normalizadores
    // ------------------------------------------------------------------

    public function setRfcAttribute($value): void
    {
        $this->attributes['rfc'] = mb_strtoupper(trim((string) $value), 'UTF-8');
    }

    public function setCurpAttribute($value): void
    {
        $this->attributes['curp'] = $value ? mb_strtoupper(trim((string) $value), 'UTF-8') : null;
    }

    public function setEmailAttribute($value): void
    {
        $this->attributes['email'] = mb_strtolower(trim((string) $value), 'UTF-8');
    }

    public function setCodigoPostalAttribute($value): void
    {
        // Deja solo dígitos y rellena a 5
        $digits = preg_replace('/\D+/', '', (string) $value);
        $this->attributes['codigo_postal'] = str_pad(substr($digits, 0, 5), 5, '0', STR_PAD_LEFT);
    }

    public function setEstadoAttribute($value): void
    {
        // Asegura clave de 3 letras en mayúsculas (AGU, SON, etc.)
        $this->attributes['estado'] = $value ? mb_strtoupper(trim((string) $value), 'UTF-8') : null;
    }

    public function setPaisAttribute($value): void
    {
        // Forzar MX si viene vacío/incorrecto
        $v = mb_strtoupper(trim((string) $value), 'UTF-8');
        $this->attributes['pais'] = ($v === 'MX' || $v === '') ? 'MX' : $v;
    }

    // ------------------------------------------------------------------
    // Accessors
    // ------------------------------------------------------------------

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

    public function getEstadoNombreAttribute(): ?string
    {
        // Devuelve "Sonora" si está cargada la relación; si no, null
        return optional($this->estadoSat)->nombre;
    }

    public function getRegimenDescripcionAttribute(): ?string
    {
        return optional($this->regimen)->descripcion;
    }

    public function getUsoCfdiDescripcionAttribute(): ?string
    {
        return optional($this->uso)->descripcion;
    }
}
