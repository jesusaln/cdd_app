<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\Venta;

class VentaItem extends Model
{
    protected $table = 'venta_items';
    protected $fillable = [
        'venta_id',
        'ventable_id',
        'ventable_type',
        'cantidad',
        'precio',
        'descuento',
        'subtotal',
        'descuento_monto',
        'costo_unitario'
    ];

    public function venta()
    {
        return $this->belongsTo(Venta::class, 'venta_id');
    }

    public function ventable()
    {
        return $this->morphTo();
    }

    public function series()
    {
        return $this->hasMany(VentaItemSerie::class, 'venta_item_id');
    }
}
