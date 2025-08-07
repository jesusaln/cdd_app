<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VentaItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'venta_id',
        'ventable_id',
        'ventable_type',
        'cantidad',
        'precio',
        'descuento',
        'subtotal',
        'descuento_monto',
    ];

    protected $casts = [
        'cantidad' => 'integer',
        'precio' => 'decimal:2',
        'descuento' => 'decimal:2',
        'subtotal' => 'decimal:2',
        'descuento_monto' => 'decimal:2',
    ];

    // Relación con Venta
    public function venta()
    {
        return $this->belongsTo(Venta::class);
    }

    // Relación polimórfica - puede ser Producto, Servicio, etc.
    public function ventable()
    {
        return $this->morphTo();
    }

    // Método auxiliar para calcular subtotal automáticamente
    protected static function boot()
    {
        parent::boot();

        static::saving(function ($ventaItem) {
            $precio_con_descuento = $ventaItem->precio - $ventaItem->descuento_monto;
            $ventaItem->subtotal = $ventaItem->cantidad * $precio_con_descuento;
        });
    }
}
