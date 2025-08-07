<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Enums\EstadoVenta; // Ajusta según tu enum

class Venta extends Model
{
    protected $table = 'ventas';

    protected $fillable = [
        'cliente_id',
        'numero_venta',
        'fecha',
        'estado',
        'subtotal',
        'descuento_general',
        'iva',
        'total',
        'notas',
    ];

    protected $casts = [
        'estado' => EstadoVenta::class,
        'fecha' => 'date',
    ];

    public function cliente()
    {
        return $this->belongsTo(Cliente::class);
    }

    // Relación polimórfica para productos
    public function productos()
    {
        return $this->morphedByMany(
            Producto::class,
            'ventable',
            'venta_items',
            'venta_id',
            'ventable_id'
        )->withPivot('cantidad', 'precio', 'descuento');
    }

    // Relación polimórfica para servicios
    public function servicios()
    {
        return $this->morphedByMany(
            Servicio::class,
            'ventable',
            'venta_items',
            'venta_id',
            'ventable_id'
        )->withPivot('cantidad', 'precio', 'descuento');
    }

    // Todos los ítems (productos + servicios)
    public function items()
    {
        return $this->hasMany(VentaItem::class, 'venta_id');
    }
}
