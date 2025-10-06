<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;
use App\Enums\EstadoVenta; // Ajusta seg�n tu enum

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
        return $this->belongsTo(User::class, 'pagado_por');
    }

    // Relaci�n polim�rfica con vendedor (User o Tecnico)
    public function vendedor()
    {
        return $this->morphTo();
    }

    // Relaci�n polim�rfica para productos
    public function productos()
    {
        return $this->morphToMany(
            Producto::class,
            'ventable',
            'venta_items',
            'venta_id',
            'ventable_id'
        )->withPivot('cantidad', 'precio', 'descuento', 'costo_unitario')
        ->wherePivot('ventable_type', Producto::class)
        ->active();
    }

    // Relaci�n polim�rfica para servicios
    public function servicios()
    {
        return $this->morphToMany(
            Servicio::class,
            'ventable',
            'venta_items',
            'venta_id',
            'ventable_id'
        )->withPivot('cantidad', 'precio', 'descuento', 'costo_unitario')
        ->wherePivot('ventable_type', Servicio::class)
        ->active();
    }

    // Todos los �tems (productos + servicios)
    public function items()
    {
        return $this->hasMany(VentaItem::class, 'venta_id');
    }

    public function cuentaPorCobrar(): HasOne
    {
        return $this->hasOne(CuentasPorCobrar::class, 'venta_id');
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

            // Aplicar comisi�n individual del producto
            $comisionProducto = $gananciaBase * ($producto->comision_vendedor / 100);
            $ganancia += $gananciaBase + $comisionProducto;

            // Si hay vendedor t�cnico, aplicar m�rgenes adicionales del t�cnico
            if ($vendedor && $vendedor instanceof Tecnico) {
                $margenTecnico = $vendedor->margen_venta_productos / 100;
                $ganancia += $gananciaBase * $margenTecnico;
            }
        }

        // Ganancia de servicios
        foreach ($this->servicios as $servicio) {
            $pivot = $servicio->pivot;
            $precioVenta = $pivot->precio - ($pivot->descuento ?? 0);
            $gananciaBase = $precioVenta * ($servicio->margen_ganancia / 100) * $pivot->cantidad;

            // Aplicar comisi�n individual del servicio
            $comisionServicio = $servicio->comision_vendedor * $pivot->cantidad;
            $ganancia += $gananciaBase + $comisionServicio;

            // Si hay vendedor t�cnico, aplicar m�rgenes adicionales del t�cnico
            if ($vendedor && $vendedor instanceof Tecnico) {
                $margenTecnico = $vendedor->margen_venta_servicios / 100;
                $ganancia += $gananciaBase * $margenTecnico;

                // Si es servicio de instalaci�n, agregar comisi�n adicional del t�cnico
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

        // Costo de productos usando costo histórico correcto
        // Iterar sobre items para obtener productos correctamente relacionados
        foreach ($this->items as $item) {
            if ($item->ventable_type === \App\Models\Producto::class && $item->ventable) {
                $producto = $item->ventable;

                // Priorizar costo_unitario del item (ya calculado históricamente)
                // Si no existe, calcular costo histórico basado en movimientos recientes
                if ($item->costo_unitario && $item->costo_unitario > 0) {
                    $costo = $item->costo_unitario;
                } else {
                    // Fallback: calcular costo histórico basado en movimientos recientes
                    $costo = $producto->calcularCostoHistorico($item->cantidad);
                }

                $costoTotal += $costo * $item->cantidad;
            }
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

    /**
     * Recalcula y actualiza los costos históricos de todos los productos en esta venta
     */
    public function recalcularCostosHistoricos()
    {
        foreach ($this->items as $item) {
            if ($item->ventable_type === \App\Models\Producto::class && $item->ventable) {
                $producto = $item->ventable;

                // Calcular costo histórico correcto
                $nuevoCostoUnitario = $producto->calcularCostoPorLotes($item->cantidad);

                // Actualizar el costo unitario en el item si es diferente
                if ($nuevoCostoUnitario != $item->costo_unitario) {
                    $item->update(['costo_unitario' => $nuevoCostoUnitario]);
                }
            }
        }

        return $this->calcularCostoTotal();
    }
}
