<?php

namespace App\Http\Controllers;

use App\Models\Pedido;
use App\Models\Cliente;
use App\Models\Producto;
use App\Models\Servicio;
use App\Models\Venta;
use App\Models\PedidoItem;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Log;

class PedidoController extends Controller
{
    /**
     * Convertir un pedido en una venta.
     *
     * @param int $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function enviarAVentas($id)
    {
        $pedido = Pedido::with(['cliente', 'items.pedible'])->findOrFail($id);

        // Crear una nueva venta
        $venta = new Venta();
        $venta->cliente_id = $pedido->cliente_id;
        $venta->total = $pedido->total;
        $venta->save();

        // Asociar productos y servicios desde pedido_items
        foreach ($pedido->items as $item) {
            $pedible = $item->pedible;

            if ($pedible instanceof Producto) {
                $venta->productos()->attach($pedible->id, [
                    'cantidad' => $item->cantidad,
                    'precio' => $item->precio,
                ]);
                // Opcional: actualizar stock
                $pedible->decrement('stock', $item->cantidad);
            } elseif ($pedible instanceof Servicio) {
                $venta->servicios()->attach($pedible->id, [
                    'cantidad' => $item->cantidad,
                    'precio' => $item->precio,
                ]);
            }
        }

        return redirect()->route('ventas.index')->with('success', 'Pedido convertido a venta exitosamente.');
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $pedidos = Pedido::with(['cliente', 'items.pedible'])->get()->map(function ($pedido) {
            $items = $pedido->items->map(function ($item) {
                $pedible = $item->pedible;
                return [
                    'id' => $pedible->id,
                    'nombre' => $pedible->nombre ?? $pedible->descripcion,
                    'tipo' => $item->pedible_type === Producto::class ? 'producto' : 'servicio',
                    'pivot' => [
                        'cantidad' => $item->cantidad,
                        'precio' => $item->precio,
                        'descuento' => $item->descuento,
                    ],
                ];
            });

            return [
                'id' => $pedido->id,
                'cliente' => $pedido->cliente,
                'productos' => $items,
                'subtotal' => $pedido->subtotal,
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
        return Inertia::render('Pedidos/Create', [
            'clientes' => Cliente::all(),
            'productos' => Producto::all(),
            'servicios' => Servicio::all(),
        ]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'cliente_id' => 'required|exists:clientes,id',
            'productos' => 'required|array',
            'productos.*.id' => 'required|integer',
            'productos.*.tipo' => 'required|in:producto,servicio',
            'productos.*.cantidad' => 'required|integer|min:1',
            'productos.*.precio' => 'required|numeric|min:0',
            'productos.*.descuento' => 'required|numeric|min:0|max:100',
            'descuento_general' => 'nullable|numeric|min:0|max:100',
            'notas' => 'nullable|string',
        ]);

        $subtotal = 0;
        foreach ($validated['productos'] as $item) {
            $subtotal += $item['cantidad'] * $item['precio'];
        }

        $descuentoGeneralPorcentaje = $request->descuento_general ?? 0;
        $descuentoGeneralMonto = $subtotal * ($descuentoGeneralPorcentaje / 100);
        $subtotalConDescuentos = $subtotal - $descuentoGeneralMonto;
        $iva = $subtotalConDescuentos * 0.16;
        $total = $subtotalConDescuentos + $iva;

        $pedido = Pedido::create([
            'cliente_id' => $validated['cliente_id'],
            'subtotal' => $subtotal,
            'descuento_general' => $descuentoGeneralMonto,
            'iva' => $iva,
            'total' => $total,
            'notas' => $request->notas,
        ]);

        foreach ($validated['productos'] as $item) {
            $class = $item['tipo'] === 'producto' ? Producto::class : Servicio::class;
            $modelo = $class::find($item['id']);

            if (! $modelo) {
                Log::warning("Ítem no encontrado: {$class} con ID {$item['id']}");
                continue;
            }

            $subtotalItem = $item['cantidad'] * $item['precio'];
            $descuentoMontoItem = $subtotalItem * ($item['descuento'] / 100);

            PedidoItem::create([
                'pedido_id' => $pedido->id,
                'pedible_id' => $item['id'],
                'pedible_type' => $class,
                'cantidad' => $item['cantidad'],
                'precio' => $item['precio'],
                'descuento' => $item['descuento'],
                'subtotal' => $subtotalItem,
                'descuento_monto' => $descuentoMontoItem,
            ]);
        }

        return redirect()->route('pedidos.index')->with('success', 'Pedido creado con éxito');
    }

    /**
     * Display the specified resource.
     *
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $pedido = Pedido::with(['cliente', 'items.pedible'])->findOrFail($id);

        $items = $pedido->items->map(function ($item) {
            $pedible = $item->pedible;
            return [
                'id' => $pedible->id,
                'nombre' => $pedible->nombre ?? $pedible->descripcion,
                'tipo' => $item->pedible_type === Producto::class ? 'producto' : 'servicio',
                'pivot' => [
                    'cantidad' => $item->cantidad,
                    'precio' => $item->precio,
                    'descuento' => $item->descuento,
                ],
            ];
        });

        return Inertia::render('Pedidos/Show', [
            'pedido' => [
                'id' => $pedido->id,
                'cliente' => $pedido->cliente,
                'productos' => $items,
                'subtotal' => $pedido->subtotal,
                'descuento_general' => $pedido->descuento_general,
                'iva' => $pedido->iva,
                'total' => $pedido->total,
                'notas' => $pedido->notas,
            ],
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $pedido = Pedido::with(['cliente', 'items.pedible'])->findOrFail($id);

        $items = $pedido->items->map(function ($item) {
            $pedible = $item->pedible;
            return [
                'id' => $pedible->id,
                'nombre' => $pedible->nombre ?? $pedible->descripcion,
                'tipo' => $item->pedible_type === Producto::class ? 'producto' : 'servicio',
                'pivot' => [
                    'cantidad' => $item->cantidad,
                    'precio' => $item->precio,
                    'descuento' => $item->descuento,
                ],
            ];
        });

        return Inertia::render('Pedidos/Edit', [
            'pedido' => [
                'id' => $pedido->id,
                'cliente_id' => $pedido->cliente_id,
                'cliente' => $pedido->cliente,
                'productos' => $items,
                'subtotal' => $pedido->subtotal,
                'descuento_general' => $pedido->descuento_general,
                'iva' => $pedido->iva,
                'total' => $pedido->total,
                'notas' => $pedido->notas,
            ],
            'clientes' => Cliente::all(),
            'productos' => Producto::all(),
            'servicios' => Servicio::all(),
        ]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $pedido = Pedido::findOrFail($id);

        $validated = $request->validate([
            'cliente_id' => 'required|exists:clientes,id',
            'productos' => 'required|array',
            'productos.*.id' => 'required|integer',
            'productos.*.tipo' => 'required|in:producto,servicio',
            'productos.*.cantidad' => 'required|integer|min:1',
            'productos.*.precio' => 'required|numeric|min:0',
            'productos.*.descuento' => 'required|numeric|min:0|max:100',
            'descuento_general' => 'nullable|numeric|min:0|max:100',
            'notas' => 'nullable|string',
        ]);

        $subtotal = 0;
        foreach ($validated['productos'] as $item) {
            $subtotal += $item['cantidad'] * $item['precio'];
        }

        $descuentoItems = 0;
        foreach ($validated['productos'] as $item) {
            $subtotalItem = $item['cantidad'] * $item['precio'];
            $descuentoItems += $subtotalItem * ($item['descuento'] / 100);
        }

        $descuentoGeneralPorcentaje = $request->descuento_general ?? 0;
        $descuentoGeneralMonto = ($subtotal - $descuentoItems) * ($descuentoGeneralPorcentaje / 100);
        $subtotalFinal = ($subtotal - $descuentoItems) - $descuentoGeneralMonto;
        $iva = $subtotalFinal * 0.16;
        $total = $subtotalFinal + $iva;

        $pedido->update([
            'cliente_id' => $validated['cliente_id'],
            'subtotal' => $subtotal,
            'descuento_general' => $descuentoGeneralMonto,
            'iva' => $iva,
            'total' => $total,
            'notas' => $request->notas,
        ]);

        $pedido->items()->delete();

        foreach ($validated['productos'] as $itemData) {
            $class = $itemData['tipo'] === 'producto' ? Producto::class : Servicio::class;
            $modelo = $class::find($itemData['id']);

            if (! $modelo) {
                Log::warning("Ítem no encontrado: {$class} con ID {$itemData['id']}");
                continue;
            }

            $subtotalItem = $itemData['cantidad'] * $itemData['precio'];
            $descuentoMontoItem = $subtotalItem * ($itemData['descuento'] / 100);

            PedidoItem::create([
                'pedido_id' => $pedido->id,
                'pedible_id' => $itemData['id'],
                'pedible_type' => $class,
                'cantidad' => $itemData['cantidad'],
                'precio' => $itemData['precio'],
                'descuento' => $itemData['descuento'],
                'subtotal' => $subtotalItem,
                'descuento_monto' => $descuentoMontoItem,
            ]);
        }

        return redirect()->route('pedidos.index')->with('success', 'Pedido actualizado exitosamente.');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $pedido = Pedido::findOrFail($id);
        $pedido->items()->delete(); // Elimina todos los ítems
        $pedido->delete();

        return redirect()->route('pedidos.index')->with('success', 'Pedido eliminado exitosamente.');
    }
}
