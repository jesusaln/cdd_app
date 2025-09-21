<?php

namespace App\Http\Controllers;

use App\Models\Cotizacion;
use Illuminate\Validation\Rule;

use App\Models\Pedido;
use App\Models\Venta;
use App\Enums\EstadoPedido;
use App\Models\Cliente;
use App\Models\Producto;
use App\Models\Servicio;
use App\Models\CotizacionItem;
use App\Enums\EstadoCotizacion;
use App\Services\MarginService;
use App\Models\SatEstado;
use App\Models\SatRegimenFiscal;
use App\Models\SatUsoCfdi;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Auth;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class CotizacionController extends Controller
{
    use AuthorizesRequests;

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $cotizaciones = \App\Models\Cotizacion::with([
            'cliente:id,nombre_razon_social,email,telefono,rfc,regimen_fiscal,uso_cfdi,calle,numero_exterior,numero_interior,colonia,codigo_postal,municipio,estado,pais',
            'items.cotizable',
            'createdBy:id,name',
            'updatedBy:id,name',
        ])
            ->orderBy('created_at', 'desc')
            ->get()
            ->filter(function ($cotizacion) {
                // Cotizaciones con cliente y al menos un ítem
                return $cotizacion->cliente !== null && $cotizacion->items->isNotEmpty();
            })
            ->map(function ($cotizacion) {
                $items = $cotizacion->items->map(function ($item) {
                    $cotizable = $item->cotizable;

                    return [
                        'id'        => $cotizable?->id,
                        'nombre'    => $cotizable->nombre ?? 'Sin nombre',
                        'tipo'      => $item->cotizable_type === \App\Models\Producto::class ? 'producto' : 'servicio',
                        'cantidad'  => (int) $item->cantidad,
                        'precio'    => (float) $item->precio,
                        'descuento' => (float) ($item->descuento ?? 0),
                    ];
                });

                $createdAtIso = optional($cotizacion->created_at)->toIso8601String();
                $updatedAtIso = optional($cotizacion->updated_at)->toIso8601String();

                return [
                    'id'                => $cotizacion->id,
                    'numero_cotizacion' => $cotizacion->numero_cotizacion,

                    // Fechas
                    'fecha'       => optional($cotizacion->created_at)->format('Y-m-d'),
                    'created_at'  => $createdAtIso,   // ← “nunca modificar” (ISO)
                    'updated_at'  => $updatedAtIso,

                    // Cliente
                    'cliente' => [
                        'id'               => $cotizacion->cliente->id,
                        'nombre'           => $cotizacion->cliente->nombre_razon_social ?? 'Sin nombre',
                        'email'            => $cotizacion->cliente->email,
                        'telefono'         => $cotizacion->cliente->telefono,
                        'rfc'              => $cotizacion->cliente->rfc,
                        'regimen_fiscal'   => $cotizacion->cliente->regimen_fiscal,
                        'uso_cfdi'         => $cotizacion->cliente->uso_cfdi,
                        'calle'            => $cotizacion->cliente->calle,
                        'numero_exterior'  => $cotizacion->cliente->numero_exterior,
                        'numero_interior'  => $cotizacion->cliente->numero_interior,
                        'colonia'          => $cotizacion->cliente->colonia,
                        'codigo_postal'    => $cotizacion->cliente->codigo_postal,
                        'municipio'        => $cotizacion->cliente->municipio,
                        'estado'           => $cotizacion->cliente->estado,
                        'pais'             => $cotizacion->cliente->pais,
                    ],

                    // Ítems
                    'productos' => $items->toArray(),

                    // Totales/estado
                    'total'  => (float) $cotizacion->total,
                    'estado' => is_object($cotizacion->estado) ? $cotizacion->estado->value : $cotizacion->estado,

                    // Auditoría (para tu modal y vistas)
                    'creado_por_nombre'      => $cotizacion->createdBy?->name,
                    'actualizado_por_nombre' => $cotizacion->updatedBy?->name,

                    // Redundancia segura para el modal (si espera un objeto metadata)
                    'metadata' => [
                        'creado_por'     => $cotizacion->createdBy?->name,
                        'actualizado_por' => $cotizacion->updatedBy?->name,
                        'creado_en'      => $createdAtIso,
                        'actualizado_en' => $updatedAtIso,
                    ],
                ];
            });

        return \Inertia\Inertia::render('Cotizaciones/Index', [
            'cotizaciones' => $cotizaciones->values(),
            'estados' => collect(\App\Enums\EstadoCotizacion::cases())->map(fn($estado) => [
                'value' => $estado->value,
                'label' => $estado->label(),
                'color' => $estado->color()
            ]),
            'filters' => request()->only(['search', 'estado', 'fecha_inicio', 'fecha_fin']),
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
            'ajustar_margen' => 'nullable|boolean',
        ]);

        // Validar márgenes de ganancia
        $marginService = new MarginService();
        $validacionMargen = $marginService->validarMargenesProductos($validated['productos']);

        Log::info('Validación de márgenes en cotización', [
            'productos_count' => count($validated['productos']),
            'todos_validos' => $validacionMargen['todos_validos'],
            'productos_bajo_margen_count' => count($validacionMargen['productos_bajo_margen']),
            'ajustar_margen_request' => $request->has('ajustar_margen') ? $request->ajustar_margen : 'no_presente'
        ]);

        if (!$validacionMargen['todos_validos']) {
            Log::info('Productos con margen insuficiente detectados', [
                'productos_bajo_margen' => $validacionMargen['productos_bajo_margen']
            ]);

            // Si hay productos con margen insuficiente, verificar si el usuario aceptó el ajuste
            if ($request->has('ajustar_margen') && $request->ajustar_margen === 'true') {
                Log::info('Usuario aceptó ajuste automático de márgenes');
                // Ajustar precios automáticamente
                foreach ($validated['productos'] as &$item) {
                    if ($item['tipo'] === 'producto') {
                        $producto = Producto::find($item['id']);
                        if ($producto) {
                            $precioOriginal = $item['precio'];
                            $item['precio'] = $marginService->ajustarPrecioAlMargen($producto, $item['precio']);
                            Log::info('Precio ajustado', [
                                'producto_id' => $producto->id,
                                'precio_original' => $precioOriginal,
                                'precio_ajustado' => $item['precio']
                            ]);
                        }
                    }
                }
            } else {
                Log::info('Mostrando modal de confirmación de márgenes insuficientes');
                // Mostrar advertencia y permitir al usuario decidir
                $mensaje = $marginService->generarMensajeAdvertencia($validacionMargen['productos_bajo_margen']);
                return redirect()->back()
                    ->withInput()
                    ->with('warning', $mensaje)
                    ->with('requiere_confirmacion_margen', true)
                    ->with('productos_bajo_margen', $validacionMargen['productos_bajo_margen']);
            }
        } else {
            Log::info('Todos los productos tienen márgenes válidos');
        }

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

            if (!$modelo) {
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
            'canEdit' => in_array($cotizacion->estado, [EstadoCotizacion::Borrador, EstadoCotizacion::Pendiente], true),
            'canDelete' => in_array($cotizacion->estado, [EstadoCotizacion::Borrador, EstadoCotizacion::Pendiente], true),
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        $cotizacion = Cotizacion::with(['cliente', 'items.cotizable'])->findOrFail($id);

        // Permitir edición solo si está en Borrador o Pendiente
        if (!in_array($cotizacion->estado, [EstadoCotizacion::Borrador, EstadoCotizacion::Pendiente], true)) {
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

        // (Opcional, si tienes Policy configurada)
        // $this->authorize('update', $cotizacion);

        // Solo permitir edición en Borrador o Pendiente
        if (!in_array($cotizacion->estado, [EstadoCotizacion::Borrador, EstadoCotizacion::Pendiente], true)) {
            return Redirect::back()->with('error', 'Solo cotizaciones en borrador o pendientes pueden ser actualizadas');
        }

        $validated = $request->validate([
            'cliente_id'           => 'required|exists:clientes,id',
            'productos'            => 'required|array|min:1',
            'productos.*.id'       => 'required|integer',
            'productos.*.tipo'     => 'required|in:producto,servicio',
            'productos.*.cantidad' => 'required|integer|min:1',
            'productos.*.precio'   => 'required|numeric|min:0',
            'productos.*.descuento' => 'required|numeric|min:0|max:100',
            'descuento_general'    => 'nullable|numeric|min:0|max:100',
            'notas'                => 'nullable|string',
            'ajustar_margen'       => 'nullable|boolean',
            // Validación de estado (si llega desde el front, se controla)
            'estado'               => ['sometimes', Rule::in(array_map(fn($c) => $c->value, EstadoCotizacion::cases()))],
        ]);

        // Validar márgenes de ganancia antes de calcular totales
        $marginService = new MarginService();
        $validacionMargen = $marginService->validarMargenesProductos($validated['productos']);

        Log::info('Validación de márgenes en actualización de cotización', [
            'cotizacion_id' => $id,
            'productos_count' => count($validated['productos']),
            'todos_validos' => $validacionMargen['todos_validos'],
            'productos_bajo_margen_count' => count($validacionMargen['productos_bajo_margen']),
            'ajustar_margen_request' => $request->has('ajustar_margen') ? $request->ajustar_margen : 'no_presente'
        ]);

        if (!$validacionMargen['todos_validos']) {
            Log::info('Productos con margen insuficiente detectados en actualización', [
                'cotizacion_id' => $id,
                'productos_bajo_margen' => $validacionMargen['productos_bajo_margen']
            ]);

            // Si hay productos con margen insuficiente, verificar si el usuario aceptó el ajuste
            if ($request->has('ajustar_margen') && $request->ajustar_margen === 'true') {
                Log::info('Usuario aceptó ajuste automático de márgenes en actualización', ['cotizacion_id' => $id]);
                // Ajustar precios automáticamente
                foreach ($validated['productos'] as &$item) {
                    if ($item['tipo'] === 'producto') {
                        $producto = Producto::find($item['id']);
                        if ($producto) {
                            $precioOriginal = $item['precio'];
                            $item['precio'] = $marginService->ajustarPrecioAlMargen($producto, $item['precio']);
                            Log::info('Precio ajustado en actualización', [
                                'cotizacion_id' => $id,
                                'producto_id' => $producto->id,
                                'precio_original' => $precioOriginal,
                                'precio_ajustado' => $item['precio']
                            ]);
                        }
                    }
                }
            } else {
                Log::info('Mostrando modal de confirmación de márgenes insuficientes en actualización', ['cotizacion_id' => $id]);
                // Mostrar advertencia y permitir al usuario decidir
                $mensaje = $marginService->generarMensajeAdvertencia($validacionMargen['productos_bajo_margen']);
                return Redirect::back()
                    ->withInput()
                    ->with('warning', $mensaje)
                    ->with('requiere_confirmacion_margen', true)
                    ->with('productos_bajo_margen', $validacionMargen['productos_bajo_margen']);
            }
        } else {
            Log::info('Todos los productos tienen márgenes válidos en actualización', ['cotizacion_id' => $id]);
        }

        // Cálculos: redondeamos a 2 decimales para evitar ruido por flotantes
        $subtotal = 0.0;
        $descuentoItems = 0.0;

        foreach ($validated['productos'] as $item) {
            $subtotalItem = (float) $item['cantidad'] * (float) $item['precio'];
            $subtotal += $subtotalItem;
            $descuentoItems += $subtotalItem * ((float) $item['descuento'] / 100);
        }

        $descuentoGeneralPorc = (float) ($request->descuento_general ?? 0);
        $descuentoGeneralMonto = ($subtotal - $descuentoItems) * ($descuentoGeneralPorc / 100);

        $subtotalFinal = ($subtotal - $descuentoItems) - $descuentoGeneralMonto;
        $iva           = $subtotalFinal * 0.16; // ajusta si tienes IVA configurable
        $total         = $subtotalFinal + $iva;

        // Redondeo final
        $subtotal            = round($subtotal, 2);
        $descuentoItems      = round($descuentoItems, 2);
        $descuentoGeneralMonto = round($descuentoGeneralMonto, 2);
        $subtotalFinal       = round($subtotalFinal, 2);
        $iva                 = round($iva, 2);
        $total               = round($total, 2);

        // Guardar estado ANTES de actualizar (para mensaje)
        $estadoAnterior = $cotizacion->estado;

        // Si estaba en Borrador, pasa a Pendiente; si no, conserva
        $nuevoEstado = $cotizacion->estado === EstadoCotizacion::Borrador
            ? EstadoCotizacion::Pendiente
            : $cotizacion->estado;

        // Atomicidad: actualizar cabecera + refrescar items
        DB::transaction(function () use (&$cotizacion, $validated, $subtotal, $descuentoGeneralMonto, $descuentoItems, $iva, $total, $nuevoEstado, $request) {
            $cotizacion->update([
                'cliente_id'        => $validated['cliente_id'],
                'subtotal'          => $subtotal,
                'descuento_general' => $descuentoGeneralMonto,
                'descuento_items'   => $descuentoItems,   // ✅ ahora se persiste
                'iva'               => $iva,
                'total'             => $total,
                'notas'             => $request->notas,
                'estado'            => $nuevoEstado,
            ]);

            // Eliminar ítems anteriores
            $cotizacion->items()->delete();

            // Guardar nuevos ítems
            foreach ($validated['productos'] as $itemData) {
                $class  = $itemData['tipo'] === 'producto' ? Producto::class : Servicio::class;
                $modelo = $class::find($itemData['id']);

                if (!$modelo) {
                    Log::warning("Ítem no encontrado: {$class} con ID {$itemData['id']}");
                    continue;
                }

                $subtotalItem      = (float) $itemData['cantidad'] * (float) $itemData['precio'];
                $descuentoMontoItem = $subtotalItem * ((float) $itemData['descuento'] / 100);

                CotizacionItem::create([
                    'cotizacion_id'   => $cotizacion->id,
                    'cotizable_id'    => $itemData['id'],
                    'cotizable_type'  => $class,
                    'cantidad'        => (int) $itemData['cantidad'],
                    'precio'          => (float) $itemData['precio'],
                    'descuento'       => (float) $itemData['descuento'],
                    'subtotal'        => round($subtotalItem, 2),
                    'descuento_monto' => round($descuentoMontoItem, 2),
                ]);
            }
        });

        // Mensaje usando estado anterior (no el ya mutado)
        $mensajeExito = ($estadoAnterior === EstadoCotizacion::Borrador && $nuevoEstado === EstadoCotizacion::Pendiente)
            ? 'Cotización actualizada y cambiada a estado pendiente exitosamente'
            : 'Cotización actualizada exitosamente';

        return Redirect::route('cotizaciones.index')->with('success', $mensajeExito);
    }


    /**
     * Cancel the specified resource (soft cancel).
     */
    public function cancel($id)
    {
        $cotizacion = Cotizacion::findOrFail($id);

        // Permitir cancelar en cualquier estado excepto ya cancelado
        if ($cotizacion->estado === EstadoCotizacion::Cancelado) {
            return Redirect::back()->with('error', 'La cotización ya está cancelada');
        }

        // Actualizar estado a cancelado y registrar quién lo canceló
        $cotizacion->update([
            'estado' => EstadoCotizacion::Cancelado,
            'deleted_by' => Auth::id(),
            'deleted_at' => now()
        ]);

        return Redirect::route('cotizaciones.index')
            ->with('success', 'Cotización cancelada exitosamente');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $cotizacion = Cotizacion::findOrFail($id);

        if (!in_array($cotizacion->estado, [EstadoCotizacion::Borrador, EstadoCotizacion::Pendiente, EstadoCotizacion::Aprobada], true)) {
            return Redirect::back()->with('error', 'Solo cotizaciones pendientes pueden ser eliminadas');
        }

        $cotizacion->items()->delete();
        $cotizacion->delete();

        return Redirect::route('cotizaciones.index')
            ->with('success', 'Cotización eliminada exitosamente');
    }

    /**
     * Convertir cotización a venta.
     * (Nota: la unificación completa con VentaItem/ventable_* la hacemos en el paso #8)
     */
    public function convertirAVenta($id)
    {
        $cotizacion = Cotizacion::with(['cliente', 'items.cotizable'])->findOrFail($id);

        if ($cotizacion->estado !== EstadoCotizacion::Aprobada) {
            return Redirect::back()->with('error', 'Solo cotizaciones aprobadas pueden convertirse a venta');
        }

        DB::beginTransaction();
        try {
            // Import ya declarado arriba: use App\Models\Venta;
            $venta = Venta::create([
                'cliente_id' => $cotizacion->cliente_id,
                'total' => $cotizacion->total,
            ]);

            foreach ($cotizacion->items as $item) {
                $class = $item->cotizable_type;
                $id = $item->cotizable_id;

                if ($class === Producto::class) {
                    $venta->productos()->attach($id, [
                        'cantidad' => $item->cantidad,
                        'precio' => $item->precio,
                    ]);
                    Producto::find($id)?->decrement('stock', $item->cantidad);
                } elseif ($class === Servicio::class) {
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


    /**
     * Duplicar una cotización.
     */
    public function duplicate(Request $request, $id)
    {
        $original = Cotizacion::with('cliente', 'items.cotizable')->findOrFail($id);

        try {
            return DB::transaction(function () use ($original) {
                // Replicar EXCLUYENDO campos problemáticos
                $nueva = $original->replicate([
                    'numero_cotizacion', // ← evita duplicar el mismo número
                    'created_at',
                    'updated_at',
                    'estado',
                ]);

                // Estado nuevo (borrador) y número único
                $nueva->estado = EstadoCotizacion::Borrador;
                $nueva->numero_cotizacion = $this->generarNumeroCotizacionUnico();
                $nueva->created_at = now();
                $nueva->updated_at = now();

                // Si usas descuento_items en el modelo, ya viene replicado.
                // Si no, podrías recalcularlo aquí si lo prefieres.

                $nueva->save();

                // Duplicar ítems (crea el FK cotizacion_id automáticamente)
                foreach ($original->items as $item) {
                    $nueva->items()->create([
                        'cotizable_id'    => $item->cotizable_id,
                        'cotizable_type'  => $item->cotizable_type,
                        'cantidad'        => $item->cantidad,
                        'precio'          => $item->precio,
                        'descuento'       => $item->descuento,
                        'subtotal'        => $item->subtotal,
                        'descuento_monto' => $item->descuento_monto,
                    ]);
                }

                return Redirect::route('cotizaciones.index')
                    ->with('success', 'Cotización duplicada correctamente.');
            });
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('Error duplicando cotización: ' . $e->getMessage(), ['id' => $id]);
            return Redirect::back()->with('error', 'Error al duplicar la cotización.');
        }
    }


    /**
     * Genera un numero_cotizacion único secuencial evitando colisiones.
     */
    private function generarNumeroCotizacionUnico(): string
    {
        // Buscar el número secuencial más alto existente
        $ultimoNumero = Cotizacion::where('numero_cotizacion', 'LIKE', 'COT-%')
            ->get()
            ->map(function ($cotizacion) {
                // Extraer el número secuencial del formato COT-XXXXX
                $matches = [];
                if (preg_match('/COT-(\d+)$/', $cotizacion->numero_cotizacion, $matches)) {
                    return (int) $matches[1];
                }
                return 0;
            })
            ->max() ?? 0;

        $seq = $ultimoNumero + 1;

        do {
            $numero = 'COT-' . str_pad($seq, 5, '0', STR_PAD_LEFT);
            $seq++;
        } while (Cotizacion::where('numero_cotizacion', $numero)->exists());

        return $numero;
    }


    /**
     * Enviar a Pedido.
     * (Nota: la unificación completa de pivots se atiende en el paso #8)
     */
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

            // Crear nuevo pedido confirmado
            $pedido = new Pedido();
            $pedido->fill([
                'cliente_id' => $cotizacion->cliente_id,
                'cotizacion_id' => $cotizacion->id,
                'numero_pedido' => $this->generarNumeroPedido(),
                'fecha' => now(),
                'estado' => EstadoPedido::Confirmado, // ← Cambiado a confirmado
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

            // Reservar inventario automáticamente (ya que está confirmado)
            foreach ($cotizacion->items as $item) {
                if ($item->cotizable_type === Producto::class) {
                    $producto = Producto::find($item->cotizable_id);
                    if ($producto) {
                        if ($producto->stock_disponible < $item->cantidad) {
                            DB::rollBack();
                            return response()->json([
                                'success' => false,
                                'error' => "Stock insuficiente para '{$producto->nombre}'. Disponible: {$producto->stock_disponible}, Solicitado: {$item->cantidad}",
                                'requiere_confirmacion' => false
                            ], 400);
                        }

                        $producto->increment('reservado', $item->cantidad);
                        Log::info("Inventario reservado automáticamente al enviar cotización a pedido", [
                            'producto_id' => $producto->id,
                            'pedido_id' => $pedido->id,
                            'cotizacion_id' => $cotizacion->id,
                            'cantidad_reservada' => $item->cantidad,
                            'reservado_actual' => $producto->reservado
                        ]);
                    }
                }
            }

            // Actualizar estado de la cotización
            $cotizacion->update(['estado' => EstadoCotizacion::EnviadoAPedido]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Pedido creado exitosamente',
                'pedido_id' => $pedido->id,
                'numero_pedido' => $pedido->numero_pedido,
                'items_count' => $pedido->items()->count()
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'error' => 'Error interno al procesar el pedido',
                'details' => app()->environment('local') ? $e->getMessage() : null
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
