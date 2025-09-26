<?php

namespace App\Http\Controllers;

use App\Models\Compra;
use App\Models\CompraItem;
use App\Models\Proveedor;
use App\Models\Producto;
use App\Enums\EstadoCompra;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Inertia\Inertia;
use Illuminate\Pagination\LengthAwarePaginator;

class CompraController extends Controller
{
    public function index(Request $request)
    {
        $perPage = (int) ($request->integer('per_page') ?: 10);
        $page    = max(1, (int) $request->get('page', 1));

        $baseQuery = Compra::with([
            'proveedor',
            'productos',
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

        // Ordenamiento
        $sortBy = $request->get('sort_by', 'created_at');
        $sortDirection = $request->get('sort_direction', 'desc');

        $allowedSorts = ['created_at', 'total', 'estado'];
        if (!in_array($sortBy, $allowedSorts)) {
            $sortBy = 'created_at';
        }

        $baseQuery->orderBy($sortBy, $sortDirection === 'asc' ? 'asc' : 'desc');

        $paginator = $baseQuery->paginate($perPage, ['*'], 'page', $page);
        $compras = collect($paginator->items());

        $transformed = $compras->map(function ($compra) {
            $compra->load('productos');
            $compra->productos = $compra->productos->map(function ($p) {
                return array_merge($p->toArray(), [
                    'cantidad' => $p->pivot->cantidad ?? 0,
                    'precio' => $p->pivot->precio ?? 0,
                    'descuento' => $p->pivot->descuento ?? 0,
                    'subtotal' => $p->pivot->subtotal ?? 0,
                    'descuento_monto' => $p->pivot->descuento_monto ?? 0,
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
                'productos' => $items,
                'productos_count' => $items->count(),
                'productos_tooltip' => $productosTooltip ?: 'Sin productos',
                'total' => (float) ($compra->total ?? 0),
                'estado' => $compra->estado ?? 'procesada',
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

        // Estadísticas para el dashboard
        $stats = [
            'total' => Compra::count(),
            'procesadas' => Compra::where('estado', EstadoCompra::Procesada)->count(),
            'canceladas' => Compra::where('estado', EstadoCompra::Cancelada)->count(),
        ];

        return Inertia::render('Compras/Index', [
            'compras' => $paginator,
            'stats' => $stats,
            'filters' => $request->only(['search', 'estado']),
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

    public function create()
    {
        $proveedores = Proveedor::all();
        $productos = Producto::all();
        return Inertia::render('Compras/Create', ['proveedores' => $proveedores, 'productos' => $productos]);
    }

    private function validateCompraRequest(Request $request)
    {
        return $request->validate([
            'proveedor_id' => 'required|exists:proveedores,id',
            'productos' => 'required|array|min:1',
            'productos.*.id' => 'required|exists:productos,id',
            'productos.*.cantidad' => 'required|integer|min:1',
            'productos.*.precio' => 'required|numeric|min:0',
        ]);
    }

    public function store(Request $request)
    {
        $validatedData = $this->validateCompraRequest($request);

        DB::transaction(function () use ($validatedData) {
            // Calcular total con descuentos
            $total = 0;
            foreach ($validatedData['productos'] as $productoData) {
                $cantidad = $productoData['cantidad'];
                $precio = $productoData['precio'];
                $descuento = $productoData['descuento'] ?? 0;
                $subtotal = $cantidad * $precio;
                $descuentoMonto = $subtotal * ($descuento / 100);
                $total += $subtotal - $descuentoMonto;
            }

            // Crear compra (automáticamente se marca como procesada en el modelo)
            $compra = Compra::create([
                'proveedor_id' => $validatedData['proveedor_id'],
                'total' => $total,
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

                // Aumentar stock automáticamente al crear la compra
                $producto->stock += $cantidad;
                $producto->save();

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
            }
        });

        return redirect()->route('compras.index')->with('success', 'Compra procesada exitosamente.');
    }

    public function show($id)
    {
        $compra = Compra::with('proveedor', 'productos')->findOrFail($id);
        $compra->productos = $compra->productos->map(function ($producto) {
            return [
                'id' => $producto->id,
                'nombre' => $producto->nombre,
                'descripcion' => $producto->descripcion,
                'cantidad' => $producto->pivot->cantidad,
                'precio' => $producto->pivot->precio,
                'descuento' => $producto->pivot->descuento,
                'subtotal' => $producto->pivot->subtotal,
                'descuento_monto' => $producto->pivot->descuento_monto,
            ];
        });
        return Inertia::render('Compras/Show', ['compra' => $compra]);
    }

    public function edit($id)
    {
        $compra = Compra::with('proveedor', 'productos')->findOrFail($id);
        $compra->productos = $compra->productos->map(function ($producto) {
            return [
                'id' => $producto->id,
                'nombre' => $producto->nombre,
                'descripcion' => $producto->descripcion,
                'cantidad' => $producto->pivot->cantidad,
                'precio' => $producto->pivot->precio,
                'descuento' => $producto->pivot->descuento,
                'subtotal' => $producto->pivot->subtotal,
                'descuento_monto' => $producto->pivot->descuento_monto,
            ];
        });
        $proveedores = Proveedor::all();
        $productos = Producto::all();
        return Inertia::render('Compras/Edit', ['compra' => $compra, 'proveedores' => $proveedores, 'productos' => $productos]);
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
            // Restar cantidades antiguas del stock
            foreach ($compra->productos as $item) {
                $producto = $item->comprable;
                $producto->stock -= $item->cantidad;
                if ($producto->stock < 0) {
                    throw new \Exception("El stock del producto '{$producto->nombre}' no puede ser negativo.");
                }
                $producto->save();
            }

            // Calcular total con descuentos
            $total = 0;
            foreach ($validatedData['productos'] as $productoData) {
                $cantidad = $productoData['cantidad'];
                $precio = $productoData['precio'];
                $descuento = $productoData['descuento'] ?? 0;
                $subtotal = $cantidad * $precio;
                $descuentoMonto = $subtotal * ($descuento / 100);
                $total += $subtotal - $descuentoMonto;
            }

            // Actualizar la compra
            $compra->update([
                'proveedor_id' => $validatedData['proveedor_id'],
                'total' => $total,
            ]);

            // Eliminar items antiguos
            $compra->productos()->delete();

            // Crear items nuevos y agregar al stock
            foreach ($validatedData['productos'] as $productoData) {
                $producto = Producto::findOrFail($productoData['id']);
                $producto->stock += $productoData['cantidad'];
                if ($producto->stock < 0) {
                    throw new \Exception("El stock del producto '{$producto->nombre}' no puede ser negativo.");
                }
                $producto->save();

                $cantidad = $productoData['cantidad'];
                $precio = $productoData['precio'];
                $descuento = $productoData['descuento'] ?? 0;
                $subtotal = $cantidad * $precio;
                $descuentoMonto = $subtotal * ($descuento / 100);
                $subtotalFinal = $subtotal - $descuentoMonto;

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
            }
        });

        return redirect()->route('compras.index')->with('success', 'Compra actualizada exitosamente.');
    }

    /**
     * Cancelar la compra y disminuir inventario
     */
    public function cancel($id)
    {
        $compra = Compra::with('productos')->findOrFail($id);

        // Solo se puede cancelar si está procesada
        if ($compra->estado !== EstadoCompra::Procesada) {
            return Redirect::back()->with('error', 'Solo se pueden cancelar compras procesadas');
        }

        DB::transaction(function () use ($compra) {
            // Verificar si los productos han sido vendidos
            $productosVendidos = [];
            foreach ($compra->productos as $item) {
                $producto = $item->comprable;
                $stockActual = $producto->stock;
                $cantidadComprada = $item->cantidad;

                // Si el stock actual es menor que la cantidad comprada,
                // significa que se han vendido productos de esta compra
                if ($stockActual < $cantidadComprada) {
                    $productosVendidos[] = $producto->nombre;
                }
            }

            // Si hay productos vendidos, no permitir la cancelación
            if (!empty($productosVendidos)) {
                throw new \Exception(
                    'No se puede cancelar la compra porque los siguientes productos ya han sido vendidos: ' .
                    implode(', ', $productosVendidos)
                );
            }

            // Disminuir inventario de todos los productos
            foreach ($compra->productos as $item) {
                $producto = $item->comprable;
                $producto->stock -= $item->cantidad;
                $producto->save();
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

        // Si ya está cancelada, no hacer nada
        if ($compra->estado === EstadoCompra::Cancelada) {
            return redirect()->route('compras.index')->with('error', 'La compra ya está cancelada.');
        }

        // Usar el método cancel para mantener consistencia
        return $this->cancel($id);
    }
}
