<?php

namespace App\Models;

use App\Enums\EstadoCompra;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\Concerns\Blameable;

class Compra extends Model
{
    use HasFactory, SoftDeletes, Blameable;

    protected $table = 'compras';

    protected $fillable = [
        'proveedor_id',
        'numero_compra',
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

    /** Ítems de la compra */
    public function items(): HasMany
    {
        return $this->hasMany(CompraItem::class);
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
                $compra->numero_compra = static::generarNumero();
            }
            if (empty($compra->estado)) {
                $compra->estado = EstadoCompra::Borrador;
            }
        });
    }

    public static function generarNumero(): string
    {
        $prefix = 'COMP-' . now()->format('Y');

        $ultimo = static::withTrashed()->where('numero_compra', 'like', "$prefix-%")
            ->orderByDesc('id')
            ->value('numero_compra');

        $n = 0;
        if ($ultimo && preg_match('/-(\d{5})$/', $ultimo, $m)) {
            $n = (int) $m[1];
        }

        for ($i = 0; $i < 5; $i++) {
            $n++;
            $num = sprintf('%s-%05d', $prefix, $n);
            if (! static::withTrashed()->where('numero_compra', $num)->exists()) {
                return $num;
            }
        }

        return $prefix . '-' . now()->format('His');
    }
}
