<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class CompraItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'compra_id',
        'comprable_id',
        'comprable_type',
        'cantidad',
        'precio',
        'descuento',
        'subtotal',
        'descuento_monto',
        'unidad_medida',
    ];

    public function comprable()
    {
        return $this->morphTo();
    }

    public function compra()
    {
        return $this->belongsTo(Compra::class);
    }
}
