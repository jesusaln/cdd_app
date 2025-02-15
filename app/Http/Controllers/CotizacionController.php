<?php

namespace App\Http\Controllers;

use App\Models\Cotizacion;
use App\Models\Cliente;
use App\Models\Producto;
use Illuminate\Http\Request;
use Inertia\Inertia;

class CotizacionController extends Controller
{


    public function index()
    {
        $cotizaciones = Cotizacion::with('cliente', 'productos')->get();
        return Inertia::render('Cotizaciones/Index', [
            'cotizaciones' => $cotizaciones
        ]);
    }

    /**
     * Mostrar el formulario para crear una nueva cotización.
     */
    public function create()
    {
        $clientes = Cliente::all();
        $productos = Producto::all();
        return response()->json([
            'clientes' => $clientes,
            'productos' => $productos
        ]);
    }

    /**
     * Almacenar una nueva cotización en la base de datos.
     */
    public function store(Request $request)
    {
        // Validar los datos recibidos
        $validated = $request->validate([
            'cliente_id' => 'required|exists:clientes,id',
            'productos' => 'required|array',
            'productos.*.id' => 'required|exists:productos,id',
            'productos.*.precio' => 'required|numeric',
            'total' => 'required|numeric'
        ]);

        // Crear la cotización
        $cotizacion = new Cotizacion();
        $cotizacion->cliente_id = $validated['cliente_id'];
        $cotizacion->total = $validated['total'];
        $cotizacion->save();

        // Adjuntar los productos a la cotización
        foreach ($validated['productos'] as $producto) {
            $cotizacion->productos()->attach($producto['id'], ['precio' => $producto['precio']]);
        }

        return response()->json(['message' => 'Cotización creada con éxito'], 201);
    }

    /**
     * Mostrar una cotización específica.
     */
    public function show($id)
    {
        $cotizacion = Cotizacion::with('cliente', 'productos')->findOrFail($id);
        return response()->json($cotizacion);
    }

    /**
     * Mostrar el formulario para editar una cotización existente.
     */
    public function edit($id)
    {
        $cotizacion = Cotizacion::with('cliente', 'productos')->findOrFail($id);
        $clientes = Cliente::all();
        $productos = Producto::all();

        return response()->json([
            'cotizacion' => $cotizacion,
            'clientes' => $clientes,
            'productos' => $productos
        ]);
    }

    /**
     * Actualizar una cotización existente en la base de datos.
     */
    public function update(Request $request, $id)
    {
        // Validar los datos recibidos
        $validated = $request->validate([
            'cliente_id' => 'required|exists:clientes,id',
            'productos' => 'required|array',
            'productos.*.id' => 'required|exists:productos,id',
            'productos.*.precio' => 'required|numeric',
            'total' => 'required|numeric'
        ]);

        // Encontrar la cotización
        $cotizacion = Cotizacion::findOrFail($id);

        // Actualizar los datos de la cotización
        $cotizacion->cliente_id = $validated['cliente_id'];
        $cotizacion->total = $validated['total'];
        $cotizacion->save();

        // Sincronizar los productos
        $productosSync = [];
        foreach ($validated['productos'] as $producto) {
            $productosSync[$producto['id']] = ['precio' => $producto['precio']];
        }
        $cotizacion->productos()->sync($productosSync);

        return response()->json(['message' => 'Cotización actualizada con éxito']);
    }

    /**
     * Eliminar una cotización existente de la base de datos.
     */
    public function destroy($id)
    {
        $cotizacion = Cotizacion::findOrFail($id);
        $cotizacion->delete();

        return response()->json(['message' => 'Cotización eliminada con éxito']);
    }
}
