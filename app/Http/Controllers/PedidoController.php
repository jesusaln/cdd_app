<?php

namespace App\Http\Controllers;

use App\Models\Pedido;
use App\Models\Cliente;
use App\Models\Producto;
use App\Models\Venta;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class PedidoController extends Controller
{

    /**
     * Convertir un pedido en una venta.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function enviarAVentas($id)
    {
        // Validar el pedido
        $pedido = Pedido::with('cliente', 'productos')->findOrFail($id);

        // Crear una nueva venta
        $venta = new Venta();
        $venta->cliente_id = $pedido->cliente_id;
        $venta->total = $pedido->total;
        //$venta->fecha_venta = now(); // Fecha actual de la venta
        $venta->save();

        // Asociar los productos a la venta
        foreach ($pedido->productos as $producto) {
            $venta->productos()->attach($producto->id, [
                'cantidad' => $producto->pivot->cantidad,
                'precio' => $producto->pivot->precio,
            ]);

            // Opcional: Actualizar el stock del producto
            $producto->decrement('stock', $producto->pivot->cantidad);
        }

        // Opcional: Marcar el pedido como convertido o eliminarlo
        // $pedido->delete();

        return redirect()->route('ventas.index')->with('success', 'Pedido convertido a venta exitosamente.');
    }




    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $pedidos = Pedido::with('cliente', 'productos')->get();
        return Inertia::render('Pedidos/Index', [
            'pedidos' => $pedidos,
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
        return Inertia::render('Pedidos/Create', [
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

        // Crear el pedido
        $pedido = Pedido::create([
            'cliente_id' => $validatedData['cliente_id'],
            'total' => array_sum(array_map(function ($producto) {
                return $producto['cantidad'] * $producto['precio'];
            }, $validatedData['productos'])),
        ]);

        // Asociar los productos al pedido
        foreach ($validatedData['productos'] as $productoData) {
            $pedido->productos()->attach($productoData['id'], [
                'cantidad' => $productoData['cantidad'],
                'precio' => $productoData['precio'],
            ]);
        }

        // Redirigir con un mensaje de éxito
        return redirect()->route('pedidos.index')->with('success', 'Pedido creado exitosamente.');
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $pedido = Pedido::with('cliente', 'productos')->findOrFail($id);
        return Inertia::render('Pedidos/Show', [
            'pedido' => $pedido,
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
        $pedido = Pedido::with('cliente', 'productos')->findOrFail($id);
        $clientes = Cliente::all();
        $productos = Producto::all();
        return Inertia::render('Pedidos/Edit', [
            'pedido' => $pedido,
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
        $pedido = Pedido::findOrFail($id);

        // Validar los datos de entrada
        $validatedData = $request->validate([
            'cliente_id' => 'required|exists:clientes,id',
            'productos' => 'required|array',
            'productos.*.id' => 'required|exists:productos,id',
            'productos.*.cantidad' => 'required|integer|min:1',
            'productos.*.precio' => 'required|numeric|min:0',
        ]);

        // Actualizar el pedido
        $pedido->update([
            'cliente_id' => $validatedData['cliente_id'],
            'total' => array_sum(array_map(function ($producto) {
                return $producto['cantidad'] * $producto['precio'];
            }, $validatedData['productos'])),
        ]);

        // Sincronizar los productos del pedido
        $pedido->productos()->sync([]); // Eliminar todos los productos actuales
        foreach ($validatedData['productos'] as $productoData) {
            $pedido->productos()->attach($productoData['id'], [
                'cantidad' => $productoData['cantidad'],
                'precio' => $productoData['precio'],
            ]);
        }

        // Redirigir con un mensaje de éxito
        return redirect()->route('pedidos.index')->with('success', 'Pedido actualizado exitosamente.');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $pedido = Pedido::findOrFail($id);
        $pedido->delete();
        return redirect()->route('pedidos.index')->with('success', 'Pedido eliminado exitosamente.');
    }
}
