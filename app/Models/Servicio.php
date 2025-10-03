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
        return $query->where('estado', 'activo'); // Ajusta según los valores que uses
    }

    // Relación con la categoría
    public function categoria()
    {
        return $this->belongsTo(Categoria::class)->withDefault([
            'nombre' => 'Sin categoría'
        ]);
    }

    // Relación con las cotizaciones
    public function cotizaciones()
    {
        return $this->morphToMany(
            Cotizacion::class,
            'cotizable',
            'cotizacion_items',
            'cotizable_id',
            'cotizacion_id'
        )->withPivot('cantidad', 'precio', 'descuento', 'subtotal', 'descuento_monto');
    }

    public function pedidos()
    {
        return $this->morphToMany(Pedido::class, 'pedible');
    }

    public function ventas()
    {
        return $this->morphToMany(Venta::class, 'vendible');
    }

    public function getGananciaAttribute()
    {
        return $this->precio * ($this->margen_ganancia / 100);
    }
}
