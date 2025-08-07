<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Enums\EstadoPedido;

class Pedido extends Model
{
    protected $table = 'pedidos';

    protected $fillable = [
        'cliente_id',
        'cotizacion_id',
        'numero_pedido',
        'subtotal',
        'descuento_general',
        'iva',
        'total',
        'notas',
        'estado',
    ];

    protected $casts = [
        'estado' => EstadoPedido::class,
    ];

    public function cliente()
    {
        return $this->belongsTo(Cliente::class);
    }



    // Relaciones polimórficas para productos y servicios
    public function productos()
    {
        return $this->morphedByMany(
            Producto::class,
            'pedible',
            'pedido_items',
            'pedido_id',
            'pedible_id'
        )->withPivot('cantidad', 'precio', 'descuento', 'subtotal', 'descuento_monto');
    }

    public function servicios()
    {
        return $this->morphedByMany(
            Servicio::class,
            'pedible',
            'pedido_items',
            'pedido_id',
            'pedible_id'
        )->withPivot('cantidad', 'precio', 'descuento', 'subtotal', 'descuento_monto');
    }

    public function cotizacion()
    {
        return $this->belongsTo(Cotizacion::class);
    }
     public function items()
    {
        return $this->hasMany(PedidoItem::class, 'pedido_id');
    }
}
