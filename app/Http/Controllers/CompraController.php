<?php

namespace App\Http\Controllers;


use App\Enums\EstadoCompra;
use App\Models\Compra;
use App\Models\CompraItem;
use App\Models\Producto;
use App\Models\ProductoSerie;
use App\Models\ProductoPrecioHistorial;
use App\Models\Proveedor;
use App\Models\Almacen;
use App\Models\CuentasPorPagar;
use App\Services\InventarioService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redirect;
use Inertia\Inertia;
use Illuminate\Pagination\LengthAwarePaginator;

class CompraController extends Controller
{
    public function __construct(private readonly InventarioService $inventarioService)
    {
    }

    public function index(Request $request)
    {
        try {
            $perPage = (int) ($request->integer('per_page') ?: 10);
        $page    = max(1, (int) $request->get('page', 1));

        // Validar elementos por p�gina
        $validPerPages = [10, 15, 25, 50, 100];
        if (!in_array($perPage, $validPerPages)) {
            $perPage = 10;
        }

        $baseQuery = Compra::with([
            'proveedor',
            'productos',
            'almacen',
            'ordenCompra',
            'movimientos', // Cargar movimientos para obtener stock histórico
        ]);

        // Aplicar filtros
        if ($search = trim($request->get('search', ''))) {
            $baseQuery->where(function ($query) use ($search) {
                $query->where('numero_compra', 'like', "%{$search}%")
                      ->orWhere('id', 'like', "%{$search}%")
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

        if ($request->filled('proveedor_id')) {
            $baseQuery->where('proveedor_id', $request->proveedor_id);
        }

        if ($request->filled('almacen_id')) {
            $baseQuery->where('almacen_id', $request->almacen_id);
        }

        if ($request->filled('origen')) {
            if ($request->origen === 'directa') {
                $baseQuery->whereNull('orden_compra_id');
            } elseif ($request->origen === 'orden_compra') {
                $baseQuery->whereNotNull('orden_compra_id');
            }
        }

        if ($request->filled('fecha_desde')) {
            $baseQuery->whereDate('created_at', '>=', $request->fecha_desde);
        }

        if ($request->filled('fecha_hasta')) {
            $baseQuery->whereDate('created_at', '<=', $request->fecha_hasta);
        }

        // Ordenamiento
        $sortBy = $request->get('sort_by', 'created_at');
        $sortDirection = $request->get('sort_direction', 'desc');

        $allowedSorts = ['created_at', 'total', 'estado', 'numero_compra'];
        if (!in_array($sortBy, $allowedSorts)) {
            $sortBy = 'created_at';
        }

        $baseQuery->orderBy($sortBy, $sortDirection === 'asc' ? 'asc' : 'desc')
                  ->orderBy('id', 'desc');

        $paginator = $baseQuery->paginate($perPage, ['*'], 'page', $page);
        $compras = collect($paginator->items());

        $transformed = $compras->map(function ($compra) {
            $compra->load('productos');
            $compra->productos = $compra->productos->map(function ($p) use ($compra) {
                // Buscar el movimiento de entrada correspondiente a este producto en esta compra
                $movimiento = $compra->movimientos
                    ->where('producto_id', $p->id)
                    ->where('tipo', 'entrada')
                    ->first();

                // Si existe el movimiento, usamos el stock_anterior real registrado
                // Si no existe (casos antiguos o error), usamos el cálculo aproximado
                if ($movimiento) {
                    $stockAntes = $movimiento->stock_anterior;
                } else {
                    // Fallback: Cálculo aproximado basado en stock actual
                    $stockActual = $p->stock ?? 0;
                    $cantidadComprada = $p->pivot->cantidad ?? 0;

                    if ($compra->estado === EstadoCompra::Cancelada) {
                        $stockAntes = $stockActual + $cantidadComprada;
                    } else {
                        $stockAntes = $stockActual - $cantidadComprada;
                    }
                }

                $stockActual = $p->stock ?? 0; // Stock actual real del producto en este momento

                return array_merge($p->toArray(), [
                    'cantidad' => $p->pivot->cantidad ?? 0,
                    'precio' => $p->pivot->precio ?? 0,
                    'descuento' => $p->pivot->descuento ?? 0,
                    'subtotal' => $p->pivot->subtotal ?? 0,
                    'descuento_monto' => $p->pivot->descuento_monto ?? 0,
                    'stock_antes' => max(0, $stockAntes),
                    'stock_despues' => (int) $stockActual, // Esto sigue siendo el stock actual del sistema
                    // 'diferencia_stock' ya no es tan relevante si mostramos el histórico, pero lo mantenemos
                    'diferencia_stock' => (int) ($stockActual - $stockAntes),
                ]);
            });
            $items = $compra->productos;

            // Crear resumen de productos para tooltip
            $productosTooltip = $items->map(function ($item) {
                return $item['nombre'] . ' (' . $item['cantidad'] . ' ' . 'u' . ')';
            })->join(', ');

            return [
                'id' => $compra->id,
                'numero_compra' => $compra->numero_compra ?? 'N/A',
                'proveedor' => $compra->proveedor ? [
                    'id' => $compra->proveedor->id,
                    'nombre_razon_social' => $compra->proveedor->nombre_razon_social,
                    'rfc' => $compra->proveedor->rfc ?? null,
                ] : null,
                'almacen' => $compra->almacen ? [
                    'id' => $compra->almacen->id,
                    'nombre' => $compra->almacen->nombre,
                ] : null,
                'productos' => $items,
                'productos_count' => $items->count(),
                'productos_tooltip' => $productosTooltip ?: 'Sin productos',
                'subtotal' => (float) ($compra->subtotal ?? 0),
                'descuento_items' => (float) ($compra->descuento_items ?? 0),
                'descuento_general' => (float) ($compra->descuento_general ?? 0),
                'iva' => (float) ($compra->iva ?? 0),
                'total' => (float) ($compra->total ?? 0),
                'estado' => $compra->estado ?? 'procesada',
                'origen' => $compra->orden_compra_id ? 'orden_compra' : 'directa',
                'orden_compra' => $compra->ordenCompra ? [
                    'id' => $compra->ordenCompra->id,
                    'numero_orden' => $compra->ordenCompra->numero_orden,
                    'estado' => $compra->ordenCompra->estado,
                ] : null,
                'created_at' => optional($compra->created_at)->format('Y-m-d H:i:s'),
                'fecha' => optional($compra->created_at)->format('Y-m-d'),
            ];
        });

        $paginator = new LengthAwarePaginator(
            $transformed,
            $paginator->total(),
            $perPage,
            $page,
            ['path' => $request->url(), 'pageName' => 'page']
        );

        // Estad�sticas para el dashboard
        $stats = [
            'total' => Compra::count(),
            'procesadas' => Compra::where('estado', 'procesada')->count(),
            'canceladas' => Compra::where('estado', 'cancelada')->count(),
            'con_orden_compra' => Compra::whereNotNull('orden_compra_id')->count(),
            'directas' => Compra::whereNull('orden_compra_id')->count(),
        ];

        // Opciones para filtros
        $proveedores = Proveedor::select('id', 'nombre_razon_social', 'rfc')
            ->orderBy('nombre_razon_social')
            ->get()
            ->mapWithKeys(function ($proveedor) {
                return [$proveedor->id => $proveedor->nombre_razon_social . ' (' . $proveedor->rfc . ')'];
            });

        $almacenes = Almacen::select('id', 'nombre', 'ubicacion')
            ->where('estado', 'activo')
            ->orderBy('nombre')
            ->get()
            ->mapWithKeys(function ($almacen) {
                return [$almacen->id => $almacen->nombre . ($almacen->ubicacion ? ' - ' . $almacen->ubicacion : '')];
            });

        $filterOptions = [
            'estados' => [
                ['value' => '', 'label' => 'Todos los Estados'],
                ['value' => 'procesada', 'label' => 'Procesadas'],
                ['value' => 'cancelada', 'label' => 'Canceladas'],
            ],
            'proveedores' => $proveedores,
            'almacenes' => $almacenes,
            'per_page_options' => [10, 15, 25, 50, 100],
        ];

        return Inertia::render('Compras/Index', [
            'compras' => $paginator,
            'stats' => $stats,
            'filterOptions' => $filterOptions,
            'filters' => $request->only(['search', 'estado', 'origen', 'proveedor_id', 'almacen_id', 'fecha_desde', 'fecha_hasta']),
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
            'is_admin' => auth()->check() && auth()->user()->hasRole('admin'),
        ]);
        } catch (\Exception $e) {
            \Log::error('Error en CompraController@index: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Error al cargar la lista de compras.');
        }
    }

    public function create()
    {
        $proveedores = Proveedor::all();

        // Obtener productos con informaci�n de stock por almac�n
        $productosBase = Producto::where('estado', 'activo')->get();
        $almacenes = Almacen::where('estado', 'activo')->get();

        $productos = $productosBase->map(function ($producto) use ($almacenes) {
            // Obtener stock disponible en cada almac�n
            $stockPorAlmacen = [];
            foreach ($almacenes as $almacen) {
                $inventario = \App\Models\Inventario::where('producto_id', $producto->id)
                    ->where('almacen_id', $almacen->id)
                    ->first();

                $stockPorAlmacen[$almacen->id] = [
                    'almacen_id' => $almacen->id,
                    'almacen_nombre' => $almacen->nombre,
                    'cantidad' => $inventario ? $inventario->cantidad : 0,
                ];
            }

            return [
                'id' => $producto->id,
                'codigo' => $producto->codigo,
                'nombre' => $producto->nombre,
                'descripcion' => $producto->descripcion,
                'categoria' => $producto->categoria ? [
                    'id' => $producto->categoria->id,
                    'nombre' => $producto->categoria->nombre,
                ] : null,
                'marca' => $producto->marca ? [
                    'id' => $producto->marca->id,
                    'nombre' => $producto->marca->nombre,
                ] : null,
                'precio_compra' => (float) $producto->precio_compra,
                'precio_venta' => (float) $producto->precio_venta,
                'stock_total' => (int) $producto->stock,
                'stock_por_almacen' => $stockPorAlmacen,
                'expires' => (bool) $producto->expires,
                'requiere_serie' => (bool) ($producto->requiere_serie ?? false),
                'unidad_medida' => $producto->unidad_medida,
                'tipo_producto' => $producto->tipo_producto,
                'estado' => $producto->estado,
            ];
        });

        // Obtener almac�n principal para establecer como predeterminado
        $almacenPrincipal = Almacen::where('estado', 'activo')
            ->where(function ($query) {
                $query->where('nombre', 'Almac�n Principal')
                      ->orWhere('nombre', 'LIKE', '%Principal%');
            })
            ->orderBy('id', 'asc')
            ->first();

        // Si no encuentra almac�n principal, usar el primero activo
        if (!$almacenPrincipal) {
            $almacenPrincipal = Almacen::where('estado', 'activo')
                ->orderBy('id', 'asc')
                ->first();
        }

        return Inertia::render('Compras/Create', [
            'proveedores' => $proveedores,
            'productos' => $productos,
            'almacenes' => $almacenes,
            'almacen_predeterminado' => $almacenPrincipal ? $almacenPrincipal->id : null,
            'recordatorio_almacen' => $almacenPrincipal ? "Almac�n Principal - {$almacenPrincipal->ubicacion}" : null
        ]);
    }

    private function validateCompraRequest(Request $request)
    {
        $rules = [
            'proveedor_id' => 'required|exists:proveedores,id',
            'almacen_id' => 'nullable|exists:almacenes,id',
            'descuento_general' => 'nullable|numeric|min:0',
            'productos' => 'required|array|min:1',
            'productos.*.id' => 'required|exists:productos,id',
            'productos.*.cantidad' => 'required|integer|min:1',
            'productos.*.precio' => 'required|numeric|min:0',
            'productos.*.descuento' => 'nullable|numeric|min:0|max:100',
        ];

        // Agregar validaci�n de lotes para productos que vencen y series por unidad
        foreach ($request->productos ?? [] as $index => $producto) {
            $productoModel = Producto::find($producto['id']);
            if ($productoModel && $productoModel->expires) {
                $rules["productos.{$index}.numero_lote"] = 'required|string|max:100';
                $rules["productos.{$index}.fecha_caducidad"] = 'nullable|date|after:today';
                $rules["productos.{$index}.costo_unitario"] = 'nullable|numeric|min:0';
            }
            if ($productoModel && ($productoModel->requiere_serie ?? false)) {
                $requiredSize = isset($producto['cantidad']) ? max(1, (int) $producto['cantidad']) : 1;
                $rules["productos.{$index}.seriales"] = ['required', 'array', 'size:' . $requiredSize];
                $rules["productos.{$index}.seriales.*"] = 'required|string|max:191|distinct';
            }
        }

        return $request->validate($rules);
    }

    public function store(Request $request)
    {
        $validatedData = $this->validateCompraRequest($request);

        // Asignar almac�n principal si no se especific�
        if (!isset($validatedData['almacen_id']) || $validatedData['almacen_id'] === null) {
            $almacen = Almacen::where('estado', 'activo')->orderBy('id')->first();
            if ($almacen) {
                $validatedData['almacen_id'] = $almacen->id;
            } else {
                return redirect()->back()->with('error', 'No hay almacenes activos disponibles.');
            }
        }

        DB::transaction(function () use ($validatedData) {
            // Calcular totales con descuentos
            $subtotal = 0;
            $descuentoItems = 0;
            $descuentoGeneral = $validatedData['descuento_general'] ?? 0;

            foreach ($validatedData['productos'] as $productoData) {
                $cantidad = $productoData['cantidad'];
                $precio = $productoData['precio'];
                $descuento = $productoData['descuento'] ?? 0;
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

            // Crear compra (autom�ticamente se marca como procesada en el modelo)
            $compra = Compra::create([
                'proveedor_id' => $validatedData['proveedor_id'],
                'almacen_id' => $validatedData['almacen_id'],
                'subtotal' => $subtotal,
                'descuento_items' => $descuentoItems,
                'descuento_general' => $descuentoGeneral,
                'iva' => $iva,
                'total' => $total,
            ]);

            // Crear cuenta por pagar autom�ticamente
            CuentasPorPagar::create([
                'compra_id' => $compra->id,
                'monto_total' => $total,
                'monto_pagado' => 0,
                'monto_pendiente' => $total,
                'fecha_vencimiento' => now()->addDays(30), // 30 d�as por defecto
                'estado' => 'pendiente',
                'notas' => 'Cuenta generada autom�ticamente por compra',
            ]);

            // Procesar productos y aumentar inventario
            foreach ($validatedData['productos'] as $productoData) {
                $producto = Producto::findOrFail($productoData['id']);
                $cantidad = $productoData['cantidad'];
                $precio = $productoData['precio'];
                $descuento = $productoData['descuento'] ?? 0;
                $subtotal = $cantidad * $precio;
                $descuentoMonto = $subtotal * ($descuento / 100);
                $subtotalFinal = $subtotal - $descuentoMonto;

                // Update product price if different
                if ($producto->precio_compra != $precio) {
                    $oldPrecioCompra = $producto->precio_compra;
                    $producto->update(['precio_compra' => $precio]);

                    // Log price change
                    ProductoPrecioHistorial::create([
                        'producto_id' => $producto->id,
                        'precio_compra_anterior' => $oldPrecioCompra,
                        'precio_compra_nuevo' => $precio,
                        'precio_venta_anterior' => null,
                        'precio_venta_nuevo' => $producto->precio_venta,
                        'tipo_cambio' => 'compra',
                        'notas' => "Actualizaci�n por compra #{$compra->id}",
                        'user_id' => Auth::id(),
                    ]);
                }

                // Preparar contexto para entrada
                $contexto = [
                    'motivo' => 'Compra procesada',
                    'almacen_id' => $validatedData['almacen_id'],
                    'user_id' => Auth::id(), // ? Usuario que realiza la compra
                    'referencia_type' => 'App\Models\Compra',
                    'referencia_id' => $compra->id,
                    'detalles' => [
                        'compra_id' => $compra->id,
                        'precio_unitario' => $precio,
                        'descuento' => $descuento,
                        'subtotal' => $subtotalFinal,
                    ],
                ];

                // Agregar informaci�n de lote si el producto vence
                if ($producto->expires) {
                    $contexto['numero_lote'] = $productoData['numero_lote'];
                    $contexto['fecha_caducidad'] = $productoData['fecha_caducidad'] ?? null;
                    $contexto['costo_unitario'] = $productoData['costo_unitario'] ?? $precio;
                }

                // Aumentar stock autom�ticamente al crear la compra
                $this->inventarioService->entrada($producto, $cantidad, $contexto);

                CompraItem::create([
                    'compra_id' => $compra->id,
                    'comprable_id' => $productoData['id'],
                    'comprable_type' => Producto::class,
                    'cantidad' => $cantidad,
                    'precio' => $precio,
                    'descuento' => $descuento,
                    'subtotal' => $subtotalFinal,
                    'descuento_monto' => $descuentoMonto,
                ]);

                // Registrar series si el producto lo requiere
                if (($producto->requiere_serie ?? false) && !empty($productoData['seriales']) && is_array($productoData['seriales'])) {
                    foreach ($productoData['seriales'] as $serie) {
                        // Verificar si la serie ya existe para este producto
                        $serieExistente = \App\Models\ProductoSerie::where('numero_serie', trim((string) $serie))
                            ->where('producto_id', $producto->id)
                            ->first();

                        if ($serieExistente) {
                            throw new \Exception("La serie '{$serie}' ya existe para el producto '{$producto->nombre}'.");
                        }

                        \App\Models\ProductoSerie::create([
                            'producto_id' => $producto->id,
                            'compra_id' => $compra->id,
                            'almacen_id' => $validatedData['almacen_id'],
                            'numero_serie' => trim((string) $serie),
                            'estado' => 'en_stock',
                        ]);
                    }
                }
            }
        });

        return redirect()->route('compras.index')->with('success', 'Compra procesada exitosamente.');
    }

    public function show($id)
    {
        $compra = Compra::with('proveedor', 'almacen', 'cuentasPorPagar')->findOrFail($id);

        // Obtener los items de la compra con informaci�n del stock
        $compraItems = CompraItem::where('compra_id', $id)->with('comprable')->get();

        // Mapeo est�ndar desde compra_items
        $productos = $compraItems->map(function ($item) use ($compra) {
            $producto = $item->comprable;
            $stockActual = $producto ? $producto->stock : 0;
            $cantidadComprada = $item->cantidad;

            // Calcular stock antes de la compra
            $stockAntes = 0;
            if ($compra->estado === EstadoCompra::Cancelada) {
                // Si est� cancelada, el stock actual es menor que la cantidad comprada
                $stockAntes = $stockActual + $cantidadComprada;
            } else {
                // Si est� procesada, el stock actual incluye la cantidad comprada
                $stockAntes = $stockActual - $cantidadComprada;
            }

            return [
                'id' => $item->comprable_id,
                'nombre' => $producto ? $producto->nombre : 'Producto no encontrado',
                'descripcion' => $producto ? $producto->descripcion : '',
                'cantidad' => (int) $item->cantidad,
                'precio' => (float) $item->precio,
                'descuento' => (float) $item->descuento,
                'subtotal' => (float) $item->subtotal,
                'descuento_monto' => (float) $item->descuento_monto,
                'stock_antes' => max(0, $stockAntes), // No mostrar valores negativos
                'stock_despues' => (int) $stockActual,
                'diferencia_stock' => (int) ($stockActual - $stockAntes),
            ];
        });

        // Asegurar que se env�e como arreglo JSON nativo (no Collection)
        $productos = $productos->values()->toArray();

        // Fallback: si por alguna raz�n no existen registros en compra_items,
        // intenta reconstruir los productos desde la relaci�n morfol�gica `productos`
        if (empty($productos)) {
            $compra->loadMissing('productos');
            $productos = $compra->productos->map(function ($p) use ($compra) {
                $stockActual = $p->stock ?? 0;
                $cantidadComprada = (int) ($p->pivot->cantidad ?? 0);
                $stockAntes = $compra->estado === EstadoCompra::Cancelada
                    ? $stockActual + $cantidadComprada
                    : $stockActual - $cantidadComprada;

                return [
                    'id' => $p->id,
                    'nombre' => $p->nombre ?? 'Producto',
                    'descripcion' => $p->descripcion ?? '',
                    'cantidad' => (int) ($p->pivot->cantidad ?? 0),
                    'precio' => (float) ($p->pivot->precio ?? 0),
                    'descuento' => (float) ($p->pivot->descuento ?? 0),
                    'subtotal' => (float) ($p->pivot->subtotal ?? (($p->pivot->cantidad ?? 0) * ($p->pivot->precio ?? 0))),
                    'descuento_monto' => (float) ($p->pivot->descuento_monto ?? 0),
                    'stock_antes' => max(0, (int) $stockAntes),
                    'stock_despues' => (int) $stockActual,
                    'diferencia_stock' => (int) ($stockActual - $stockAntes),
                ];
            })->values()->toArray();
        }

        $compra->productos = $productos;

        // Asegurar que los valores num�ricos sean float
        $compra->subtotal = (float) $compra->subtotal;
        $compra->descuento_items = (float) $compra->descuento_items;
        $compra->descuento_general = (float) $compra->descuento_general;
        $compra->iva = (float) $compra->iva;
        $compra->total = (float) $compra->total;

        // Permisos: solo mostrar eliminar si es admin y la compra est� cancelada
        $isAdmin = auth()->check() && auth()->user()->hasRole('admin');
        $canDelete = $isAdmin && ($compra->estado === EstadoCompra::Cancelada);

        return Inertia::render('Compras/Show', [
            'compra' => $compra,
            'canDelete' => $canDelete,
        ]);
    }

    public function edit($id)
    {
        $compra = Compra::with('proveedor', 'productos', 'almacen')->findOrFail($id);

        $compra->productos = $compra->productos->map(function ($producto) use ($compra) {
            $seriales = ProductoSerie::where('compra_id', $compra->id)
                ->where('producto_id', $producto->id)
                ->pluck('numero_serie')
                ->values()
                ->toArray();

            return [
                'id' => $producto->id,
                'nombre' => $producto->nombre,
                'descripcion' => $producto->descripcion,
                'cantidad' => $producto->pivot->cantidad,
                'precio' => $producto->pivot->precio,
                'descuento' => $producto->pivot->descuento,
                'subtotal' => $producto->pivot->subtotal,
                'descuento_monto' => $producto->pivot->descuento_monto,
                'requiere_serie' => (bool) ($producto->requiere_serie ?? false),
                'seriales' => $seriales,
            ];
        });

        $proveedores = Proveedor::all();

        // Obtener productos con informaci�n de stock por almac�n (igual que en create)
        $productosBase = Producto::where('estado', 'activo')->get();
        $almacenes = Almacen::where('estado', 'activo')->get();

        $productos = $productosBase->map(function ($producto) use ($almacenes) {
            // Obtener stock disponible en cada almac�n
            $stockPorAlmacen = [];
            foreach ($almacenes as $almacen) {
                $inventario = \App\Models\Inventario::where('producto_id', $producto->id)
                    ->where('almacen_id', $almacen->id)
                    ->first();

                $stockPorAlmacen[$almacen->id] = [
                    'almacen_id' => $almacen->id,
                    'almacen_nombre' => $almacen->nombre,
                    'cantidad' => $inventario ? $inventario->cantidad : 0,
                ];
            }

            return [
                'id' => $producto->id,
                'codigo' => $producto->codigo,
                'nombre' => $producto->nombre,
                'descripcion' => $producto->descripcion,
                'categoria' => $producto->categoria ? [
                    'id' => $producto->categoria->id,
                    'nombre' => $producto->categoria->nombre,
                ] : null,
                'marca' => $producto->marca ? [
                    'id' => $producto->marca->id,
                    'nombre' => $producto->marca->nombre,
                ] : null,
                'precio_compra' => (float) $producto->precio_compra,
                'precio_venta' => (float) $producto->precio_venta,
                'stock_total' => (int) $producto->stock,
                'stock_por_almacen' => $stockPorAlmacen,
                'expires' => (bool) $producto->expires,
                'requiere_serie' => (bool) ($producto->requiere_serie ?? false),
                'unidad_medida' => $producto->unidad_medida,
                'tipo_producto' => $producto->tipo_producto,
                'estado' => $producto->estado,
            ];
        });

        // Obtener almac�n principal para establecer como predeterminado
        $almacenPrincipal = Almacen::where('estado', 'activo')
            ->where(function ($query) {
                $query->where('nombre', 'Almac�n Principal')
                      ->orWhere('nombre', 'LIKE', '%Principal%');
            })
            ->orderBy('id', 'asc')
            ->first();

        // Si no encuentra almac�n principal, usar el primero activo
        if (!$almacenPrincipal) {
            $almacenPrincipal = Almacen::where('estado', 'activo')
                ->orderBy('id', 'asc')
                ->first();
        }

        // Crear el array de compra con toda la informaci�n necesaria
        $compraData = array_merge($compra->toArray(), [
            'almacen_id' => $compra->almacen_id,
            'almacen' => $compra->almacen ? [
                'id' => $compra->almacen->id,
                'nombre' => $compra->almacen->nombre,
                'ubicacion' => $compra->almacen->ubicacion ?? null,
            ] : null,
        ]);

        return Inertia::render('Compras/Edit', [
            'compra' => $compraData,
            'proveedores' => $proveedores,
            'productos' => $productos,
            'almacenes' => $almacenes,
            'almacen_predeterminado' => $compra->almacen_id ?? ($almacenPrincipal ? $almacenPrincipal->id : null),
            'recordatorio_almacen' => $compra->almacen ? "Almac�n Actual: {$compra->almacen->nombre}" .
                (isset($compra->almacen->ubicacion) ? " - {$compra->almacen->ubicacion}" : "") :
                ($almacenPrincipal ? "Almac�n Principal - {$almacenPrincipal->ubicacion}" : null)
        ]);
    }

    public function update(Request $request, $id)
    {
        $compra = Compra::with('productos')->findOrFail($id);

        // Solo se pueden editar compras procesadas
        if ($compra->estado !== EstadoCompra::Procesada) {
            return redirect()->back()->with('error', 'Solo se pueden editar compras procesadas.');
        }

        $validatedData = $this->validateCompraRequest($request);

        DB::transaction(function () use ($compra, $validatedData) {
            // Validar que no existan series vendidas asociadas a esta compra
            $seriesVendidas = \App\Models\ProductoSerie::where('compra_id', $compra->id)
                ->where('estado', '!=', 'en_stock')
                ->exists();

            if ($seriesVendidas) {
                throw new \Exception('No se puede editar la compra porque algunos productos (series) ya han sido vendidos o dados de baja.');
            }

            // Eliminar series antiguas asociadas a esta compra
            \App\Models\ProductoSerie::where('compra_id', $compra->id)->delete();

            // Guardar items antiguos antes de eliminar
            $oldItems = $compra->productos;

            // Restar cantidades antiguas del stock
            foreach ($oldItems as $item) {
                $producto = Producto::find($item->comprable_id);
                if (!$producto) {
                    throw new \Exception("Producto con ID {$item->comprable_id} no encontrado");
                }

                $this->inventarioService->salida($producto, $item->cantidad, [
                    'motivo' => 'Edici�n de compra: reversa de stock previo',
                    'almacen_id' => $compra->almacen_id,
                    'user_id' => Auth::id(), // ? Usuario que edita la compra
                    'referencia_type' => 'App\Models\Compra',
                    'referencia_id' => $compra->id,
                    'detalles' => [
                        'compra_id' => $compra->id,
                        'compra_item_id' => $item->id,
                    ],
                ]);
            }

            // Calcular totales con descuentos
            $subtotal = 0;
            $descuentoItems = 0;
            $descuentoGeneral = $validatedData['descuento_general'] ?? 0;

            foreach ($validatedData['productos'] as $productoData) {
                $cantidad = $productoData['cantidad'];
                $precio = $productoData['precio'];
                $descuento = $productoData['descuento'] ?? 0;
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

            // Actualizar la compra
            // Actualizar la compra
            $compra->update([
                'proveedor_id' => $validatedData['proveedor_id'],
                'almacen_id' => $validatedData['almacen_id'],
                'subtotal' => $subtotal,
                'descuento_items' => $descuentoItems,
                'descuento_general' => $descuentoGeneral,
                'iva' => $iva,
                'total' => $total,
            ]);

            // Actualizar Cuentas por Pagar
            $cuentaPorPagar = CuentasPorPagar::where('compra_id', $compra->id)->first();
            if ($cuentaPorPagar) {
                // Calcular nuevo saldo pendiente
                // Asumimos que si se edita, se recalcula sobre lo pagado
                $montoPagado = $cuentaPorPagar->monto_pagado;
                $nuevoPendiente = $total - $montoPagado;

                $cuentaPorPagar->update([
                    'monto_total' => $total,
                    'monto_pendiente' => $nuevoPendiente,
                    'estado' => $nuevoPendiente <= 0 ? 'pagada' : ($montoPagado > 0 ? 'parcial' : 'pendiente'),
                ]);
            } else {
                // Si no existe (por error de datos antiguos), crearla
                CuentasPorPagar::create([
                    'compra_id' => $compra->id,
                    'monto_total' => $total,
                    'monto_pagado' => 0,
                    'monto_pendiente' => $total,
                    'fecha_vencimiento' => now()->addDays(30),
                    'estado' => 'pendiente',
                    'notas' => 'Cuenta regenerada por edición de compra',
                ]);
            }

            // Eliminar items antiguos
            $compra->productos()->delete();

            // Crear items nuevos y agregar al stock
            foreach ($validatedData['productos'] as $productoData) {
                $producto = Producto::findOrFail($productoData['id']);

                $cantidad = $productoData['cantidad'];
                $precio = $productoData['precio'];
                $descuento = $productoData['descuento'] ?? 0;
                $subtotal = $cantidad * $precio;
                $descuentoMonto = $subtotal * ($descuento / 100);
                $subtotalFinal = $subtotal - $descuentoMonto;

                // Update product price if different
                if ($producto->precio_compra != $precio) {
                    $oldPrecioCompra = $producto->precio_compra;
                    $producto->update(['precio_compra' => $precio]);

                    // Log price change
                    ProductoPrecioHistorial::create([
                        'producto_id' => $producto->id,
                        'precio_compra_anterior' => $oldPrecioCompra,
                        'precio_compra_nuevo' => $precio,
                        'precio_venta_anterior' => null,
                        'precio_venta_nuevo' => $producto->precio_venta,
                        'tipo_cambio' => 'compra',
                        'notas' => "Actualizaci�n por edici�n de compra #{$compra->id}",
                        'user_id' => Auth::id(),
                    ]);
                }

                $this->inventarioService->entrada($producto, $cantidad, [
                    'motivo' => 'Edici�n de compra: stock actualizado',
                    'almacen_id' => $validatedData['almacen_id'],
                    'user_id' => Auth::id(), // ? Usuario que edita la compra
                    'referencia_type' => 'App\Models\Compra',
                    'referencia_id' => $compra->id,
                    'detalles' => [
                        'compra_id' => $compra->id,
                        'producto_id' => $productoData['id'],
                        'precio_unitario' => $precio,
                        'descuento' => $descuento,
                    ],
                ]);

                CompraItem::create([
                    'compra_id' => $compra->id,
                    'comprable_id' => $productoData['id'],
                    'comprable_type' => Producto::class,
                    'cantidad' => $cantidad,
                    'precio' => $precio,
                    'descuento' => $descuento,
                    'subtotal' => $subtotalFinal,
                    'descuento_monto' => $descuentoMonto,
                ]);

                // Registrar series si el producto lo requiere (NUEVAS SERIES)
                if (($producto->requiere_serie ?? false) && !empty($productoData['seriales']) && is_array($productoData['seriales'])) {
                    foreach ($productoData['seriales'] as $serie) {
                        // Verificar si la serie ya existe para este producto (aunque acabamos de borrar las de esta compra, podría haber conflicto con otras)
                        $serieExistente = \App\Models\ProductoSerie::where('numero_serie', trim((string) $serie))
                            ->where('producto_id', $producto->id)
                            ->first();

                        if (!$serieExistente) {
                            \App\Models\ProductoSerie::create([
                                'producto_id' => $producto->id,
                                'compra_id' => $compra->id,
                                'almacen_id' => $validatedData['almacen_id'],
                                'numero_serie' => trim((string) $serie),
                                'estado' => 'en_stock',
                            ]);
                        }
                    }
                }
            }
        });

        return redirect()->route('compras.index')->with('success', 'Compra actualizada exitosamente.');
    }

    public function actualizarSeries(Request $request, $id)
    {
        $compra = Compra::with('productos')->findOrFail($id);

        if ($compra->estado !== EstadoCompra::Procesada) {
            return redirect()->back()->with('error', 'Solo se pueden editar series de compras procesadas.');
        }

        $productosConSerie = $compra->productos->filter(function ($producto) {
            return (bool) ($producto->requiere_serie ?? false);
        });

        if ($productosConSerie->isEmpty()) {
            return redirect()->back()->with('info', 'Esta compra no tiene productos que requieran series.');
        }

        $rules = [
            'productos' => 'required|array|min:1',
        ];

        $productoIdsEsperados = $productosConSerie->pluck('id')->toArray();

        foreach ($productosConSerie->values() as $index => $producto) {
            $requiredSize = max(1, (int) ($producto->pivot->cantidad ?? 1));
            $rules["productos.{$index}.id"] = 'required|integer|in:' . $producto->id;
            $rules["productos.{$index}.seriales"] = ['required', 'array', 'size:' . $requiredSize];
            $rules["productos.{$index}.seriales.*"] = 'required|string|max:191|distinct';
        }

        $validated = $request->validate($rules);

        $serialesPorProducto = [];
        foreach ($validated['productos'] as $productoData) {
            $pid = (int) $productoData['id'];
            $seriales = array_map(function ($serie) {
                return trim((string) $serie);
            }, $productoData['seriales'] ?? []);

            $serialesPorProducto[$pid] = $seriales;
        }

        // Validar que vengan todos los productos que requieren serie
        sort($productoIdsEsperados);
        $productoIdsRecibidos = array_keys($serialesPorProducto);
        sort($productoIdsRecibidos);
        if ($productoIdsEsperados !== $productoIdsRecibidos) {
            return redirect()->back()->with('error', 'Debes capturar series para todos los productos que lo requieren.');
        }

        // Validar que no existan series vendidas asociadas a esta compra
        $seriesVendidas = ProductoSerie::where('compra_id', $compra->id)
            ->where('estado', '!=', 'en_stock')
            ->exists();

        if ($seriesVendidas) {
            return redirect()->back()->with('error', 'No se pueden editar las series porque algunas ya fueron usadas (vendidas o dadas de baja).');
        }

        DB::transaction(function () use ($compra, $serialesPorProducto) {
            // Eliminar series previas de esta compra
            ProductoSerie::where('compra_id', $compra->id)->delete();

            foreach ($serialesPorProducto as $productoId => $seriales) {
                foreach ($seriales as $serie) {
                    // Validar que la serie no exista para este producto en otra compra
                    $existe = ProductoSerie::where('producto_id', $productoId)
                        ->where('numero_serie', $serie)
                        ->where('compra_id', '!=', $compra->id)
                        ->exists();

                    if ($existe) {
                        throw new \Exception("La serie '{$serie}' ya existe para este producto en otra compra.");
                    }

                    ProductoSerie::create([
                        'producto_id' => $productoId,
                        'compra_id' => $compra->id,
                        'almacen_id' => $compra->almacen_id,
                        'numero_serie' => $serie,
                        'estado' => 'en_stock',
                    ]);
                }
            }
        });

        return redirect()->back()->with('success', 'Series actualizadas correctamente.');
    }

    /**
     * Cancelar la compra y disminuir inventario
     */
    public function cancel($id)
    {
        $compra = Compra::findOrFail($id);

        // Solo se puede cancelar si est procesada
        if ($compra->estado !== EstadoCompra::Procesada) {
            return Redirect::back()->with('error', 'Solo se pueden cancelar compras procesadas');
        }

        DB::transaction(function () use ($compra) {
            // 1. Validar Series Vendidas
            $seriesVendidas = \App\Models\ProductoSerie::where('compra_id', $compra->id)
                ->where('estado', '!=', 'en_stock')
                ->exists();

            if ($seriesVendidas) {
                throw new \Exception('No se puede cancelar la compra porque algunos productos (series) ya han sido vendidos o dados de baja.');
            }

            // Obtener los items de la compra
            $compraItems = CompraItem::where('compra_id', $compra->id)->get();

            // 2. Validar Stock General (para productos sin serie o validación adicional)
            foreach ($compraItems as $item) {
                $producto = Producto::find($item->comprable_id);
                if (!$producto) {
                    continue;
                }

                // Si el producto requiere serie, la validación anterior ya cubrió lo más importante.
                // Pero verificamos stock global por seguridad.
                if ($producto->stock < $item->cantidad) {
                     throw new \Exception("No se puede cancelar: Stock insuficiente del producto '{$producto->nombre}' para revertir la compra.");
                }
            }

            // 3. Revertir Stock
            foreach ($compraItems as $item) {
                $producto = Producto::find($item->comprable_id);
                if ($producto) {
                    $this->inventarioService->salida($producto, $item->cantidad, [
                        'motivo' => 'Cancelación de compra',
                        'almacen_id' => $compra->almacen_id,
                        'user_id' => Auth::id(), // ? Usuario que cancela la compra
                        'referencia_type' => 'App\Models\Compra',
                        'referencia_id' => $compra->id,
                        'detalles' => [
                            'compra_id' => $compra->id,
                            'compra_item_id' => $item->id,
                        ],
                    ]);
                }
            }

            // 4. Eliminar Series
            \App\Models\ProductoSerie::where('compra_id', $compra->id)->delete();

            // 5. Cancelar Cuentas por Pagar
            $cuentaPorPagar = CuentasPorPagar::where('compra_id', $compra->id)->first();
            if ($cuentaPorPagar) {
                if ($cuentaPorPagar->monto_pagado > 0) {
                     // Si hay pagos parciales, marcamos como cancelada pero mantenemos registro
                     $cuentaPorPagar->update([
                         'estado' => 'cancelada',
                         'monto_pendiente' => 0,
                         'notas' => ($cuentaPorPagar->notas ? $cuentaPorPagar->notas . " | " : "") . 'Cancelada por cancelación de compra'
                     ]);
                } else {
                    // Si no hay pagos, eliminamos el registro
                    $cuentaPorPagar->delete();
                }
            }

            // Cambiar estado a cancelado
            $compra->update([
                'estado' => EstadoCompra::Cancelada,
            ]);

            // Si la compra viene de una orden de compra (OCC-), cambiar la orden a pendiente
            if ($compra->orden_compra_id) {
                $ordenCompra = \App\Models\OrdenCompra::find($compra->orden_compra_id);
                if ($ordenCompra) {
                    $ordenCompra->update([
                        'estado' => 'pendiente',
                        'observaciones' => ($ordenCompra->observaciones ? $ordenCompra->observaciones . "\n\n" : '') .
                            '*** COMPRA CANCELADA - ORDEN REGRESADA A PENDIENTE *** ' . now()->format('d/m/Y H:i')
                    ]);
                }
            }
        });

        $mensaje = 'Compra cancelada exitosamente.';
        if ($compra->orden_compra_id) {
            $mensaje .= ' La orden de compra asociada ha sido regresada a estado pendiente.';
        }

        return Redirect::route('compras.index')->with('success', $mensaje);
    }

    public function destroy($id)
    {
        $compra = Compra::findOrFail($id);

        // Si esta cancelada, permitir eliminar solo a administradores (soft delete)
        if ($compra->estado === EstadoCompra::Cancelada) {
            if (!auth()->check() || !auth()->user()->hasRole('admin')) {
                return redirect()->back()->with('error', 'Solo un usuario administrador puede eliminar compras canceladas.');
            }

            $compra->delete();
            return redirect()->route('compras.index')->with('success', 'Compra eliminada correctamente.');
        }

        // Si no esta cancelada, usar cancelacion para mantener consistencia
        return $this->cancel($id);
    }

    /**
     * Eliminación definitiva (hard delete) solo para administradores y compras canceladas.
     */
    public function forceDestroy($id)
    {
        $compra = Compra::withTrashed()->findOrFail($id);

        if (!auth()->check() || !auth()->user()->hasRole('admin')) {
            return redirect()->back()->with('error', 'Solo un usuario administrador puede eliminar definitivamente compras.');
        }

        // Por integridad, solo permitir hard delete si está cancelada
        if ($compra->estado !== EstadoCompra::Cancelada) {
            return redirect()->back()->with('error', 'Solo se pueden eliminar definitivamente compras canceladas.');
        }

        if (is_null($compra->deleted_at)) {
            $compra->delete();
        }

        $compra->forceDelete();

        return redirect()->route('compras.index')->with('success', 'Compra eliminada definitivamente de la base de datos.');
    }

    /**
     * Genera un número de compra único secuencial.
     */
    private function generarNumeroCompra()
    {
        // Buscar el último número de compra existente
        $ultimaCompra = Compra::where('numero_compra', 'LIKE', 'C%')
            ->orderBy('id', 'desc')
            ->first();

        if (!$ultimaCompra || !$ultimaCompra->numero_compra) {
            return 'C0001';
        }

        // Extraer el número de la compra
        $matches = [];
        if (preg_match('/C(\d+)$/', $ultimaCompra->numero_compra, $matches)) {
            $ultimoNumero = (int) $matches[1];
            $siguienteNumero = $ultimoNumero + 1;
            $nuevoNumero = 'C' . str_pad($siguienteNumero, 4, '0', STR_PAD_LEFT);

            // Verificar que no exista ya (evitar colisiones)
            while (Compra::where('numero_compra', $nuevoNumero)->exists()) {
                $siguienteNumero++;
                $nuevoNumero = 'C' . str_pad($siguienteNumero, 4, '0', STR_PAD_LEFT);
            }

            return $nuevoNumero;
        }

        // Si no se puede extraer el número, empezar desde C0001
        return 'C0001';
    }

    /**
     * Obtiene el siguiente número de compra para mostrar en el frontend.
     */
    public function obtenerSiguienteNumero()
    {
        $siguienteNumero = $this->generarNumeroCompra();
        return response()->json(['siguiente_numero' => $siguienteNumero]);
    }

}
