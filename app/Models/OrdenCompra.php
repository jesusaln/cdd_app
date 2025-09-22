<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrdenCompra extends Model
{
    use HasFactory;

    protected $table = 'orden_compras';

    protected $fillable = [
        'proveedor_id',
        'numero_orden',
        'fecha_orden',
        'fecha_entrega_esperada',
        'prioridad',
        'direccion_entrega',
        'terminos_pago',
        'metodo_pago',
        'subtotal',
        'descuento_items',
        'descuento_general',
        'iva',
        'total',
        'observaciones',
        'estado',
        'fecha_recepcion',
    ];

    protected $casts = [
        'fecha_orden' => 'date',
        'fecha_entrega_esperada' => 'date',
        'fecha_recepcion' => 'datetime',
        'subtotal' => 'decimal:2',
        'descuento_items' => 'decimal:2',
        'descuento_general' => 'decimal:2',
        'iva' => 'decimal:2',
        'total' => 'decimal:2',
    ];

    // Relación con el Proveedor
    public function proveedor()
    {
        return $this->belongsTo(Proveedor::class);
    }

    // Relación muchos a muchos con Productos
    public function productos()
    {
        return $this->belongsToMany(Producto::class, 'orden_compra_producto')
            ->withPivot('cantidad', 'precio', 'descuento', 'unidad_medida');
    }
}
