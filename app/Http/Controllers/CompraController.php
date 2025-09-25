<?php

namespace App\Http\Controllers;

use App\Models\Compra;
use App\Models\CompraItem;
use App\Models\Proveedor;
use App\Models\Producto;
use App\Enums\EstadoCompra;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Inertia\Inertia;

class CompraController extends Controller
{
    public function index()
    {
        $compras = Compra::with('proveedor', 'productos')->get()->map(function ($compra) {
            $compra->productos = $compra->productos->map(function ($producto) {
                return [
                    'id' => $producto->id,
                    'nombre' => $producto->nombre,
                    'descripcion' => $producto->descripcion,
                    'cantidad' => $producto->pivot->cantidad,
                    'precio' => $producto->pivot->precio,
                    'descuento' => $producto->pivot->descuento,
                    'subtotal' => $producto->pivot->subtotal,
                    'descuento_monto' => $producto->pivot->descuento_monto,
                ];
            });
            return $compra;
        });

        // Estadísticas para el frontend
        $stats = [
            'total' => $compras->count(),
            'procesadas' => $compras->where('estado', EstadoCompra::Procesada)->count(),
            'canceladas' => $compras->where('estado', EstadoCompra::Cancelada)->count(),
        ];

        return Inertia::render('Compras/Index', [
            'compras' => $compras,
            'stats' => $stats
        ]);
    }

    public function create()
    {
        $proveedores = Proveedor::all();
        $productos = Producto::all();
        return Inertia::render('Compras/Create', ['proveedores' => $proveedores, 'productos' => $productos]);
    }

    private function validateCompraRequest(Request $request)
    {
        return $request->validate([
            'proveedor_id' => 'required|exists:proveedores,id',
            'productos' => 'required|array|min:1',
            'productos.*.id' => 'required|exists:productos,id',
            'productos.*.cantidad' => 'required|integer|min:1',
            'productos.*.precio' => 'required|numeric|min:0',
        ]);
    }

    public function store(Request $request)
    {
        $validatedData = $this->validateCompraRequest($request);

        DB::transaction(function () use ($validatedData) {
            // Calcular total con descuentos
            $total = 0;
            foreach ($validatedData['productos'] as $productoData) {
                $cantidad = $productoData['cantidad'];
                $precio = $productoData['precio'];
                $descuento = $productoData['descuento'] ?? 0;
                $subtotal = $cantidad * $precio;
                $descuentoMonto = $subtotal * ($descuento / 100);
                $total += $subtotal - $descuentoMonto;
            }

            // Crear compra (automáticamente se marca como procesada en el modelo)
            $compra = Compra::create([
                'proveedor_id' => $validatedData['proveedor_id'],
                'total' => $total,
            ]);

            // Procesar productos y aumentar inventario
            foreach ($validatedData['productos'] as $productoData) {
                $producto = Producto::findOrFail($productoData['id']);
                $cantidad = $productoData['cantidad'];
                $precio = $productoData['precio'];
                $descuento = $productoData['descuento'] ?? 0;
                $subtotal = $cantidad * $precio;
                $descuentoMonto = $subtotal * ($descuento / 100);
                $subtotalFinal = $subtotal - $descuentoMonto;

                // Aumentar stock automáticamente al crear la compra
                $producto->stock += $cantidad;
                $producto->save();

                CompraItem::create([
                    'compra_id' => $compra->id,
                    'comprable_id' => $productoData['id'],
                    'comprable_type' => Producto::class,
                    'cantidad' => $cantidad,
                    'precio' => $precio,
                    'descuento' => $descuento,
                    'subtotal' => $subtotalFinal,
                    'descuento_monto' => $descuentoMonto,
                ]);
            }
        });

        return redirect()->route('compras.index')->with('success', 'Compra procesada exitosamente.');
    }

    public function show($id)
    {
        $compra = Compra::with('proveedor', 'productos')->findOrFail($id);
        $compra->productos = $compra->productos->map(function ($producto) {
            return [
                'id' => $producto->id,
                'nombre' => $producto->nombre,
                'descripcion' => $producto->descripcion,
                'cantidad' => $producto->pivot->cantidad,
                'precio' => $producto->pivot->precio,
                'descuento' => $producto->pivot->descuento,
                'subtotal' => $producto->pivot->subtotal,
                'descuento_monto' => $producto->pivot->descuento_monto,
            ];
        });
        return Inertia::render('Compras/Show', ['compra' => $compra]);
    }

    public function edit($id)
    {
        $compra = Compra::with('proveedor', 'productos')->findOrFail($id);
        $compra->productos = $compra->productos->map(function ($producto) {
            return [
                'id' => $producto->id,
                'nombre' => $producto->nombre,
                'descripcion' => $producto->descripcion,
                'cantidad' => $producto->pivot->cantidad,
                'precio' => $producto->pivot->precio,
                'descuento' => $producto->pivot->descuento,
                'subtotal' => $producto->pivot->subtotal,
                'descuento_monto' => $producto->pivot->descuento_monto,
            ];
        });
        $proveedores = Proveedor::all();
        $productos = Producto::all();
        return Inertia::render('Compras/Edit', ['compra' => $compra, 'proveedores' => $proveedores, 'productos' => $productos]);
    }

    public function update(Request $request, $id)
    {
        $compra = Compra::with('productos')->findOrFail($id);

        // Solo se pueden editar compras procesadas
        if ($compra->estado !== EstadoCompra::Procesada) {
            return redirect()->back()->with('error', 'Solo se pueden editar compras procesadas.');
        }

        $validatedData = $this->validateCompraRequest($request);

        DB::transaction(function () use ($compra, $validatedData) {
            // Restar cantidades antiguas del stock
            foreach ($compra->productos as $item) {
                $producto = $item->comprable;
                $producto->stock -= $item->cantidad;
                if ($producto->stock < 0) {
                    throw new \Exception("El stock del producto '{$producto->nombre}' no puede ser negativo.");
                }
                $producto->save();
            }

            // Calcular total con descuentos
            $total = 0;
            foreach ($validatedData['productos'] as $productoData) {
                $cantidad = $productoData['cantidad'];
                $precio = $productoData['precio'];
                $descuento = $productoData['descuento'] ?? 0;
                $subtotal = $cantidad * $precio;
                $descuentoMonto = $subtotal * ($descuento / 100);
                $total += $subtotal - $descuentoMonto;
            }

            // Actualizar la compra
            $compra->update([
                'proveedor_id' => $validatedData['proveedor_id'],
                'total' => $total,
            ]);

            // Eliminar items antiguos
            $compra->productos()->delete();

            // Crear items nuevos y agregar al stock
            foreach ($validatedData['productos'] as $productoData) {
                $producto = Producto::findOrFail($productoData['id']);
                $producto->stock += $productoData['cantidad'];
                if ($producto->stock < 0) {
                    throw new \Exception("El stock del producto '{$producto->nombre}' no puede ser negativo.");
                }
                $producto->save();

                $cantidad = $productoData['cantidad'];
                $precio = $productoData['precio'];
                $descuento = $productoData['descuento'] ?? 0;
                $subtotal = $cantidad * $precio;
                $descuentoMonto = $subtotal * ($descuento / 100);
                $subtotalFinal = $subtotal - $descuentoMonto;

                CompraItem::create([
                    'compra_id' => $compra->id,
                    'comprable_id' => $productoData['id'],
                    'comprable_type' => Producto::class,
                    'cantidad' => $cantidad,
                    'precio' => $precio,
                    'descuento' => $descuento,
                    'subtotal' => $subtotalFinal,
                    'descuento_monto' => $descuentoMonto,
                ]);
            }
        });

        return redirect()->route('compras.index')->with('success', 'Compra actualizada exitosamente.');
    }

    /**
     * Cancelar la compra y disminuir inventario
     */
    public function cancel($id)
    {
        $compra = Compra::with('productos')->findOrFail($id);

        // Solo se puede cancelar si está procesada
        if ($compra->estado !== EstadoCompra::Procesada) {
            return Redirect::back()->with('error', 'Solo se pueden cancelar compras procesadas');
        }

        DB::transaction(function () use ($compra) {
            // Disminuir inventario de todos los productos
            foreach ($compra->productos as $item) {
                $producto = $item->comprable;
                $producto->stock -= $item->cantidad;
                if ($producto->stock < 0) {
                    throw new \Exception("El stock del producto '{$producto->nombre}' no puede ser negativo.");
                }
                $producto->save();
            }

            // Cambiar estado a cancelado
            $compra->update([
                'estado' => EstadoCompra::Cancelada,
            ]);
        });

        return Redirect::route('compras.index')
            ->with('success', 'Compra cancelada exitosamente');
    }


    public function destroy($id)
    {
        $compra = Compra::findOrFail($id);

        // Si ya está cancelada, no hacer nada
        if ($compra->estado === EstadoCompra::Cancelada) {
            return redirect()->route('compras.index')->with('error', 'La compra ya está cancelada.');
        }

        // Usar el método cancel para mantener consistencia
        return $this->cancel($id);
    }
}
