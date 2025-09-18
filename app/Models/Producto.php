<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

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
        'reservado',
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

    /* =========================
     * Relaciones base
     * ========================= */

    /** @return BelongsTo<Categoria, Producto> */
    public function categoria(): BelongsTo
    {
        return $this->belongsTo(Categoria::class);
    }

    /** @return BelongsTo<Marca, Producto> */
    public function marca(): BelongsTo
    {
        return $this->belongsTo(Marca::class);
    }

    /** @return BelongsTo<Proveedor, Producto> */
    public function proveedor(): BelongsTo
    {
        return $this->belongsTo(Proveedor::class);
    }

    /** @return BelongsTo<Almacen, Producto> */
    public function almacen(): BelongsTo
    {
        return $this->belongsTo(Almacen::class);
    }

    /** @return BelongsToMany<Compra> */
    public function compras(): BelongsToMany
    {
        return $this->belongsToMany(Compra::class)->withPivot('cantidad', 'precio');
    }

    /** @return HasMany<Inventario> */
    public function inventarios(): HasMany
    {
        return $this->hasMany(Inventario::class);
    }

    /* =========================================================================
     * Relaciones polimórficas UNIFICADAS a *items (coinciden con tus controladores)
     * ========================================================================= */

    /** Ítems en ventas donde este producto fue usado. @return MorphMany<VentaItem> */
    public function ventaItems(): MorphMany
    {
        return $this->morphMany(VentaItem::class, 'ventable');
    }

    /** Ítems en pedidos donde este producto fue usado. @return MorphMany<PedidoItem> */
    public function pedidoItems(): MorphMany
    {
        return $this->morphMany(PedidoItem::class, 'pedible');
    }

    /** Ítems en cotizaciones donde este producto fue usado. @return MorphMany<CotizacionItem> */
    public function cotizacionItems(): MorphMany
    {
        return $this->morphMany(CotizacionItem::class, 'cotizable');
    }

    /* =========================
     * Accessors
     * ========================= */

    public function getStockDisponibleAttribute()
    {
        return $this->stock - $this->reservado;
    }

    /* =========================
     * Scopes
     * ========================= */

    public function scopeActive($query)
    {
        return $query->where('estado', 'activo');
    }
}
