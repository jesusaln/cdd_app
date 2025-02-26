<?php

namespace App\Http\Controllers;

use App\Models\Venta;
use App\Models\Cliente;
use App\Models\Producto;
use Illuminate\Http\Request;
use Inertia\Inertia;

class VentaController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $ventas = Venta::with('cliente', 'productos')->get();
        return Inertia::render('Ventas/Index', [
            'ventas' => $ventas,
        ]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $clientes = Cliente::all();
        $productos = Producto::all();
        return Inertia::render('Ventas/Create', [
            'clientes' => $clientes,
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
            'cliente_id' => 'required|exists:clientes,id',
            'productos' => 'required|array',
            'productos.*.id' => 'required|exists:productos,id',
            'productos.*.cantidad' => 'required|integer|min:1',
            'productos.*.precio' => 'required|numeric|min:0',
        ]);

        // Crear la venta
        $venta = Venta::create([
            'cliente_id' => $validatedData['cliente_id'],
            'total' => array_sum(array_map(function ($producto) {
                return $producto['cantidad'] * $producto['precio'];
            }, $validatedData['productos'])),
        ]);

        // Asociar los productos a la venta
        foreach ($validatedData['productos'] as $productoData) {
            $venta->productos()->attach($productoData['id'], [
                'cantidad' => $productoData['cantidad'],
                'precio' => $productoData['precio'],
            ]);
        }

        // Redirigir con un mensaje de éxito
        return redirect()->route('ventas.index')->with('success', 'Venta creada exitosamente.');
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $venta = Venta::with('cliente', 'productos')->findOrFail($id);
        return Inertia::render('Ventas/Show', [
            'venta' => $venta,
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
        $venta = Venta::with('cliente', 'productos')->findOrFail($id);
        $clientes = Cliente::all();
        $productos = Producto::all();
        return Inertia::render('Ventas/Edit', [
            'venta' => $venta,
            'clientes' => $clientes,
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
        $venta = Venta::findOrFail($id);

        // Validar los datos de entrada
        $validatedData = $request->validate([
            'cliente_id' => 'required|exists:clientes,id',
            'productos' => 'required|array',
            'productos.*.id' => 'required|exists:productos,id',
            'productos.*.cantidad' => 'required|integer|min:1',
            'productos.*.precio' => 'required|numeric|min:0',
        ]);

        // Actualizar la venta
        $venta->update([
            'cliente_id' => $validatedData['cliente_id'],
            'total' => array_sum(array_map(function ($producto) {
                return $producto['cantidad'] * $producto['precio'];
            }, $validatedData['productos'])),
        ]);

        // Sincronizar los productos de la venta
        $venta->productos()->sync([]); // Eliminar todos los productos actuales
        foreach ($validatedData['productos'] as $productoData) {
            $venta->productos()->attach($productoData['id'], [
                'cantidad' => $productoData['cantidad'],
                'precio' => $productoData['precio'],
            ]);
        }

        // Redirigir con un mensaje de éxito
        return redirect()->route('ventas.index')->with('success', 'Venta actualizada exitosamente.');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $venta = Venta::findOrFail($id);
        $venta->delete();
        return redirect()->route('ventas.index')->with('success', 'Venta eliminada exitosamente.');
    }
}
