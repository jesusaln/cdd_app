<?php

namespace App\Http\Controllers;

use App\Models\Pedido;
use App\Models\Cliente;
use App\Models\Producto;
use App\Models\Servicio;
use App\Models\Venta;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Log;
use App\Models\Cotizacion;
use App\Models\CotizacionProducto;

class PedidoController extends Controller
{
    /**
     * Convertir un pedido en una venta.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function enviarAVentas($id)
    {
        // Validar el pedido
        $pedido = Pedido::with('cliente', 'productos', 'servicios')->findOrFail($id);

        // Crear una nueva venta
        $venta = new Venta();
        $venta->cliente_id = $pedido->cliente_id;
        $venta->total = $pedido->total;
        $venta->save();

        // Asociar los productos y servicios a la venta
        foreach ($pedido->productos as $producto) {
            $venta->productos()->attach($producto->id, [
                'cantidad' => $producto->pivot->cantidad,
                'precio' => $producto->pivot->precio,
            ]);

            // Opcional: Actualizar el stock del producto
            $producto->decrement('stock', $producto->pivot->cantidad);
        }

        foreach ($pedido->servicios as $servicio) {
            $venta->servicios()->attach($servicio->id, [
                'cantidad' => $servicio->pivot->cantidad,
                'precio' => $servicio->pivot->precio,
            ]);
        }

        // Opcional: Marcar el pedido como convertido o eliminarlo
        // $pedido->delete();

        return redirect()->route('ventas.index')->with('success', 'Pedido convertido a venta exitosamente.');
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $pedidos = Pedido::with(['cliente', 'productos', 'servicios'])->get()->map(function ($pedido) {
            $productos = $pedido->productos->map(function ($producto) {
                return [
                    'id' => $producto->id,
                    'nombre' => $producto->nombre,
                    'tipo' => 'producto',
                    'pivot' => [
                        'cantidad' => $producto->pivot->cantidad,
                        'precio' => $producto->pivot->precio,
                    ],
                ];
            });

            $servicios = $pedido->servicios->map(function ($servicio) {
                return [
                    'id' => $servicio->id,
                    'nombre' => $servicio->nombre,
                    'tipo' => 'servicio',
                    'pivot' => [
                        'cantidad' => $servicio->pivot->cantidad,
                        'precio' => $servicio->pivot->precio,
                    ],
                ];
            });

            // Combinar productos y servicios usando collect y all()
            $items = collect($productos->all())->merge($servicios->all());

            return [
                'id' => $pedido->id,
                'cliente' => $pedido->cliente,
                'productos' => $items,
                'total' => $pedido->total,
            ];
        });

        return Inertia::render('Pedidos/Index', [
            'pedidos' => $pedidos,
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
        return Inertia::render('Pedidos/Create', [
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
        // 1. Validar los datos
        $validated = $request->validate([
            'cliente_id' => 'required|exists:clientes,id',
            'productos' => 'required|array',
            'productos.*.id' => 'required|integer',
            'productos.*.tipo' => 'required|in:producto,servicio',
            'productos.*.cantidad' => 'required|integer|min:1',
            'productos.*.precio' => 'required|numeric|min:0',
            'productos.*.descuento' => 'required|numeric|min:0|max:100',
        ]);

        // 2. Calcular totales
        $subtotal = 0;
        foreach ($validated['productos'] as $item) {
            $subtotal += $item['cantidad'] * $item['precio'];
        }

        $descuentoGeneralMonto = $subtotal * ($request->descuento_general / 100);
        $subtotalConDescuentos = $subtotal - $descuentoGeneralMonto;
        $iva = $subtotalConDescuentos * 0.16;
        $total = $subtotalConDescuentos + $iva;

        // 3. Crear la cotización
        $cotizacion = Cotizacion::create([
            'cliente_id' => $validated['cliente_id'],
            'subtotal' => $subtotal,
            'descuento_general' => $descuentoGeneralMonto,
            'iva' => $iva,
            'total' => $total,
        ]);

        // 4. Insertar manualmente cada ítem en la tabla pivote
        foreach ($validated['productos'] as $item) {
            // Determinar el modelo real (Producto o Servicio)
            $class = $item['tipo'] === 'producto' ? Producto::class : Servicio::class;

            // Verificar que el ítem exista
            $modelo = $class::find($item['id']);
            if (! $modelo) {
                Log::warning("Ítem no encontrado: {$class} con ID {$item['id']}");
                continue;
            }

            // Calcular campos adicionales
            $subtotalItem = $item['cantidad'] * $item['precio'];
            $descuentoMontoItem = $subtotalItem * ($item['descuento'] / 100);

            // 5. Crear registro en la tabla pivote manualmente
            $pivot = new CotizacionProducto();
            $pivot->cotizacion_id = $cotizacion->id;
            $pivot->cotizable_id = $item['id'];
            $pivot->cotizable_type = $class;
            $pivot->cantidad = $item['cantidad'];
            $pivot->precio = $item['precio'];
            $pivot->descuento = $item['descuento'];
            $pivot->subtotal = $subtotalItem;
            $pivot->descuento_monto = $descuentoMontoItem;
            $pivot->save(); // ✅ Guarda en cotizacion_producto
        }

        // 6. Redirigir con Inertia (respuesta válida)
        return redirect()->route('cotizaciones.index')
            ->with('success', 'Cotización creada con éxito');
    }
    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $pedido = Pedido::with(['cliente', 'productos', 'servicios'])->findOrFail($id);
        $items = $pedido->productos->map(function ($producto) {
            return [
                'id' => $producto->id,
                'nombre' => $producto->nombre,
                'tipo' => 'producto',
                'pivot' => [
                    'cantidad' => $producto->pivot->cantidad,
                    'precio' => $producto->pivot->precio,
                ],
            ];
        })->merge($pedido->servicios->map(function ($servicio) {
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

        return Inertia::render('Pedidos/Show', [
            'pedido' => [
                'id' => $pedido->id,
                'cliente' => $pedido->cliente,
                'productos' => $items,
                'total' => $pedido->total,
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
        try {
            $pedido = Pedido::with(['cliente', 'productos', 'servicios'])->findOrFail($id);

            $items = $pedido->productos->map(function ($producto) {
                if (!$producto instanceof \App\Models\Producto || !$producto->pivot) {
                    Log::error('Producto inválido o pivot faltante para el producto ID: ' . ($producto->id ?? 'null'));
                    return null;
                }
                return [
                    'id' => $producto->id,
                    'nombre' => $producto->nombre,
                    'tipo' => 'producto',
                    'pivot' => [
                        'cantidad' => $producto->pivot->cantidad,
                        'precio' => $producto->pivot->precio,
                    ],
                ];
            })->merge($pedido->servicios->map(function ($servicio) {
                if (!$servicio instanceof \App\Models\Servicio || !$servicio->pivot) {
                    Log::error('Servicio inválido o pivot faltante para el servicio ID: ' . ($servicio->id ?? 'null'));
                    return null;
                }
                return [
                    'id' => $servicio->id,
                    'nombre' => $servicio->nombre,
                    'tipo' => 'servicio',
                    'pivot' => [
                        'cantidad' => $servicio->pivot->cantidad,
                        'precio' => $servicio->pivot->precio,
                    ],
                ];
            }))->filter(fn($item) => $item !== null);

            $clientes = Cliente::all();
            $productos = Producto::all();
            $servicios = Servicio::all();

            return Inertia::render('Pedidos/Edit', [
                'pedido' => [
                    'id' => $pedido->id,
                    'cliente_id' => $pedido->cliente_id,
                    'cliente' => $pedido->cliente,
                    'productos' => $items,
                    'total' => $pedido->total,
                ],
                'clientes' => $clientes,
                'productos' => $productos,
                'servicios' => $servicios,
            ]);
        } catch (\Exception $e) {
            Log::error('Error en PedidoController@edit: ' . $e->getMessage());
            throw $e;
        }
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
        $pedido = Pedido::findOrFail($id);

        // Validar los datos de entrada
        $validatedData = $request->validate([
            'cliente_id' => 'required|exists:clientes,id',
            'productos' => 'required|array',
            'productos.*.id' => 'required|integer',
            'productos.*.tipo' => 'required|in:producto,servicio',
            'productos.*.cantidad' => 'required|integer|min:1',
            'productos.*.precio' => 'required|numeric|min:0',
        ]);

        // Actualizar el pedido
        $pedido->update([
            'cliente_id' => $validatedData['cliente_id'],
            'total' => array_sum(array_map(function ($item) {
                return $item['cantidad'] * $item['precio'];
            }, $validatedData['productos'])),
        ]);

        // Sincronizar productos y servicios
        $pedido->productos()->sync([]); // Eliminar productos actuales
        $pedido->servicios()->sync([]); // Eliminar servicios actuales
        foreach ($validatedData['productos'] as $itemData) {
            if ($itemData['tipo'] === 'producto') {
                $pedido->productos()->attach($itemData['id'], [
                    'cantidad' => $itemData['cantidad'],
                    'precio' => $itemData['precio'],
                ]);
            } elseif ($itemData['tipo'] === 'servicio') {
                $pedido->servicios()->attach($itemData['id'], [
                    'cantidad' => $itemData['cantidad'],
                    'precio' => $itemData['precio'],
                ]);
            }
        }

        // Redirigir con un mensaje de éxito
        return redirect()->route('pedidos.index')->with('success', 'Pedido actualizado exitosamente.');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $pedido = Pedido::findOrFail($id);
        $pedido->productos()->detach(); // Desasociar productos
        $pedido->servicios()->detach(); // Desasociar servicios
        $pedido->delete();
        return redirect()->route('pedidos.index')->with('success', 'Pedido eliminado exitosamente.');
    }
}
