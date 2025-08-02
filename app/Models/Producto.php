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



    public function compras()
    {
        return $this->belongsToMany(Compra::class)->withPivot('cantidad', 'precio');
    }


    public function inventarios()
    {
        return $this->hasMany(Inventario::class);
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

    public function cotizaciones()
    {
        return $this->morphToMany(
            Cotizacion::class,
            'cotizable',
            'cotizacion_producto'
        )->withPivot('cantidad', 'precio', 'descuento', 'subtotal', 'descuento_monto')
            ->withTimestamps();
    }
    /**
     * Scope para filtrar productos activos
     */
    public function scopeActive($query)
    {
        return $query->where('estado', 'activo'); // Ajusta según los valores que uses
    }

    // ... (el resto de tus métodos)
}
