<?php
//cambioamayusuclas
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class CotizacionItem extends Model
{

    use HasFactory;

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

    public function cotizable()
    {
        return $this->morphTo();
    }

    public function cotizacion()
    {
        return $this->belongsTo(Cotizacion::class);
    }
}
