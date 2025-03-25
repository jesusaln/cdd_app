<?php

namespace App\Http\Controllers\Api;

use App\Models\Venta;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class VentaController extends Controller
{
    /**
     * Muestra una lista de todas las ventas en formato JSON.
     */
    public function index(Request $request)
    {
        try {
            $nombreCliente = $request->query('cliente');
            $limit = $request->query('limit', 10);

            Log::info('Solicitud completa:', [
                'url' => $request->fullUrl(),
                'params' => $request->all(),
                'cliente' => $nombreCliente,
            ]);

            $query = Venta::with(['cliente', 'productos']);

            if ($nombreCliente) {
                $query->whereIn('cliente_id', function ($subQuery) use ($nombreCliente) {
                    $subQuery->select('id')
                        ->from('clientes')
                        ->where('nombre_razon_social', 'LIKE', '%' . $nombreCliente . '%');
                });
                Log::info('Filtro aplicado para cliente: ' . $nombreCliente);
            }

            $ventas = $query->orderByDesc('created_at')->limit($limit)->get();

            Log::info('Ventas encontradas: ' . $ventas->count());
            return response()->json($ventas, 200);
        } catch (\Exception $e) {
            Log::info('Error: ' . $e->getMessage());
            return response()->json(['error' => 'Error al obtener las ventas: ' . $e->getMessage()], 500);
        }
    }

    public function create()
    {
        try {
            // Cargar datos adicionales si son necesarios (por ejemplo, clientes y productos)
            $clientes = \App\Models\Cliente::all();
            $productos = \App\Models\Producto::all();

            return response()->json([
                'clientes' => $clientes,
                'productos' => $productos,
            ], 200);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Error al cargar datos para nueva venta: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Muestra los detalles de una venta específica.
     */
    public function show($id)
    {
        try {
            $venta = Venta::with(['cliente', 'productos'])->findOrFail($id);
            return response()->json($venta, 200);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Error al obtener la venta: ' . $e->getMessage()], 404);
        }
    }

    /**
     * Crea una nueva venta.
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

            // Crear la venta
            $venta = Venta::create([
                'cliente_id' => $validatedData['cliente_id'],
                'total' => $validatedData['total'],
            ]);

            // Asociar productos a la venta (usando una tabla pivote)
            $productos = [];
            foreach ($validatedData['productos'] as $producto) {
                $productos[$producto['producto_id']] = [
                    'cantidad' => $producto['cantidad'],
                    'precio' => $producto['precio'],
                ];

                // Descontar la cantidad del inventario del producto
                $productoModel = \App\Models\Producto::find($producto['producto_id']);
                if ($productoModel) {
                    $productoModel->decrement('stock', $producto['cantidad']);
                }
            }

            $venta->productos()->sync($productos);

            // Devolver la venta creada con relaciones cargadas
            return response()->json($venta->load(['cliente', 'productos']), 201);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Error al crear la venta: ' . $e->getMessage()], 400);
        }
    }

    /**
     * Actualiza una venta existente.
     */
    public function update(Request $request, $id)
    {
        try {
            // Buscar la venta por ID
            $venta = Venta::with('productos')->findOrFail($id);

            // Validar los datos de entrada
            $validatedData = $request->validate([
                'cliente_id' => 'sometimes|exists:clientes,id',
                'total' => 'sometimes|numeric|min:0',
                'productos' => 'sometimes|array',
                'productos.*.producto_id' => 'required_with:productos|exists:productos,id',
                'productos.*.cantidad' => 'required_with:productos|integer|min:1',
                'productos.*.precio' => 'required_with:productos|numeric|min:0',
            ]);

            // Revertir las cantidades previas antes de actualizar
            foreach ($venta->productos as $producto) {
                $pivot = $venta->productos()->where('producto_id', $producto->id)->first()->pivot;
                $producto->increment('stock', $pivot->cantidad); // Revertir el descuento anterior
            }

            // Actualizar los campos principales de la venta
            $venta->update($validatedData);

            // Actualizar productos si se proporcionan
            if (isset($validatedData['productos'])) {
                $productos = [];
                foreach ($validatedData['productos'] as $producto) {
                    $productos[$producto['producto_id']] = [
                        'cantidad' => $producto['cantidad'],
                        'precio' => $producto['precio'],
                    ];

                    // Descontar la nueva cantidad del inventario del producto
                    $productoModel = \App\Models\Producto::find($producto['producto_id']);
                    if ($productoModel) {
                        $productoModel->decrement('stock', $producto['cantidad']);
                    }
                }
                $venta->productos()->sync($productos);
            }

            // Devolver la venta actualizada con relaciones cargadas
            return response()->json($venta->load(['cliente', 'productos']), 200);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Error al actualizar la venta: ' . $e->getMessage()], 400);
        }
    }

    /**
     * Elimina una venta.
     */
    public function destroy($id)
    {
        try {
            $venta = Venta::with('productos')->findOrFail($id);

            // Revertir las cantidades descontadas al eliminar la venta
            foreach ($venta->productos as $producto) {
                $pivot = $venta->productos()->where('producto_id', $producto->id)->first()->pivot;
                $producto->increment('stock', $pivot->cantidad);
            }

            $venta->productos()->detach(); // Eliminar relaciones con productos
            $venta->delete();

            return response()->json(['message' => 'Venta eliminada con éxito'], 200);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Error al eliminar la venta: ' . $e->getMessage()], 500);
        }
    }
}
