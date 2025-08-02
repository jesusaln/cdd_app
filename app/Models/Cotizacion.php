<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Enums\EstadoCotizacion;
use Illuminate\Database\Eloquent\Factories\Factory;

use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Cotizacion extends Model
{
    use HasFactory;

    protected $table = 'cotizaciones';

    protected $fillable = [
        'cliente_id',
        'total',

    ];

    protected $casts = [
        'estado' => EstadoCotizacion::class,
    ];


    // Relación con el cliente
    public function cliente()
    {
        return $this->belongsTo(Cliente::class);
    }

    // Relación polimórfica para productos
    public function productos()
    {
        return $this->morphedByMany(
            Producto::class,
            'cotizable',
            'cotizacion_producto' // nombre de la tabla pivote
        )->withPivot('cantidad', 'precio', 'descuento', 'subtotal', 'descuento_monto')
            ->withTimestamps();
    }

    // Relación polimórfica para servicios
    public function servicios()
    {
        return $this->morphedByMany(
            Servicio::class,
            'cotizable',
            'cotizacion_producto'
        )->withPivot('cantidad', 'precio', 'descuento', 'subtotal', 'descuento_monto')
            ->withTimestamps();
    }
}
