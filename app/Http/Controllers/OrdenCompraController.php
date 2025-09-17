<?php

namespace App\Http\Controllers;

use App\Models\OrdenCompra;
use App\Models\Proveedor;
use App\Models\Producto;
use App\Models\Servicio;
use App\Models\Compra; // <-- Importa el modelo Compra aquí
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB; // Para transacciones de base de datos
use Illuminate\Pagination\LengthAwarePaginator;

class OrdenCompraController extends Controller
{
    /**
     * Muestra una lista de las órdenes de compra.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $perPage = (int) ($request->integer('per_page') ?: 10);
        $page    = max(1, (int) $request->get('page', 1));

        $baseQuery = OrdenCompra::with([
            'proveedor',
            'productos' => function ($q) {
                $q->withPivot(['cantidad', 'precio', 'descuento']);
            },
        ])->orderByDesc('created_at');

        $total = (clone $baseQuery)->count();
        $ordenes = $baseQuery->forPage($page, $perPage)->get();

        $transformed = $ordenes->map(function ($orden) {
            $items = $orden->productos->map(function ($p) {
                return [
                    'id'        => $p->id,
                    'nombre'    => $p->nombre ?? $p->descripcion ?? '',
                    'cantidad'  => (int) ($p->pivot->cantidad ?? 0),
                    'precio'    => (float) ($p->pivot->precio ?? 0),
                    'descuento' => (float) ($p->pivot->descuento ?? 0),
                ];
            });

            return [
                'id'                 => $orden->id,
                'numero_orden'       => $orden->numero_orden ?? $orden->id,
                'proveedor'          => $orden->proveedor ? [
                    'id'                  => $orden->proveedor->id,
                    'nombre_razon_social' => $orden->proveedor->nombre_razon_social,
                    'rfc'                 => $orden->proveedor->rfc ?? null,
                ] : null,
                'items'              => $items,
                'subtotal'           => (float) ($orden->subtotal ?? 0),
                'descuento_items'    => (float) ($orden->descuento_items ?? 0),
                'descuento_general'  => (float) ($orden->descuento_general ?? 0),
                'iva'                => (float) ($orden->iva ?? 0),
                'total'              => (float) ($orden->total ?? 0),
                'estado'             => $orden->estado ?? 'pendiente',
                'created_at'         => optional($orden->created_at)->format('Y-m-d H:i:s'),
                'fecha'              => optional($orden->created_at)->format('Y-m-d'),
            ];
        });

        $paginator = new LengthAwarePaginator(
            $transformed,
            $total,
            $perPage,
            $page,
            ['path' => $request->url(), 'pageName' => 'page']
        );

        // Estadísticas para el dashboard
        $stats = [
            'total' => OrdenCompra::count(),
            'aprobadas' => OrdenCompra::where('estado', 'recibida')->count(),
            'pendientes' => OrdenCompra::where('estado', 'pendiente')->count(),
            'borrador' => OrdenCompra::where('estado', 'borrador')->count(),
            'cancelada' => OrdenCompra::where('estado', 'cancelada')->count(),
        ];

        return Inertia::render('OrdenesCompra/Index', [
            'ordenesCompra' => $paginator,
            'stats' => $stats,
            'filters' => $request->only(['search', 'estado']),
            'sorting' => ['sort_by' => 'created_at', 'sort_direction' => 'desc'],
        ]);
    }


    /**
     * Muestra el formulario para crear una nueva orden de compra.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        // Obtiene todos los proveedores y productos para los selectores/búsquedas en el frontend
        $proveedores = Proveedor::all();
        $productos = Producto::all();

        // Renderiza la vista de creación de órdenes de compra con Inertia
        return Inertia::render('OrdenesCompra/Create', [
            'proveedores' => $proveedores,
            'productos' => $productos,
        ]);
    }

    /**
     * Almacena una orden de compra recién creada en la base de datos.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        // Inicia una transacción de base de datos para asegurar la integridad
        DB::beginTransaction();
        try {
            // Valida los datos de entrada del formulario
            $validatedData = $request->validate([
                'numero_orden' => 'required|string|unique:orden_compras,numero_orden',
                'fecha_orden' => 'required|date',
                'fecha_entrega_esperada' => 'nullable|date',
                'prioridad' => 'required|in:baja,media,alta,urgente',
                'proveedor_id' => 'required|exists:proveedores,id',
                'direccion_entrega' => 'nullable|string',
                'terminos_pago' => 'required|in:contado,15_dias,30_dias,45_dias,60_dias,90_dias',
                'metodo_pago' => 'required|in:transferencia,cheque,efectivo,tarjeta',
                'subtotal' => 'required|numeric|min:0',
                'descuento_items' => 'required|numeric|min:0',
                'descuento_general' => 'required|numeric|min:0',
                'iva' => 'required|numeric|min:0',
                'total' => 'required|numeric|min:0',
                'observaciones' => 'nullable|string',
                'items' => 'required|array',
                'items.*.id' => 'required|integer',
                'items.*.tipo' => 'required|in:producto',
                'items.*.cantidad' => 'required|integer|min:1',
                'items.*.precio' => 'required|numeric|min:0',
                'items.*.descuento' => 'required|numeric|min:0',
            ]);

            // Crea la nueva orden de compra en la tabla 'orden_compras'
            $ordenCompra = OrdenCompra::create([
                'numero_orden' => $validatedData['numero_orden'],
                'fecha_orden' => $validatedData['fecha_orden'],
                'fecha_entrega_esperada' => $validatedData['fecha_entrega_esperada'],
                'prioridad' => $validatedData['prioridad'],
                'proveedor_id' => $validatedData['proveedor_id'],
                'direccion_entrega' => $validatedData['direccion_entrega'],
                'terminos_pago' => $validatedData['terminos_pago'],
                'metodo_pago' => $validatedData['metodo_pago'],
                'subtotal' => $validatedData['subtotal'],
                'descuento_items' => $validatedData['descuento_items'],
                'descuento_general' => $validatedData['descuento_general'],
                'iva' => $validatedData['iva'],
                'total' => $validatedData['total'],
                'observaciones' => $validatedData['observaciones'],
                'estado' => 'pendiente',
            ]);

            // Asocia los productos a la orden de compra a través de la tabla pivote
            foreach ($validatedData['items'] as $itemData) {
                if ($itemData['tipo'] === 'producto') {
                    $ordenCompra->productos()->attach($itemData['id'], [
                        'cantidad' => $itemData['cantidad'],
                        'precio' => $itemData['precio'],
                        'descuento' => $itemData['descuento'] ?? 0,
                    ]);
                }
                // Nota: Solo se permiten productos, no servicios
            }

            // Confirma la transacción si todo fue exitoso
            DB::commit();

            // Redirige al índice de órdenes de compra con un mensaje de éxito
            return redirect()->route('ordenescompra.index')->with('success', 'Orden de compra creada exitosamente.');
        } catch (\Illuminate\Validation\ValidationException $e) {
            // Si hay errores de validación, se revierte la transacción y se redirige con los errores
            DB::rollBack();
            Log::error('Error de validación al crear orden de compra: ' . $e->getMessage(), $e->errors());
            return redirect()->back()->withErrors($e->errors())->withInput();
        } catch (\Exception $e) {
            // Si ocurre cualquier otro error, se revierte la transacción y se registra el error
            DB::rollBack();
            Log::error('Error al crear la orden de compra: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Ocurrió un error al crear la orden de compra. Por favor, inténtalo de nuevo.');
        }
    }

    /**
     * Muestra los detalles de una orden de compra específica.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        // Busca la orden de compra y carga sus relaciones
        $ordenCompra = OrdenCompra::with(['proveedor', 'productos'])->findOrFail($id);

        // Mapea productos (solo productos, sin servicios)
        $items = $ordenCompra->productos->map(function ($producto) {
            return [
                'id' => $producto->id,
                'nombre' => $producto->nombre,
                'tipo' => 'producto',
                'pivot' => [
                    'cantidad' => $producto->pivot->cantidad,
                    'precio' => $producto->pivot->precio,
                    'descuento' => $producto->pivot->descuento ?? 0,
                ],
            ];
        });

        // Renderiza la vista de detalles de la orden de compra
        return Inertia::render('OrdenesCompra/Show', [
            'ordenCompra' => [
                'id' => $ordenCompra->id,
                'numero_orden' => $ordenCompra->numero_orden,
                'fecha_orden' => $ordenCompra->fecha_orden?->format('d/m/Y'),
                'fecha_entrega_esperada' => $ordenCompra->fecha_entrega_esperada?->format('d/m/Y'),
                'prioridad' => $ordenCompra->prioridad,
                'proveedor' => $ordenCompra->proveedor,
                'direccion_entrega' => $ordenCompra->direccion_entrega,
                'terminos_pago' => $ordenCompra->terminos_pago,
                'metodo_pago' => $ordenCompra->metodo_pago,
                'subtotal' => $ordenCompra->subtotal,
                'descuento_items' => $ordenCompra->descuento_items,
                'descuento_general' => $ordenCompra->descuento_general,
                'iva' => $ordenCompra->iva,
                'total' => $ordenCompra->total,
                'observaciones' => $ordenCompra->observaciones,
                'estado' => $ordenCompra->estado,
                'items' => $items,
                'created_at' => $ordenCompra->created_at->format('d/m/Y H:i'),
            ],
        ]);
    }

    /**
     * Muestra el formulario para editar una orden de compra existente.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        try {
            // Busca la orden de compra con sus relaciones
            $ordenCompra = OrdenCompra::with(['proveedor', 'productos'])->findOrFail($id);

            // Mapea productos, añadiendo manejo de errores para pivots
            $items = $ordenCompra->productos->map(function ($producto) {
                if (!$producto instanceof \App\Models\Producto || !$producto->pivot) {
                    Log::error('Producto inválido o pivot faltante para el producto ID: ' . ($producto->id ?? 'null'));
                    return null;
                }
                return [
                    'id' => $producto->id,
                    'nombre' => $producto->nombre,
                    'descripcion' => $producto->descripcion,
                    'precio' => $producto->precio_venta ?? $producto->precio ?? 0,
                    'precio_compra' => $producto->precio_compra ?? 0,
                    'tipo' => 'producto',
                    'pivot' => [
                        'cantidad' => $producto->pivot->cantidad,
                        'precio' => $producto->pivot->precio,
                        'descuento' => $producto->pivot->descuento ?? 0,
                    ],
                ];
            })->filter(fn($item) => $item !== null); // Elimina cualquier ítem nulo si hubo errores

            // Obtiene todos los proveedores y productos para los selectores/búsquedas en el frontend
            $proveedores = Proveedor::all();
            $productos = Producto::all();

            // Renderiza la vista de edición de órdenes de compra
            return Inertia::render('OrdenesCompra/Edit', [
                'ordenCompra' => [
                    'id' => $ordenCompra->id,
                    'numero_orden' => $ordenCompra->numero_orden,
                    'fecha_orden' => $ordenCompra->fecha_orden?->format('Y-m-d'),
                    'fecha_entrega_esperada' => $ordenCompra->fecha_entrega_esperada?->format('Y-m-d'),
                    'prioridad' => $ordenCompra->prioridad,
                    'proveedor_id' => $ordenCompra->proveedor_id,
                    'proveedor' => $ordenCompra->proveedor,
                    'direccion_entrega' => $ordenCompra->direccion_entrega,
                    'terminos_pago' => $ordenCompra->terminos_pago,
                    'metodo_pago' => $ordenCompra->metodo_pago,
                    'subtotal' => $ordenCompra->subtotal,
                    'descuento_items' => $ordenCompra->descuento_items,
                    'descuento_general' => $ordenCompra->descuento_general,
                    'iva' => $ordenCompra->iva,
                    'total' => $ordenCompra->total,
                    'observaciones' => $ordenCompra->observaciones,
                    'estado' => $ordenCompra->estado,
                    'items' => $items,
                ],
                'proveedores' => $proveedores,
                'productos' => $productos,
            ]);
        } catch (\Exception $e) {
            Log::error('Error en OrdenCompraController@edit: ' . $e->getMessage());
            // Puedes redirigir a una página de error o mostrar un mensaje al usuario
            return redirect()->route('ordenescompra.index')->with('error', 'No se pudo cargar la orden de compra para edición.');
        }
    }

    /**
     * Actualiza la orden de compra especificada en la base de datos.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        // Inicia una transacción
        DB::beginTransaction();
        try {
            // Busca la orden de compra
            $ordenCompra = OrdenCompra::findOrFail($id);

            // Valida los datos de entrada
            $validatedData = $request->validate([
                'numero_orden' => 'required|string|unique:orden_compras,numero_orden,' . $id,
                'fecha_orden' => 'required|date',
                'fecha_entrega_esperada' => 'nullable|date',
                'prioridad' => 'required|in:baja,media,alta,urgente',
                'proveedor_id' => 'required|exists:proveedores,id',
                'direccion_entrega' => 'nullable|string',
                'terminos_pago' => 'required|in:contado,15_dias,30_dias,45_dias,60_dias,90_dias',
                'metodo_pago' => 'required|in:transferencia,cheque,efectivo,tarjeta',
                'subtotal' => 'required|numeric|min:0',
                'descuento_items' => 'required|numeric|min:0',
                'descuento_general' => 'required|numeric|min:0',
                'iva' => 'required|numeric|min:0',
                'total' => 'required|numeric|min:0',
                'observaciones' => 'nullable|string',
                'items' => 'required|array',
                'items.*.id' => 'required|integer',
                'items.*.tipo' => 'required|in:producto',
                'items.*.cantidad' => 'required|integer|min:1',
                'items.*.precio' => 'required|numeric|min:0',
                'items.*.descuento' => 'required|numeric|min:0',
            ]);

            // Actualiza todos los campos de la orden de compra
            $ordenCompra->update([
                'numero_orden' => $validatedData['numero_orden'],
                'fecha_orden' => $validatedData['fecha_orden'],
                'fecha_entrega_esperada' => $validatedData['fecha_entrega_esperada'],
                'prioridad' => $validatedData['prioridad'],
                'proveedor_id' => $validatedData['proveedor_id'],
                'direccion_entrega' => $validatedData['direccion_entrega'],
                'terminos_pago' => $validatedData['terminos_pago'],
                'metodo_pago' => $validatedData['metodo_pago'],
                'subtotal' => $validatedData['subtotal'],
                'descuento_items' => $validatedData['descuento_items'],
                'descuento_general' => $validatedData['descuento_general'],
                'iva' => $validatedData['iva'],
                'total' => $validatedData['total'],
                'observaciones' => $validatedData['observaciones'],
            ]);

            // Sincroniza los productos adjuntos.
            // Primero, desasocia todos los productos actuales para luego adjuntar los nuevos.
            $ordenCompra->productos()->detach();

            foreach ($validatedData['items'] as $itemData) {
                if ($itemData['tipo'] === 'producto') {
                    $ordenCompra->productos()->attach($itemData['id'], [
                        'cantidad' => $itemData['cantidad'],
                        'precio' => $itemData['precio'],
                        'descuento' => $itemData['descuento'] ?? 0,
                    ]);
                }
                // Nota: Solo se permiten productos, no servicios
            }

            // Confirma la transacción
            DB::commit();

            // Redirige con mensaje de éxito
            return redirect()->route('ordenescompra.index')->with('success', 'Orden de compra actualizada exitosamente.');
        } catch (\Illuminate\Validation\ValidationException $e) {
            DB::rollBack();
            Log::error('Error de validación al actualizar orden de compra: ' . $e->getMessage(), $e->errors());
            return redirect()->back()->withErrors($e->errors())->withInput();
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error al actualizar la orden de compra: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Ocurrió un error al actualizar la orden de compra. Por favor, inténtalo de nuevo.');
        }
    }

    /**
     * Elimina la orden de compra especificada de la base de datos.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        // Inicia una transacción
        DB::beginTransaction();
        try {
            $ordenCompra = OrdenCompra::findOrFail($id);
            // Desasocia los productos antes de eliminar la orden
            $ordenCompra->productos()->detach();
            $ordenCompra->delete();

            // Confirma la transacción
            DB::commit();
            return redirect()->route('ordenescompra.index')->with('success', 'Orden de compra eliminada exitosamente.');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error al eliminar la orden de compra: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Ocurrió un error al eliminar la orden de compra. Por favor, inténtalo de nuevo.');
        }
    }

    /**
     * Marca una orden de compra como recibida, actualiza el stock y crea un registro de compra.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function recibirOrden(Request $request, $id)
    {
        // Inicia una transacción para asegurar que el stock y la creación de la compra se actualicen correctamente
        DB::beginTransaction();
        try {
            // Carga la orden de compra con sus productos para acceder a la información necesaria
            $ordenCompra = OrdenCompra::with('proveedor', 'productos')->findOrFail($id);

            // Solo procesar si la orden está pendiente para evitar duplicados o errores lógicos
            if ($ordenCompra->estado !== 'pendiente') {
                DB::rollBack();
                return redirect()->back()->with('error', 'La orden de compra ya ha sido procesada o no está en estado pendiente.');
            }

            // Prepara los datos para la creación de la Compra y la actualización del stock
            $productosParaCompra = []; // Para adjuntar a la nueva Compra

            foreach ($ordenCompra->productos as $producto) {
                // Verifica que el pivot existe y tiene los datos necesarios
                if (!$producto->pivot || !isset($producto->pivot->cantidad) || !isset($producto->pivot->precio)) {
                    Log::error('Datos de pivot faltantes para producto ID: ' . $producto->id . ' en orden ID: ' . $ordenCompra->id);
                    continue;
                }

                $prodModel = Producto::find($producto->id);
                if ($prodModel) {
                    // Incrementa el stock del producto con la cantidad recibida
                    $cantidadRecibida = (int) $producto->pivot->cantidad;
                    $precioUnitario = (float) $producto->pivot->precio;

                    $prodModel->increment('stock', $cantidadRecibida);

                    // Prepara los datos para la tabla pivote de la nueva Compra
                    $productosParaCompra[$producto->id] = [
                        'cantidad' => $cantidadRecibida,
                        'precio' => $precioUnitario,
                    ];

                    Log::info("Stock actualizado para producto ID {$producto->id}: +{$cantidadRecibida} unidades");
                } else {
                    Log::warning('Producto no encontrado para incrementar stock en orden de compra ID: ' . $ordenCompra->id . ', Producto ID: ' . $producto->id);
                }
            }

            // Crea un nuevo registro en la tabla `compras`
            $compra = Compra::create([
                'proveedor_id' => $ordenCompra->proveedor_id,
                'total' => $ordenCompra->total,
                'fecha_compra' => now(),
            ]);

            // Adjunta los productos a la Compra recién creada a través de la tabla pivote `compra_producto`
            if (!empty($productosParaCompra)) {
                $compra->productos()->attach($productosParaCompra);
                Log::info("Compra creada con ID {$compra->id} y productos adjuntados");
            }

            // Actualiza el estado de la OrdenCompra a "recibida"
            $ordenCompra->update([
                'estado' => 'recibida',
                'fecha_recepcion' => now()
            ]);

            Log::info("Orden de compra ID {$ordenCompra->id} marcada como recibida");

            // Confirma todas las operaciones de la transacción si no hubo errores
            DB::commit();

            // Retorna una respuesta JSON si es una petición AJAX, o redirección normal
            if ($request->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Orden de compra recibida exitosamente',
                    'orden' => [
                        'id' => $ordenCompra->id,
                        'estado' => $ordenCompra->estado,
                        'fecha_recepcion' => $ordenCompra->fecha_recepcion->format('d/m/Y H:i')
                    ]
                ]);
            }

            return redirect()->route('ordenescompra.index')
                ->with('success', 'Orden de compra marcada como recibida, stock actualizado y registro de compra creado exitosamente.');
        } catch (\Exception $e) {
            // Si ocurre algún error, se revierte toda la transacción para mantener la integridad de los datos
            DB::rollBack();
            Log::error('Error al recibir la orden de compra: ' . $e->getMessage());
            Log::error('Stack trace: ' . $e->getTraceAsString());

            if ($request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Error al procesar la orden de compra'
                ], 500);
            }

            return redirect()->back()
                ->with('error', 'Ocurrió un error al procesar la recepción de la orden de compra. Por favor, inténtalo de nuevo.');
        }
    }

    /**
     * Obtiene el estado actual de una orden de compra (útil para AJAX)
     *
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function getEstado($id)
    {
        try {
            $ordenCompra = OrdenCompra::findOrFail($id);

            return response()->json([
                'success' => true,
                'estado' => $ordenCompra->estado,
                'fecha_recepcion' => $ordenCompra->fecha_recepcion ? $ordenCompra->fecha_recepcion->format('d/m/Y H:i') : null
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener el estado de la orden'
            ], 404);
        }
    }

    /**
     * Duplica una orden de compra
     *
     * @param  int  $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function duplicate($id)
    {
        try {
            $ordenOriginal = OrdenCompra::with('productos')->findOrFail($id);

            DB::beginTransaction();

            // Crear la nueva orden duplicada
            $ordenDuplicada = OrdenCompra::create([
                'numero_orden' => 'DUP-' . $ordenOriginal->numero_orden . '-' . now()->format('His'),
                'fecha_orden' => now(),
                'fecha_entrega_esperada' => $ordenOriginal->fecha_entrega_esperada,
                'prioridad' => $ordenOriginal->prioridad,
                'proveedor_id' => $ordenOriginal->proveedor_id,
                'direccion_entrega' => $ordenOriginal->direccion_entrega,
                'terminos_pago' => $ordenOriginal->terminos_pago,
                'metodo_pago' => $ordenOriginal->metodo_pago,
                'subtotal' => $ordenOriginal->subtotal,
                'descuento_items' => $ordenOriginal->descuento_items,
                'descuento_general' => $ordenOriginal->descuento_general,
                'iva' => $ordenOriginal->iva,
                'total' => $ordenOriginal->total,
                'observaciones' => $ordenOriginal->observaciones,
                'estado' => 'borrador',
            ]);

            // Duplicar los productos
            foreach ($ordenOriginal->productos as $producto) {
                $ordenDuplicada->productos()->attach($producto->id, [
                    'cantidad' => $producto->pivot->cantidad,
                    'precio' => $producto->pivot->precio,
                    'descuento' => $producto->pivot->descuento ?? 0,
                ]);
            }

            DB::commit();

            return redirect()->route('ordenescompra.edit', $ordenDuplicada->id)
                ->with('success', 'Orden de compra duplicada exitosamente. Revisa y ajusta los datos antes de guardar.');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error al duplicar orden de compra: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Error al duplicar la orden de compra.');
        }
    }

    /**
     * Marca una orden como urgente
     *
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function marcarUrgente($id)
    {
        try {
            $ordenCompra = OrdenCompra::findOrFail($id);

            $ordenCompra->update([
                'prioridad' => 'urgente',
                'observaciones' => ($ordenCompra->observaciones ? $ordenCompra->observaciones . "\n\n" : '') .
                                  '*** ORDEN MARCADA COMO URGENTE *** ' . now()->format('d/m/Y H:i')
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Orden marcada como urgente',
                'prioridad' => $ordenCompra->prioridad
            ]);

        } catch (\Exception $e) {
            Log::error('Error al marcar orden como urgente: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error al marcar la orden como urgente'
            ], 500);
        }
    }

    /**
     * Convierte una orden de compra en una compra (simula el proceso)
     *
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function convertirACompra($id)
    {
        try {
            $ordenCompra = OrdenCompra::with('productos')->findOrFail($id);

            // Solo procesar si la orden está pendiente
            if ($ordenCompra->estado !== 'pendiente') {
                return response()->json([
                    'success' => false,
                    'message' => 'Solo se pueden convertir órdenes en estado pendiente'
                ], 400);
            }

            DB::beginTransaction();

            // Crear una compra simulada
            $compra = Compra::create([
                'proveedor_id' => $ordenCompra->proveedor_id,
                'total' => $ordenCompra->total,
                'fecha_compra' => now(),
            ]);

            // Adjuntar productos a la compra
            $productosParaCompra = [];
            foreach ($ordenCompra->productos as $producto) {
                $productosParaCompra[$producto->id] = [
                    'cantidad' => $producto->pivot->cantidad,
                    'precio' => $producto->pivot->precio,
                ];
            }

            if (!empty($productosParaCompra)) {
                $compra->productos()->attach($productosParaCompra);
            }

            // Marcar la orden como convertida
            $ordenCompra->update([
                'estado' => 'convertida',
                'observaciones' => ($ordenCompra->observaciones ? $ordenCompra->observaciones . "\n\n" : '') .
                                  '*** CONVERTIDA A COMPRA #' . $compra->id . ' *** ' . now()->format('d/m/Y H:i')
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Orden convertida a compra exitosamente',
                'compra_id' => $compra->id,
                'estado' => $ordenCompra->estado
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error al convertir orden a compra: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error al convertir la orden a compra'
            ], 500);
        }
    }
}
