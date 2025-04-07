<?php

namespace App\Http\Controllers;

use App\Models\Compra;
use App\Models\Proveedor;
use App\Models\Producto;
use Illuminate\Http\Request;
use Inertia\Inertia;

class CompraController extends Controller
{
    public function index()
    {
        $compras = Compra::with('proveedor', 'productos')->get();
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
            'productos' => 'required|array',
            'productos.*.id' => 'required|exists:productos,id',
            'productos.*.cantidad' => 'required|integer|min:1',
            'productos.*.precio' => 'required|numeric|min:0',
        ]);
    }

    public function store(Request $request)
    {
        $validatedData = $this->validateCompraRequest($request);

        $compra = Compra::create([
            'proveedor_id' => $validatedData['proveedor_id'],
            'total' => array_sum(array_map(function ($producto) {
                return $producto['cantidad'] * $producto['precio'];
            }, $validatedData['productos'])),
        ]);

        foreach ($validatedData['productos'] as $productoData) {
            $producto = Producto::findOrFail($productoData['id']);
            $producto->stock += $productoData['cantidad'];
            $producto->save();

            $compra->productos()->attach($productoData['id'], [
                'cantidad' => $productoData['cantidad'],
                'precio' => $productoData['precio'],
            ]);
        }

        return redirect()->route('compras.index')->with('success', 'Compra creada exitosamente.');
    }

    public function show($id)
    {
        $compra = Compra::with('proveedor', 'productos')->findOrFail($id);
        return Inertia::render('Compras/Show', ['compra' => $compra]);
    }

    public function edit($id)
    {
        $compra = Compra::with('proveedor', 'productos')->findOrFail($id);
        $proveedores = Proveedor::all();
        $productos = Producto::all();
        return Inertia::render('Compras/Edit', ['compra' => $compra, 'proveedores' => $proveedores, 'productos' => $productos]);
    }

    public function update(Request $request, $id)
    {
        $compra = Compra::findOrFail($id);
        $validatedData = $this->validateCompraRequest($request);

        // Restar cantidades antiguas del stock
        foreach ($compra->productos as $producto) {
            $producto->stock -= $producto->pivot->cantidad;
            if ($producto->stock < 0) {
                throw new \Exception("El stock del producto '{$producto->nombre}' no puede ser negativo.");
            }
            $producto->save();
        }

        // Actualizar la compra
        $compra->update([
            'proveedor_id' => $validatedData['proveedor_id'],
            'total' => array_sum(array_map(function ($producto) {
                return $producto['cantidad'] * $producto['precio'];
            }, $validatedData['productos'])),
        ]);

        // Agregar cantidades nuevas al stock
        $syncData = [];
        foreach ($validatedData['productos'] as $productoData) {
            $producto = Producto::findOrFail($productoData['id']);
            $producto->stock += $productoData['cantidad'];
            if ($producto->stock < 0) {
                throw new \Exception("El stock del producto '{$producto->nombre}' no puede ser negativo.");
            }
            $producto->save();

            $syncData[$productoData['id']] = [
                'cantidad' => $productoData['cantidad'],
                'precio' => $productoData['precio'],
            ];
        }

        $compra->productos()->sync($syncData);

        return redirect()->route('compras.index')->with('success', 'Compra actualizada exitosamente.');
    }

    public function destroy($id)
    {
        $compra = Compra::with('productos')->findOrFail($id);

        foreach ($compra->productos as $producto) {
            $producto->stock -= $producto->pivot->cantidad;
            if ($producto->stock < 0) {
                throw new \Exception("El stock del producto '{$producto->nombre}' no puede ser negativo.");
            }
            $producto->save();
        }

        $compra->delete();

        return redirect()->route('compras.index')->with('success', 'Compra eliminada exitosamente.');
    }
}
