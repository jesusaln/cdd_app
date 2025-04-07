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
            $pedidos = Pedido::with(['cliente', 'productos', 'servicios'])->get()->map(function ($pedido) {
                $items = collect($pedido->productos->map(function ($producto) {
                    return [
                        'id' => $producto->id,
                        'nombre' => $producto->nombre,
                        'tipo' => 'producto',
                        'cantidad' => $producto->pivot->cantidad,
                        'precio' => $producto->pivot->precio,
                    ];
                }))->merge(collect($pedido->servicios->map(function ($servicio) {
                    return [
                        'id' => $servicio->id,
                        'nombre' => $servicio->nombre,
                        'tipo' => 'servicio',
                        'cantidad' => $servicio->pivot->cantidad,
                        'precio' => $servicio->pivot->precio,
                    ];
                })));

                return [
                    'id' => $pedido->id,
                    'cliente' => $pedido->cliente,
                    'items' => $items,
                    'total' => $pedido->total,
                ];
            });

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
            $pedido = Pedido::with(['cliente', 'productos', 'servicios'])->findOrFail($id);

            // Asegúrate de que productos y servicios sean colecciones
            $items = collect($pedido->productos)->map(function ($producto) {
                return [
                    'id' => $producto->id,
                    'nombre' => $producto->nombre,
                    'tipo' => 'producto',
                    'cantidad' => $producto->pivot->cantidad,
                    'precio' => $producto->pivot->precio,
                ];
            })->merge(collect($pedido->servicios)->map(function ($servicio) {
                return [
                    'id' => $servicio->id,
                    'nombre' => $servicio->nombre,
                    'tipo' => 'servicio',
                    'cantidad' => $servicio->pivot->cantidad,
                    'precio' => $servicio->pivot->precio,
                ];
            }));

            return response()->json([
                'id' => $pedido->id,
                'cliente' => $pedido->cliente,
                'items' => $items,
                'total' => $pedido->total,
            ], 200);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Error al obtener la cotización: ' . $e->getMessage()], 404);
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
                'items' => 'required|array',
                'items.*.id' => 'required|integer',
                'items.*.tipo' => 'required|in:producto,servicio',
                'items.*.cantidad' => 'required|integer|min:1',
                'items.*.precio' => 'required|numeric|min:0',
            ]);

            // Crear el pedido
            $pedido = Pedido::create([
                'cliente_id' => $validatedData['cliente_id'],
                'total' => array_sum(array_map(function ($item) {
                    return $item['cantidad'] * $item['precio'];
                }, $validatedData['items'])),
            ]);

            // Asociar productos y servicios
            $productos = [];
            $servicios = [];
            foreach ($validatedData['items'] as $item) {
                if ($item['tipo'] === 'producto') {
                    $productos[$item['id']] = [
                        'cantidad' => $item['cantidad'],
                        'precio' => $item['precio'],
                    ];
                } elseif ($item['tipo'] === 'servicio') {
                    $servicios[$item['id']] = [
                        'cantidad' => $item['cantidad'],
                        'precio' => $item['precio'],
                    ];
                }
            }
            $pedido->productos()->sync($productos);
            $pedido->servicios()->sync($servicios);

            return response()->json($pedido->load(['cliente', 'productos', 'servicios']), 201);
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
            $pedido = Pedido::findOrFail($id);

            // Validar los datos de entrada
            $validatedData = $request->validate([
                'cliente_id' => 'sometimes|exists:clientes,id',
                'items' => 'sometimes|array',
                'items.*.id' => 'required_with:items|integer',
                'items.*.tipo' => 'required_with:items|in:producto,servicio',
                'items.*.cantidad' => 'required_with:items|integer|min:1',
                'items.*.precio' => 'required_with:items|numeric|min:0',
            ]);

            // Actualizar campos principales
            $updateData = [];
            if (isset($validatedData['cliente_id'])) {
                $updateData['cliente_id'] = $validatedData['cliente_id'];
            }
            if (isset($validatedData['items'])) {
                $updateData['total'] = array_sum(array_map(function ($item) {
                    return $item['cantidad'] * $item['precio'];
                }, $validatedData['items']));
            }
            $pedido->update($updateData);

            // Actualizar productos y servicios si se proporcionan
            if (isset($validatedData['items'])) {
                $productos = [];
                $servicios = [];
                foreach ($validatedData['items'] as $item) {
                    if ($item['tipo'] === 'producto') {
                        $productos[$item['id']] = [
                            'cantidad' => $item['cantidad'],
                            'precio' => $item['precio'],
                        ];
                    } elseif ($item['tipo'] === 'servicio') {
                        $servicios[$item['id']] = [
                            'cantidad' => $item['cantidad'],
                            'precio' => $item['precio'],
                        ];
                    }
                }
                $pedido->productos()->sync($productos);
                $pedido->servicios()->sync($servicios);
            }

            return response()->json($pedido->load(['cliente', 'productos', 'servicios']), 200);
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
            $pedido->productos()->detach();
            $pedido->servicios()->detach();
            $pedido->delete();

            return response()->json(['message' => 'Pedido eliminado con éxito'], 200);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Error al eliminar el pedido: ' . $e->getMessage()], 500);
        }
    }
}
