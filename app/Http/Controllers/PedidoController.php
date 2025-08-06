<?php

namespace App\Http\Controllers;

use App\Models\Pedido;
use App\Models\PedidoItem;
use App\Models\Cliente;
use App\Models\Producto;
use App\Models\Servicio;
use App\Enums\EstadoPedido;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class PedidoController extends Controller
{
    use AuthorizesRequests;

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $pedidos = Pedido::with(['cliente', 'items.pedible'])
            ->get()
            ->filter(function ($pedido) {
                // Filtrar pedidos con cliente y al menos un item válido
                return $pedido->cliente !== null && $pedido->items->isNotEmpty();
            })
            ->map(function ($pedido) {
                $items = $pedido->items->map(function ($item) {
                    $pedible = $item->pedible;
                    return [
                        'id' => $pedible->id,
                        'nombre' => $pedible->nombre ?? 'Sin nombre',
                        'tipo' => $item->pedible_type === Producto::class ? 'producto' : 'servicio',
                        'cantidad' => $item->cantidad,
                        'precio' => $item->precio,
                        'descuento' => $item->descuento ?? 0,
                    ];
                });

                return [
                    'id' => $pedido->id,
                    'fecha' => $pedido->fecha ? $pedido->fecha->format('Y-m-d') : $pedido->created_at->format('Y-m-d'),
                    'created_at' => $pedido->created_at->format('Y-m-d\TH:i:sP'),
                    'cliente' => [
                        'id' => $pedido->cliente->id,
                        'nombre' => $pedido->cliente->nombre_razon_social ?? 'Sin nombre',
                        'email' => $pedido->cliente->email,
                        'telefono' => $pedido->cliente->telefono,
                        'rfc' => $pedido->cliente->rfc,
                        'regimen_fiscal' => $pedido->cliente->regimen_fiscal,
                        'uso_cfdi' => $pedido->cliente->uso_cfdi,
                        'calle' => $pedido->cliente->calle,
                        'numero_exterior' => $pedido->cliente->numero_exterior,
                        'numero_interior' => $pedido->cliente->numero_interior,
                        'colonia' => $pedido->cliente->colonia,
                        'codigo_postal' => $pedido->cliente->codigo_postal,
                        'municipio' => $pedido->cliente->municipio,
                        'estado' => $pedido->cliente->estado,
                        'pais' => $pedido->cliente->pais,
                    ],
                    'productos' => $items->toArray(),
                    'subtotal' => $pedido->subtotal,
                    'descuento_general' => $pedido->descuento_general,
                    'iva' => $pedido->iva,
                    'total' => $pedido->total,
                    'estado' => $pedido->estado->value,
                    'numero_pedido' => $pedido->numero_pedido,
                    'cotizacion_id' => $pedido->cotizacion_id,
                ];
            });

        return Inertia::render('Pedidos/Index', [
            'pedidos' => $pedidos->values(),
            'estados' => collect(EstadoPedido::cases())->map(fn($estado) => [
                'value' => $estado->value,
                'label' => $estado->label(),
                'color' => $estado->color() // Asumiendo que EstadoPedido tiene un método color()
            ]),
            'filters' => request()->only(['search', 'estado', 'fecha_inicio', 'fecha_fin'])
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return Inertia::render('Pedidos/Create', [
            'clientes' => Cliente::select('id', 'nombre_razon_social', 'email', 'telefono')->get(),
            'productos' => Producto::select('id', 'nombre', 'precio_venta', 'descripcion')->get(),
            'servicios' => Servicio::select('id', 'nombre', 'precio', 'descripcion')->get(),
            'defaults' => [
                'fecha' => now()->format('Y-m-d'),
                'moneda' => 'MXN'
            ]
        ]);
    }

    /**
     * Store a newly created resource in storage.
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
        $descuentoItems = 0;
        foreach ($validated['productos'] as $item) {
            $subtotalItem = $item['cantidad'] * $item['precio'];
            $descuentoItems += $subtotalItem * ($item['descuento'] / 100);
            $subtotal += $subtotalItem;
        }

        $descuentoGeneralMonto = ($subtotal - $descuentoItems) * ($request->descuento_general / 100);
        $subtotalFinal = ($subtotal - $descuentoItems) - $descuentoGeneralMonto;
        $iva = $subtotalFinal * 0.16;
        $total = $subtotalFinal + $iva;

        $numero_pedido = $this->generarNumeroPedido();

        $pedido = Pedido::create([
            'cliente_id' => $validated['cliente_id'],
            'cotizacion_id' => null, // Puede llenarse si se crea desde una cotización
            'numero_pedido' => $numero_pedido,
            'subtotal' => $subtotal,
            'descuento_general' => $descuentoGeneralMonto,
            'iva' => $iva,
            'total' => $total,
            'fecha' => now(),
            'estado' => EstadoPedido::Borrador,
            'notas' => $request->notas,
        ]);

        foreach ($validated['productos'] as $item) {
            $class = $item['tipo'] === 'producto' ? Producto::class : Servicio::class;
            $modelo = $class::find($item['id']);

            if (!$modelo) {
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
                'fecha' => $pedido->fecha ? $pedido->fecha->format('Y-m-d') : $pedido->created_at->format('Y-m-d'),
                'notas' => $pedido->notas,
                'estado' => $pedido->estado->value,
                'numero_pedido' => $pedido->numero_pedido,
                'cotizacion_id' => $pedido->cotizacion_id,
            ],
            'canEdit' => $pedido->estado === EstadoPedido::Borrador || $pedido->estado === EstadoPedido::Pendiente,
            'canDelete' => $pedido->estado === EstadoPedido::Borrador || $pedido->estado === EstadoPedido::Pendiente,
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        $pedido = Pedido::with(['cliente', 'items.pedible'])->findOrFail($id);

        // Permitir edición solo si está en Borrador o Pendiente
        if (!in_array($pedido->estado, [EstadoPedido::Borrador, EstadoPedido::Pendiente])) {
            return Redirect::route('pedidos.show', $pedido->id)
                ->with('warning', 'Solo pedidos en borrador o pendientes pueden ser editados');
        }

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
                'fecha' => $pedido->fecha ? $pedido->fecha->format('Y-m-d') : $pedido->created_at->format('Y-m-d'),
                'notas' => $pedido->notas,
                'numero_pedido' => $pedido->numero_pedido,
                'cotizacion_id' => $pedido->cotizacion_id,
            ],
            'clientes' => Cliente::select('id', 'nombre_razon_social', 'email', 'telefono')->get(),
            'productos' => Producto::select('id', 'nombre', 'precio_venta', 'descripcion')->get(),
            'servicios' => Servicio::select('id', 'nombre', 'precio', 'descripcion')->get(),
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $pedido = Pedido::findOrFail($id);

        // Permitir edición solo si está en Borrador o Pendiente
        if (!in_array($pedido->estado, [EstadoPedido::Borrador, EstadoPedido::Pendiente])) {
            return Redirect::back()->with('error', 'Solo pedidos en borrador o pendientes pueden ser actualizados');
        }

        $validated = $request->validate([
            'cliente_id' => 'required|exists:clientes,id',
            'numero_pedido' => 'required|string|unique:pedidos,numero_pedido,' . $pedido->id,
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
        $descuentoItems = 0;
        foreach ($validated['productos'] as $item) {
            $subtotalItem = $item['cantidad'] * $item['precio'];
            $descuentoItems += $subtotalItem * ($item['descuento'] / 100);
            $subtotal += $subtotalItem;
        }

        $descuentoGeneralMonto = ($subtotal - $descuentoItems) * ($request->descuento_general / 100);
        $subtotalFinal = ($subtotal - $descuentoItems) - $descuentoGeneralMonto;
        $iva = $subtotalFinal * 0.16;
        $total = $subtotalFinal + $iva;

        // Determinar el nuevo estado: si está en Borrador, cambiarlo a Pendiente
        $nuevoEstado = $pedido->estado === EstadoPedido::Borrador
            ? EstadoPedido::Pendiente
            : $pedido->estado;

        $pedido->update([
            'cliente_id' => $validated['cliente_id'],
            'numero_pedido' => $validated['numero_pedido'],
            'subtotal' => $subtotal,
            'descuento_general' => $descuentoGeneralMonto,
            'iva' => $iva,
            'total' => $total,
            'fecha' => now(),
            'estado' => $nuevoEstado,
            'notas' => $request->notas,
        ]);

        // Eliminar ítems anteriores
        $pedido->items()->delete();

        // Guardar nuevos ítems
        foreach ($validated['productos'] as $itemData) {
            $class = $itemData['tipo'] === 'producto' ? Producto::class : Servicio::class;
            $modelo = $class::find($itemData['id']);

            if (!$modelo) {
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

        $mensajeExito = $nuevoEstado === EstadoPedido::Pendiente && $pedido->estado === EstadoPedido::Borrador
            ? 'Pedido actualizado y cambiado a estado pendiente exitosamente'
            : 'Pedido actualizado exitosamente';

        return Redirect::route('pedidos.index')
            ->with('success', $mensajeExito);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $pedido = Pedido::findOrFail($id);

        if (!in_array($pedido->estado, [EstadoPedido::Borrador, EstadoPedido::Pendiente])) {
            return Redirect::back()->with('error', 'Solo pedidos en borrador o pendientes pueden ser eliminados');
        }

        $pedido->items()->delete();
        $pedido->delete();

        return Redirect::route('pedidos.index')
            ->with('success', 'Pedido eliminado exitosamente');
    }

    /**
     * Duplicate a pedido.
     */
    public function duplicate(Request $request, $id)
    {
        $pedido = Pedido::with('cliente', 'items.pedible')->findOrFail($id);

        DB::beginTransaction();
        try {
            // Duplicar el pedido
            $nuevo = $pedido->replicate();
            $nuevo->estado = EstadoPedido::Borrador;
            $nuevo->numero_pedido = $this->generarNumeroPedido();
            $nuevo->fecha = now();
            $nuevo->created_at = now();
            $nuevo->updated_at = now();
            $nuevo->save();

            // Duplicar los ítems
            foreach ($pedido->items as $item) {
                $nuevo->items()->create([
                    'pedible_id' => $item->pedible_id,
                    'pedible_type' => $item->pedible_type,
                    'cantidad' => $item->cantidad,
                    'precio' => $item->precio,
                    'descuento' => $item->descuento,
                    'subtotal' => $item->subtotal,
                    'descuento_monto' => $item->descuento_monto,
                ]);
            }

            DB::commit();

            return Redirect::route('pedidos.index')
                ->with('success', 'Pedido duplicado correctamente.');
        } catch (\Exception $e) {
            DB::rollback();
            Log::error('Error duplicando pedido: ' . $e->getMessage());

            return Redirect::back()
                ->with('error', 'Error al duplicar el pedido.');
        }
    }

    /**
     * Generate a unique numero_pedido.
     */
    private function generarNumeroPedido()
    {
        $ultimo = Pedido::orderBy('id', 'desc')->first();
        $numero = $ultimo ? $ultimo->id + 1 : 1;
        return 'PED-' . date('Ymd') . '-' . str_pad($numero, 5, '0', STR_PAD_LEFT);
    }
}
