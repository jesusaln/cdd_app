<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Cotizacion;

class CotizacionProducto extends Model
{
    protected $table = 'cotizacion_producto';
    public $timestamps = true;

    protected $fillable = [
        'cotizacion_id',
        'cotizable_id',
        'cotizable_type',
        'cantidad',
        'precio',
        'descuento',
        'subtotal',
        'descuento_monto',
    ];

    // Relación con la cotización
    public function cotizacion()
    {
        return $this->belongsTo(Cotizacion::class);
    }

    // Relación polimórfica: producto o servicio
    public function cotizable()
    {
        return $this->morphTo();
    }
}
