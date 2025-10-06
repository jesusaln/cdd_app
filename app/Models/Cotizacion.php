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
        'fecha_cotizacion',    // ✅ agregado
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
     * Solo incluye productos activos.
     */
    public function productos(): MorphToMany
    {
        return $this->morphedByMany(
            Producto::class,
            'cotizable',
            'cotizacion_items',
            'cotizacion_id',
            'cotizable_id'
        )->withPivot('cantidad', 'precio', 'descuento', 'subtotal', 'descuento_monto')
        ->wherePivot('cotizable_type', Producto::class)
        ->whereHasMorph('cotizable', Producto::class, function ($query) {
            $query->active();
        });
    }

    /**
     * Servicios cotizados (relación polimórfica a través de cotizacion_items).
     * Nota: Solo es necesaria si en algún punto usas attach/detach directamente.
     * Solo incluye servicios activos.
     */
    public function servicios(): MorphToMany
    {
        return $this->morphedByMany(
            Servicio::class,
            'cotizable',
            'cotizacion_items',
            'cotizacion_id',
            'cotizable_id'
        )->withPivot('cantidad', 'precio', 'descuento', 'subtotal', 'descuento_monto')
        ->wherePivot('cotizable_type', Servicio::class)
        ->whereHasMorph('cotizable', Servicio::class, function ($query) {
            $query->active();
        });
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
            EstadoCotizacion::Aprobada->value,
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
        // Buscar el último número de cotización existente
        $ultimaCotizacion = static::where('numero_cotizacion', 'LIKE', 'C%')
            ->orderBy('id', 'desc')
            ->first();

        if (!$ultimaCotizacion || !$ultimaCotizacion->numero_cotizacion) {
            return 'C001';
        }

        // Extraer el número de la cotización
        $matches = [];
        if (preg_match('/C(\d+)$/', $ultimaCotizacion->numero_cotizacion, $matches)) {
            $ultimoNumero = (int) $matches[1];
            $siguienteNumero = $ultimoNumero + 1;
            return 'C' . str_pad($siguienteNumero, 3, '0', STR_PAD_LEFT);
        }

        // Si no se puede extraer el número, empezar desde C001
        return 'C001';
    }
}
