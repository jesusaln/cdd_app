<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Enums\EstadoCotizacion;

class Cotizacion extends Model
{
    protected $table = 'cotizaciones';

    protected $fillable = [
        'cliente_id',
        'subtotal',
        'descuento_general',
        'iva',
        'total',
        'notas',
        'estado',
    ];

    protected $casts = [
        'estado' => EstadoCotizacion::class,
    ];

    public function cliente()
    {
        return $this->belongsTo(Cliente::class);
    }

    public function items()
    {
        return $this->hasMany(CotizacionItem::class);
    }

    // Relaciones polimórficas (opcional, si usas attach/detach)
    public function productos()
    {
        return $this->morphedByMany(
            Producto::class,
            'cotizable',
            'cotizacion_items',
            'cotizacion_id',
            'cotizable_id'
        )->withPivot('cantidad', 'precio', 'descuento', 'subtotal', 'descuento_monto');
    }

    public function servicios()
    {
        return $this->morphedByMany(
            Servicio::class,
            'cotizable',
            'cotizacion_items',
            'cotizacion_id',
            'cotizable_id'
        )->withPivot('cantidad', 'precio', 'descuento', 'subtotal', 'descuento_monto');
    }
}
