<?php

namespace App\Http\Controllers\Api;

use App\Models\Cotizacion;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class CotizacionController extends Controller
{
    /**
     * Muestra una lista de todas las cotizaciones en formato JSON.
     */
    public function index()
    {
        try {
            $cotizaciones = Cotizacion::with(['cliente', 'productos'])->get();
            return response()->json($cotizaciones, 200);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Error al obtener las cotizaciones: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Muestra los detalles de una cotización específica.
     */
    public function show($id)
    {
        try {
            $cotizacion = Cotizacion::with(['cliente', 'productos'])->findOrFail($id);
            return response()->json($cotizacion, 200);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Error al obtener la cotización: ' . $e->getMessage()], 404);
        }
    }

    /**
     * Crea una nueva cotización.
     */
    public function store(Request $request)
    {
        try {
            // Validar los datos de entrada
            $validatedData = $request->validate([
                'cliente_id' => 'required|exists:clientes,id',
                'total' => 'required|numeric|min:0',
                'productos' => 'required|array', // Lista de productos
                'productos.*.producto_id' => 'required|exists:productos,id',
                'productos.*.cantidad' => 'required|integer|min:1',
                'productos.*.precio' => 'required|numeric|min:0',
            ]);

            // Crear la cotización
            $cotizacion = Cotizacion::create([
                'cliente_id' => $validatedData['cliente_id'],
                'total' => $validatedData['total'],
            ]);

            // Asociar productos a la cotización (usando una tabla pivote)
            $productos = [];
            foreach ($validatedData['productos'] as $producto) {
                $productos[$producto['producto_id']] = [
                    'cantidad' => $producto['cantidad'],
                    'precio' => $producto['precio'],
                ];
            }
            $cotizacion->productos()->sync($productos);

            // Devolver la cotización creada con relaciones cargadas
            return response()->json($cotizacion->load(['cliente', 'productos']), 201);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Error al crear la cotización: ' . $e->getMessage()], 400);
        }
    }

    /**
     * Actualiza una cotización existente.
     */
    /**
     * Actualiza una cotización existente.
     */
    public function update(Request $request, $id)
    {
        try {
            // Buscar la cotización por ID
            $cotizacion = Cotizacion::findOrFail($id);

            // Validar los datos de entrada
            $validatedData = $request->validate([
                'cliente_id' => 'sometimes|exists:clientes,id',
                'total' => 'sometimes|numeric|min:0',
                'productos' => 'sometimes|array',
                'productos.*.producto_id' => 'required_with:productos|exists:productos,id',
                'productos.*.cantidad' => 'required_with:productos|integer|min:1',
                'productos.*.precio' => 'required_with:productos|numeric|min:0',
            ]);

            // Actualizar los campos principales de la cotización
            $cotizacion->update($validatedData);

            // Actualizar productos si se proporcionan
            if (isset($validatedData['productos'])) {
                $productos = [];
                foreach ($validatedData['productos'] as $producto) {
                    $productos[$producto['producto_id']] = [
                        'cantidad' => $producto['cantidad'],
                        'precio' => $producto['precio'],
                    ];
                }
                // Sincronizar productos con la cotización
                $cotizacion->productos()->sync($productos);
            }

            // Devolver la cotización actualizada con relaciones cargadas
            return response()->json($cotizacion->load(['cliente', 'productos']), 200);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Error al actualizar la cotización: ' . $e->getMessage()], 400);
        }
    }


    /**
     * Elimina una cotización.
     */
    public function destroy($id)
    {
        try {
            $cotizacion = Cotizacion::findOrFail($id);
            $cotizacion->productos()->detach(); // Eliminar relaciones con productos
            $cotizacion->delete();

            return response()->json(['message' => 'Cotización eliminada con éxito'], 200);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Error al eliminar la cotización: ' . $e->getMessage()], 500);
        }
    }
}
