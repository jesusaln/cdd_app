<?php

namespace App\Http\Controllers;

use App\Models\Cotizacion;
use App\Models\Pedido;
use App\Models\Cliente;
use App\Models\Producto;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Inertia\Inertia;

class CotizacionController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $cotizaciones = Cotizacion::with('cliente', 'productos')->get();
        return Inertia::render('Cotizaciones/Index', [
            'cotizaciones' => $cotizaciones,
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
        return Inertia::render('Cotizaciones/Create', [
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

        // Crear la cotización
        $cotizacion = Cotizacion::create([
            'cliente_id' => $validatedData['cliente_id'],
            'total' => array_sum(array_map(function ($producto) {
                return $producto['cantidad'] * $producto['precio'];
            }, $validatedData['productos'])),
        ]);

        // Asociar los productos a la cotización
        foreach ($validatedData['productos'] as $productoData) {
            $cotizacion->productos()->attach($productoData['id'], [
                'cantidad' => $productoData['cantidad'],
                'precio' => $productoData['precio'],
            ]);
        }

        // Redirigir con un mensaje de éxito
        return redirect()->route('cotizaciones.index')->with('success', 'Cotización creada exitosamente.');
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $cotizacion = Cotizacion::with('cliente', 'productos')->findOrFail($id);
        return Inertia::render('Cotizaciones/Show', [
            'cotizacion' => $cotizacion,
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
        $cotizacion = Cotizacion::with('cliente', 'productos')->findOrFail($id);
        $clientes = Cliente::all();
        $productos = Producto::all();
        return Inertia::render('Cotizaciones/Edit', [
            'cotizacion' => $cotizacion,
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
        $cotizacion = Cotizacion::findOrFail($id);

        // Validar los datos de entrada
        $validatedData = $request->validate([
            'cliente_id' => 'required|exists:clientes,id',
            'productos' => 'required|array',
            'productos.*.id' => 'required|exists:productos,id',
            'productos.*.cantidad' => 'required|integer|min:1',
            'productos.*.precio' => 'required|numeric|min:0',
        ]);

        // Actualizar la cotización
        $cotizacion->update([
            'cliente_id' => $validatedData['cliente_id'],
            'total' => array_sum(array_map(function ($producto) {
                return $producto['cantidad'] * $producto['precio'];
            }, $validatedData['productos'])),
        ]);

        // Sincronizar los productos de la cotización
        $cotizacion->productos()->sync([]); // Eliminar todos los productos actuales
        foreach ($validatedData['productos'] as $productoData) {
            $cotizacion->productos()->attach($productoData['id'], [
                'cantidad' => $productoData['cantidad'],
                'precio' => $productoData['precio'],
            ]);
        }

        // Redirigir con un mensaje de éxito
        return redirect()->route('cotizaciones.index')->with('success', 'Cotización actualizada exitosamente.');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $cotizacion = Cotizacion::findOrFail($id);
        $cotizacion->delete();
        return redirect()->route('cotizaciones.index')->with('success', 'Cotización eliminada exitosamente.');
    }


    /**
     * Convertir una cotización en un pedido.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function convertirAPedido($id)
    {
        // Validar la cotización
        $cotizacion = Cotizacion::with('cliente', 'productos')->findOrFail($id);

        // Crear un nuevo pedido
        $pedido = new Pedido();
        $pedido->cliente_id = $cotizacion->cliente_id;
        $pedido->total = $cotizacion->total;
        $pedido->save();

        // Asociar los productos al pedido
        foreach ($cotizacion->productos as $producto) {
            $pedido->productos()->attach($producto->id, [
                'cantidad' => $producto->pivot->cantidad,
                'precio' => $producto->pivot->precio,
            ]);
        }

        // Opcional: Marcar la cotización como convertida o eliminarla
        // $cotizacion->delete();

        return redirect()->route('pedidos.index')->with('success', 'Cotización convertida a pedido exitosamente.');
    }
}
