<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

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
        return $this->morphTo('venta', 'ventable_type', 'ventable_id');
    }

    public function ventable()
    {
        return $this->morphTo();
    }
}
