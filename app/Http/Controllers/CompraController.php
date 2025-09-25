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
        return Inertia::render('Compras/Index', ['compras' => $compras]);
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

            $compra = Compra::create([
                'proveedor_id' => $validatedData['proveedor_id'],
                'total' => $total,
            ]);

            foreach ($validatedData['productos'] as $productoData) {
                $producto = Producto::findOrFail($productoData['id']);
                $cantidad = $productoData['cantidad'];
                $precio = $productoData['precio'];
                $descuento = $productoData['descuento'] ?? 0;
                $subtotal = $cantidad * $precio;
                $descuentoMonto = $subtotal * ($descuento / 100);
                $subtotalFinal = $subtotal - $descuentoMonto;

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

        return redirect()->route('compras.index')->with('success', 'Compra creada exitosamente.');
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
        $compra = Compra::findOrFail($id);
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
     * Cancel the specified resource (soft cancel).
     */
    public function cancel($id)
    {
        $compra = Compra::findOrFail($id);

        // Permitir cancelar en cualquier estado excepto ya devuelto
        if ($compra->estado === EstadoCompra::Devuelto) {
            return Redirect::back()->with('error', 'La compra ya está devuelta');
        }

        // Actualizar estado a devuelto y registrar quién lo canceló
        $compra->update([
            'estado' => EstadoCompra::Devuelto,
            'deleted_by' => Auth::id(),
            'deleted_at' => now()
        ]);

        return Redirect::route('compras.index')
            ->with('success', 'Compra devuelta exitosamente');
    }

    /**
     * Duplicar una compra.
     */
    public function duplicate(Request $request, $id)
    {
        $original = Compra::with('proveedor', 'productos')->findOrFail($id);

        try {
            return DB::transaction(function () use ($original) {
                // Replicar EXCLUYENDO campos problemáticos
                $nueva = $original->replicate([
                    'numero_compra', // ← evita duplicar el mismo número
                    'created_at',
                    'updated_at',
                    'estado',
                ]);

                // Estado nuevo (borrador) y número único
                $nueva->estado = EstadoCompra::Borrador;
                $nueva->numero_compra = Compra::generarNumero();
                $nueva->created_at = now();
                $nueva->updated_at = now();

                $nueva->save();

                // Duplicar ítems (crea el FK compra_id automáticamente)
                foreach ($original->productos as $item) {
                    $nueva->productos()->create([
                        'comprable_id'    => $item->comprable_id,
                        'comprable_type'  => $item->comprable_type,
                        'cantidad'        => $item->cantidad,
                        'precio'          => $item->precio,
                        'descuento'       => $item->descuento,
                        'subtotal'        => $item->subtotal,
                        'descuento_monto' => $item->descuento_monto,
                    ]);
                }

                return Redirect::route('compras.index')
                    ->with('success', 'Compra duplicada correctamente.');
            });
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('Error duplicando compra: ' . $e->getMessage(), ['id' => $id]);
            return Redirect::back()->with('error', 'Error al duplicar la compra.');
        }
    }

    public function destroy($id)
    {
        $compra = Compra::with('productos')->findOrFail($id);

        DB::transaction(function () use ($compra) {
            foreach ($compra->productos as $item) {
                $producto = $item->comprable;
                $producto->stock -= $item->cantidad;
                if ($producto->stock < 0) {
                    throw new \Exception("El stock del producto '{$producto->nombre}' no puede ser negativo.");
                }
                $producto->save();
            }

            $compra->delete();
        });

        return redirect()->route('compras.index')->with('success', 'Compra eliminada exitosamente.');
    }
}
