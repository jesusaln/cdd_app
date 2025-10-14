<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Servicio extends Model
{
    use HasFactory;

    protected $casts = [
        'margen_ganancia' => 'decimal:2',
        'es_instalacion' => 'boolean',
        'comision_vendedor' => 'decimal:2',
    ];

    protected $fillable = [
        'nombre',
        'descripcion',
        'codigo',
        'categoria_id',
        'precio',
        'margen_ganancia',
        'duracion',
        'estado',
        'es_instalacion',
        'comision_vendedor',
    ];

    public function scopeActive($query)
    {
        return $query->where('estado', 'activo');
    }

    // Relación con la categoría
    public function categoria()
    {
        return $this->belongsTo(Categoria::class);
    }

    // Relación con las cotizaciones
    public function cotizaciones()
    {
        return $this->morphToMany(Cotizacion::class, 'cotizable', 'cotizacion_items')
            ->withPivot(['cantidad', 'precio', 'descuento', 'subtotal', 'descuento_monto'])
            ->withTimestamps();
    }

    public function pedidos()
    {
        return $this->morphToMany(Pedido::class, 'pedible', 'pedido_producto')
            ->withPivot('precio', 'cantidad');
    }

    public function ventas()
    {
        return $this->morphedByMany(Venta::class, 'ventable', 'venta_items')
            ->withPivot('cantidad', 'precio', 'descuento', 'costo_unitario');
    }

    public function getGananciaAttribute()
    {
        return $this->precio * ($this->margen_ganancia / 100);
    }
}
