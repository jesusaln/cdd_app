<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProductoSerie extends Model
{
    use HasFactory;

    protected $table = 'producto_series';

    protected $fillable = [
        'producto_id',
        'compra_id',
        'almacen_id',
        'numero_serie',
        'estado',
    ];

    /** @return BelongsTo<Producto, ProductoSerie> */
    public function producto(): BelongsTo
    {
        return $this->belongsTo(Producto::class);
    }
}

