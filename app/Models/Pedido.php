<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Pedido extends Model
{
    use HasFactory;

    // Nombre de la tabla en la base de datos
    protected $table = 'pedidos';

    // Atributos que pueden ser asignados masivamente
    protected $fillable = [
        'cliente_id',
        'total',
        'estado', // Opcional: para gestionar el estado del pedido (pendiente, completado, cancelado, etc.)
    ];

    /**
     * Relación con el cliente.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function cliente()
    {
        return $this->belongsTo(Cliente::class);
    }



    public function items()
    {
        return $this->hasMany(PedidoItem::class);
    }

    // Relación específica: productos (a través de pedido_items)
    public function productos()
    {
        return $this->morphedByMany(Producto::class, 'pedible', 'pedido_items', 'pedido_id', 'pedible_id')
            ->withPivot('cantidad', 'precio', 'descuento', 'subtotal', 'descuento_monto');
    }

    // Relación específica: servicios (a través de pedido_items)
    public function servicios()
    {
        return $this->morphedByMany(Servicio::class, 'pedible', 'pedido_items', 'pedido_id', 'pedible_id')
            ->withPivot('cantidad', 'precio', 'descuento', 'subtotal', 'descuento_monto');
    }
}
