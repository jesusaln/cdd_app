<?php

namespace App\Http\Controllers;

use App\Models\Cotizacion;
use App\Models\Pedido;
use App\Models\Cliente;
use App\Models\Producto;
use App\Models\Servicio;
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
        $cotizaciones = Cotizacion::with(['cliente', 'productos', 'servicios'])->get()->map(function ($cotizacion) {
            $items = collect($cotizacion->productos)->map(function ($producto) {
                return [
                    'id' => $producto->id,
                    'nombre' => $producto->nombre,
                    'tipo' => 'producto',
                    'pivot' => [
                        'cantidad' => $producto->pivot->cantidad,
                        'precio' => $producto->pivot->precio,
                    ],
                ];
            })->merge(collect($cotizacion->servicios)->map(function ($servicio) {
                return [
                    'id' => $servicio->id,
                    'nombre' => $servicio->nombre,
                    'tipo' => 'servicio',
                    'pivot' => [
                        'cantidad' => $servicio->pivot->cantidad,
                        'precio' => $servicio->pivot->precio,
                    ],
                ];
            }));


            return [
                'id' => $cotizacion->id,
                'cliente' => $cotizacion->cliente,
                'productos' => $items, // Ahora incluye productos y servicios
                'total' => $cotizacion->total,
            ];
        });

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
        $servicios = Servicio::all();
        return Inertia::render('Cotizaciones/Create', [
            'clientes' => $clientes,
            'productos' => $productos,
            'servicios' => $servicios,
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
            'productos.*.id' => 'required|integer',
            'productos.*.tipo' => 'required|in:producto,servicio',
            'productos.*.cantidad' => 'required|integer|min:1',
            'productos.*.precio' => 'required|numeric|min:0',
        ]);

        // Crear la cotización
        $cotizacion = Cotizacion::create([
            'cliente_id' => $validatedData['cliente_id'],
            'total' => array_sum(array_map(function ($item) {
                return $item['cantidad'] * $item['precio'];
            }, $validatedData['productos'])),
        ]);

        // Asociar productos y servicios a la cotización
        foreach ($validatedData['productos'] as $itemData) {
            if ($itemData['tipo'] === 'producto') {
                $cotizacion->productos()->attach($itemData['id'], [
                    'cantidad' => $itemData['cantidad'],
                    'precio' => $itemData['precio'],
                ]);
            } elseif ($itemData['tipo'] === 'servicio') {
                $cotizacion->servicios()->attach($itemData['id'], [
                    'cantidad' => $itemData['cantidad'],
                    'precio' => $itemData['precio'],
                ]);
            }
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
        $cotizacion = Cotizacion::with(['cliente', 'productos', 'servicios'])->findOrFail($id);
        $items = $cotizacion->productos->map(function ($producto) {
            return [
                'id' => $producto->id,
                'nombre' => $producto->nombre,
                'tipo' => 'producto',
                'pivot' => [
                    'cantidad' => $producto->pivot->cantidad,
                    'precio' => $producto->pivot->precio,
                ],
            ];
        })->merge($cotizacion->servicios->map(function ($servicio) {
            return [
                'id' => $servicio->id,
                'nombre' => $servicio->nombre,
                'tipo' => 'servicio',
                'pivot' => [
                    'cantidad' => $servicio->pivot->cantidad,
                    'precio' => $servicio->pivot->precio,
                ],
            ];
        }));

        return Inertia::render('Cotizaciones/Show', [
            'cotizacion' => [
                'id' => $cotizacion->id,
                'cliente' => $cotizacion->cliente,
                'productos' => $items,
                'total' => $cotizacion->total,
            ],
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
        $cotizacion = Cotizacion::with(['cliente', 'productos', 'servicios'])->findOrFail($id);
        $items = $cotizacion->productos->map(function ($producto) {
            return [
                'id' => $producto->id,
                'nombre' => $producto->nombre,
                'tipo' => 'producto',
                'pivot' => [
                    'cantidad' => $producto->pivot->cantidad,
                    'precio' => $producto->pivot->precio,
                ],
            ];
        })->merge($cotizacion->servicios->map(function ($servicio) {
            return [
                'id' => $servicio->id,
                'nombre' => $servicio->nombre,
                'tipo' => 'servicio',
                'pivot' => [
                    'cantidad' => $servicio->pivot->cantidad,
                    'precio' => $servicio->pivot->precio,
                ],
            ];
        }));

        $clientes = Cliente::all();
        $productos = Producto::all();
        $servicios = Servicio::all();

        return Inertia::render('Cotizaciones/Edit', [
            'cotizacion' => [
                'id' => $cotizacion->id,
                'cliente_id' => $cotizacion->cliente_id,
                'cliente' => $cotizacion->cliente,
                'productos' => $items,
                'total' => $cotizacion->total,
            ],
            'clientes' => $clientes,
            'productos' => $productos,
            'servicios' => $servicios,
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
            'productos.*.id' => 'required|integer',
            'productos.*.tipo' => 'required|in:producto,servicio',
            'productos.*.cantidad' => 'required|integer|min:1',
            'productos.*.precio' => 'required|numeric|min:0',
        ]);

        // Actualizar la cotización
        $cotizacion->update([
            'cliente_id' => $validatedData['cliente_id'],
            'total' => array_sum(array_map(function ($item) {
                return $item['cantidad'] * $item['precio'];
            }, $validatedData['productos'])),
        ]);

        // Sincronizar productos y servicios
        $cotizacion->productos()->sync([]); // Eliminar productos actuales
        $cotizacion->servicios()->sync([]); // Eliminar servicios actuales
        foreach ($validatedData['productos'] as $itemData) {
            if ($itemData['tipo'] === 'producto') {
                $cotizacion->productos()->attach($itemData['id'], [
                    'cantidad' => $itemData['cantidad'],
                    'precio' => $itemData['precio'],
                ]);
            } elseif ($itemData['tipo'] === 'servicio') {
                $cotizacion->servicios()->attach($itemData['id'], [
                    'cantidad' => $itemData['cantidad'],
                    'precio' => $itemData['precio'],
                ]);
            }
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
        $cotizacion->productos()->detach(); // Desasociar productos
        $cotizacion->servicios()->detach(); // Desasociar servicios
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
        $cotizacion = Cotizacion::with(['cliente', 'productos', 'servicios'])->findOrFail($id);

        // Crear un nuevo pedido
        $pedido = new Pedido();
        $pedido->cliente_id = $cotizacion->cliente_id;
        $pedido->total = $cotizacion->total;
        $pedido->save();

        // Asociar productos y servicios al pedido
        foreach ($cotizacion->productos as $producto) {
            $pedido->productos()->attach($producto->id, [
                'cantidad' => $producto->pivot->cantidad,
                'precio' => $producto->pivot->precio,
            ]);
        }
        foreach ($cotizacion->servicios as $servicio) {
            $pedido->servicios()->attach($servicio->id, [
                'cantidad' => $servicio->pivot->cantidad,
                'precio' => $servicio->pivot->precio,
            ]);
        }

        // Opcional: Eliminar la cotización después de convertirla
        // $cotizacion->delete();

        return redirect()->route('pedidos.index')->with('success', 'Cotización convertida a pedido exitosamente.');
    }
}
