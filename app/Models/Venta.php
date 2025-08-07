<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Enums\EstadoVenta;

class Venta extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'cliente_id',
        'factura_id',
        'numero_venta',
        'subtotal',
        'descuento_general',
        'iva',
        'total',
        'fecha',
        'estado',
        'notas',
    ];

    protected $casts = [
        'estado' => EstadoVenta::class,
        'fecha' => 'datetime',
        'subtotal' => 'decimal:2',
        'descuento_general' => 'decimal:2',
        'iva' => 'decimal:2',
        'total' => 'decimal:2',
    ];

    // Relación con Cliente
    public function cliente()
    {
        return $this->belongsTo(Cliente::class);
    }

    // Relación con Factura (si existe)
    public function factura()
    {
        return $this->belongsTo(Factura::class);
    }

    // Relación con VentaItems
    public function items()
    {
        return $this->hasMany(VentaItem::class);
    }

    // Relación para obtener los productos a través de los items
    // Esta es la que necesitabas como "productos"
    public function productos()
    {
        return $this->items()->where('ventable_type', 'App\\Models\\Producto');
    }

    // Método para obtener todos los ventables (productos, servicios, etc.)
    public function ventables()
    {
        return $this->hasManyThrough(
            'App\Models\Producto', // Ajusta según tu modelo
            'App\Models\VentaItem',
            'venta_id',
            'id',
            'id',
            'ventable_id'
        )->where('venta_items.ventable_type', 'App\\Models\\Producto');
    }

    // Método auxiliar para obtener el total de items
    public function getTotalItemsAttribute()
    {
        return $this->items->sum('cantidad');
    }

    // Método auxiliar para recalcular totales
    public function recalcularTotales()
    {
        $subtotal = $this->items->sum('subtotal');
        $this->subtotal = $subtotal;
        $this->total = $subtotal - $this->descuento_general + $this->iva;
        $this->save();
    }
}
