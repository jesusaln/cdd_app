<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Compra extends Model
{
    use HasFactory;

    // Nombre de la tabla en la base de datos
    protected $table = 'compras';

    // Atributos que pueden ser asignados masivamente
    protected $fillable = [
        'proveedor_id',
        'total',
        'estado', // Opcional: para gestionar el estado de la compra (pendiente, completada, cancelada, etc.)
    ];

    // Si quieres un valor predeterminado
    protected $attributes = [
        'estado' => 'pendiente', // o cualquier valor que desees como predeterminado
    ];

    /**
     * Relación con el proveedor.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function proveedor()
    {
        return $this->belongsTo(Proveedor::class); // Aquí asumo que tienes el modelo de Proveedor
    }

    /**
     * Relación con los productos.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function productos()
    {
        return $this->belongsToMany(Producto::class, 'compra_producto')
            ->withPivot('cantidad', 'precio'); // Asegura que los campos estén en la tabla pivote
    }
}
