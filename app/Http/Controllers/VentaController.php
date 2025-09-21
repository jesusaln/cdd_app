<?php

namespace App\Http\Controllers;

use App\Models\Venta;
use App\Models\VentaItem;
use App\Models\Cliente;
use App\Models\Producto;
use App\Models\Servicio;
use App\Models\User;
use App\Models\Tecnico;
use App\Enums\EstadoVenta;
use App\Services\MarginService;
use App\Models\SatEstado;
use App\Models\SatRegimenFiscal;
use App\Models\SatUsoCfdi;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class VentaController extends Controller
{
    use AuthorizesRequests;

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $ventas = Venta::with(['cliente', 'items.ventable'])
            ->get()
            ->filter(function ($venta) {
                // Filtrar ventas con cliente y al menos un item válido
                return $venta->cliente !== null && $venta->items->isNotEmpty();
            })
            ->map(function ($venta) {
                $items = $venta->items->map(function ($item) {
                    $ventable = $item->ventable;
                    return [
                        'id' => $ventable->id,
                        'nombre' => $ventable->nombre ?? 'Sin nombre',
                        'tipo' => $item->ventable_type === Producto::class ? 'producto' : 'servicio',
                        'cantidad' => $item->cantidad,
                        'precio' => $item->precio,
                        'descuento' => $item->descuento ?? 0,
                        'descuento_monto' => $item->descuento_monto ?? 0,
                        'subtotal' => $item->subtotal,
                    ];
                });

                return [
                    'id' => $venta->id,
                    'fecha' => $venta->fecha ? $venta->fecha->format('Y-m-d') : $venta->created_at->format('Y-m-d'),
                    'created_at' => $venta->created_at->format('Y-m-d\TH:i:sP'),
                    'cliente' => [
                        'id' => $venta->cliente->id,
                        'nombre' => $venta->cliente->nombre_razon_social ?? 'Sin nombre',
                        'nombre_razon_social' => $venta->cliente->nombre_razon_social ?? 'Sin nombre', // Para compatibilidad
                        'email' => $venta->cliente->email,
                        'telefono' => $venta->cliente->telefono,
                        'rfc' => $venta->cliente->rfc,
                        'regimen_fiscal' => $venta->cliente->regimen_fiscal,
                        'uso_cfdi' => $venta->cliente->uso_cfdi,
                        'calle' => $venta->cliente->calle,
                        'numero_exterior' => $venta->cliente->numero_exterior,
                        'numero_interior' => $venta->cliente->numero_interior,
                        'colonia' => $venta->cliente->colonia,
                        'codigo_postal' => $venta->cliente->codigo_postal,
                        'municipio' => $venta->cliente->municipio,
                        'estado' => $venta->cliente->estado,
                        'pais' => $venta->cliente->pais,
                    ],
                    'productos' => $items->toArray(),
                    'subtotal' => $venta->subtotal,
                    'descuento_general' => $venta->descuento_general ?? 0,
                    'iva' => $venta->iva,
                    'total' => $venta->total,
                    'estado' => $venta->estado->value,
                    'numero_venta' => $venta->numero_venta,
                    'pedido_id' => $venta->pedido_id ?? null,
                    'pagado' => $venta->pagado,
                    'metodo_pago' => $venta->metodo_pago,
                    'fecha_pago' => $venta->fecha_pago ? $venta->fecha_pago->format('Y-m-d') : null,
                    'notas_pago' => $venta->notas_pago,
                ];
            });

        // Calcular estadísticas
        $totalVentas = $ventas->count();
        $estadisticas = [
            'total' => $totalVentas,
            'borrador' => $ventas->where('estado', EstadoVenta::Borrador->value)->count(),
            'pendientes' => $ventas->where('estado', EstadoVenta::Pendiente->value)->count(),
            'aprobadas' => $ventas->where('estado', EstadoVenta::Aprobada->value)->count(),
            'cancelada' => $ventas->where('estado', EstadoVenta::Cancelada->value)->count(),
        ];

        return Inertia::render('Ventas/Index', [
            'ventas' => $ventas->values(),
            'estados' => collect(EstadoVenta::cases())->map(fn($estado) => [
                'value' => $estado->value,
                'label' => $estado->label(),
                'color' => $estado->color()
            ]),
            'estadisticas' => $estadisticas,
            'filters' => request()->only(['search', 'estado', 'fecha_inicio', 'fecha_fin'])
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return Inertia::render('Ventas/Create', [
            'clientes' => Cliente::select('id', 'nombre_razon_social', 'email', 'telefono')->get(),
            'productos' => Producto::select('id', 'nombre', 'precio_venta', 'descripcion')->get(),
            'servicios' => Servicio::select('id', 'nombre', 'precio', 'descripcion')->get(),
            'usuarios' => User::select('id', 'name', 'email')->get(),
            'tecnicos' => Tecnico::select('id', 'nombre', 'apellido', 'email')->get(),
            'catalogs' => [
                'tiposPersona' => [
                    ['value' => 'fisica', 'text' => 'Persona Física'],
                    ['value' => 'moral', 'text' => 'Persona Moral'],
                ],
                'estados' => SatEstado::orderBy('nombre')
                    ->get(['clave', 'nombre'])
                    ->map(function ($estado) {
                        return [
                            'value' => $estado->clave,
                            'text' => $estado->clave . ' — ' . $estado->nombre
                        ];
                    })
                    ->toArray(),
                'regimenesFiscales' => SatRegimenFiscal::orderBy('clave')
                    ->get(['clave', 'descripcion', 'persona_fisica', 'persona_moral'])
                    ->toArray(),
                'usosCFDI' => SatUsoCfdi::orderBy('clave')
                    ->get(['clave', 'descripcion', 'persona_fisica', 'persona_moral'])
                    ->map(function ($uso) {
                        return [
                            'value' => $uso->clave,
                            'text' => $uso->clave . ' — ' . $uso->descripcion
                        ];
                    })
                    ->toArray(),
            ],
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
            'vendedor_type' => 'nullable|in:App\\Models\\User,App\\Models\\Tecnico',
            'vendedor_id' => 'nullable|integer',
            'productos' => 'required|array',
            'productos.*.id' => 'required|integer',
            'productos.*.tipo' => 'required|in:producto,servicio',
            'productos.*.cantidad' => 'required|integer|min:1',
            'productos.*.precio' => 'required|numeric|min:0',
            'productos.*.descuento' => 'required|numeric|min:0|max:100',
            'descuento_general' => 'nullable|numeric|min:0|max:100',
            'notas' => 'nullable|string',
        ]);

        DB::beginTransaction();
        try {
            // Validar stock disponible para productos (considerando reservas)
            foreach ($validated['productos'] as $item) {
                if ($item['tipo'] === 'producto') {
                    $producto = Producto::find($item['id']);
                    if (!$producto) {
                        return redirect()->back()->with('error', "Producto con ID {$item['id']} no encontrado");
                    }

                    if ($producto->stock_disponible < $item['cantidad']) {
                        return redirect()->back()->with('error',
                            "Stock insuficiente para '{$producto->nombre}'. Disponible: {$producto->stock_disponible}, Solicitado: {$item['cantidad']}"
                        );
                    }
                }
            }

            // Validar márgenes de ganancia
            $marginService = new MarginService();
            $validacionMargen = $marginService->validarMargenesProductos($validated['productos']);

            if (!$validacionMargen['todos_validos']) {
                // Si hay productos con margen insuficiente, verificar si el usuario aceptó el ajuste
                if ($request->has('ajustar_margen') && $request->ajustar_margen === 'true') {
                    // Ajustar precios automáticamente
                    foreach ($validated['productos'] as &$item) {
                        if ($item['tipo'] === 'producto') {
                            $producto = Producto::find($item['id']);
                            if ($producto) {
                                $item['precio'] = $marginService->ajustarPrecioAlMargen($producto, $item['precio']);
                            }
                        }
                    }
                } else {
                    // Mostrar advertencia y permitir al usuario decidir
                    $mensaje = $marginService->generarMensajeAdvertencia($validacionMargen['productos_bajo_margen']);
                    return redirect()->back()
                        ->withInput()
                        ->with('warning', $mensaje)
                        ->with('requiere_confirmacion_margen', true)
                        ->with('productos_bajo_margen', $validacionMargen['productos_bajo_margen']);
                }
            }

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

            $numero_venta = $this->generarNumeroVenta();

            $venta = Venta::create([
                'cliente_id' => $validated['cliente_id'],
                'vendedor_type' => $validated['vendedor_type'] ?? null,
                'vendedor_id' => $validated['vendedor_id'] ?? null,
                'factura_id' => null, // Puede llenarse si se asocia con una factura
                'numero_venta' => $numero_venta,
                'subtotal' => $subtotal,
                'descuento_general' => $descuentoGeneralMonto,
                'iva' => $iva,
                'total' => $total,
                'fecha' => now(),
                'estado' => EstadoVenta::Borrador,
                'notas' => $request->notas,
            ]);

            // Crear items y reducir inventario
            foreach ($validated['productos'] as $item) {
                $class = $item['tipo'] === 'producto' ? Producto::class : Servicio::class;
                $modelo = $class::find($item['id']);

                if (!$modelo) {
                    Log::warning("Ítem no encontrado: {$class} con ID {$item['id']}");
                    continue;
                }

                $subtotalItem = $item['cantidad'] * $item['precio'];
                $descuentoMontoItem = $subtotalItem * ($item['descuento'] / 100);

                VentaItem::create([
                    'venta_id' => $venta->id,
                    'ventable_id' => $item['id'],
                    'ventable_type' => $class,
                    'cantidad' => $item['cantidad'],
                    'precio' => $item['precio'],
                    'descuento' => $item['descuento'],
                    'subtotal' => $subtotalItem,
                    'descuento_monto' => $descuentoMontoItem,
                ]);

                // Reducir inventario solo para productos (priorizar reservas)
                if ($item['tipo'] === 'producto') {
                    $cantidadRestante = $item['cantidad'];

                    // Primero consumir reservas si existen
                    if ($modelo->reservado > 0) {
                        $consumirReserva = min($modelo->reservado, $cantidadRestante);
                        $modelo->decrement('reservado', $consumirReserva);
                        $cantidadRestante -= $consumirReserva;

                        Log::info("Reserva consumida para producto {$modelo->id}", [
                            'producto_id' => $modelo->id,
                            'reserva_consumida' => $consumirReserva,
                            'reservado_anterior' => $modelo->reservado + $consumirReserva,
                            'reservado_actual' => $modelo->reservado
                        ]);
                    }

                    // Si queda cantidad por consumir, reducir stock físico
                    if ($cantidadRestante > 0) {
                        $modelo->decrement('stock', $cantidadRestante);
                        Log::info("Stock reducido para producto {$modelo->id}", [
                            'producto_id' => $modelo->id,
                            'cantidad_reducida' => $cantidadRestante,
                            'stock_anterior' => $modelo->stock + $cantidadRestante,
                            'stock_actual' => $modelo->stock
                        ]);
                    }
                }
            }

            DB::commit();
            return redirect()->route('ventas.index')->with('success', 'Venta creada con éxito');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error al crear venta: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Error al crear la venta: ' . $e->getMessage());
        }
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        $venta = Venta::with(['cliente', 'items.ventable'])->findOrFail($id);

        $items = $venta->items->map(function ($item) {
            $ventable = $item->ventable;
            return [
                'id' => $ventable->id,
                'nombre' => $ventable->nombre ?? $ventable->descripcion,
                'tipo' => $item->ventable_type === Producto::class ? 'producto' : 'servicio',
                'pivot' => [
                    'cantidad' => $item->cantidad,
                    'precio' => $item->precio,
                    'descuento' => $item->descuento,
                ],
            ];
        });

        return Inertia::render('Ventas/Show', [
            'venta' => [
                'id' => $venta->id,
                'cliente' => $venta->cliente,
                'productos' => $items,
                'subtotal' => $venta->subtotal,
                'descuento_general' => $venta->descuento_general,
                'iva' => $venta->iva,
                'total' => $venta->total,
                'fecha' => $venta->fecha ? $venta->fecha->format('Y-m-d') : $venta->created_at->format('Y-m-d'),
                'notas' => $venta->notas,
                'estado' => $venta->estado->value,
                'numero_venta' => $venta->numero_venta,
                'factura_id' => $venta->factura_id,
            ],
            'canEdit' => $venta->estado === EstadoVenta::Borrador || $venta->estado === EstadoVenta::Pendiente,
            'canDelete' => $venta->estado === EstadoVenta::Borrador || $venta->estado === EstadoVenta::Pendiente,
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        $venta = Venta::with([
            'cliente',
            'items.ventable' // ← Esto carga productos y servicios
        ])->findOrFail($id);

        $clientes = Cliente::all();
        $productos = Producto::all();
        $servicios = Servicio::all();

        return Inertia::render('Ventas/Edit', compact('venta', 'clientes', 'productos', 'servicios'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $venta = Venta::findOrFail($id);

        // Permitir edición solo si está en Borrador o Pendiente
        if (!in_array($venta->estado, [EstadoVenta::Borrador, EstadoVenta::Pendiente])) {
            return Redirect::back()->with('error', 'Solo ventas en borrador o pendientes pueden ser actualizadas');
        }

        $validated = $request->validate([
            'cliente_id' => 'required|exists:clientes,id',
            'numero_venta' => 'required|string|unique:ventas,numero_venta,' . $venta->id,
            'productos' => 'required|array',
            'productos.*.id' => 'required|integer',
            'productos.*.tipo' => 'required|in:producto,servicio',
            'productos.*.cantidad' => 'required|integer|min:1',
            'productos.*.precio' => 'required|numeric|min:0',
            'productos.*.descuento' => 'required|numeric|min:0|max:100',
            'descuento_general' => 'nullable|numeric|min:0|max:100',
            'notas' => 'nullable|string',
            'ajustar_margen' => 'nullable|boolean',
        ]);

        // Validar márgenes de ganancia antes de calcular totales
        $marginService = new MarginService();
        $validacionMargen = $marginService->validarMargenesProductos($validated['productos']);

        if (!$validacionMargen['todos_validos']) {
            // Si hay productos con margen insuficiente, verificar si el usuario aceptó el ajuste
            if ($request->has('ajustar_margen') && $request->ajustar_margen === 'true') {
                // Ajustar precios automáticamente
                foreach ($validated['productos'] as &$item) {
                    if ($item['tipo'] === 'producto') {
                        $producto = Producto::find($item['id']);
                        if ($producto) {
                            $item['precio'] = $marginService->ajustarPrecioAlMargen($producto, $item['precio']);
                        }
                    }
                }
            } else {
                // Mostrar advertencia y permitir al usuario decidir
                $mensaje = $marginService->generarMensajeAdvertencia($validacionMargen['productos_bajo_margen']);
                return Redirect::back()
                    ->withInput()
                    ->with('warning', $mensaje)
                    ->with('requiere_confirmacion_margen', true)
                    ->with('productos_bajo_margen', $validacionMargen['productos_bajo_margen']);
            }
        }

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

        // Guarda el estado ANTES de actualizar (clave para mensaje)
        $estadoAnterior = $venta->estado;

        // Determinar el nuevo estado: si está en Borrador, cambiarlo a Pendiente
        $nuevoEstado = $venta->estado === EstadoVenta::Borrador
            ? EstadoVenta::Pendiente
            : $venta->estado;

        // Atomicidad: actualización + refresco de items
        DB::transaction(function () use (&$venta, $validated, $subtotal, $descuentoGeneralMonto, $iva, $total, $nuevoEstado, $request) {
            $venta->update([
                'cliente_id' => $validated['cliente_id'],
                'numero_venta' => $validated['numero_venta'],
                'subtotal' => $subtotal,
                'descuento_general' => $descuentoGeneralMonto,
                'iva' => $iva,
                'total' => $total,
                'fecha' => now(),
                'estado' => $nuevoEstado,
                'notas' => $request->notas,
            ]);

            // Eliminar ítems anteriores
            $venta->items()->delete();

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

                VentaItem::create([
                    'venta_id' => $venta->id,
                    'ventable_id' => $itemData['id'],
                    'ventable_type' => $class,
                    'cantidad' => $itemData['cantidad'],
                    'precio' => $itemData['precio'],
                    'descuento' => $itemData['descuento'],
                    'subtotal' => $subtotalItem,
                    'descuento_monto' => $descuentoMontoItem,
                ]);
            }
        });

        // Usa el estado anterior para construir el mensaje correcto
        $mensajeExito = ($estadoAnterior === EstadoVenta::Borrador) && ($nuevoEstado === EstadoVenta::Pendiente)
            ? 'Venta actualizada y cambiada a estado pendiente exitosamente'
            : 'Venta actualizada exitosamente';

        return Redirect::route('ventas.index')
            ->with('success', $mensajeExito);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        return DB::transaction(function () use ($id) {
            try {
                $venta = Venta::with('factura')->findOrFail($id);

                // Verificar que la venta puede ser eliminada
                if (!in_array($venta->estado, [EstadoVenta::Borrador, EstadoVenta::Pendiente])) {
                    return Redirect::back()->with('error', 'Solo ventas en borrador o pendientes pueden ser eliminadas');
                }

                // Guardar información de la factura antes de eliminar
                $facturaId = $venta->factura_id;
                $factura = $venta->factura;

                // Eliminar los ítems de la venta primero
                $venta->items()->delete();

                // Eliminar la venta
                $venta->delete();

                // Revertir el estado de la factura asociada DESPUÉS de eliminar la venta
                if ($facturaId && $factura) {
                    $factura->estado = 'pendiente';
                    $factura->save();

                    Log::info("Venta ID {$id} eliminada y Factura ID {$facturaId} revertida a estado pendiente");
                }

                return Redirect::route('ventas.index')
                    ->with('success', 'Venta eliminada exitosamente y factura revertida a pendiente');
            } catch (\Exception $e) {
                Log::error('Error al eliminar venta: ' . $e->getMessage());

                // La transacción se revertirá automáticamente
                return Redirect::back()
                    ->with('error', 'Error al eliminar la venta: ' . $e->getMessage());
            }
        });
    }

    /**
     * Duplicate a venta.
     */
    public function duplicate(Request $request, $id)
    {
        try {
            $venta = Venta::with('cliente', 'items.ventable')->findOrFail($id);

            // Solo permitir duplicar ventas que no estén canceladas
            if ($venta->estado === EstadoVenta::Cancelada) {
                return response()->json([
                    'success' => false,
                    'error' => 'No se pueden duplicar ventas canceladas.'
                ], 400);
            }

            // Validar que la venta tenga ítems
            if ($venta->items->isEmpty()) {
                return response()->json([
                    'success' => false,
                    'error' => 'No se puede duplicar una venta sin ítems.'
                ], 400);
            }

            DB::beginTransaction();

            // Crear nueva venta con datos básicos
            $nuevaVenta = new Venta([
                'cliente_id' => $venta->cliente_id,
                'pedido_id' => $venta->pedido_id,
                'numero_venta' => $this->generarNumeroVenta(),
                'subtotal' => $venta->subtotal,
                'descuento_general' => $venta->descuento_general,
                'iva' => $venta->iva,
                'total' => $venta->total,
                'notas' => $venta->notas,
                'estado' => EstadoVenta::Borrador,
                'fecha' => now(),
            ]);

            $nuevaVenta->save();

            // Duplicar los ítems validando que los productos/servicios existan
            $itemsDuplicados = 0;
            foreach ($venta->items as $item) {
                // Verificar que el producto/servicio aún existe
                $modelo = $item->ventable;
                if (!$modelo) {
                    Log::warning("Producto/Servicio no encontrado al duplicar venta", [
                        'venta_id' => $id,
                        'ventable_id' => $item->ventable_id,
                        'ventable_type' => $item->ventable_type
                    ]);
                    continue; // Saltar este ítem
                }

                $nuevaVenta->items()->create([
                    'ventable_id' => $item->ventable_id,
                    'ventable_type' => $item->ventable_type,
                    'cantidad' => $item->cantidad,
                    'precio' => $item->precio,
                    'descuento' => $item->descuento,
                    'subtotal' => $item->subtotal,
                    'descuento_monto' => $item->descuento_monto,
                ]);

                $itemsDuplicados++;
            }

            // Verificar que se duplicaron ítems
            if ($itemsDuplicados === 0) {
                DB::rollBack();
                return response()->json([
                    'success' => false,
                    'error' => 'No se pudieron duplicar los ítems de la venta.'
                ], 400);
            }

            DB::commit();

            Log::info('Venta duplicada exitosamente', [
                'venta_original_id' => $id,
                'venta_nueva_id' => $nuevaVenta->id,
                'items_duplicados' => $itemsDuplicados
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Venta duplicada correctamente.',
                'venta_id' => $nuevaVenta->id,
                'numero_venta' => $nuevaVenta->numero_venta,
                'items_count' => $itemsDuplicados
            ]);

        } catch (\Illuminate\Database\QueryException $e) {
            DB::rollBack();
            Log::error('Error de base de datos al duplicar venta: ' . $e->getMessage(), [
                'venta_id' => $id,
                'sql' => $e->getSql(),
                'bindings' => $e->getBindings()
            ]);

            return response()->json([
                'success' => false,
                'error' => 'Error de base de datos al duplicar la venta.',
                'details' => app()->environment('local') ? $e->getMessage() : null
            ], 500);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error general al duplicar venta: ' . $e->getMessage(), [
                'venta_id' => $id,
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'error' => 'Error interno al duplicar la venta.',
                'details' => app()->environment('local') ? $e->getMessage() : null
            ], 500);
        }
    }

    /**
     * Cancel the specified resource (soft cancel).
     */
    public function cancel($id)
    {
        $venta = Venta::with('items.ventable')->findOrFail($id);

        // Permitir cancelar en cualquier estado excepto ya cancelada
        if ($venta->estado === EstadoVenta::Cancelada) {
            return response()->json([
                'success' => false,
                'error' => 'La venta ya está cancelada'
            ], 400);
        }

        DB::beginTransaction();
        try {
            // Devolver inventario de productos (incrementar reservas)
            foreach ($venta->items as $item) {
                if ($item->ventable_type === Producto::class) {
                    $producto = $item->ventable;
                    if ($producto) {
                        // Devolver a reservas (ya que la venta consumió reservas)
                        $producto->increment('reservado', $item->cantidad);
                        Log::info("Reserva devuelta para producto {$producto->id}", [
                            'producto_id' => $producto->id,
                            'cantidad_devuelta' => $item->cantidad,
                            'reservado_anterior' => $producto->reservado - $item->cantidad,
                            'reservado_actual' => $producto->reservado
                        ]);
                    }
                }
            }

            // Actualizar estado a cancelada y registrar quién lo canceló
            $venta->update([
                'estado' => EstadoVenta::Cancelada,
                'deleted_by' => \Illuminate\Support\Facades\Auth::id(),
                'deleted_at' => now()
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Venta cancelada exitosamente',
                'eliminado_por' => \Illuminate\Support\Facades\Auth::user()->name ?? 'Usuario actual'
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error al cancelar venta: ' . $e->getMessage(), [
                'venta_id' => $id,
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'error' => 'Error al cancelar la venta',
                'details' => app()->environment('local') ? $e->getMessage() : null
            ], 500);
        }
    }

    /**
     * Mark a sale as paid.
     */
    public function marcarPagado(Request $request, $id)
    {
        $request->validate([
            'metodo_pago' => 'required|in:efectivo,transferencia,cheque,tarjeta,otros',
            'notas_pago' => 'nullable|string|max:500'
        ]);

        $venta = Venta::findOrFail($id);

        // Verificar que la venta no esté ya pagada
        if ($venta->pagado) {
            return response()->json([
                'success' => false,
                'error' => 'Esta venta ya está marcada como pagada'
            ], 400);
        }

        // Verificar que la venta no esté cancelada
        if ($venta->estado === EstadoVenta::Cancelada) {
            return response()->json([
                'success' => false,
                'error' => 'No se puede marcar como pagada una venta cancelada'
            ], 400);
        }

        DB::beginTransaction();
        try {
            // Actualizar la venta con la información de pago
            $venta->update([
                'pagado' => true,
                'metodo_pago' => $request->metodo_pago,
                'fecha_pago' => now(),
                'notas_pago' => $request->notas_pago,
                'pagado_por' => $request->user()->id,
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Venta marcada como pagada exitosamente',
                'venta' => [
                    'id' => $venta->id,
                    'pagado' => true,
                    'metodo_pago' => $venta->metodo_pago,
                    'fecha_pago' => $venta->fecha_pago->format('Y-m-d'),
                    'notas_pago' => $venta->notas_pago,
                ]
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error al marcar venta como pagada: ' . $e->getMessage(), [
                'venta_id' => $id,
                'user_id' => $request->user()->id,
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'error' => 'Error interno al procesar el pago',
                'details' => app()->environment('local') ? $e->getMessage() : null
            ], 500);
        }
    }

    /**
     * Generate a unique numero_venta.
     */
    private function generarNumeroVenta()
    {
        $ultima = Venta::orderBy('id', 'desc')->first();
        $numero = $ultima ? $ultima->id + 1 : 1;
        return 'VEN-' . date('Ymd') . '-' . str_pad($numero, 5, '0', STR_PAD_LEFT);
    }
}
