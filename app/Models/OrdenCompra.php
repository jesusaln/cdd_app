<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrdenCompra extends Model
{
    use HasFactory;

    protected $table = 'orden_compras';

    protected $fillable = [
        'proveedor_id',
        'almacen_id',
        'numero_orden',
        'fecha_orden',
        'fecha_entrega_esperada',
        'prioridad',
        'direccion_entrega',
        'terminos_pago',
        'metodo_pago',
        'subtotal',
        'descuento_items',
        'descuento_general',
        'iva',
        'total',
        'observaciones',
        'estado',
        'fecha_recepcion',
    ];

    protected $casts = [
        'fecha_orden' => 'date',
        'fecha_entrega_esperada' => 'date',
        'fecha_recepcion' => 'datetime',
        'subtotal' => 'decimal:2',
        'descuento_items' => 'decimal:2',
        'descuento_general' => 'decimal:2',
        'iva' => 'decimal:2',
        'total' => 'decimal:2',
    ];

    // Relación con el Proveedor
    public function proveedor()
    {
        return $this->belongsTo(Proveedor::class);
    }

    // Relación con el Almacén
    public function almacen()
    {
        return $this->belongsTo(Almacen::class);
    }

    // Relación muchos a muchos con Productos (solo activos)
    public function productos()
    {
        return $this->belongsToMany(Producto::class, 'orden_compra_producto')
            ->withPivot('cantidad', 'precio', 'descuento', 'unidad_medida')
            ->active();
    }

    protected static function booted(): void
    {
        static::creating(function (OrdenCompra $orden) {
            // Solo generar numero_orden si no viene en los datos de entrada
            if (empty($orden->numero_orden) && !isset($orden->getAttributes()['numero_orden'])) {
                $orden->numero_orden = self::getProximoNumero();
            }
        });
    }

    public static function getProximoNumero()
    {
        // Buscar el último número de orden
        $ultimo = self::where('numero_orden', 'like', 'OC-%')
            ->orderByRaw("CAST(SUBSTRING(numero_orden, 4) AS UNSIGNED) DESC")
            ->first();

        if ($ultimo) {
            $numero = intval(substr($ultimo->numero_orden, 3)) + 1;
        } else {
            $numero = 1;
        }

        return 'OC-' . str_pad($numero, 3, '0', STR_PAD_LEFT);
    }
}
