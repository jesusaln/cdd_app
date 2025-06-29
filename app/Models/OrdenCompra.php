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
        'total',
        'estado',
        'fecha_recepcion', // Campo opcional
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
            ->withPivot('cantidad', 'precio');
    }

    // Relación muchos a muchos con Servicios (si se compran servicios)
    public function servicios()
    {
        return $this->belongsToMany(Servicio::class, 'orden_compra_servicio')
            ->withPivot('cantidad', 'precio');
    }
}
