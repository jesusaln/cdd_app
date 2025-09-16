<?php

namespace App\Models;

use App\Enums\EstadoCotizacion;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\Concerns\Blameable;

class Cotizacion extends Model

{
    use HasFactory, SoftDeletes, Blameable;

    protected $table = 'cotizaciones';

    protected $fillable = [
        'cliente_id',
        'numero_cotizacion',   // ✅ agregado
        'subtotal',
        'descuento_general',
        'descuento_items',     // ✅ agregado
        'iva',
        'total',
        'notas',
        'estado',
    ];

    protected $casts = [
        'estado'            => EstadoCotizacion::class,
        // (Opcional) ayuda a mantener consistencia de decimales
        'subtotal'          => 'decimal:2',
        'descuento_general' => 'decimal:2',
        'descuento_items'   => 'decimal:2',
        'iva'               => 'decimal:2',
        'total'             => 'decimal:2',
    ];

    /** Relación con cliente */
    public function cliente(): BelongsTo
    {
        return $this->belongsTo(Cliente::class);
    }

    /** Ítems de la cotización (tabla cotizacion_items) */
    public function items(): HasMany
    {
        return $this->hasMany(CotizacionItem::class);
    }

    // Relaciones de “culpables” (opcionales, para mostrar en UI)
    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
    public function updatedBy()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }
    public function deletedBy()
    {
        return $this->belongsTo(User::class, 'deleted_by');
    }

    /**
     * Productos cotizados (relación polimórfica a través de cotizacion_items).
     * Nota: Solo es necesaria si en algún punto usas attach/detach directamente.
     */
    public function productos(): MorphToMany
    {
        return $this->morphedByMany(
            Producto::class,
            'cotizable',
            'cotizacion_items',
            'cotizacion_id',
            'cotizable_id'
        )->withPivot('cantidad', 'precio', 'descuento', 'subtotal', 'descuento_monto');
    }

    /**
     * Servicios cotizados (relación polimórfica a través de cotizacion_items).
     * Nota: Solo es necesaria si en algún punto usas attach/detach directamente.
     */
    public function servicios(): MorphToMany
    {
        return $this->morphedByMany(
            Servicio::class,
            'cotizable',
            'cotizacion_items',
            'cotizacion_id',
            'cotizable_id'
        )->withPivot('cantidad', 'precio', 'descuento', 'subtotal', 'descuento_monto');
    }

    /** Marcado de estado helper */
    public function marcarComoEnviadoAPedido(): void
    {
        $this->update(['estado' => EstadoCotizacion::EnviadoAPedido]);
    }

    /** Puede enviarse a pedido según su estado actual */
    public function puedeEnviarseAPedido(): bool
    {
        $estadoActual = $this->estado->value;
        return in_array($estadoActual, [
            EstadoCotizacion::Pendiente->value,
            EstadoCotizacion::Borrador->value,
        ], true);
    }

    /** Relación con pedidos generados desde esta cotización */
    public function pedidos(): HasMany
    {
        return $this->hasMany(Pedido::class);
    }

    /** Último pedido asociado (si existe) */
    public function pedido(): HasOne
    {
        return $this->hasOne(Pedido::class)->latest();
    }

    protected static function booted(): void
    {
        static::creating(function (Cotizacion $cot) {
            if (empty($cot->numero_cotizacion)) {
                $cot->numero_cotizacion = static::generarNumero();
            }
            if (empty($cot->estado)) {
                $cot->estado = 'pendiente';
            }
        });
    }

    public static function generarNumero(): string
    {
        $prefix = 'COT-' . now()->format('Y');

        // Tomamos el último dentro del año actual
        $ultimo = static::where('numero_cotizacion', 'like', "$prefix-%")
            ->orderByDesc('id')
            ->value('numero_cotizacion');

        $n = 0;
        if ($ultimo && preg_match('/-(\d{5})$/', $ultimo, $m)) {
            $n = (int) $m[1];
        }

        // Intentos por si hay choque con unique (raro en dev, útil en prod)
        for ($i = 0; $i < 5; $i++) {
            $n++;
            $num = sprintf('%s-%05d', $prefix, $n);
            if (! static::where('numero_cotizacion', $num)->exists()) {
                return $num;
            }
        }

        // Si hubo mucha concurrencia, usa timestamp de respaldo
        return $prefix . '-' . now()->format('His');
    }
}
