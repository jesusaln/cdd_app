<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PedidoItem extends Model
{
    protected $fillable = [
        'pedido_id',
        'pedible_id',
        'pedible_type',
        'cantidad',
        'precio',
        'descuento',
        'subtotal',
        'descuento_monto',
    ];

    public function pedible()
    {
        return $this->morphTo();
    }

    public function pedido()
    {
        return $this->belongsTo(Pedido::class);
    }
}
