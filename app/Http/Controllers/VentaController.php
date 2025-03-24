<?php

namespace App\Http\Controllers;

use App\Models\Venta;
use App\Models\Cliente;
use App\Models\Producto;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\DB;

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

        // Verificar si hay suficiente stock
        foreach ($validatedData['productos'] as $productoData) {
            $producto = Producto::findOrFail($productoData['id']);
            if ($producto->stock < $productoData['cantidad']) {
                return redirect()->back()->withErrors(['stock' => 'No hay suficiente stock para el producto: ' . $producto->nombre]);
            }
        }

        // Crear la venta
        $venta = Venta::create([
            'cliente_id' => $validatedData['cliente_id'],
            'total' => array_sum(array_map(function ($producto) {
                return $producto['cantidad'] * $producto['precio'];
            }, $validatedData['productos'])),
        ]);

        // Asociar los productos a la venta y actualizar el inventario
        foreach ($validatedData['productos'] as $productoData) {
            $producto = Producto::findOrFail($productoData['id']);
            $producto->stock -= $productoData['cantidad']; // Restar la cantidad del stock
            $producto->save();

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
            'productos.*.original_cantidad' => 'nullable|integer|min:0', // Añadido para comparar cantidades originales
        ]);

        // Ejecutar en una transacción para garantizar integridad
        DB::transaction(function () use ($venta, $validatedData) {
            // Calcular el total
            $total = array_sum(array_map(function ($producto) {
                return $producto['cantidad'] * $producto['precio'];
            }, $validatedData['productos']));

            // Actualizar datos de la venta
            $venta->update([
                'cliente_id' => $validatedData['cliente_id'],
                'total' => $total,
            ]);

            // Obtener los productos actuales de la venta para comparar
            $productosActuales = $venta->productos()
                ->get()
                ->pluck('pivot.cantidad', 'id')
                ->all();

            // Preparar los nuevos productos para sincronizar
            $syncData = [];
            foreach ($validatedData['productos'] as $productoData) {
                $productoId = $productoData['id'];
                $nuevaCantidad = $productoData['cantidad'];
                $originalCantidad = $productoData['original_cantidad'] ?? ($productosActuales[$productoId] ?? 0);

                // Calcular diferencia para ajustar el stock
                $diferencia = $nuevaCantidad - $originalCantidad;
                if ($diferencia != 0) {
                    $producto = Producto::find($productoId);
                    if ($producto->stock - $diferencia < 0) {
                        throw new \Exception("Stock insuficiente para el producto: {$producto->nombre}");
                    }
                    $producto->stock -= $diferencia; // Descontar o aumentar stock
                    $producto->save();
                }

                $syncData[$productoId] = [
                    'cantidad' => $nuevaCantidad,
                    'precio' => $productoData['precio'],
                ];
            }

            // Sincronizar los productos (reemplaza los existentes)
            $venta->productos()->sync($syncData);
        });

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
        // Encontrar la venta por ID
        $venta = Venta::with('productos')->findOrFail($id);

        // Recorrer los productos asociados a la venta y devolver la cantidad al inventario
        foreach ($venta->productos as $producto) {
            $producto->stock += $producto->pivot->cantidad; // Sumar la cantidad vendida al stock
            $producto->save();
        }

        // Eliminar la venta
        $venta->delete();

        // Redirigir con un mensaje de éxito
        return redirect()->route('ventas.index')->with('success', 'Venta eliminada exitosamente.');
    }
}
