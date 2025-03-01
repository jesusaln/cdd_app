<?php

namespace App\Http\Controllers;

use App\Models\Compra;
use App\Models\Proveedor;
use App\Models\Producto;
use Illuminate\Http\Request;
use Inertia\Inertia;

class CompraController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $compras = Compra::with('proveedor', 'productos')->get();
        return Inertia::render('Compras/Index', [
            'compras' => $compras,
        ]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $proveedores = Proveedor::all();
        $productos = Producto::all();
        return Inertia::render('Compras/Create', [
            'proveedores' => $proveedores,
            'productos' => $productos,
        ]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        // Validar los datos de entrada
        $validatedData = $request->validate([
            'proveedor_id' => 'required|exists:proveedores,id',
            'productos' => 'required|array',
            'productos.*.id' => 'required|exists:productos,id',
            'productos.*.cantidad' => 'required|integer|min:1', // Asegúrate de que este campo exista en el frontend
            'productos.*.precio' => 'required|numeric|min:0',
        ]);

        // Crear la compra
        $compra = Compra::create([
            'proveedor_id' => $validatedData['proveedor_id'],
            'total' => array_sum(array_map(function ($producto) {
                return $producto['cantidad'] * $producto['precio'];
            }, $validatedData['productos'])),
        ]);

        // Asociar los productos a la compra y actualizar el inventario
        foreach ($validatedData['productos'] as $productoData) {
            // Actualizar el inventario (usando el campo "stock")
            $producto = Producto::findOrFail($productoData['id']);
            $producto->stock += $productoData['cantidad']; // Incrementar el stock
            $producto->save();

            // Asociar los productos a la compra
            $compra->productos()->attach($productoData['id'], [
                'cantidad' => $productoData['cantidad'],
                'precio' => $productoData['precio'],
            ]);
        }

        // Redirigir con un mensaje de éxito
        return redirect()->route('compras.index')->with('success', 'Compra creada exitosamente.');
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $compra = Compra::with('proveedor', 'productos')->findOrFail($id);
        return Inertia::render('Compras/Show', [
            'compra' => $compra,
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $compra = Compra::with('proveedor', 'productos')->findOrFail($id);
        $proveedores = Proveedor::all();
        $productos = Producto::all();
        return Inertia::render('Compras/Edit', [
            'compra' => $compra,
            'proveedores' => $proveedores,
            'productos' => $productos,
        ]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $compra = Compra::findOrFail($id);

        // Validar los datos de entrada
        $validatedData = $request->validate([
            'proveedor_id' => 'required|exists:proveedores,id',
            'productos' => 'required|array',
            'productos.*.id' => 'required|exists:productos,id',
            'productos.*.cantidad' => 'required|integer|min:1',
            'productos.*.precio' => 'required|numeric|min:0',
        ]);

        // Actualizar la compra
        $compra->update([
            'proveedor_id' => $validatedData['proveedor_id'],
            'total' => array_sum(array_map(function ($producto) {
                return $producto['cantidad'] * $producto['precio'];
            }, $validatedData['productos'])),
        ]);

        // Sincronizar los productos de la compra
        $compra->productos()->sync([]); // Eliminar todos los productos actuales
        foreach ($validatedData['productos'] as $productoData) {
            // Actualizar el inventario
            $producto = Producto::findOrFail($productoData['id']);
            $producto->cantidad += $productoData['cantidad']; // Aumentar la cantidad en el inventario
            $producto->save();

            // Asociar los productos a la compra
            $compra->productos()->attach($productoData['id'], [
                'cantidad' => $productoData['cantidad'],
                'precio' => $productoData['precio'],
            ]);
        }

        // Redirigir con un mensaje de éxito
        return redirect()->route('compras.index')->with('success', 'Compra actualizada exitosamente.');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $compra = Compra::with('productos')->findOrFail($id);

        // Recorrer los productos asociados a la compra y restar la cantidad del inventario
        foreach ($compra->productos as $producto) {
            $producto->stock -= $producto->pivot->cantidad; // Restar la cantidad del stock
            $producto->save();
        }

        // Eliminar la compra
        $compra->delete();

        // Redirigir con un mensaje de éxito
        return redirect()->route('compras.index')->with('success', 'Compra eliminada exitosamente.');
    }
}
