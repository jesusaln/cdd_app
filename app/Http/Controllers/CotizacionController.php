<?php

namespace App\Http\Controllers;

use App\Models\Cotizacion;
use App\Models\Pedido;
use App\Enums\EstadoPedido;
use App\Models\PedidoItem;
use App\Models\Cliente;
use App\Models\Producto;
use App\Models\Servicio;
use App\Models\CotizacionItem;
use App\Enums\EstadoCotizacion;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;



class CotizacionController extends Controller
{
    use AuthorizesRequests;
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $cotizaciones = Cotizacion::with(['cliente', 'items.cotizable'])
            ->get()
            ->filter(function ($cotizacion) {
                // Filtrar cotizaciones con cliente y al menos un item válido
                return $cotizacion->cliente !== null && $cotizacion->items->isNotEmpty();
            })
            ->map(function ($cotizacion) {
                $items = $cotizacion->items->map(function ($item) {
                    $cotizable = $item->cotizable;
                    return [
                        'id' => $cotizable->id,
                        'nombre' => $cotizable->nombre ?? 'Sin nombre', // Corrected: Use nombre instead of nombre_razon_social
                        'tipo' => $item->cotizable_type === Producto::class ? 'producto' : 'servicio',
                        'cantidad' => $item->cantidad,
                        'precio' => $item->precio,
                        'descuento' => $item->descuento ?? 0,
                    ];
                });

                return [
                    'id' => $cotizacion->id,
                    'fecha' => $cotizacion->created_at->format('Y-m-d'), // ✅ Añadido
                    'created_at' => $cotizacion->created_at->format('Y-m-d\TH:i:sP'),
                    'cliente' => [
                        'id' => $cotizacion->cliente->id,
                        'nombre' => $cotizacion->cliente->nombre_razon_social ?? 'Sin nombre', // Corrected: Use nombre_razon_social
                        'email' => $cotizacion->cliente->email,
                        'telefono' => $cotizacion->cliente->telefono,
                        'rfc' => $cotizacion->cliente->rfc,
                        'regimen_fiscal' => $cotizacion->cliente->regimen_fiscal,
                        'uso_cfdi' => $cotizacion->cliente->uso_cfdi,
                        'calle' => $cotizacion->cliente->calle,
                        'numero_exterior' => $cotizacion->cliente->numero_exterior,
                        'numero_interior' => $cotizacion->cliente->numero_interior,
                        'colonia' => $cotizacion->cliente->colonia,
                        'codigo_postal' => $cotizacion->cliente->codigo_postal,
                        'municipio' => $cotizacion->cliente->municipio,
                        'estado' => $cotizacion->cliente->estado,
                        'pais' => $cotizacion->cliente->pais,

                    ],
                    'productos' => $items->toArray(),
                    'total' => $cotizacion->total,
                    'estado' => $cotizacion->estado->value,
                    'created_at' => $cotizacion->created_at->format('Y-m-d\TH:i:sP'), //nunca modificar
                    'numero_cotizacion' => $cotizacion->numero_cotizacion
                ];
            });

        return Inertia::render('Cotizaciones/Index', [
            'cotizaciones' => $cotizaciones->values(),
            'estados' => collect(EstadoCotizacion::cases())->map(fn($estado) => [
                'value' => $estado->value,
                'label' => $estado->label(),
                'color' => $estado->color()
            ]),
            'filters' => request()->only(['search', 'estado', 'fecha_inicio', 'fecha_fin'])
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return Inertia::render('Cotizaciones/Create', [
            'clientes' => Cliente::select('id', 'nombre_razon_social', 'email', 'telefono')->get(),
            'productos' => Producto::select('id', 'nombre', 'precio_venta', 'descripcion')->get(),
            'servicios' => Servicio::select('id', 'nombre', 'precio', 'descripcion')->get(),
            'defaults' => [
                'fecha' => now()->format('Y-m-d'),
                'validez' => 30,
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
        foreach ($validated['productos'] as $item) {
            $subtotal += $item['cantidad'] * $item['precio'];
        }

        $descuentoItems = 0;
        foreach ($validated['productos'] as $item) {
            $subtotalItem = $item['cantidad'] * $item['precio'];
            $descuentoItems += $subtotalItem * ($item['descuento'] / 100);
        }

        $descuentoGeneralMonto = ($subtotal - $descuentoItems) * ($request->descuento_general / 100);
        $subtotalFinal = ($subtotal - $descuentoItems) - $descuentoGeneralMonto;
        $iva = $subtotalFinal * 0.16;
        $total = $subtotalFinal + $iva;

        $cotizacion = Cotizacion::create([
            'cliente_id' => $validated['cliente_id'],
            'subtotal' => $subtotal,
            'descuento_general' => $descuentoGeneralMonto,
            'iva' => $iva,
            'total' => $total,
            'notas' => $request->notas,
            'estado' => EstadoCotizacion::Pendiente,
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

            CotizacionItem::create([
                'cotizacion_id' => $cotizacion->id,
                'cotizable_id' => $item['id'],
                'cotizable_type' => $class,
                'cantidad' => $item['cantidad'],
                'precio' => $item['precio'],
                'descuento' => $item['descuento'],
                'subtotal' => $subtotalItem,
                'descuento_monto' => $descuentoMontoItem,
            ]);
        }

        return redirect()->route('cotizaciones.index')->with('success', 'Cotización creada con éxito');
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        $cotizacion = Cotizacion::with(['cliente', 'items.cotizable'])->findOrFail($id);

        $items = $cotizacion->items->map(function ($item) {
            $cotizable = $item->cotizable;
            return [
                'id' => $cotizable->id,
                'nombre' => $cotizable->nombre ?? $cotizable->descripcion,
                'tipo' => $item->cotizable_type === Producto::class ? 'producto' : 'servicio',
                'pivot' => [
                    'cantidad' => $item->cantidad,
                    'precio' => $item->precio,
                    'descuento' => $item->descuento,
                ],
            ];
        });

        return Inertia::render('Cotizaciones/Show', [
            'cotizacion' => [
                'id' => $cotizacion->id,
                'cliente' => $cotizacion->cliente,
                'productos' => $items,
                'subtotal' => $cotizacion->subtotal,
                'descuento_general' => $cotizacion->descuento_general,
                'iva' => $cotizacion->iva,
                'total' => $cotizacion->total,
                'notas' => $cotizacion->notas,
                'estado' => $cotizacion->estado->value,
            ],
            'canConvert' => $cotizacion->estado === EstadoCotizacion::Aprobada,
            'canEdit' => $cotizacion->estado === EstadoCotizacion::Pendiente,
            'canDelete' => $cotizacion->estado === EstadoCotizacion::Pendiente,
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        $cotizacion = Cotizacion::with(['cliente', 'items.cotizable'])->findOrFail($id);

        // Permitir edición solo si está en Borrador o Pendiente
        if (!in_array($cotizacion->estado, [EstadoCotizacion::Borrador, EstadoCotizacion::Pendiente])) {
            return Redirect::route('cotizaciones.show', $cotizacion->id)
                ->with('warning', 'Solo cotizaciones en borrador o pendientes pueden ser editadas');
        }

        $items = $cotizacion->items->map(function ($item) {
            $cotizable = $item->cotizable;
            return [
                'id' => $cotizable->id,
                'nombre' => $cotizable->nombre ?? $cotizable->descripcion,
                'tipo' => $item->cotizable_type === Producto::class ? 'producto' : 'servicio',
                'pivot' => [
                    'cantidad' => $item->cantidad,
                    'precio' => $item->precio,
                    'descuento' => $item->descuento,
                ],
            ];
        });

        return Inertia::render('Cotizaciones/Edit', [
            'cotizacion' => [
                'id' => $cotizacion->id,
                'cliente_id' => $cotizacion->cliente_id,
                'cliente' => $cotizacion->cliente,
                'productos' => $items,
                'subtotal' => $cotizacion->subtotal,
                'descuento_general' => $cotizacion->descuento_general,
                'iva' => $cotizacion->iva,
                'total' => $cotizacion->total,
                'notas' => $cotizacion->notas,
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
        $cotizacion = Cotizacion::findOrFail($id);

        // Permitir edición solo si está en Borrador o Pendiente
        if (!in_array($cotizacion->estado, [EstadoCotizacion::Borrador, EstadoCotizacion::Pendiente])) {
            return Redirect::back()->with('error', 'Solo cotizaciones en borrador o pendientes pueden ser actualizadas');
        }

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

        $descuentoGeneralMonto = ($subtotal - $descuentoItems) * ($request->descuento_general / 100);
        $subtotalFinal = ($subtotal - $descuentoItems) - $descuentoGeneralMonto;
        $iva = $subtotalFinal * 0.16;
        $total = $subtotalFinal + $iva;

        // Determinar el nuevo estado: si está en Borrador, cambiarlo a Pendiente
        $nuevoEstado = $cotizacion->estado === EstadoCotizacion::Borrador
            ? EstadoCotizacion::Pendiente
            : $cotizacion->estado;

        $cotizacion->update([
            'cliente_id' => $validated['cliente_id'],
            'subtotal' => $subtotal,
            'descuento_general' => $descuentoGeneralMonto,
            'iva' => $iva,
            'total' => $total,
            'notas' => $request->notas,
            'estado' => $nuevoEstado, // Actualizar el estado
        ]);

        // Eliminar ítems anteriores
        $cotizacion->items()->delete();

        // Guardar nuevos ítems
        foreach ($validated['productos'] as $itemData) {
            $class = $itemData['tipo'] === 'producto' ? Producto::class : Servicio::class;
            $modelo = $class::find($itemData['id']);

            if (! $modelo) {
                Log::warning("Ítem no encontrado: {$class} con ID {$itemData['id']}");
                continue;
            }

            $subtotalItem = $itemData['cantidad'] * $itemData['precio'];
            $descuentoMontoItem = $subtotalItem * ($itemData['descuento'] / 100);

            CotizacionItem::create([
                'cotizacion_id' => $cotizacion->id,
                'cotizable_id' => $itemData['id'],
                'cotizable_type' => $class,
                'cantidad' => $itemData['cantidad'],
                'precio' => $itemData['precio'],
                'descuento' => $itemData['descuento'],
                'subtotal' => $subtotalItem,
                'descuento_monto' => $descuentoMontoItem,
            ]);
        }

        $mensajeExito = $nuevoEstado === EstadoCotizacion::Pendiente && $cotizacion->estado === EstadoCotizacion::Borrador
            ? 'Cotización actualizada y cambiada a estado pendiente exitosamente'
            : 'Cotización actualizada exitosamente';

        return Redirect::route('cotizaciones.index')
            ->with('success', $mensajeExito);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $cotizacion = Cotizacion::findOrFail($id);

        if (!in_array($cotizacion->estado, [EstadoCotizacion::Borrador, EstadoCotizacion::Pendiente, EstadoCotizacion::Aprobada])) {
            return Redirect::back()->with('error', 'Solo cotizaciones pendientes pueden ser eliminadas');
        }

        $cotizacion->items()->delete();
        $cotizacion->delete();

        return Redirect::route('cotizaciones.index')
            ->with('success', 'Cotización eliminada exitosamente');
    }


    /**
     * Convertir cotización a venta.
     */
    public function convertirAVenta($id)
    {
        $cotizacion = Cotizacion::with(['cliente', 'items.cotizable'])->findOrFail($id);

        if ($cotizacion->estado !== EstadoCotizacion::Aprobada) {
            return Redirect::back()->with('error', 'Solo cotizaciones aprobadas pueden convertirse a venta');
        }

        DB::beginTransaction();
        try {
            $venta = \App\Models\Venta::create([
                'cliente_id' => $cotizacion->cliente_id,
                'total' => $cotizacion->total,
            ]);

            foreach ($cotizacion->items as $item) {
                $class = $item->cotizable_type;
                $id = $item->cotizable_id;

                if ($class === \App\Models\Producto::class) {
                    $venta->productos()->attach($id, [
                        'cantidad' => $item->cantidad,
                        'precio' => $item->precio,
                    ]);
                    $class::find($id)->decrement('stock', $item->cantidad);
                } elseif ($class === \App\Models\Servicio::class) {
                    $venta->servicios()->attach($id, [
                        'cantidad' => $item->cantidad,
                        'precio' => $item->precio,
                    ]);
                }
            }

            DB::commit();
            return Redirect::route('ventas.show', $venta->id)
                ->with('success', 'Venta creada exitosamente a partir de la cotización');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error al convertir cotización a venta', ['error' => $e->getMessage()]);
            return Redirect::back()->with('error', 'Error al crear la venta');
        }
    }

    public function duplicate(Request $request, $id)
    {
        $cotizacion = Cotizacion::with('cliente', 'items.cotizable')->findOrFail($id);
        //$this->authorize('create', Cotizacion::class);

        DB::beginTransaction();
        try {
            // Duplicar la cotización
            $nueva = $cotizacion->replicate();
            $nueva->estado = EstadoCotizacion::Borrador; // Asegúrate de usar el enum correctamente
            $nueva->created_at = now();
            $nueva->updated_at = now();
            $nueva->save();

            // Duplicar los ítems
            foreach ($cotizacion->items as $item) {
                $nueva->items()->create([
                    'cotizable_id' => $item->cotizable_id,
                    'cotizable_type' => $item->cotizable_type,
                    'cantidad' => $item->cantidad,
                    'precio' => $item->precio,
                    'descuento' => $item->descuento,
                    'subtotal' => $item->subtotal,
                    'descuento_monto' => $item->descuento_monto,
                ]);
            }

            DB::commit();

            // Redirigir a la página de detalles de la nueva cotización

            return Redirect::route('cotizaciones.index')
                ->with('success', 'Cotización duplicada correctamente.');
        } catch (\Exception $e) {
            DB::rollback();
            Log::error('Error duplicando cotización: ' . $e->getMessage());

            // Redirigir de vuelta con un mensaje de error
            return Redirect::back()
                ->with('error', 'Error al duplicar la cotización.');
        }
    }


    public function enviarAPedido($id)
    {
        try {
            DB::beginTransaction();

            // Obtener la cotización con relaciones completas
            $cotizacion = Cotizacion::with([
                'items.cotizable',
                'cliente',
                'productos',
                'servicios'
            ])->findOrFail($id);

            // Validar estado
            if (!$cotizacion->puedeEnviarseAPedido()) {
                return response()->json([
                    'success' => false,
                    'error' => 'La cotización no está en estado válido para enviar a pedido',
                    'estado_actual' => $cotizacion->estado->value,
                    'requiere_confirmacion' => false
                ], 400);
            }

            // Validar items
            if ($cotizacion->items->isEmpty()) {
                return response()->json([
                    'success' => false,
                    'error' => 'La cotización no contiene items para enviar a pedido',
                    'requiere_confirmacion' => false
                ], 400);
            }

            // Validar cliente
            if (!$cotizacion->cliente) {
                return response()->json([
                    'success' => false,
                    'error' => 'La cotización no tiene cliente asociado',
                    'requiere_confirmacion' => false
                ], 400);
            }

            // Crear nuevo pedido
            $pedido = new Pedido();
            $pedido->fill([
                'cliente_id' => $cotizacion->cliente_id,
                'cotizacion_id' => $cotizacion->id,
                'numero_pedido' => $this->generarNumeroPedido(),
                'fecha' => now(),
                'estado' => EstadoPedido::Borrador,
                'subtotal' => $cotizacion->subtotal,
                'descuento_general' => $cotizacion->descuento_general,
                'iva' => $cotizacion->iva,
                'total' => $cotizacion->total,
                'notas' => "Generado desde cotización #{$cotizacion->id}"
            ]);
            $pedido->save();

            // Copiar items de cotización a pedido
            foreach ($cotizacion->items as $item) {
                $pedido->items()->create([
                    'pedible_id' => $item->cotizable_id,
                    'pedible_type' => $item->cotizable_type,
                    'cantidad' => $item->cantidad,
                    'precio' => $item->precio,
                    'descuento' => $item->descuento,
                    'subtotal' => $item->subtotal,
                    'descuento_monto' => $item->descuento_monto
                ]);
            }

            // Actualizar estado de la cotización

            $cotizacion->update(['estado' => EstadoCotizacion::EnviadoAPedido]);

            //$cotizacion->estado = EstadoCotizacion::EnviadoAPedido;
            //$cotizacion->save();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Pedido creado exitosamente',
                'pedido_id' => $pedido->id,
                'numero_pedido' => $pedido->numero_pedido,
                'items_count' => $pedido->items->count()
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'error' => 'Error interno al procesar el pedido',
                'details' => env('APP_DEBUG') ? $e->getMessage() : null
            ], 500);
        }
    }

    private function generarNumeroPedido()
    {
        $ultimo = Pedido::orderBy('id', 'desc')->first();
        $numero = $ultimo ? $ultimo->id + 1 : 1;
        return 'PED-' . str_pad($numero, 6, '0', STR_PAD_LEFT);
    }
}
