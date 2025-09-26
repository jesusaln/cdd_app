<?php

namespace App\Models;

use App\Enums\EstadoCompra;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\Concerns\Blameable;

class Compra extends Model
{
    use HasFactory, SoftDeletes, Blameable;

    protected $table = 'compras';

    protected $fillable = [
        'proveedor_id',
        'orden_compra_id',
        'numero_compra',
        'fecha_compra',
        'subtotal',
        'descuento_general',
        'descuento_items',
        'iva',
        'total',
        'notas',
        'estado',
    ];

    protected $casts = [
        'estado' => EstadoCompra::class,
        'subtotal' => 'decimal:2',
        'descuento_general' => 'decimal:2',
        'descuento_items' => 'decimal:2',
        'iva' => 'decimal:2',
        'total' => 'decimal:2',
    ];

    /** Relación con proveedor */
    public function proveedor(): BelongsTo
    {
        return $this->belongsTo(Proveedor::class);
    }

    /** Productos de la compra */
    public function productos(): MorphToMany
    {
        return $this->morphedByMany(
            Producto::class,
            'comprable',
            'compra_items',
            'compra_id',
            'comprable_id'
        )->withPivot('cantidad', 'precio', 'descuento');
    }

    /** Relación con orden de compra */
    public function ordenCompra(): BelongsTo
    {
        return $this->belongsTo(OrdenCompra::class);
    }

    // Relaciones de "culpables"
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

    protected static function booted(): void
    {
        static::creating(function (Compra $compra) {
            if (empty($compra->numero_compra)) {
                $compra->numero_compra = static::generarNumero($compra->orden_compra_id);
            }
            // Todas las compras se crean automáticamente como procesadas
            $compra->estado = EstadoCompra::Procesada;
        });
    }

    public static function generarNumero($ordenCompraId = null): string
    {
        if ($ordenCompraId) {
            // Numeración para compras que vienen de órdenes de compra
            $ultimo = self::whereNotNull('orden_compra_id')->orderBy('id', 'desc')->first();
            $numero = $ultimo ? intval(substr($ultimo->numero_compra, 4)) + 1 : 1;
            return 'OCC-' . str_pad($numero, 4, '0', STR_PAD_LEFT);
        } else {
            // Numeración para compras directas
            $ultimo = self::whereNull('orden_compra_id')->orderBy('id', 'desc')->first();
            $numero = $ultimo ? intval(substr($ultimo->numero_compra, 1)) + 1 : 1;
            return 'C' . str_pad($numero, 4, '0', STR_PAD_LEFT);
        }
    }
}
