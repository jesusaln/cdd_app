<?php

namespace App\Http\Controllers;

use App\Models\Cotizacion;
use App\Models\Producto;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Inertia\Inertia;
use App\Models\Cliente;
use App\Models\ProductoCotizacion;

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

    public function store(Request $request)
    {
        // Validar los datos de entrada
        $validator = Validator::make($request->all(), [
            'cliente_id' => 'required|exists:clientes,id',
            'productos' => 'required|array',
            'productos.*.id' => 'required|exists:productos,id',
            'productos.*.cantidad' => 'required|integer|min:1',
            'productos.*.precio' => 'required|numeric|min:0',
        ]);

        if ($validator->fails()) {
            // Redirigir con errores si la validación falla
            return back()->withErrors($validator)->withInput();
        }

        // Crear la cotización
        $cotizacion = Cotizacion::create([
            'cliente_id' => $request->cliente_id,
            'total' => $request->total,
        ]);

        // Asociar los productos a la cotización
        foreach ($request->productos as $productoData) {
            $producto = Producto::find($productoData['id']);
            $cotizacion->productos()->attach($producto->id, [
                'cantidad' => $productoData['cantidad'],
                'precio' => $productoData['precio'],
            ]);
        }

        // Redirigir con un mensaje de éxito
        return redirect()->route('cotizaciones.index')
            ->with('success', 'Cotización creada exitosamente.');
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
        $validator = Validator::make($request->all(), [
            'cliente_id' => 'required|exists:clientes,id',
            'productos' => 'required|array',
            'productos.*.id' => 'required|exists:productos,id',
            'productos.*.cantidad' => 'required|integer|min:1',
            'productos.*.precio' => 'required|numeric|min:0',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        // Actualizar la cotización
        $cotizacion->update([
            'cliente_id' => $request->cliente_id,
            'total' => $request->total,
        ]);

        // Sincronizar los productos de la cotización
        $cotizacion->productos()->sync([]); // Eliminar todos los productos actuales
        foreach ($request->productos as $productoData) {
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

    public function create()
    {
        $clientes = Cliente::all();
        $productos = Producto::all();
        return Inertia::render('Cotizaciones/Create', [
            'clientes' => $clientes,
            'productos' => $productos
        ]);
    }

    public function edit($id)
    {
        // Encuentra la cotización, incluyendo los productos y el cliente
        $cotizacion = Cotizacion::with('cliente', 'productos')->findOrFail($id);

        // Obtener la lista de clientes y productos para los selectores
        $clientes = Cliente::all();
        $productos = Producto::all();

        // Devolver los datos en formato JSON, para que Vue.js los pueda usar
        return inertia('Cotizaciones/Edit', [
            'cotizacion' => $cotizacion,
            'clientes' => $clientes,
            'productos' => $productos,
        ]);
    }
}
