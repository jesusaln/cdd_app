<?php

namespace App\Http\Controllers;

use App\Enums\EstadoCompra;
use App\Models\Compra; // <-- Importa el modelo Compra aquí
use App\Models\CuentasPorPagar;
use App\Models\OrdenCompra;
use App\Models\Producto;
use App\Models\ProductoPrecioHistorial;
use App\Models\Proveedor;
use App\Services\InventarioService;
use App\Mail\OrdenCompraEnviada;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB; // Para transacciones de base de datos
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Auth;
use Illuminate\Pagination\LengthAwarePaginator;
use Barryvdh\DomPDF\Facade\Pdf;

class OrdenCompraController extends Controller
{
    public function __construct(private readonly InventarioService $inventarioService)
    {
    }

    /**
     * Muestra una lista de las órdenes de compra.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $perPage = (int) ($request->integer('per_page') ?: 10);

        $baseQuery = OrdenCompra::with([
            'proveedor' => function ($query) {
                $query->select('id', 'nombre_razon_social', 'email', 'rfc', 'telefono');
            },
            'productos',
            'almacen',
            'emailEnviadoPor' => function ($query) {
                $query->select('id', 'name');
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

        $baseQuery->orderBy($sortBy, $sortDirection === 'asc' ? 'asc' : 'desc')
            ->orderBy('id', 'desc');

        $ordenes = $baseQuery->paginate($perPage)->appends($request->query());

        // Transformar la colección del paginator
        $collection = collect($ordenes->items())->map(function ($orden) {
            // Asegurarse de trabajar con colecciones para productos
            $orden->productos = collect($orden->productos)->map(function ($p) {
                return array_merge($p->toArray(), [
                    'cantidad' => $p->pivot->cantidad ?? 0,
                    'precio' => $p->pivot->precio ?? 0,
                    'descuento' => $p->pivot->descuento ?? 0,
                    'unidad_medida' => $p->pivot->unidad_medida ?? '',
                ]);
            });
            $items = $orden->productos;

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
                    'email'               => $orden->proveedor->email ?? null,
                ] : null,
                'almacen'            => $orden->almacen ? [
                    'id'   => $orden->almacen->id,
                    'nombre' => $orden->almacen->nombre,
                ] : null,
                'productos'           => $items,
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
                // Información de email
                'email_enviado'      => (bool) ($orden->email_enviado ?? false),
                'email_enviado_fecha' => $orden->email_enviado_fecha?->format('d/m/Y H:i'),
                'email_enviado_por'  => $orden->emailEnviadoPor?->name,
                // URLs para acciones
                'urls' => [
                    'show' => route('ordenescompra.show', $orden->id),
                    'edit' => route('ordenescompra.edit', $orden->id),
                    'duplicate' => route('ordenescompra.duplicate', $orden->id),
                    'enviar' => route('ordenescompra.enviar-compra', $orden->id),
                    'convertir_directo' => route('ordenescompra.convertir-directo', $orden->id),
                    'recibir' => route('ordenescompra.recibir-mercancia', $orden->id),
                    'cancelar' => route('ordenescompra.cancelar', $orden->id),
                    'delete' => route('ordenescompra.destroy', $orden->id),
                ],
                // Permisos de acciones
                'can' => [
                    'edit' => in_array($orden->estado, ['borrador', 'pendiente']),
                    'enviar' => $orden->estado === 'pendiente',
                    'convertir_directo' => $orden->estado === 'pendiente',
                    'recibir' => $orden->estado === 'enviado_a_compra',
                    'cancelar' => in_array($orden->estado, ['pendiente', 'enviado_a_compra', 'convertida']),
                    'delete' => in_array($orden->estado, ['borrador', 'pendiente']),
                ],
            ];
        });

        // Reemplaza la colección interna del paginador por la colección transformada
        // Algunos analizadores o versiones no reconocen setCollection; en su lugar creamos
        // un nuevo LengthAwarePaginator con la colección transformada y conservamos metadata.
        $ordenes = new LengthAwarePaginator(
            $collection,
            $ordenes->total(),
            $ordenes->perPage(),
            $ordenes->currentPage(),
            [
                'path' => $request->url(),
                'query' => $request->query(),
            ]
        );

        // Estadísticas para el dashboard (usando estados de BD)
        $stats = [
            'total' => OrdenCompra::count(),
            'pendientes' => OrdenCompra::where('estado', 'pendiente')->count(),
            'procesadas' => OrdenCompra::where('estado', 'convertida')->count(),
            'enviadas_a_proveedor' => OrdenCompra::where('estado', 'enviado_a_proveedor')->count(),
            'recibidas' => OrdenCompra::where('estado', 'recibida')->count(),
            'canceladas' => OrdenCompra::where('estado', 'cancelada')->count(),
        ];

        // Etiquetas para estados (coincidiendo con BD)
        $estadoLabels = [
            'pendiente' => 'Pendiente',
            'procesada' => 'Procesada',
            'enviada' => 'Enviada',
            'enviado_a_proveedor' => 'Enviado a Proveedor',
            'recibida' => 'Recibida',
            'cancelada' => 'Cancelada',
            // Estados del sistema (por compatibilidad)
            'borrador' => 'Borrador',
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
            'ordenesCompra' => $ordenes,
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
                'current_page' => $ordenes->currentPage(),
                'last_page' => $ordenes->lastPage(),
                'per_page' => $perPage,
                'total' => $ordenes->total(),
                'from' => $ordenes->firstItem(),
                'to' => $ordenes->lastItem(),
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
        // Obtiene todos los proveedores, productos y almacenes para los selectores/búsquedas en el frontend
        $proveedores = Proveedor::all();
        $productos = Producto::all();
        $almacenes = \App\Models\Almacen::all();

        // Obtener el próximo número de orden
        $proximoNumero = OrdenCompra::getProximoNumero();

        // Renderiza la vista de creación de órdenes de compra con Inertia
        return Inertia::render('OrdenesCompra/Create', [
            'proveedores' => $proveedores,
            'productos' => $productos,
            'almacenes' => $almacenes,
            'proximoNumero' => $proximoNumero,
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
        // Log de depuración
        Log::info('OrdenCompraController@store - Datos recibidos:', $request->all());

        // Inicia una transacción de base de datos para asegurar la integridad
        DB::beginTransaction();
        try {
            // Preparar los datos antes de validar
            $requestData = $request->all();
            if (empty($requestData['almacen_id'])) {
                $requestData['almacen_id'] = null;
            }

            // Valida los datos de entrada del formulario
            $validatedData = $request->validate([
                'numero_orden' => 'nullable|string',
                'fecha_orden' => 'required|date',
                'fecha_entrega_esperada' => 'nullable|date',
                'prioridad' => 'required|in:baja,media,alta,urgente',
                'proveedor_id' => 'required|exists:proveedores,id',
                'almacen_id' => 'nullable|exists:almacenes,id',
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

            Log::info('OrdenCompraController@store - Datos validados:', $validatedData);

            // Crea la nueva orden de compra en la tabla 'orden_compras'
            $ordenCompra = OrdenCompra::create([
                'fecha_orden' => $validatedData['fecha_orden'],
                'fecha_entrega_esperada' => $validatedData['fecha_entrega_esperada'],
                'prioridad' => $validatedData['prioridad'],
                'proveedor_id' => $validatedData['proveedor_id'],
                'almacen_id' => $validatedData['almacen_id'],
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

            Log::info('OrdenCompraController@store - Orden creada exitosamente:', [
                'orden_id' => $ordenCompra->id,
                'numero_orden' => $ordenCompra->numero_orden,
                'proveedor_id' => $ordenCompra->proveedor_id,
                'total' => $ordenCompra->total
            ]);

            // Redirige al índice de órdenes de compra con un mensaje de éxito
            return redirect()->route('ordenescompra.index')->with('success', 'Orden de compra creada exitosamente.');
        } catch (\Illuminate\Validation\ValidationException $e) {
            // Si hay errores de validación, se revierte la transacción y se redirige con los errores
            DB::rollBack();
            Log::error('Error de validación al crear orden de compra: ' . $e->getMessage(), [
                'errors' => $e->errors(),
                'request_data' => $request->all()
            ]);
            return redirect()->back()->withErrors($e->errors())->withInput();
        } catch (\Exception $e) {
            // Si ocurre cualquier otro error, se revierte la transacción y se registra el error
            DB::rollBack();
            Log::error('Error al crear la orden de compra: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
                'request_data' => $request->all()
            ]);
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
        $productos = $ordenCompra->productos->map(function ($producto) {
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
                'productos' => $productos,
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
            $productos = $ordenCompra->productos->map(function ($producto) {
                return [
                    'id' => $producto->id,
                    'nombre' => $producto->nombre,
                    'descripcion' => $producto->descripcion,
                    'precio' => $producto->pivot->precio ?? 0,
                    'precio_compra' => $producto->precio_compra ?? 0,
                    'tipo' => 'producto',
                    'pivot' => [
                        'cantidad' => $producto->pivot->cantidad ?? 0,
                        'precio' => $producto->pivot->precio ?? 0,
                        'descuento' => $producto->pivot->descuento ?? 0,
                        'unidad_medida' => $producto->pivot->unidad_medida ?? '',
                    ],
                ];
            });

            // Recalcular totales basados en los productos actuales para asegurar consistencia
            $totalesCalculados = $this->calcularTotalesDesdeItems($productos, $ordenCompra->descuento_general ?? 0);

            // Obtiene todos los proveedores, productos y almacenes para los selectores/búsquedas en el frontend
            $proveedores = Proveedor::all();
            $productosAll = Producto::all();
            $almacenes = \App\Models\Almacen::all();

            // Renderiza la vista de edición de órdenes de compra
            return Inertia::render('OrdenesCompra/Edit', [
                'ordenCompra' => [
                    'id' => $ordenCompra->id,
                    'numero_orden' => $ordenCompra->numero_orden,
                    'fecha_orden' => $ordenCompra->fecha_orden?->format('Y-m-d'),
                    'fecha_entrega_esperada' => $ordenCompra->fecha_entrega_esperada?->format('Y-m-d'),
                    'prioridad' => $ordenCompra->prioridad,
                    'proveedor_id' => $ordenCompra->proveedor_id,
                    'almacen_id' => $ordenCompra->almacen_id,
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
                    'productos' => $productos,
                ],
                'proveedores' => $proveedores,
                'productos' => $productosAll,
                'almacenes' => $almacenes,
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
                'almacen_id' => 'nullable|exists:almacenes,id',
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
                'almacen_id' => $validatedData['almacen_id'],
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

            // Enviar correo al proveedor si tiene email
            if ($ordenCompra->proveedor && $ordenCompra->proveedor->email) {
                // Modo de prueba: si está en producción y no hay configuración SMTP real, enviar a email de prueba
                $emailDestino = $ordenCompra->proveedor->email;

                // Si estamos en producción y queremos hacer pruebas, podemos forzar envío a un email de prueba
                if (app()->environment('production') && config('mail.test_mode', false)) {
                    $emailDestino = config('mail.test_email', 'test@example.com');
                    Log::info('Modo prueba activado: enviando correo de prueba', [
                        'orden_id' => $ordenCompra->id,
                        'email_original' => $ordenCompra->proveedor->email,
                        'email_prueba' => $emailDestino
                    ]);
                }

                try {
                    Mail::to($emailDestino)->send(new OrdenCompraEnviada($ordenCompra));
                    Log::info('Correo de orden de compra enviado exitosamente', [
                        'orden_id' => $ordenCompra->id,
                        'proveedor_email' => $ordenCompra->proveedor->email,
                        'email_enviado_a' => $emailDestino
                    ]);
                } catch (\Exception $e) {
                    Log::error('Error al enviar correo de orden de compra', [
                        'orden_id' => $ordenCompra->id,
                        'proveedor_email' => $ordenCompra->proveedor->email,
                        'email_enviado_a' => $emailDestino,
                        'error' => $e->getMessage()
                    ]);
                    // No fallar la operación si el correo no se puede enviar
                }
            } else {
                Log::warning('No se pudo enviar correo: proveedor sin email', [
                    'orden_id' => $ordenCompra->id,
                    'proveedor_id' => $ordenCompra->proveedor_id
                ]);
            }

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
                if (
                    !$producto->pivot ||
                    !isset($producto->pivot->cantidad) ||
                    !isset($producto->pivot->precio) ||
                    $producto->pivot->cantidad <= 0 ||
                    $producto->pivot->precio <= 0
                ) {
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

                    // Update product price if different
                    if ($prodModel->precio_compra != $precioUnitario) {
                        $oldPrecioCompra = $prodModel->precio_compra;
                        $prodModel->update(['precio_compra' => $precioUnitario]);

                        // Log price change
                        ProductoPrecioHistorial::create([
                            'producto_id' => $prodModel->id,
                            'precio_compra_anterior' => $oldPrecioCompra,
                            'precio_compra_nuevo' => $precioUnitario,
                            'precio_venta_anterior' => null,
                            'precio_venta_nuevo' => $prodModel->precio_venta,
                            'tipo_cambio' => 'orden_compra',
                            'notas' => "Actualización por recepción de orden de compra #{$ordenCompra->id}",
                            'user_id' => Auth::id(),
                        ]);
                    }

                    // Actualizar el stock del producto con registro de movimiento
                    $this->inventarioService->entrada($prodModel, $cantidadRecibida, [
                        'motivo' => 'Recepción de orden de compra',
                        'referencia' => $ordenCompra,
                        'almacen_id' => $ordenCompra->almacen_id,
                        'detalles' => [
                            'orden_compra_id' => $ordenCompra->id,
                            'precio_unitario' => $precioUnitario,
                            'unidad_medida' => $unidadMedida,
                        ],
                    ]);

                    $productosParaCompra[$producto->id] = [
                        'cantidad' => $cantidadRecibida,
                        'precio' => $precioUnitario,
                        'unidad_medida' => $unidadMedida,
                    ];
                } else {
                    Log::warning('Producto no encontrado para incrementar stock en orden de compra ID: ' . $ordenCompra->id . ', Producto ID: ' . $producto->id);
                }
            }

            // Calcular totales basados en los productos de la orden de compra
            $subtotal = 0;
            $descuentoItems = 0;
            $descuentoGeneral = $ordenCompra->descuento_general ?? 0;

            foreach ($ordenCompra->productos as $producto) {
                $cantidad = $producto->pivot->cantidad;
                $precio = $producto->pivot->precio;
                $descuento = $producto->pivot->descuento ?? 0;

                $subtotalProducto = $cantidad * $precio;
                $descuentoMonto = $subtotalProducto * ($descuento / 100);

                $subtotal += $subtotalProducto;
                $descuentoItems += $descuentoMonto;
            }

            // Aplicar descuento general
            $subtotalDespuesDescuentoGeneral = $subtotal - $descuentoItems - $descuentoGeneral;

            // Calcular IVA configurable
            $ivaRate = \App\Services\EmpresaConfiguracionService::getIvaPorcentaje() / 100;
            $iva = $subtotalDespuesDescuentoGeneral * $ivaRate;

            // Total final
            $total = $subtotalDespuesDescuentoGeneral + $iva;

            // Crear notas combinando información de la orden de compra
            $notasCompra = "Compra generada desde Orden de Compra #{$ordenCompra->numero_orden}\n\n";
            $notasCompra .= "Fecha de Orden: " . $ordenCompra->fecha_orden->format('d/m/Y') . "\n";
            if ($ordenCompra->fecha_entrega_esperada) {
                $notasCompra .= "Fecha de Entrega Esperada: " . $ordenCompra->fecha_entrega_esperada->format('d/m/Y') . "\n";
            }
            $notasCompra .= "Prioridad: " . ucfirst($ordenCompra->prioridad) . "\n";
            $notasCompra .= "Términos de Pago: " . ucfirst(str_replace('_', ' ', $ordenCompra->terminos_pago)) . "\n";
            $notasCompra .= "Método de Pago: " . ucfirst($ordenCompra->metodo_pago) . "\n";
            if ($ordenCompra->direccion_entrega) {
                $notasCompra .= "Dirección de Entrega: " . $ordenCompra->direccion_entrega . "\n";
            }
            if ($ordenCompra->observaciones) {
                $notasCompra .= "\nObservaciones de la Orden:\n" . $ordenCompra->observaciones . "\n";
            }

            // Crea la compra con todos los campos de la orden de compra
            $compra = Compra::create([
                'proveedor_id' => $ordenCompra->proveedor_id,
                'almacen_id' => $ordenCompra->almacen_id,
                'orden_compra_id' => $ordenCompra->id,
                'fecha_compra' => now(),
                'subtotal' => $subtotal,
                'descuento_general' => $descuentoGeneral,
                'descuento_items' => $descuentoItems,
                'iva' => $iva,
                'total' => $total,
                'notas' => $notasCompra,
                'estado' => EstadoCompra::Procesada,
            ]);

            // Crear cuenta por pagar automáticamente (igual que en CompraController)
            CuentasPorPagar::create([
                'compra_id' => $compra->id,
                'monto_total' => $total,
                'monto_pagado' => 0,
                'monto_pendiente' => $total,
                'fecha_vencimiento' => now()->addDays(30), // 30 días por defecto
                'estado' => 'pendiente',
                'notas' => 'Cuenta generada automáticamente por recepción de orden de compra',
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
                            $this->inventarioService->salida($prodModel, $cantidadARevertir, [
                                'motivo' => 'Cancelación de orden de compra convertida',
                                'referencia' => $ordenCompra,
                                'detalles' => [
                                    'orden_compra_id' => $ordenCompra->id,
                                ],
                            ]);
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
                'pendiente' => ['enviado_a_proveedor', 'convertida', 'cancelada'], // Puede enviarse al proveedor, convertirse directamente o cancelarse
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
     * Convierte una orden de compra directamente a compra sin pasar por proveedor
     * Útil cuando el proveedor no tiene correo o se quiere agilizar el proceso
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function convertirDirecto(Request $request, $id)
    {
        DB::beginTransaction();
        try {
            $ordenCompra = OrdenCompra::with([
                'proveedor',
                'productos' => function ($q) {
                    $q->withPivot(['cantidad', 'precio', 'descuento']);
                }
            ])->findOrFail($id);

            // Solo se puede convertir directamente si está pendiente
            if ($ordenCompra->estado !== 'pendiente') {
                return redirect()->back()->with('error', 'Solo se pueden convertir directamente órdenes en estado pendiente.');
            }

            $productosParaCompra = [];

            foreach ($ordenCompra->productos as $producto) {
                // Validar que los datos esenciales del pivot estén presentes
                if (
                    !$producto->pivot ||
                    !isset($producto->pivot->cantidad) ||
                    !isset($producto->pivot->precio) ||
                    $producto->pivot->cantidad <= 0 ||
                    $producto->pivot->precio <= 0
                ) {
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

                    // Update product price if different
                    if ($prodModel->precio_compra != $precioUnitario) {
                        $oldPrecioCompra = $prodModel->precio_compra;
                        $prodModel->update(['precio_compra' => $precioUnitario]);

                        // Log price change
                        ProductoPrecioHistorial::create([
                            'producto_id' => $prodModel->id,
                            'precio_compra_anterior' => $oldPrecioCompra,
                            'precio_compra_nuevo' => $precioUnitario,
                            'precio_venta_anterior' => null,
                            'precio_venta_nuevo' => $prodModel->precio_venta,
                            'tipo_cambio' => 'orden_compra_directa',
                            'notas' => "Actualización por conversión directa de orden de compra #{$ordenCompra->id}",
                            'user_id' => Auth::id(),
                        ]);
                    }

                    // Actualizar el stock del producto con registro de movimiento
                    $this->inventarioService->entrada($prodModel, $cantidadRecibida, [
                        'motivo' => 'Conversión directa de orden de compra',
                        'referencia' => $ordenCompra,
                        'almacen_id' => $ordenCompra->almacen_id,
                        'detalles' => [
                            'orden_compra_id' => $ordenCompra->id,
                            'precio_unitario' => $precioUnitario,
                            'unidad_medida' => $unidadMedida,
                        ],
                    ]);

                    $productosParaCompra[$producto->id] = [
                        'cantidad' => $cantidadRecibida,
                        'precio' => $precioUnitario,
                        'unidad_medida' => $unidadMedida,
                    ];
                } else {
                    Log::warning('Producto no encontrado para incrementar stock en orden de compra ID: ' . $ordenCompra->id . ', Producto ID: ' . $producto->id);
                }
            }

            // Calcular totales basados en los productos de la orden de compra
            $subtotal = 0;
            $descuentoItems = 0;
            $descuentoGeneral = $ordenCompra->descuento_general ?? 0;

            foreach ($ordenCompra->productos as $producto) {
                $cantidad = $producto->pivot->cantidad;
                $precio = $producto->pivot->precio;
                $descuento = $producto->pivot->descuento ?? 0;

                $subtotalProducto = $cantidad * $precio;
                $descuentoMonto = $subtotalProducto * ($descuento / 100);

                $subtotal += $subtotalProducto;
                $descuentoItems += $descuentoMonto;
            }

            // Aplicar descuento general
            $subtotalDespuesDescuentoGeneral = $subtotal - $descuentoItems - $descuentoGeneral;

            // Calcular IVA configurable
            $ivaRate = \App\Services\EmpresaConfiguracionService::getIvaPorcentaje() / 100;
            $iva = $subtotalDespuesDescuentoGeneral * $ivaRate;

            // Total final
            $total = $subtotalDespuesDescuentoGeneral + $iva;

            // Crear notas combinando información de la orden de compra
            $notasCompra = "Compra generada directamente desde Orden de Compra #{$ordenCompra->numero_orden}\n\n";
            $notasCompra .= "Fecha de Orden: " . $ordenCompra->fecha_orden->format('d/m/Y') . "\n";
            if ($ordenCompra->fecha_entrega_esperada) {
                $notasCompra .= "Fecha de Entrega Esperada: " . $ordenCompra->fecha_entrega_esperada->format('d/m/Y') . "\n";
            }
            $notasCompra .= "Prioridad: " . ucfirst($ordenCompra->prioridad) . "\n";
            $notasCompra .= "Términos de Pago: " . ucfirst(str_replace('_', ' ', $ordenCompra->terminos_pago)) . "\n";
            $notasCompra .= "Método de Pago: " . ucfirst($ordenCompra->metodo_pago) . "\n";
            if ($ordenCompra->direccion_entrega) {
                $notasCompra .= "Dirección de Entrega: " . $ordenCompra->direccion_entrega . "\n";
            }
            if ($ordenCompra->observaciones) {
                $notasCompra .= "\nObservaciones de la Orden:\n" . $ordenCompra->observaciones . "\n";
            }

            // Crea la compra con todos los campos de la orden de compra
            $compra = Compra::create([
                'proveedor_id' => $ordenCompra->proveedor_id,
                'almacen_id' => $ordenCompra->almacen_id,
                'orden_compra_id' => $ordenCompra->id,
                'fecha_compra' => now(),
                'subtotal' => $subtotal,
                'descuento_general' => $descuentoGeneral,
                'descuento_items' => $descuentoItems,
                'iva' => $iva,
                'total' => $total,
                'notas' => $notasCompra,
                'estado' => EstadoCompra::Procesada,
            ]);

            // Crear cuenta por pagar automáticamente
            CuentasPorPagar::create([
                'compra_id' => $compra->id,
                'monto_total' => $total,
                'monto_pagado' => 0,
                'monto_pendiente' => $total,
                'fecha_vencimiento' => now()->addDays(30), // 30 días por defecto
                'estado' => 'pendiente',
                'notas' => 'Cuenta generada automáticamente por conversión directa de orden de compra',
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
                    '*** CONVERSIÓN DIRECTA A COMPRA #' . $compra->id . ' *** ' . now()->format('d/m/Y H:i') .
                    ' - Stock actualizado automáticamente (sin envío a proveedor)'
            ]);

            DB::commit();

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Orden convertida directamente a compra exitosamente. Compra #' . $compra->id . ' creada y stock actualizado.',
                    'compra_id' => $compra->id
                ]);
            }

            return redirect()->route('compras.index')
                ->with('success', 'Orden convertida directamente a compra exitosamente. Compra #' . $compra->id . ' creada y stock actualizado.');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error al convertir orden directamente a compra: ' . $e->getMessage());
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'error' => 'Error al procesar la conversión directa: ' . $e->getMessage()
                ], 500);
            }
            return redirect()->back()->with('error', 'Error al procesar la conversión directa a compra.');
        }
    }

    /**
     * Enviar orden de compra por email con PDF adjunto
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
   public function enviarEmail(Request $request, $id)
   {
       // No necesitamos validar email_destino ya que usamos el del proveedor

       try {
           // Obtener la orden de compra con todas las relaciones necesarias
           $ordenCompra = OrdenCompra::with(['proveedor', 'productos'])->findOrFail($id);

           // Verificar que el proveedor tenga email
           if (!$ordenCompra->proveedor->email) {
               throw ValidationException::withMessages([
                   'email' => 'El proveedor no tiene email configurado',
               ]);
           }

           // El envío de email está permitido en cualquier estado

           // Obtener configuración de empresa para el PDF
           $configuracion = \App\Models\EmpresaConfiguracion::getConfig();

           // Verificar que la configuración SMTP esté disponible (usando valores por defecto si es necesario)
           $smtp_host = $configuracion->smtp_host ?: 'smtp.hostinger.com';
           $smtp_port = $configuracion->smtp_port ?: 587;
           $smtp_username = $configuracion->smtp_username ?: 'documentos_digitales@asistenciavircom.com';
           $smtp_password = $configuracion->smtp_password ?: 'Anahid2188';
           $smtp_encryption = $configuracion->smtp_encryption ?: 'tls';

           $email_from_address = $configuracion->email_from_address ?: 'documentos_digitales@asistenciavircom.com';
           $email_from_name = $configuracion->email_from_name ?: 'CDD - Sistema de Gestión';

           Log::info('Configuración SMTP para orden de compra', [
               'orden_id' => $ordenCompra->id,
               'smtp_host' => $smtp_host,
               'smtp_port' => $smtp_port,
               'smtp_username' => $smtp_username,
               'has_password' => !empty($smtp_password),
               'email_from_address' => $email_from_address,
           ]);

           // Generar PDF de la orden de compra
           $pdf = Pdf::loadView('orden_compra_pdf', [
               'ordenCompra' => $ordenCompra,
               'configuracion' => $configuracion,
           ]);

           // Configurar opciones del PDF
           $pdf->setPaper('letter', 'portrait');
           $pdf->setOptions([
               'defaultFont' => 'sans-serif',
               'isHtml5ParserEnabled' => true,
               'isPhpEnabled' => true,
           ]);

           // Preparar datos del email
           $datosEmail = [
               'ordenCompra' => $ordenCompra,
               'proveedor' => $ordenCompra->proveedor,
               'configuracion' => $configuracion,
           ];

           // Configurar SMTP con datos de la base de datos (usando valores por defecto si es necesario)
           config([
               'mail.mailers.smtp.host' => $smtp_host,
               'mail.mailers.smtp.port' => $smtp_port,
               'mail.mailers.smtp.username' => $smtp_username,
               'mail.mailers.smtp.password' => $smtp_password,
               'mail.mailers.smtp.encryption' => $smtp_encryption,
               'mail.from.address' => $email_from_address,
               'mail.from.name' => $email_from_name,
           ]);

           // Enviar email con PDF adjunto
           Mail::send('emails.orden_compra', $datosEmail, function ($message) use ($ordenCompra, $pdf, $configuracion) {
               $message->to($ordenCompra->proveedor->email)
                   ->subject("Orden de Compra #{$ordenCompra->numero_orden} - {$configuracion->nombre_empresa}")
                   ->attachData($pdf->output(), "orden-compra-{$ordenCompra->numero_orden}.pdf", [
                       'mime' => 'application/pdf',
                   ]);

               // Agregar reply-to si está configurado
               if ($configuracion->email_reply_to) {
                   $message->replyTo($configuracion->email_reply_to);
               }
           });

           Log::info("PDF de orden de compra enviado por email", [
               'orden_compra_id' => $ordenCompra->id,
               'proveedor_email' => $ordenCompra->proveedor->email,
               'numero_orden' => $ordenCompra->numero_orden,
               'configuracion_smtp' => [
                   'host' => $smtp_host,
                   'port' => $smtp_port,
                   'encryption' => $smtp_encryption,
               ]
           ]);

           // Registrar el envío en la orden de compra para mostrar en el frontend
           $ordenCompra->update([
               'email_enviado' => true,
               'email_enviado_fecha' => now(),
               'email_enviado_por' => Auth::id(),
           ]);

           // Si es una petición AJAX, devolver JSON; de lo contrario, redirect
           if ($request->expectsJson() || $request->is('api/*')) {
               return response()->json([
                   'success' => true,
                   'message' => 'Orden de compra enviada por email correctamente',
                   'ordenCompra' => [
                       'id' => $ordenCompra->id,
                       'email_enviado' => true,
                       'email_enviado_fecha' => $ordenCompra->email_enviado_fecha?->format('d/m/Y H:i'),
                       'estado' => $ordenCompra->estado
                   ]
               ]);
           }

           return redirect()->back()->with('success', 'Orden de compra enviada por email correctamente');
       } catch (\Exception $e) {
           Log::error("Error al enviar PDF de orden de compra por email", [
               'orden_compra_id' => $id,
               'proveedor_email' => $ordenCompra->proveedor->email ?? 'no disponible',
               'error' => $e->getMessage(),
               'file' => $e->getFile(),
               'line' => $e->getLine(),
               'trace' => $e->getTraceAsString()
           ]);

           // Mensaje más específico para debugging
           $errorMessage = $e->getMessage();
           $mensaje = 'Error al enviar orden de compra por email';

           if (strpos($errorMessage, 'authentication failed') !== false) {
               $mensaje = 'Error de autenticación SMTP. Verifica usuario/contraseña.';
           } elseif (strpos($errorMessage, 'Connection refused') !== false) {
               $mensaje = 'No se pudo conectar al servidor SMTP. Verifica host/puerto.';
           } elseif (strpos($errorMessage, 'timeout') !== false) {
               $mensaje = 'Timeout de conexión. Servidor no responde.';
           } elseif (strpos($errorMessage, 'View') !== false) {
               $mensaje = 'Error en plantilla de email. Verifica archivos de vistas.';
           }

           throw ValidationException::withMessages([
               'email' => $mensaje . ' | Detalle: ' . $errorMessage,
           ]);
       }
   }

    /**
     * Edita el precio de un producto específico en una orden de compra
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $ordenId
     * @param  int  $productoId
     * @return \Illuminate\Http\JsonResponse
     */
    public function editarPrecioProducto(Request $request, $ordenId, $productoId)
    {
        DB::beginTransaction();
        try {
            $ordenCompra = OrdenCompra::findOrFail($ordenId);
            $producto = Producto::findOrFail($productoId);

            // Validar que el producto esté en la orden de compra
            $ordenProducto = $ordenCompra->productos()->where('producto_id', $productoId)->first();
            if (!$ordenProducto) {
                return response()->json([
                    'success' => false,
                    'message' => 'El producto no está asociado a esta orden de compra'
                ], 404);
            }

            $request->validate([
                'precio' => 'required|numeric|min:0',
                'notas' => 'nullable|string|max:500'
            ]);

            $nuevoPrecio = (float) $request->precio;
            $precioAnterior = (float) $ordenProducto->pivot->precio;

            // Si el precio cambió, actualizar tanto en el pivot como en el producto
            if ($precioAnterior != $nuevoPrecio) {
                // Actualizar precio en el pivot de la orden de compra
                $ordenCompra->productos()->updateExistingPivot($productoId, [
                    'precio' => $nuevoPrecio
                ]);

                // Actualizar precio en el producto si es diferente
                if ($producto->precio_compra != $nuevoPrecio) {
                    $precioCompraAnterior = $producto->precio_compra;

                    $producto->update(['precio_compra' => $nuevoPrecio]);

                    // Registrar en el historial de precios
                    ProductoPrecioHistorial::create([
                        'producto_id' => $producto->id,
                        'precio_compra_anterior' => $precioCompraAnterior,
                        'precio_compra_nuevo' => $nuevoPrecio,
                        'precio_venta_anterior' => $producto->precio_venta,
                        'precio_venta_nuevo' => $producto->precio_venta,
                        'tipo_cambio' => 'edicion_orden_compra',
                        'notas' => $request->notas ?: "Precio actualizado en orden de compra #{$ordenCompra->numero_orden}",
                        'user_id' => Auth::id(),
                    ]);

                    Log::info("Precio de producto actualizado desde orden de compra", [
                        'orden_id' => $ordenCompra->id,
                        'producto_id' => $producto->id,
                        'precio_anterior' => $precioCompraAnterior,
                        'precio_nuevo' => $nuevoPrecio,
                        'user_id' => Auth::id()
                    ]);
                }

                // Recalcular totales de la orden de compra
                $this->recalcularTotalesOrdenCompra($ordenCompra);

                DB::commit();

                return response()->json([
                    'success' => true,
                    'message' => 'Precio actualizado exitosamente',
                    'data' => [
                        'precio_anterior' => $precioAnterior,
                        'precio_nuevo' => $nuevoPrecio,
                        'orden_actualizada' => [
                            'id' => $ordenCompra->id,
                            'subtotal' => $ordenCompra->fresh()->subtotal,
                            'total' => $ordenCompra->fresh()->total,
                        ]
                    ]
                ]);
            } else {
                DB::rollBack();
                return response()->json([
                    'success' => true,
                    'message' => 'El precio no cambió',
                    'data' => [
                        'precio_anterior' => $precioAnterior,
                        'precio_nuevo' => $nuevoPrecio
                    ]
                ]);
            }

        } catch (\Illuminate\Validation\ValidationException $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Error de validación',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error al editar precio de producto en orden de compra: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error al actualizar el precio: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Recalcula los totales de una orden de compra basados en sus productos actuales
     *
     * @param OrdenCompra $ordenCompra
     * @return void
     */
    private function recalcularTotalesOrdenCompra(OrdenCompra $ordenCompra)
    {
        $productos = $ordenCompra->productos;

        $subtotal = 0;
        $descuentoItems = 0;

        foreach ($productos as $producto) {
            $cantidad = (float) $producto->pivot->cantidad;
            $precio = (float) $producto->pivot->precio;
            $descuento = (float) $producto->pivot->descuento ?? 0;

            $subtotalProducto = $cantidad * $precio;
            $descuentoProducto = ($subtotalProducto * $descuento) / 100;

            $subtotal += $subtotalProducto;
            $descuentoItems += $descuentoProducto;
        }

        // Aplicar descuento general
        $descuentoGeneral = (float) $ordenCompra->descuento_general ?? 0;
        $subtotalDespuesDescuentos = $subtotal - $descuentoItems - $descuentoGeneral;

        // Calcular IVA configurable
        $ivaRate = \App\Services\EmpresaConfiguracionService::getIvaPorcentaje() / 100;
        $iva = $subtotalDespuesDescuentos * $ivaRate;

        // Total final
        $total = $subtotalDespuesDescuentos + $iva;

        // Actualizar la orden de compra
        $ordenCompra->update([
            'subtotal' => round($subtotal, 2),
            'descuento_items' => round($descuentoItems, 2),
            'iva' => round($iva, 2),
            'total' => round($total, 2),
        ]);
    }

    /**
     * Obtiene el historial de precios de un producto
     *
     * @param  int  $productoId
     * @return \Illuminate\Http\JsonResponse
     */
    public function obtenerHistorialPrecios($productoId)
    {
        try {
            $producto = Producto::findOrFail($productoId);

            $historial = ProductoPrecioHistorial::where('producto_id', $productoId)
                ->with('user:id,name')
                ->orderBy('created_at', 'desc')
                ->take(50) // Últimos 50 cambios
                ->get()
                ->map(function ($registro) {
                    return [
                        'id' => $registro->id,
                        'fecha' => $registro->created_at->format('d/m/Y H:i:s'),
                        'precio_compra_anterior' => (float) $registro->precio_compra_anterior,
                        'precio_compra_nuevo' => (float) $registro->precio_compra_nuevo,
                        'precio_venta_anterior' => $registro->precio_venta_anterior ? (float) $registro->precio_venta_anterior : null,
                        'precio_venta_nuevo' => $registro->precio_venta_nuevo ? (float) $registro->precio_venta_nuevo : null,
                        'tipo_cambio' => $registro->tipo_cambio,
                        'notas' => $registro->notas,
                        'usuario' => $registro->user?->name,
                        'cambio_compra' => (float) $registro->precio_compra_nuevo - (float) $registro->precio_compra_anterior,
                        'cambio_venta' => $registro->precio_venta_nuevo && $registro->precio_venta_anterior
                            ? (float) $registro->precio_venta_nuevo - (float) $registro->precio_venta_anterior
                            : null,
                    ];
                });

            return response()->json([
                'success' => true,
                'producto' => [
                    'id' => $producto->id,
                    'nombre' => $producto->nombre,
                    'precio_compra_actual' => (float) $producto->precio_compra,
                    'precio_venta_actual' => (float) $producto->precio_venta,
                ],
                'historial' => $historial,
                'total_registros' => $historial->count()
            ]);

        } catch (\Exception $e) {
            Log::error('Error al obtener historial de precios: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener el historial de precios'
            ], 500);
        }
    }

    /**
     * Muestra la página de reporte de historial de precios de un producto
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $productoId
     * @return \Illuminate\Http\Response
     */
    public function mostrarHistorialPrecios(Request $request, $productoId)
    {
        try {
            $producto = Producto::findOrFail($productoId);

            // Construir consulta base
            $query = ProductoPrecioHistorial::where('producto_id', $productoId)
                ->with('user:id,name')
                ->orderBy('created_at', 'desc');

            // Aplicar filtros
            if ($tipoCambio = $request->get('tipo_cambio')) {
                $query->where('tipo_cambio', $tipoCambio);
            }

            if ($fechaDesde = $request->get('fecha_desde')) {
                $query->whereDate('created_at', '>=', $fechaDesde);
            }

            if ($fechaHasta = $request->get('fecha_hasta')) {
                $query->whereDate('created_at', '<=', $fechaHasta);
            }

            $historial = $query->get()->map(function ($registro) {
                return [
                    'id' => $registro->id,
                    'fecha' => $registro->created_at->format('d/m/Y H:i:s'),
                    'precio_compra_anterior' => (float) $registro->precio_compra_anterior,
                    'precio_compra_nuevo' => (float) $registro->precio_compra_nuevo,
                    'precio_venta_anterior' => $registro->precio_venta_anterior ? (float) $registro->precio_venta_anterior : null,
                    'precio_venta_nuevo' => $registro->precio_venta_nuevo ? (float) $registro->precio_venta_nuevo : null,
                    'tipo_cambio' => $registro->tipo_cambio,
                    'notas' => $registro->notas,
                    'usuario' => $registro->user?->name,
                    'cambio_compra' => (float) $registro->precio_compra_nuevo - (float) $registro->precio_compra_anterior,
                    'cambio_venta' => $registro->precio_venta_nuevo && $registro->precio_venta_anterior
                        ? (float) $registro->precio_venta_nuevo - (float) $registro->precio_venta_anterior
                        : null,
                ];
            });

            return Inertia::render('Reportes/HistorialPrecios', [
                'producto' => [
                    'id' => $producto->id,
                    'nombre' => $producto->nombre,
                    'descripcion' => $producto->descripcion,
                    'precio_compra_actual' => (float) $producto->precio_compra,
                    'precio_venta_actual' => (float) $producto->precio_venta,
                ],
                'historial' => $historial,
                'filtros' => $request->only(['tipo_cambio', 'fecha_desde', 'fecha_hasta'])
            ]);

        } catch (\Exception $e) {
            Log::error('Error al mostrar historial de precios: ' . $e->getMessage());
            return redirect()->route('productos.index')->with('error', 'Error al cargar el historial de precios.');
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

        // Calcular IVA configurable
        $ivaRate = \App\Services\EmpresaConfiguracionService::getIvaPorcentaje() / 100;
        $iva = $subtotalDespuesDescuentoGeneral * $ivaRate;

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
