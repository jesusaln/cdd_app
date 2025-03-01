<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\Factory;

use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Venta extends Model
{
    use HasFactory;

    // Nombre de la tabla en la base de datos
    protected $table = 'ventas';

    // Atributos que pueden ser asignados masivamente
    protected $fillable = [
        'cliente_id',
        'total',
        'estado', // Opcional: para gestionar el estado de la venta (pendiente, completada, cancelada, etc.)
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
        return $this->belongsToMany(Producto::class, 'venta_producto')
            ->withPivot('cantidad', 'precio'); // Asegura que los campos estén en la tabla pivote
    }

    public function calcularCostoTotal()
    {
        return $this->productos->sum(function ($producto) {
            // Usar el precio_compra del producto
            return $producto->pivot->cantidad * $producto->precio_compra;
        });
    }
}
