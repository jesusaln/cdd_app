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
        'pagado',
        'metodo_pago',
        'fecha_pago',
        'notas_pago',
        'pagado_por',
    ];

    protected $casts = [
        'estado' => EstadoVenta::class,
        'fecha' => 'date',
        'fecha_pago' => 'date',
        'pagado' => 'boolean',
    ];

    public function cliente()
    {
        return $this->belongsTo(Cliente::class);
    }

    public function pagadoPor()
    {
        return $this->belongsTo(\App\Models\User::class, 'pagado_por');
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
