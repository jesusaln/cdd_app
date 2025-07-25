<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Servicio extends Model
{
    use HasFactory;

    protected $fillable = [
        'nombre',
        'descripcion',
        'codigo',
        'categoria_id',
        'precio',
        'duracion',
        'estado',
    ];

    // Relación con la categoría
    public function categoria()
    {
        return $this->belongsTo(Categoria::class);
    }

    // Relación con las cotizaciones
    public function cotizaciones()
    {
        return $this->morphToMany(Cotizacion::class, 'cotizable', 'cotizacion_servicio')
            ->withPivot('precio', 'cantidad');
    }


    public function pedidos()
    {
        return $this->morphToMany(Pedido::class, 'pedible', 'pedido_producto')
            ->withPivot('precio', 'cantidad');
    }

    public function ventas()
    {
        return $this->morphToMany(Venta::class, 'vendible', 'venta_producto')
            ->withPivot('precio', 'cantidad');
    }
}
