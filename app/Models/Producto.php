<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Producto extends Model
{
    use HasFactory;

    protected $casts = [
        'precio_compra' => 'decimal:2',
        'precio_venta' => 'decimal:2',
        'margen_ganancia' => 'decimal:2',
        'comision_vendedor' => 'decimal:2',
    ];

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
        'expires',
        'precio_compra',
        'precio_venta',
        'margen_ganancia',
        'comision_vendedor',
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

    /** @return MorphToMany<Compra, Producto> */
    public function compras(): MorphToMany
    {
        return $this->morphToMany(Compra::class, 'comprable', 'compra_items');
    }

    /** @return BelongsToMany<OrdenCompra> */
    public function ordenesCompra(): BelongsToMany
    {
        return $this->belongsToMany(OrdenCompra::class, 'orden_compra_producto')->withPivot('cantidad', 'precio');
    }

    /** @return HasMany<Inventario> */
    public function inventarios(): HasMany
    {
        return $this->hasMany(Inventario::class);
    }

    /** @return HasMany<InventarioMovimiento> */
    public function movimientos(): HasMany
    {
        return $this->hasMany(InventarioMovimiento::class);
    }

    /** @return HasMany<ProductoPrecioHistorial> */
    public function precioHistorial(): HasMany
    {
        return $this->hasMany(ProductoPrecioHistorial::class);
    }

    /** @return HasMany<Lote> */
    public function lotes(): HasMany
    {
        return $this->hasMany(Lote::class);
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
        // Suma de cantidades en todos los inventarios
        return $this->inventarios->sum('cantidad');
    }

    public function getStockTotalAttribute()
    {
        return $this->inventarios->sum('cantidad');
    }

    public function getGananciaAttribute()
    {
        return $this->precio_venta - $this->precio_compra;
    }

    public function getGananciaMargenAttribute()
    {
        return $this->ganancia * ($this->margen_ganancia / 100);
    }

    /* =========================
     * Scopes
     * ========================= */

    public function scopeActive($query)
    {
        return $query->where('estado', 'activo');
    }
}
