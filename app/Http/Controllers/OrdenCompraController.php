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

class OrdenCompraController extends Controller
{
    /**
     * Muestra una lista de las órdenes de compra.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        // Número de elementos por página
        $perPage = $request->get('per_page', 10);

        // Construir la consulta base
        $query = OrdenCompra::with(['proveedor', 'productos', 'servicios']);

        // Aplicar filtros si existen
        if ($search = $request->get('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('id', 'like', "%{$search}%")
                    ->orWhereHas('proveedor', function ($subQ) use ($search) {
                        $subQ->where('nombre_razon_social', 'like', "%{$search}%")
                             ->orWhere('email', 'like', "%{$search}%");
                    })
                    ->orWhere('estado', 'like', "%{$search}%");
            });
        }

        if ($estado = $request->get('estado')) {
            $query->where('estado', $estado);
        }

        // Aplicar ordenamiento
        $sortBy = $request->get('sort_by', 'created_at');
        $sortDirection = $request->get('sort_direction', 'desc');

        $validSortFields = ['id', 'created_at', 'total', 'estado'];
        if (!in_array($sortBy, $validSortFields)) {
            $sortBy = 'created_at';
        }

        $query->orderBy($sortBy, $sortDirection);

        // Obtener el total de registros para paginación
        $total = $query->count();

        // Obtener los registros para la página actual
        $ordenes = $query->skip(($request->get('page', 1) - 1) * $perPage)->take($perPage)->get();

        // Transformar los datos
        $transformedData = $ordenes->map(function ($orden) {
            // Mapea los productos adjuntos a la orden
            $productos = $orden->productos->map(function ($producto) {
                return [
                    'id' => $producto->id,
                    'nombre' => $producto->nombre,
                    'tipo' => 'producto',
                    'pivot' => [ // Datos de la tabla pivote (cantidad, precio)
                        'cantidad' => $producto->pivot->cantidad,
                        'precio' => $producto->pivot->precio,
                    ],
                ];
            });

            // Mapea los servicios adjuntos a la orden (si aplica)
            $servicios = $orden->servicios->map(function ($servicio) {
                return [
                    'id' => $servicio->id,
                    'nombre' => $servicio->nombre,
                    'tipo' => 'servicio',
                    'pivot' => [ // Datos de la tabla pivote (cantidad, precio)
                        'cantidad' => $servicio->pivot->cantidad,
                        'precio' => $servicio->pivot->precio,
                    ],
                ];
            });

            // Combina productos y servicios en una única colección de ítems para la vista
            $items = collect($productos->all())->merge($servicios->all());

            return [
                'id' => $orden->id,
                'numero_orden' => $orden->id, // Usar el ID como número de orden por ahora
                'proveedor' => $orden->proveedor, // Información del proveedor
                'items' => $items, // Productos y servicios de la orden (mantener como 'items' para coincidir con frontend)
                'total' => $orden->total,
                'estado' => $orden->estado, // Por ejemplo, 'pendiente', 'recibida', 'cancelada'
                'created_at' => $orden->created_at->format('Y-m-d H:i:s'), // Formato ISO para que el frontend lo maneje
            ];
        });

        // Crear paginador manualmente
        $ordenesCompra = new \Illuminate\Pagination\LengthAwarePaginator(
            $transformedData,
            $total,
            $perPage,
            $request->get('page', 1),
            ['path' => $request->url(), 'pageName' => 'page']
        );

        // Estadísticas
        $stats = [
            'total' => OrdenCompra::count(),
            'pendientes' => OrdenCompra::where('estado', 'pendiente')->count(),
            'recibidas' => OrdenCompra::where('estado', 'recibida')->count(),
            'canceladas' => OrdenCompra::where('estado', 'cancelada')->count(),
            'borrador' => OrdenCompra::where('estado', 'borrador')->count(),
        ];

        // Renderiza la vista de índice de órdenes de compra con Inertia
        return Inertia::render('OrdenesCompra/Index', [
            'ordenesCompra' => $ordenesCompra,
            'stats' => $stats,
            'filters' => $request->only(['search', 'estado']),
            'sorting' => ['sort_by' => $sortBy, 'sort_direction' => $sortDirection],
        ]);
    }

    /**
     * Muestra el formulario para crear una nueva orden de compra.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        // Obtiene todos los proveedores, productos y servicios para los selectores/búsquedas en el frontend
        $proveedores = Proveedor::all();
        $productos = Producto::all();
        $servicios = Servicio::all();

        // Renderiza la vista de creación de órdenes de compra con Inertia
        return Inertia::render('OrdenesCompra/Create', [
            'proveedores' => $proveedores,
            'productos' => $productos,
            'servicios' => $servicios,
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
                'proveedor_id' => 'required|exists:proveedores,id',
                'items' => 'required|array', // Se espera un array de ítems (productos/servicios)
                'items.*.id' => 'required|integer',
                'items.*.tipo' => 'required|in:producto,servicio',
                'items.*.cantidad' => 'required|integer|min:1',
                'items.*.precio' => 'required|numeric|min:0',
                // Puedes agregar más validaciones, como 'fecha_esperada_entrega', 'estado_inicial', etc.
            ]);

            // Calcula el total de la orden de compra
            $total = array_sum(array_map(function ($item) {
                return $item['cantidad'] * $item['precio'];
            }, $validatedData['items']));

            // Crea la nueva orden de compra en la tabla 'orden_compras'
            $ordenCompra = OrdenCompra::create([
                'proveedor_id' => $validatedData['proveedor_id'],
                'total' => $total,
                'estado' => 'pendiente', // Estado inicial de una orden de compra
                // Agrega aquí cualquier otro campo relevante para la orden de compra
            ]);

            // Asocia los productos y servicios a la orden de compra a través de las tablas pivote
            foreach ($validatedData['items'] as $itemData) {
                if ($itemData['tipo'] === 'producto') {
                    $ordenCompra->productos()->attach($itemData['id'], [
                        'cantidad' => $itemData['cantidad'],
                        'precio' => $itemData['precio'],
                    ]);
                } elseif ($itemData['tipo'] === 'servicio') {
                    $ordenCompra->servicios()->attach($itemData['id'], [
                        'cantidad' => $itemData['cantidad'],
                        'precio' => $itemData['precio'],
                    ]);
                }
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
        $ordenCompra = OrdenCompra::with(['proveedor', 'productos', 'servicios'])->findOrFail($id);

        // Mapea y combina productos y servicios de la misma manera que en el index
        $items = $ordenCompra->productos->map(function ($producto) {
            return [
                'id' => $producto->id,
                'nombre' => $producto->nombre,
                'tipo' => 'producto',
                'pivot' => [
                    'cantidad' => $producto->pivot->cantidad,
                    'precio' => $producto->pivot->precio,
                ],
            ];
        })->merge($ordenCompra->servicios->map(function ($servicio) {
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

        // Renderiza la vista de detalles de la orden de compra
        return Inertia::render('OrdenesCompra/Show', [
            'ordenCompra' => [
                'id' => $ordenCompra->id,
                'proveedor' => $ordenCompra->proveedor,
                'items' => $items,
                'total' => $ordenCompra->total,
                'estado' => $ordenCompra->estado,
                'fecha_orden' => $ordenCompra->created_at->format('d/m/Y H:i'),
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
            $ordenCompra = OrdenCompra::with(['proveedor', 'productos', 'servicios'])->findOrFail($id);

            // Mapea y combina productos y servicios, añadiendo manejo de errores para pivots
            $items = $ordenCompra->productos->map(function ($producto) {
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
            })->merge($ordenCompra->servicios->map(function ($servicio) {
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
            }))->filter(fn($item) => $item !== null); // Elimina cualquier ítem nulo si hubo errores

            // Obtiene todos los proveedores, productos y servicios para los selectores/búsquedas en el frontend
            $proveedores = Proveedor::all();
            $productos = Producto::all();
            $servicios = Servicio::all();

            // Renderiza la vista de edición de órdenes de compra
            return Inertia::render('OrdenesCompra/Edit', [
                'ordenCompra' => [
                    'id' => $ordenCompra->id,
                    'proveedor_id' => $ordenCompra->proveedor_id,
                    'proveedor' => $ordenCompra->proveedor,
                    'items' => $items,
                    'total' => $ordenCompra->total,
                    'estado' => $ordenCompra->estado,
                ],
                'proveedores' => $proveedores,
                'productos' => $productos,
                'servicios' => $servicios,
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
                'proveedor_id' => 'required|exists:proveedores,id',
                'items' => 'required|array',
                'items.*.id' => 'required|integer',
                'items.*.tipo' => 'required|in:producto,servicio',
                'items.*.cantidad' => 'required|integer|min:1',
                'items.*.precio' => 'required|numeric|min:0',
                // Asegúrate de validar el estado si permites cambiarlo desde el frontend
                // 'estado' => 'required|string|in:pendiente,recibida,cancelada',
            ]);

            // Calcula el nuevo total
            $total = array_sum(array_map(function ($item) {
                return $item['cantidad'] * $item['precio'];
            }, $validatedData['items']));

            // Actualiza los campos principales de la orden de compra
            $ordenCompra->update([
                'proveedor_id' => $validatedData['proveedor_id'],
                'total' => $total,
                // 'estado' => $validatedData['estado'] ?? $ordenCompra->estado, // Actualizar estado si se recibe
            ]);

            // Sincroniza los productos y servicios adjuntos.
            // Primero, desasocia todos los ítems actuales para luego adjuntar los nuevos.
            $ordenCompra->productos()->detach();
            $ordenCompra->servicios()->detach();

            foreach ($validatedData['items'] as $itemData) {
                if ($itemData['tipo'] === 'producto') {
                    $ordenCompra->productos()->attach($itemData['id'], [
                        'cantidad' => $itemData['cantidad'],
                        'precio' => $itemData['precio'],
                    ]);
                } elseif ($itemData['tipo'] === 'servicio') {
                    $ordenCompra->servicios()->attach($itemData['id'], [
                        'cantidad' => $itemData['cantidad'],
                        'precio' => $itemData['precio'],
                    ]);
                }
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
            // Desasocia los productos y servicios antes de eliminar la orden
            $ordenCompra->productos()->detach();
            $ordenCompra->servicios()->detach();
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
            // Carga la orden de compra con sus productos y servicios para acceder a la información necesaria
            $ordenCompra = OrdenCompra::with('proveedor', 'productos', 'servicios')->findOrFail($id);

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
}
