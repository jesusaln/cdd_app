<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class VentaItemSerie extends Model
{
    protected $table = 'venta_item_series';

    protected $fillable = [
        'venta_item_id',
        'producto_serie_id',
        'numero_serie',
    ];

    public function ventaItem(): BelongsTo
    {
        return $this->belongsTo(VentaItem::class, 'venta_item_id');
    }

    public function productoSerie(): BelongsTo
    {
        return $this->belongsTo(ProductoSerie::class, 'producto_serie_id');
    }
}
