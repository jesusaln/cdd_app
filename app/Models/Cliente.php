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
        'regimen_fiscal',  // clave SAT c_RegimenFiscal
        'uso_cfdi',        // clave SAT c_UsoCFDI
        'domicilio_fiscal_cp', // Código postal del domicilio fiscal (CFDI 4.0)
        'residencia_fiscal',   // c_Pais para extranjeros (CFDI 4.0)
        'num_reg_id_trib',     // Número de registro fiscal extranjero (CFDI 4.0)
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

        // ------ Facturación / Facturapi ------
        'facturapi_customer_id',
        'cfdi_default_use',
        'payment_form_default',
    ];

    protected $casts = [
        'activo' => 'boolean',
    ];

    protected $attributes = [
        'activo' => true,
        // 'pais' se deja sin valor por defecto para permitir extranjeros
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
        // Efectivos para Facturapi (con fallback a defaults de config):
        'cfdi_use_effective',
        'payment_form_effective',
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
            // No forzar país a MX para permitir clientes extranjeros
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

    public function rentas(): HasMany
    {
        return $this->hasMany(Renta::class);
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
        return $query->where(function ($q) {
            $q->where('activo', true)->orWhereNull('activo');
        });
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
        // Normalizar RFC: mayúsculas, sin espacios/guiones
        $normalized = mb_strtoupper(trim((string) $value), 'UTF-8');
        $normalized = str_replace([' ', '-', '_'], '', $normalized);
        $this->attributes['rfc'] = $normalized;
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
        // Normalizar código de país a mayúsculas, permitir vacío
        $this->attributes['pais'] = $value ? mb_strtoupper(trim((string) $value), 'UTF-8') : null;
    }

    public function setDomicilioFiscalCpAttribute($value): void
    {
        // Código postal del domicilio fiscal - debe ser válido según SAT
        $digits = preg_replace('/\D+/', '', (string) $value);
        $this->attributes['domicilio_fiscal_cp'] = str_pad(substr($digits, 0, 5), 5, '0', STR_PAD_LEFT);
    }

    public function setResidenciaFiscalAttribute($value): void
    {
        // Código de país según catálogo c_Pais del SAT (solo para extranjeros)
        $this->attributes['residencia_fiscal'] = $value ? mb_strtoupper(trim((string) $value), 'UTF-8') : null;
    }

    public function setNumRegIdTribAttribute($value): void
    {
        // Número de registro fiscal extranjero (máximo 40 caracteres)
        $this->attributes['num_reg_id_trib'] = $value ? mb_strtoupper(trim((string) $value), 'UTF-8') : null;
    }

    // ------------------------------------------------------------------
    // Accessors (incluye descripciones SAT y helpers Facturapi)
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

    /**
     * Helpers para facturación (con fallback a config/facturapi.php)
     * - cfdi_use_effective: usa cfdi_default_use, si no uso_cfdi, si no config('facturapi.defaults.use', 'G03')
     * - payment_form_effective: usa payment_form_default o config('facturapi.defaults.payment_form', '03')
     */
    public function getCfdiUseEffectiveAttribute(): string
    {
        return $this->cfdi_default_use
            ?: $this->uso_cfdi
            ?: (string) config('facturapi.defaults.use', 'G03');
    }

    public function getPaymentFormEffectiveAttribute(): string
    {
        return $this->payment_form_default
            ?: (string) config('facturapi.defaults.payment_form', '03');
    }

    /**
     * Verificar si el cliente es extranjero (no mexicano)
     */
    public function getEsExtranjeroAttribute(): bool
    {
        return $this->pais !== 'MX' || $this->rfc === 'XEXX010101000';
    }

    /**
     * Validar que el cliente tenga todos los datos requeridos para CFDI 4.0
     */
    public function validarParaCfdi(): array
    {
        $errores = [];

        // RFC obligatorio y válido
        if (empty($this->rfc)) {
            $errores[] = 'RFC es obligatorio';
        } elseif (!$this->validarRfc($this->rfc)) {
            $errores[] = 'RFC no tiene formato válido';
        }

        // Nombre/Razón social obligatorio
        if (empty($this->nombre_razon_social)) {
            $errores[] = 'Nombre o razón social es obligatorio';
        }

        // Régimen fiscal obligatorio
        if (empty($this->regimen_fiscal)) {
            $errores[] = 'Régimen fiscal es obligatorio';
        }

        // Uso CFDI obligatorio
        if (empty($this->uso_cfdi) && empty($this->cfdi_default_use)) {
            $errores[] = 'Uso CFDI es obligatorio';
        }

        // Código postal del domicilio fiscal obligatorio
        if (empty($this->domicilio_fiscal_cp)) {
            $errores[] = 'Código postal del domicilio fiscal es obligatorio';
        }

        // Para extranjeros: residencia fiscal y num_reg_id_trib obligatorios
        if ($this->es_extranjero) {
            if (empty($this->residencia_fiscal)) {
                $errores[] = 'Residencia fiscal es obligatoria para clientes extranjeros';
            }
            if (empty($this->num_reg_id_trib)) {
                $errores[] = 'Número de registro fiscal extranjero es obligatorio';
            }
        }

        return $errores;
    }

    /**
     * Validar formato de RFC básico
     */
    private function validarRfc(string $rfc): bool
    {
        $rfc = strtoupper($rfc);

        // RFC genérico extranjero
        if ($rfc === 'XEXX010101000') {
            return true;
        }

        // Persona física: 13 caracteres
        if (preg_match('/^[A-ZÑ&]{4}\d{6}[A-Z\d]{3}$/', $rfc)) {
            return true;
        }

        // Persona moral: 12 caracteres
        if (preg_match('/^[A-ZÑ&]{3}\d{6}[A-Z\d]{3}$/', $rfc)) {
            return true;
        }

        return false;
    }
}
