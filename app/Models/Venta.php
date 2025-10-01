<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Enums\EstadoVenta; // Ajusta según tu enum

class Venta extends Model
{
    protected $table = 'ventas';

    protected $fillable = [
        'cliente_id',
        'numero_venta',
        'fecha',
        'estado',
        'subtotal',
        'descuento_general',
        'iva',
        'total',
        'notas',
        'pagado',
        'metodo_pago',
        'fecha_pago',
        'notas_pago',
        'pagado_por',
        'vendedor_type',
        'vendedor_id',
    ];

    protected $casts = [
        'estado' => EstadoVenta::class,
        'fecha' => 'date',
        'fecha_pago' => 'date',
        'pagado' => 'boolean',
    ];

    public function cliente()
    {
        return $this->belongsTo(Cliente::class);
    }

    public function pagadoPor()
    {
        return $this->belongsTo(\App\Models\User::class, 'pagado_por');
    }

    // Relación polimórfica con vendedor (User o Tecnico)
    public function vendedor()
    {
        return $this->morphTo();
    }

    // Relación polimórfica para productos
    public function productos()
    {
        return $this->morphedByMany(
            Producto::class,
            'ventable',
            'venta_items',
            'venta_id',
            'ventable_id'
        )->withPivot('cantidad', 'precio', 'descuento', 'costo_unitario')
        ->wherePivot('ventable_type', Producto::class)
        ->whereHasMorph('ventable', Producto::class, function ($query) {
            $query->active();
        });
    }

    // Relación polimórfica para servicios
    public function servicios()
    {
        return $this->morphedByMany(
            Servicio::class,
            'ventable',
            'venta_items',
            'venta_id',
            'ventable_id'
        )->withPivot('cantidad', 'precio', 'descuento', 'costo_unitario')
        ->wherePivot('ventable_type', Servicio::class)
        ->whereHasMorph('ventable', Servicio::class, function ($query) {
            $query->active();
        });
    }

    // Todos los ítems (productos + servicios)
    public function items()
    {
        return $this->hasMany(VentaItem::class, 'venta_id');
    }

    // Calcular ganancia total de la venta
    public function getGananciaTotalAttribute()
    {
        $ganancia = 0;

        // Obtener el vendedor (User o Tecnico)
        $vendedor = $this->vendedor;

        // Ganancia de productos
        foreach ($this->productos as $producto) {
            $pivot = $producto->pivot;
            $precioVenta = $pivot->precio - ($pivot->descuento ?? 0);
            $costo = $pivot->costo_unitario ?? $producto->precio_compra;
            $gananciaBase = ($precioVenta - $costo) * $pivot->cantidad;

            // Aplicar comisión individual del producto
            $comisionProducto = $gananciaBase * ($producto->comision_vendedor / 100);
            $ganancia += $gananciaBase + $comisionProducto;

            // Si hay vendedor técnico, aplicar márgenes adicionales del técnico
            if ($vendedor && $vendedor instanceof \App\Models\Tecnico) {
                $margenTecnico = $vendedor->margen_venta_productos / 100;
                $ganancia += $gananciaBase * $margenTecnico;
            }
        }

        // Ganancia de servicios
        foreach ($this->servicios as $servicio) {
            $pivot = $servicio->pivot;
            $precioVenta = $pivot->precio - ($pivot->descuento ?? 0);
            $gananciaBase = $precioVenta * ($servicio->margen_ganancia / 100) * $pivot->cantidad;

            // Aplicar comisión individual del servicio
            $comisionServicio = $servicio->comision_vendedor * $pivot->cantidad;
            $ganancia += $gananciaBase + $comisionServicio;

            // Si hay vendedor técnico, aplicar márgenes adicionales del técnico
            if ($vendedor && $vendedor instanceof \App\Models\Tecnico) {
                $margenTecnico = $vendedor->margen_venta_servicios / 100;
                $ganancia += $gananciaBase * $margenTecnico;

                // Si es servicio de instalación, agregar comisión adicional del técnico
                if ($servicio->es_instalacion) {
                    $ganancia += $vendedor->comision_instalacion * $pivot->cantidad;
                }
            }
        }

        return $ganancia;
    }

    // Calcular costo total de la venta
    public function calcularCostoTotal()
    {
        $costoTotal = 0;

        // Costo de productos
        foreach ($this->productos as $producto) {
            $pivot = $producto->pivot;
            $costo = $pivot->costo_unitario ?? $producto->precio_compra;
            $costoTotal += $costo * $pivot->cantidad;
        }

        // Costo de servicios (considerando margen de ganancia)
        foreach ($this->servicios as $servicio) {
            $pivot = $servicio->pivot;
            $precioVenta = $pivot->precio - ($pivot->descuento ?? 0);
            // El costo del servicio es el precio de venta menos el margen de ganancia
            $costoServicio = $precioVenta * (1 - $servicio->margen_ganancia / 100);
            $costoTotal += $costoServicio * $pivot->cantidad;
        }

        return $costoTotal;
    }
}
