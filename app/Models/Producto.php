<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Producto extends Model
{
    use HasFactory;

    protected $fillable = [
        'nombre',
        'descripcion',
        'codigo',
        'codigo_barras',
        'numero_serie',
        'categoria_id',
        'marca_id',
        'proveedor_id',
        'almacen_id',
        'stock',
        'stock_minimo',
        'precio_compra',
        'precio_venta',
        'impuesto',
        'unidad_medida',
        'fecha_vencimiento',
        'tipo_producto',
        'imagen',
        'estado',
    ];

    // Relacionar con otras tablas
    public function categoria()
    {
        return $this->belongsTo(Categoria::class);
    }

    public function marca()
    {
        return $this->belongsTo(Marca::class);
    }

    public function proveedor()
    {
        return $this->belongsTo(Proveedor::class);
    }

    // Relación con el almacén
    public function almacen()
    {
        return $this->belongsTo(Almacen::class);
    }

    // Relación con cotizaciones
    public function cotizaciones()
    {
        return $this->belongsToMany(Cotizacion::class, 'cotizacion_producto')
            ->withPivot('precio', 'cantidad');
    }

    public function compras()
    {
        return $this->belongsToMany(Compra::class)->withPivot('cantidad', 'precio');
    }

    public function ventas()
    {
        return $this->belongsToMany(Venta::class)->withPivot('cantidad', 'precio');
    }
}
