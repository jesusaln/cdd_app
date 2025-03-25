<?php

namespace App\Http\Controllers\Api;

use App\Models\Pedido;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class PedidoController extends Controller
{
    /**
     * Muestra una lista de todos los pedidos en formato JSON.
     */
    public function index()
    {
        try {
            $pedidos = Pedido::with(['cliente', 'productos'])->get();
            return response()->json($pedidos, 200);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Error al obtener los pedidos: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Muestra los detalles de un pedido específico.
     */
    public function show($id)
    {
        try {
            $pedido = Pedido::with(['cliente', 'productos'])->findOrFail($id);
            return response()->json($pedido, 200);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Error al obtener el pedido: ' . $e->getMessage()], 404);
        }
    }

    /**
     * Crea un nuevo pedido.
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

            // Crear el pedido
            $pedido = Pedido::create([
                'cliente_id' => $validatedData['cliente_id'],
                'total' => $validatedData['total'],
            ]);

            // Asociar productos al pedido (usando una tabla pivote)
            $productos = [];
            foreach ($validatedData['productos'] as $producto) {
                $productos[$producto['producto_id']] = [
                    'cantidad' => $producto['cantidad'],
                    'precio' => $producto['precio'],
                ];
            }
            $pedido->productos()->sync($productos);

            // Devolver el pedido creado con relaciones cargadas
            return response()->json($pedido->load(['cliente', 'productos']), 201);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Error al crear el pedido: ' . $e->getMessage()], 400);
        }
    }

    /**
     * Actualiza un pedido existente.
     */
    public function update(Request $request, $id)
    {
        try {
            // Buscar el pedido por ID
            $pedido = Pedido::findOrFail($id);

            // Validar los datos de entrada
            $validatedData = $request->validate([
                'cliente_id' => 'sometimes|exists:clientes,id',
                'total' => 'sometimes|numeric|min:0',
                'productos' => 'sometimes|array',
                'productos.*.producto_id' => 'required_with:productos|exists:productos,id',
                'productos.*.cantidad' => 'required_with:productos|integer|min:1',
                'productos.*.precio' => 'required_with:productos|numeric|min:0',
            ]);

            // Actualizar los campos principales del pedido
            $pedido->update($validatedData);

            // Actualizar productos si se proporcionan
            if (isset($validatedData['productos'])) {
                $productos = [];
                foreach ($validatedData['productos'] as $producto) {
                    $productos[$producto['producto_id']] = [
                        'cantidad' => $producto['cantidad'],
                        'precio' => $producto['precio'],
                    ];
                }
                // Sincronizar productos con el pedido
                $pedido->productos()->sync($productos);
            }

            // Devolver el pedido actualizado con relaciones cargadas
            return response()->json($pedido->load(['cliente', 'productos']), 200);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Error al actualizar el pedido: ' . $e->getMessage()], 400);
        }
    }

    /**
     * Elimina un pedido.
     */
    public function destroy($id)
    {
        try {
            $pedido = Pedido::findOrFail($id);
            $pedido->productos()->detach(); // Eliminar relaciones con productos
            $pedido->delete();

            return response()->json(['message' => 'Pedido eliminado con éxito'], 200);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Error al eliminar el pedido: ' . $e->getMessage()], 500);
        }
    }
}
