<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Venta;
use App\Services\InventarioService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class VentaController extends Controller
{
    public function __construct(private readonly InventarioService $inventarioService)
    {
    }

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

            $query = Venta::with(['cliente', 'productos', 'servicios']); // Añadimos servicios

            if ($nombreCliente) {
                $query->whereIn('cliente_id', function ($subQuery) use ($nombreCliente) {
                    $subQuery->select('id')
                        ->from('clientes')
                        ->where('nombre_razon_social', 'LIKE', '%' . $nombreCliente . '%');
                });
                Log::info('Filtro aplicado para cliente: ' . $nombreCliente);
            }

            $ventas = $query->orderByDesc('created_at')->limit($limit)->get()->map(function ($venta) {
                $items = collect($venta->productos->map(function ($producto) {
                    return [
                        'id' => $producto->id,
                        'nombre' => $producto->nombre,
                        'tipo' => 'producto',
                        'cantidad' => $producto->pivot->cantidad,
                        'precio' => $producto->pivot->precio,
                    ];
                }))->merge(collect($venta->servicios->map(function ($servicio) {
                    return [
                        'id' => $servicio->id,
                        'nombre' => $servicio->nombre,
                        'tipo' => 'servicio',
                        'cantidad' => $servicio->pivot->cantidad,
                        'precio' => $servicio->pivot->precio,
                    ];
                })));

                return [
                    'id' => $venta->id,
                    'cliente' => $venta->cliente,
                    'items' => $items,
                    'total' => $venta->total,
                ];
            });

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
            $clientes = \App\Models\Cliente::all();
            $productos = \App\Models\Producto::all();
            $servicios = \App\Models\Servicio::all(); // Añadimos servicios

            return response()->json([
                'clientes' => $clientes,
                'productos' => $productos,
                'servicios' => $servicios,
            ], 200);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Error al cargar datos para nueva venta: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Muestra los detalles de una venta específica.
     */
    /**
     * Muestra los detalles de una venta específica.
     */
    public function show($id)
    {
        try {
            $venta = Venta::with(['cliente', 'productos', 'servicios'])->findOrFail($id);

            $items = collect($venta->productos)->map(function ($producto) {
                return [
                    'id' => $producto->id,
                    'nombre' => $producto->nombre,
                    'tipo' => 'producto',
                    'cantidad' => $producto->pivot->cantidad,
                    'precio' => $producto->pivot->precio,
                ];
            })->merge(collect($venta->servicios)->map(function ($servicio) {
                return [
                    'id' => $servicio->id,
                    'nombre' => $servicio->nombre,
                    'tipo' => 'servicio',
                    'cantidad' => $servicio->pivot->cantidad,
                    'precio' => $servicio->pivot->precio,
                ];
            }));

            return response()->json([
                'id' => $venta->id,
                'cliente' => $venta->cliente,
                'items' => $items,
                'total' => $venta->total,
            ], 200);
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
                'items' => 'required|array',
                'items.*.id' => 'required|integer',
                'items.*.tipo' => 'required|in:producto,servicio',
                'items.*.cantidad' => 'required|integer|min:1',
                'items.*.precio' => 'required|numeric|min:0',
            ]);

            // Crear la venta
            $venta = Venta::create([
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
                    // Descontar stock
                    $productoModel = \App\Models\Producto::find($item['id']);
                    if ($productoModel) {
                        $this->inventarioService->salida($productoModel, $item['cantidad'], [
                            'motivo' => 'Venta API creada',
                            'referencia' => $venta,
                            'detalles' => [
                                'payload' => $item,
                                'fuente' => 'api.ventas.store',
                            ],
                        ]);
                    }
                } elseif ($item['tipo'] === 'servicio') {
                    $servicios[$item['id']] = [
                        'cantidad' => $item['cantidad'],
                        'precio' => $item['precio'],
                    ];
                }
            }
            $venta->productos()->sync($productos);
            $venta->servicios()->sync($servicios);

            return response()->json($venta->load(['cliente', 'productos', 'servicios']), 201);
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
            $venta = Venta::with(['productos', 'servicios'])->findOrFail($id);

            // Validar los datos de entrada
            $validatedData = $request->validate([
                'cliente_id' => 'sometimes|exists:clientes,id',
                'items' => 'sometimes|array',
                'items.*.id' => 'required_with:items|integer',
                'items.*.tipo' => 'required_with:items|in:producto,servicio',
                'items.*.cantidad' => 'required_with:items|integer|min:1',
                'items.*.precio' => 'required_with:items|numeric|min:0',
            ]);

            // Revertir stock de productos previos
            foreach ($venta->productos as $producto) {
                $pivot = $venta->productos()->where('producto_id', $producto->id)->first()->pivot;
                $this->inventarioService->entrada($producto, $pivot->cantidad, [
                    'motivo' => 'Actualización de venta API (reversión)',
                    'referencia' => $venta,
                    'detalles' => [
                        'fuente' => 'api.ventas.update',
                        'producto_id' => $producto->id,
                    ],
                ]);
            }

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
            $venta->update($updateData);

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
                        // Descontar stock
                        $productoModel = \App\Models\Producto::find($item['id']);
                        if ($productoModel) {
                            $this->inventarioService->salida($productoModel, $item['cantidad'], [
                                'motivo' => 'Actualización de venta API (aplicación)',
                                'referencia' => $venta,
                                'detalles' => [
                                    'fuente' => 'api.ventas.update',
                                    'payload' => $item,
                                ],
                            ]);
                        }
                    } elseif ($item['tipo'] === 'servicio') {
                        $servicios[$item['id']] = [
                            'cantidad' => $item['cantidad'],
                            'precio' => $item['precio'],
                        ];
                    }
                }
                $venta->productos()->sync($productos);
                $venta->servicios()->sync($servicios);
            }

            return response()->json($venta->load(['cliente', 'productos', 'servicios']), 200);
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
            $venta = Venta::with(['productos', 'servicios'])->findOrFail($id);

            // Revertir stock de productos
            foreach ($venta->productos as $producto) {
                $pivot = $venta->productos()->where('producto_id', $producto->id)->first()->pivot;
                $this->inventarioService->entrada($producto, $pivot->cantidad, [
                    'motivo' => 'Eliminación de venta API',
                    'referencia' => $venta,
                    'detalles' => [
                        'fuente' => 'api.ventas.destroy',
                        'producto_id' => $producto->id,
                    ],
                ]);
            }

            $venta->productos()->detach();
            $venta->servicios()->detach();
            $venta->delete();

            return response()->json(['message' => 'Venta eliminada con éxito'], 200);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Error al eliminar la venta: ' . $e->getMessage()], 500);
        }
    }
}
