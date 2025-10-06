<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class InventarioMovimiento extends Model
{
    use HasFactory;

    public const TIPO_ENTRADA = 'entrada';
    public const TIPO_SALIDA = 'salida';

    /**
     * @var array<int, string>
     */
    protected $fillable = [
        'producto_id',
        'almacen_id',
        'lote_id',
        'tipo',
        'cantidad',
        'stock_anterior',
        'stock_posterior',
        'motivo',
        'referencia_type',
        'referencia_id',
        'user_id',
        'detalles',
    ];

    /**
     * @var array<string, string>
     */
    protected $casts = [
        'detalles' => 'array',
    ];

    /**
     * @return BelongsTo<Producto, InventarioMovimiento>
     */
    public function producto(): BelongsTo
    {
        return $this->belongsTo(Producto::class);
    }

    /**
     * @return MorphTo<Model, InventarioMovimiento>
     */
    public function referencia(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * @return BelongsTo<User, InventarioMovimiento>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * @return BelongsTo<Almacen, InventarioMovimiento>
     */
    public function almacen(): BelongsTo
    {
        return $this->belongsTo(Almacen::class);
    }

    /**
     * @return BelongsTo<Lote, InventarioMovimiento>
     */
    public function lote(): BelongsTo
    {
        return $this->belongsTo(Lote::class);
    }
}
