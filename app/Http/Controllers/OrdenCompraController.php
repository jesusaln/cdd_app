<?php

namespace App\Http\Controllers;

use App\Models\OrdenCompra;
use App\Models\Proveedor;
use App\Models\Producto;
use App\Models\Compra; // <-- Importa el modelo Compra aquí
use App\Enums\EstadoCompra;
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
                $q->withPivot(['cantidad', 'precio', 'descuento', 'unidad_medida']);
            },
        ]);

        // Aplicar filtros
        if ($search = trim($request->get('search', ''))) {
            $baseQuery->where(function ($query) use ($search) {
                $query->where('numero_orden', 'like', "%{$search}%")
                      ->orWhere('observaciones', 'like', "%{$search}%")
                      ->orWhereHas('proveedor', function ($q) use ($search) {
                          $q->where('nombre_razon_social', 'like', "%{$search}%")
                            ->orWhere('rfc', 'like', "%{$search}%");
                      })
                      ->orWhereHas('productos', function ($q) use ($search) {
                          $q->where('nombre', 'like', "%{$search}%")
                            ->orWhere('descripcion', 'like', "%{$search}%");
                      });
            });
        }

        if ($request->filled('estado')) {
            $baseQuery->where('estado', $request->estado);
        }

        if ($request->filled('prioridad')) {
            $baseQuery->where('prioridad', $request->prioridad);
        }

        if ($request->filled('proveedor_id')) {
            $baseQuery->where('proveedor_id', $request->proveedor_id);
        }

        if ($request->filled('fecha_desde')) {
            $baseQuery->whereDate('fecha_orden', '>=', $request->fecha_desde);
        }

        if ($request->filled('fecha_hasta')) {
            $baseQuery->whereDate('fecha_orden', '<=', $request->fecha_hasta);
        }

        // Ordenamiento
        $sortBy = $request->get('sort_by', 'created_at');
        $sortDirection = $request->get('sort_direction', 'desc');

        $allowedSorts = ['created_at', 'numero_orden', 'total', 'estado', 'prioridad', 'fecha_orden'];
        if (!in_array($sortBy, $allowedSorts)) {
            $sortBy = 'created_at';
        }

        $baseQuery->orderBy($sortBy, $sortDirection === 'asc' ? 'asc' : 'desc');

        $total = (clone $baseQuery)->count();
        $ordenes = $baseQuery->forPage($page, $perPage)->get();

        $transformed = $ordenes->map(function ($orden) {
            $items = $orden->productos->map(function ($p) {
                return [
                    'id'           => $p->id,
                    'nombre'       => $p->nombre ?? $p->descripcion ?? '',
                    'cantidad'     => (int) ($p->pivot->cantidad ?? 0),
                    'precio'       => (float) ($p->pivot->precio ?? 0),
                    'descuento'    => (float) ($p->pivot->descuento ?? 0),
                    'unidad_medida' => $p->pivot->unidad_medida ?? '',
                ];
            });

            // Crear resumen de productos para tooltip
            $productosTooltip = $items->map(function ($item) {
                return $item['nombre'] . ' (' . $item['cantidad'] . ' ' . ($item['unidad_medida'] ?: 'u') . ')';
            })->join(', ');

            // Etiquetas legibles para estados (coincidiendo con BD)
            $estadoLabels = [
                'pendiente' => 'Pendiente',
                'aprobada' => 'Aprobada',
                'enviada' => 'Enviada',
                'recibida' => 'Recibida',
                'cancelada' => 'Cancelada',
                // Estados del sistema (por si acaso)
                'borrador' => 'Borrador',
                'enviado_a_compra' => 'Enviado a Compra',
                'convertida' => 'Recibida',
            ];

            return [
                'id'                 => $orden->id,
                'numero_orden'       => $orden->numero_orden ?? $orden->id,
                'proveedor'          => $orden->proveedor ? [
                    'id'                  => $orden->proveedor->id,
                    'nombre_razon_social' => $orden->proveedor->nombre_razon_social,
                    'rfc'                 => $orden->proveedor->rfc ?? null,
                ] : null,
                'items'              => $items,
                'productos_count'    => $items->count(),
                'productos_tooltip'  => $productosTooltip ?: 'Sin productos',
                'subtotal'           => (float) ($orden->subtotal ?? 0),
                'descuento_items'    => (float) ($orden->descuento_items ?? 0),
                'descuento_general'  => (float) ($orden->descuento_general ?? 0),
                'iva'                => (float) ($orden->iva ?? 0),
                'total'              => (float) ($orden->total ?? 0),
                'estado'             => $orden->estado ?? 'pendiente',
                'estado_label'       => $estadoLabels[$orden->estado] ?? 'Desconocido',
                'prioridad'          => $orden->prioridad ?? 'baja',
                'fecha_orden'        => $orden->fecha_orden?->format('d/m/Y'),
                'fecha_entrega_esperada' => $orden->fecha_entrega_esperada?->format('d/m/Y'),
                'created_at'         => optional($orden->created_at)->format('Y-m-d H:i:s'),
                'fecha'              => optional($orden->created_at)->format('Y-m-d'),
                // URLs para acciones
                'urls' => [
                    'show' => route('ordenescompra.show', $orden->id),
                    'edit' => route('ordenescompra.edit', $orden->id),
                    'duplicate' => route('ordenescompra.duplicate', $orden->id),
                    'enviar' => route('ordenescompra.enviar-compra', $orden->id),
                    'recibir' => route('ordenescompra.recibir-mercancia', $orden->id),
                    'cancelar' => route('ordenescompra.cancelar', $orden->id),
                    'delete' => route('ordenescompra.destroy', $orden->id),
                ],
                // Permisos de acciones
                'can' => [
                    'edit' => in_array($orden->estado, ['borrador', 'pendiente']),
                    'enviar' => $orden->estado === 'pendiente',
                    'recibir' => $orden->estado === 'enviado_a_compra',
                    'cancelar' => in_array($orden->estado, ['pendiente', 'enviado_a_compra', 'convertida']),
                    'delete' => in_array($orden->estado, ['borrador', 'pendiente']),
                ],
            ];
        });

        $paginator = new LengthAwarePaginator(
            $transformed,
            $total,
            $perPage,
            $page,
            ['path' => $request->url(), 'pageName' => 'page']
        );

        // Estadísticas para el dashboard (usando estados de BD)
        $stats = [
            'total' => OrdenCompra::count(),
            'pendientes' => OrdenCompra::where('estado', 'pendiente')->count(),
            'aprobadas' => OrdenCompra::where('estado', 'aprobada')->count(),
            'enviadas' => OrdenCompra::whereIn('estado', ['enviada', 'enviado_a_proveedor'])->count(),
            'recibidas' => OrdenCompra::where('estado', 'recibida')->count(),
            'canceladas' => OrdenCompra::where('estado', 'cancelada')->count(),
        ];

        // Etiquetas para estados (coincidiendo con BD)
        $estadoLabels = [
            'pendiente' => 'Pendiente',
            'aprobada' => 'Aprobada',
            'enviada' => 'Enviada',
            'recibida' => 'Recibida',
            'cancelada' => 'Cancelada',
            // Estados del sistema (por compatibilidad)
            'borrador' => 'Borrador',
            'enviado_a_proveedor' => 'Enviado a Proveedor',
            'convertida' => 'Recibida',
        ];

        // Opciones para filtros
        $proveedores = Proveedor::select('id', 'nombre_razon_social', 'rfc')
            ->orderBy('nombre_razon_social')
            ->get()
            ->mapWithKeys(function ($proveedor) {
                return [$proveedor->id => $proveedor->nombre_razon_social . ' (' . $proveedor->rfc . ')'];
            });

        $filterOptions = [
            'estados' => $estadoLabels,
            'prioridades' => [
                'baja' => 'Baja',
                'media' => 'Media',
                'alta' => 'Alta',
                'urgente' => 'Urgente',
            ],
            'proveedores' => $proveedores,
            'per_page_options' => [10, 15, 25, 50],
        ];

        return Inertia::render('OrdenesCompra/Index', [
            'ordenesCompra' => $paginator,
            'stats' => $stats,
            'estadoLabels' => $estadoLabels,
            'filterOptions' => $filterOptions,
            'filters' => $request->only(['search', 'estado', 'prioridad', 'proveedor_id', 'fecha_desde', 'fecha_hasta']),
            'sorting' => [
                'sort_by' => $sortBy,
                'sort_direction' => $sortDirection,
                'allowed_sorts' => $allowedSorts,
            ],
            'pagination' => [
                'current_page' => $paginator->currentPage(),
                'last_page' => $paginator->lastPage(),
                'per_page' => $perPage,
                'total' => $paginator->total(),
                'from' => $paginator->firstItem(),
                'to' => $paginator->lastItem(),
            ],
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
                'numero_orden' => 'nullable|string',
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
                    // Obtener la unidad de medida del producto
                    $producto = Producto::find($itemData['id']);
                    $unidadMedida = $producto ? $producto->unidad_medida : '';

                    $ordenCompra->productos()->attach($itemData['id'], [
                        'cantidad' => $itemData['cantidad'],
                        'precio' => $itemData['precio'],
                        'descuento' => $itemData['descuento'] ?? 0,
                        'unidad_medida' => $unidadMedida,
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
                        'unidad_medida' => $producto->pivot->unidad_medida ?? $producto->unidad_medida ?? '',
                    ],
                ];
            })->filter(fn($item) => $item !== null); // Elimina cualquier ítem nulo si hubo errores

            // Recalcular totales basados en los items actuales para asegurar consistencia
            $totalesCalculados = $this->calcularTotalesDesdeItems($items, $ordenCompra->descuento_general ?? 0);

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
                    'subtotal' => $totalesCalculados['subtotal'],
                    'descuento_items' => $totalesCalculados['descuento_items'],
                    'descuento_general' => $ordenCompra->descuento_general ?? 0,
                    'iva' => $totalesCalculados['iva'],
                    'total' => $totalesCalculados['total'],
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
                'numero_orden' => 'nullable|string',
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
                    // Obtener la unidad de medida del producto
                    $producto = Producto::find($itemData['id']);
                    $unidadMedida = $producto ? $producto->unidad_medida : '';

                    $ordenCompra->productos()->attach($itemData['id'], [
                        'cantidad' => $itemData['cantidad'],
                        'precio' => $itemData['precio'],
                        'descuento' => $itemData['descuento'] ?? 0,
                        'unidad_medida' => $unidadMedida,
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
     * Envía una orden de compra al proveedor (cambia estado a enviado_a_compra)
     * No actualiza inventario aún - eso se hace cuando se recibe la mercancía
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function enviarACompra(Request $request, $id)
    {
        DB::beginTransaction();
        try {
            $ordenCompra = OrdenCompra::findOrFail($id);

            // Solo se puede enviar si está pendiente
            if ($ordenCompra->estado !== 'pendiente') {
                return redirect()->back()->with('error', 'Solo se pueden enviar órdenes en estado pendiente.');
            }

            $ordenCompra->update([
                'estado' => 'enviado_a_proveedor',
                'observaciones' => ($ordenCompra->observaciones ? $ordenCompra->observaciones . "\n\n" : '') .
                    '*** ORDEN ENVIADA AL PROVEEDOR *** ' . now()->format('d/m/Y H:i')
            ]);

            DB::commit();

            return redirect()->back()->with('success', 'Orden de compra enviada al proveedor exitosamente.');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error al enviar orden a compra: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Error al enviar la orden de compra.');
        }
    }

    /**
     * Recibe la mercancía de una orden enviada (actualiza inventario y crea compra)
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function recibirMercancia(Request $request, $id)
    {
        DB::beginTransaction();
        try {
            $ordenCompra = OrdenCompra::with([
                'proveedor',
                'productos' => function ($q) {
                    $q->withPivot(['cantidad', 'precio', 'descuento']);
                }
            ])->findOrFail($id);

            // Solo se puede recibir si está enviada a proveedor
            if ($ordenCompra->estado !== 'enviado_a_proveedor') {
                return redirect()->back()->with('error', 'Solo se puede recibir mercancía de órdenes enviadas al proveedor.');
            }

            $productosParaCompra = [];

            foreach ($ordenCompra->productos as $producto) {
                // Validar que los datos esenciales del pivot estén presentes
                if (!$producto->pivot ||
                    !isset($producto->pivot->cantidad) ||
                    !isset($producto->pivot->precio) ||
                    $producto->pivot->cantidad <= 0 ||
                    $producto->pivot->precio <= 0) {
                    Log::error('Datos de pivot faltantes o inválidos para producto ID: ' . $producto->id . ' en orden ID: ' . $ordenCompra->id, [
                        'pivot_data' => $producto->pivot ? $producto->pivot->toArray() : null,
                        'cantidad' => $producto->pivot->cantidad ?? null,
                        'precio' => $producto->pivot->precio ?? null,
                        'unidad_medida' => $producto->pivot->unidad_medida ?? null
                    ]);
                    continue;
                }

                $prodModel = Producto::find($producto->id);
                if ($prodModel) {
                    $cantidadRecibida = (int) $producto->pivot->cantidad;
                    $precioUnitario = (float) $producto->pivot->precio;
                    // Obtener unidad de medida del pivot o del modelo producto
                    $unidadMedida = $producto->pivot->unidad_medida ?? $prodModel->unidad_medida ?? '';

                    // Actualizar el stock del producto
                    $prodModel->increment('stock', $cantidadRecibida);

                    $productosParaCompra[$producto->id] = [
                        'cantidad' => $cantidadRecibida,
                        'precio' => $precioUnitario,
                        'unidad_medida' => $unidadMedida,
                    ];
                } else {
                    Log::warning('Producto no encontrado para incrementar stock en orden de compra ID: ' . $ordenCompra->id . ', Producto ID: ' . $producto->id);
                }
            }

            // Crea la compra
            $compra = Compra::create([
                'proveedor_id' => $ordenCompra->proveedor_id,
                'total' => $ordenCompra->total,
                'estado' => EstadoCompra::Recibido,
            ]);

            // Crea los items de la compra
            if (!empty($productosParaCompra)) {
                foreach ($productosParaCompra as $productoId => $datos) {
                    // Obtener la unidad de medida del producto desde la orden de compra
                    $productoPivot = $ordenCompra->productos->find($productoId);
                    $unidadMedida = '';
                    $descuento = 0;

                    if ($productoPivot) {
                        $unidadMedida = $productoPivot->pivot->unidad_medida ?? '';
                        $descuento = $productoPivot->pivot->descuento ?? 0;
                    }

                    // Si no hay unidad de medida en el pivot, obtenerla del modelo producto
                    if (empty($unidadMedida)) {
                        $prodModel = Producto::find($productoId);
                        if ($prodModel) {
                            $unidadMedida = $prodModel->unidad_medida ?? '';
                        }
                    }

                    // Calcular subtotal considerando descuento
                    $subtotal = $datos['cantidad'] * $datos['precio'];
                    $descuentoMonto = ($subtotal * $descuento) / 100;
                    $subtotalFinal = $subtotal - $descuentoMonto;

                    \App\Models\CompraItem::create([
                        'compra_id' => $compra->id,
                        'comprable_id' => $productoId,
                        'comprable_type' => \App\Models\Producto::class,
                        'cantidad' => $datos['cantidad'],
                        'precio' => $datos['precio'],
                        'descuento' => $descuento,
                        'subtotal' => $subtotalFinal,
                        'descuento_monto' => $descuentoMonto,
                    ]);
                }
            }

            // Marcar la orden como convertida y agregar fecha de recepción
            $ordenCompra->update([
                'estado' => 'convertida',
                'fecha_recepcion' => now(),
                'observaciones' => ($ordenCompra->observaciones ? $ordenCompra->observaciones . "\n\n" : '') .
                    '*** MERCANCÍA RECIBIDA - COMPRA #' . $compra->id . ' *** ' . now()->format('d/m/Y H:i') .
                    ' - Stock actualizado automáticamente'
            ]);

            DB::commit();

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Mercancía recibida exitosamente. Compra #' . $compra->id . ' creada y stock actualizado.',
                    'compra_id' => $compra->id
                ]);
            }

            return redirect()->route('compras.index')
                ->with('success', 'Mercancía recibida exitosamente. Compra #' . $compra->id . ' creada y stock actualizado.');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error al recibir mercancía: ' . $e->getMessage());
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'error' => 'Error al procesar la recepción de mercancía: ' . $e->getMessage()
                ], 500);
            }
            return redirect()->back()->with('error', 'Error al procesar la recepción de mercancía.');
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
                    'unidad_medida' => $producto->pivot->unidad_medida ?? '',
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
     * Cancela una orden de compra y revierte el inventario si es necesario
     *
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function cancelar($id)
    {
        DB::beginTransaction();
        try {
            $ordenCompra = OrdenCompra::with('productos')->findOrFail($id);

            // Solo se puede cancelar si está en estado pendiente, enviada a proveedor o convertida
            if (!in_array($ordenCompra->estado, ['pendiente', 'enviado_a_proveedor', 'convertida'])) {
                return response()->json([
                    'success' => false,
                    'message' => 'Solo se pueden cancelar órdenes en estado pendiente, enviada a proveedor o procesada'
                ], 400);
            }

            // Si la orden fue convertida (mercancía recibida), necesitamos revertir el inventario
            if ($ordenCompra->estado === 'convertida') {
                foreach ($ordenCompra->productos as $producto) {
                    if ($producto->pivot && isset($producto->pivot->cantidad)) {
                        $cantidadARevertir = (int) $producto->pivot->cantidad;
                        $prodModel = Producto::find($producto->id);
                        if ($prodModel) {
                            // Decrementar el stock (revertir el incremento que se hizo al recibir mercancía)
                            $prodModel->decrement('stock', $cantidadARevertir);
                            Log::info("Stock revertido para producto ID {$producto->id}: -{$cantidadARevertir}");
                        }
                    }
                }
            }

            // Actualizar el estado de la orden a cancelada
            $ordenCompra->update([
                'estado' => 'cancelada',
                'observaciones' => ($ordenCompra->observaciones ? $ordenCompra->observaciones . "\n\n" : '') .
                    '*** ORDEN CANCELADA *** ' . now()->format('d/m/Y H:i') .
                    ' - Inventario revertido automáticamente'
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Orden de compra cancelada exitosamente',
                'estado' => $ordenCompra->estado
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error al cancelar orden de compra: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error al cancelar la orden de compra'
            ], 500);
        }
    }

    /**
     * Cambia el estado de una orden de compra
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function cambiarEstado(Request $request, $id)
    {
        try {
            $ordenCompra = OrdenCompra::findOrFail($id);

            $request->validate([
                'estado' => 'required|in:pendiente,enviado_a_compra,convertida,cancelada,borrador'
            ]);

            $estadoAnterior = $ordenCompra->estado;
            $nuevoEstado = $request->estado;

            // Validar transiciones de estado permitidas
            $transicionesPermitidas = [
                'borrador' => ['pendiente', 'cancelada'],
                'pendiente' => ['enviado_a_proveedor', 'cancelada'], // Solo puede enviarse al proveedor o cancelarse
                'enviado_a_proveedor' => ['convertida', 'cancelada'], // Puede recibir mercancía o cancelarse
                'convertida' => ['cancelada'], // Solo se puede cancelar una vez procesada (revertirá inventario)
                'cancelada' => [] // Estado final
            ];

            if (!in_array($nuevoEstado, $transicionesPermitidas[$estadoAnterior] ?? [])) {
                return response()->json([
                    'success' => false,
                    'message' => "No se puede cambiar de estado '{$estadoAnterior}' a '{$nuevoEstado}'"
                ], 400);
            }

            $ordenCompra->update([
                'estado' => $nuevoEstado,
                'observaciones' => ($ordenCompra->observaciones ? $ordenCompra->observaciones . "\n\n" : '') .
                    "*** CAMBIO DE ESTADO *** {$estadoAnterior} → {$nuevoEstado} - " . now()->format('d/m/Y H:i')
            ]);

            return response()->json([
                'success' => true,
                'message' => "Estado cambiado de '{$estadoAnterior}' a '{$nuevoEstado}'",
                'estado' => $ordenCompra->estado
            ]);

        } catch (\Exception $e) {
            Log::error('Error al cambiar estado de orden de compra: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error al cambiar el estado de la orden'
            ], 500);
        }
    }

    /**
     * Calcula los totales basados en los items actuales
     *
     * @param \Illuminate\Support\Collection $items
     * @param float $descuentoGeneral
     * @return array
     */
    private function calcularTotalesDesdeItems($items, $descuentoGeneral = 0)
    {
        $subtotal = 0;
        $descuentoItems = 0;

        foreach ($items as $item) {
            if (!isset($item['pivot'])) continue;

            $cantidad = (float) ($item['pivot']['cantidad'] ?? 0);
            $precio = (float) ($item['pivot']['precio'] ?? 0);
            $descuento = (float) ($item['pivot']['descuento'] ?? 0);

            $subtotalItem = $cantidad * $precio;
            $descuentoItem = ($subtotalItem * $descuento) / 100;

            $subtotal += $subtotalItem;
            $descuentoItems += $descuentoItem;
        }

        // Aplicar descuento general
        $subtotalDespuesDescuentoGeneral = $subtotal - $descuentoItems - $descuentoGeneral;

        // Calcular IVA (16%)
        $iva = $subtotalDespuesDescuentoGeneral * 0.16;

        // Total final
        $total = $subtotalDespuesDescuentoGeneral + $iva;

        return [
            'subtotal' => round($subtotal, 2),
            'descuento_items' => round($descuentoItems, 2),
            'iva' => round($iva, 2),
            'total' => round($total, 2),
        ];
    }
}
