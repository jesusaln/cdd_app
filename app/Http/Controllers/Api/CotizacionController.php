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
    public function Index()
    {
        try {
            $cotizaciones = Cotizacion::with(['cliente', 'productos', 'servicios'])->get()->map(function ($cotizacion) {
                $items = collect($cotizacion->productos)->map(function ($producto) {
                    return [
                        'id' => $producto->id,
                        'nombre' => $producto->nombre,
                        'tipo' => 'producto',
                        'cantidad' => $producto->pivot->cantidad,
                        'precio' => $producto->pivot->precio,
                    ];
                })->merge(collect($cotizacion->servicios)->map(function ($servicio) {
                    return [
                        'id' => $servicio->id,
                        'nombre' => $servicio->nombre,
                        'tipo' => 'servicio',
                        'cantidad' => $servicio->pivot->cantidad,
                        'precio' => $servicio->pivot->precio,
                    ];
                }));

                return [
                    'id' => $cotizacion->id,
                    'cliente' => $cotizacion->cliente,
                    'items' => $items,
                    'total' => $cotizacion->total,
                    'fecha' => $cotizacion->created_at->format('Y-m-d'), // Incluir la fecha de creación

                ];
            });

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
            $cotizacion = Cotizacion::with(['cliente', 'productos', 'servicios'])->findOrFail($id);
            $items = $cotizacion->productos->map(function ($producto) {
                return [
                    'id' => $producto->id,
                    'nombre' => $producto->nombre,
                    'tipo' => 'producto',
                    'cantidad' => $producto->pivot->cantidad,
                    'precio' => $producto->pivot->precio,
                ];
            })->merge($cotizacion->servicios->map(function ($servicio) {
                return [
                    'id' => $servicio->id,
                    'nombre' => $servicio->nombre,
                    'tipo' => 'servicio',
                    'cantidad' => $servicio->pivot->cantidad,
                    'precio' => $servicio->pivot->precio,
                ];
            }));

            return response()->json([
                'id' => $cotizacion->id,
                'cliente' => $cotizacion->cliente,
                'items' => $items,
                'total' => $cotizacion->total,
                'fecha' => $cotizacion->created_at->format('Y-m-d'), // Incluir la fecha de creación
            ], 200);
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
                'items' => 'required|array',
                'items.*.id' => 'required|integer',
                'items.*.tipo' => 'required|in:producto,servicio',
                'items.*.cantidad' => 'required|integer|min:1',
                'items.*.precio' => 'required|numeric|min:0',
            ]);

            // Crear la cotización
            $cotizacion = Cotizacion::create([
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
            $cotizacion->productos()->sync($productos);
            $cotizacion->servicios()->sync($servicios);

            return response()->json($cotizacion->load(['cliente', 'productos', 'servicios']), 201);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Error al crear la cotización: ' . $e->getMessage()], 400);
        }
    }

    /**
     * Actualiza una cotización existente.
     */
    public function update(Request $request, $id)
    {
        try {
            $cotizacion = Cotizacion::findOrFail($id);

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
            $cotizacion->update($updateData);

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
                $cotizacion->productos()->sync($productos);
                $cotizacion->servicios()->sync($servicios);
            }

            return response()->json($cotizacion->load(['cliente', 'productos', 'servicios']), 200);
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
            $cotizacion->productos()->detach();
            $cotizacion->servicios()->detach();
            $cotizacion->delete();

            return response()->json(['message' => 'Cotización eliminada con éxito'], 200);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Error al eliminar la cotización: ' . $e->getMessage()], 500);
        }
    }
}
