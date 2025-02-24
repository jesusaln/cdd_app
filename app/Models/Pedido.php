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

    /**
     * Relación con los productos.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function productos()
    {
        return $this->belongsToMany(Producto::class, 'pedido_producto')
            ->withPivot('cantidad', 'precio'); // Asegura que los campos estén en la tabla pivote
    }
}
