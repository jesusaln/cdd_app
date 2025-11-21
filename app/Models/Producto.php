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
        'requiere_serie' => 'boolean',
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
        'requiere_serie',
        'precio_compra',
        'precio_venta',
        'margen_ganancia',
        'comision_vendedor',
        'unidad_medida',
        'fecha_vencimiento',
        'tipo_producto',
        'imagen',
        'estado',
    ];

    protected $attributes = [
        'descripcion' => 'Sin descripción disponible',
        'reservado' => 0,
        'stock' => 0,
        'stock_minimo' => 0,
        'expires' => false,
        'requiere_serie' => false,
        'margen_ganancia' => 0,
        'comision_vendedor' => 0,
    ];

    protected static function booted()
    {
        static::creating(function (Producto $producto) {
            if (empty($producto->codigo)) {
                $producto->codigo = self::generateNextCodigo();
            }
        });
    }

    public static function generateNextCodigo(): string
    {
        try {
            $driver = config('database.default');
            $query = self::query();

            if ($driver === 'pgsql') {
                $max = $query->whereRaw("codigo ~ '^[0-9]+'")->selectRaw('MAX(CAST(codigo AS BIGINT)) as max')->value('max');
            } elseif ($driver === 'mysql') {
                $max = $query->whereRaw("codigo REGEXP '^[0-9]+'")->selectRaw('MAX(CAST(codigo AS UNSIGNED)) as max')->value('max');
            } else {
                // Fallback genérico: intentar parsear en PHP
                $max = self::all()->reduce(function ($carry, $item) {
                    $c = $item->codigo;
                    return ctype_digit((string)$c) ? max($carry, (int)$c) : $carry;
                }, 0);
            }

            $next = ((int) $max) + 1;
            return (string) $next;
        } catch (\Throwable $e) {
            return (string) (time());
        }
    }

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

    /**
     * Citas donde este producto fue utilizado
     */
    public function citasComoUtilizado(): BelongsToMany
    {
        return $this->belongsToMany(Cita::class, 'cita_productos_utilizados')
            ->withPivot('cantidad', 'precio_unitario', 'tipo_uso')
            ->withTimestamps();
    }

    /**
     * Citas donde este producto fue vendido
     */
    public function citasComoVendido(): BelongsToMany
    {
        return $this->belongsToMany(Cita::class, 'cita_productos_vendidos')
            ->withPivot('cantidad', 'precio_venta', 'venta_id')
            ->withTimestamps();
    }

    /** @return HasMany<ProductoSerie> */
    public function series(): HasMany
    {
        return $this->hasMany(ProductoSerie::class);
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

    /** Ítems en citas donde este producto fue usado. @return MorphMany<CitaItem> */
    public function citaItems(): MorphMany
    {
        return $this->morphMany(CitaItem::class, 'citable');
    }

    /** @return MorphToMany<Venta> */
    public function ventas()
    {
        return $this->morphedByMany(Venta::class, 'ventable', 'venta_items')
            ->withPivot('cantidad', 'precio', 'descuento', 'costo_unitario');
    }

    /* =========================
     * Accessors
     * ========================= */

    public function getStockDisponibleAttribute()
    {
        // Stock disponible = stock total - cantidad reservada
        $stockTotal = (int) $this->stock;
        $reservado = (int) $this->reservado;

        return max(0, $stockTotal - $reservado);
    }

    public function getStockTotalAttribute()
    {
        // Stock total es la suma de cantidades en inventarios
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

    /**
     * Calcula el costo histórico promedio basado en los últimos movimientos de entrada
     */
    public function calcularCostoHistorico($cantidad = null)
    {
        try {
            // Si no hay movimientos de entrada, usar el precio de compra actual
            $movimientosEntrada = $this->movimientos()
                ->where('tipo', 'entrada')
                ->where('cantidad', '>', 0)
                ->orderBy('created_at', 'desc');

            if ($cantidad) {
                $movimientosEntrada->limit($cantidad);
            }

            $movimientos = $movimientosEntrada->get();

            if ($movimientos->isEmpty()) {
                return $this->precio_compra ?: 0;
            }

            // Calcular costo promedio ponderado
            $totalCantidad = 0;
            $totalCosto = 0;

            foreach ($movimientos as $movimiento) {
                $costoUnitario = $movimiento->detalles['costo_unitario'] ?? $this->precio_compra ?: 0;
                $totalCosto += $costoUnitario * $movimiento->cantidad;
                $totalCantidad += $movimiento->cantidad;
            }

            return $totalCantidad > 0 ? $totalCosto / $totalCantidad : ($this->precio_compra ?: 0);
        } catch (\Exception $e) {
            // En caso de error, devolver el precio de compra actual
            return $this->precio_compra ?: 0;
        }
    }

    /**
     * Calcula el costo histórico usando el sistema de lotes (si aplica)
     */
    public function calcularCostoPorLotes($cantidadNecesaria = null)
    {
        try {
            if (!$this->expires) {
                return $this->calcularCostoHistorico($cantidadNecesaria);
            }

            // Para productos con lotes, calcular basado en los lotes disponibles
            $lotes = $this->lotes()
                ->where('cantidad_actual', '>', 0)
                ->where(function ($q) {
                    $q->whereNull('fecha_caducidad')
                      ->orWhere('fecha_caducidad', '>', now());
                })
                ->orderBy('fecha_caducidad', 'asc') // FIFO
                ->get();

            if ($lotes->isEmpty()) {
                return $this->precio_compra ?: 0;
            }

            $cantidadRestante = $cantidadNecesaria ?? $this->stock;
            $costoTotal = 0;

            foreach ($lotes as $lote) {
                if ($cantidadRestante <= 0) break;

                $cantidadUsar = min($cantidadRestante, $lote->cantidad_actual);
                $costoUnitario = $lote->costo_unitario ?: $this->precio_compra ?: 0;
                $costoTotal += $costoUnitario * $cantidadUsar;
                $cantidadRestante -= $cantidadUsar;
            }

            $cantidadUsada = ($cantidadNecesaria ?? $this->stock) - $cantidadRestante;
            return $cantidadUsada > 0 ? $costoTotal / $cantidadUsada : ($this->precio_compra ?: 0);
        } catch (\Exception $e) {
            // En caso de error, devolver el precio de compra actual
            return $this->precio_compra ?: 0;
        }
    }
}
