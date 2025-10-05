<?php

namespace App\Http\Controllers;

use App\Models\Producto;
use App\Models\Servicio;
use App\Models\Categoria;
use App\Models\Marca;
use App\Models\Proveedor;
use App\Models\Almacen;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;

class ProductoController extends Controller
{
    /**
     * Muestra una lista de todos los productos con paginación y filtros.
     */
    public function index(Request $request)
    {
        try {
            $query = Producto::query()->with(['categoria', 'marca', 'proveedor', 'almacen']);

            // Filtros
            if ($search = trim($request->input('search', ''))) {
                $query->where(function ($q) use ($search) {
                    $q->where('nombre', 'like', "%{$search}%")
                      ->orWhere('codigo', 'like', "%{$search}%")
                      ->orWhere('codigo_barras', 'like', "%{$search}%")
                      ->orWhere('descripcion', 'like', "%{$search}%");
                });
            }

            if ($estado = $request->input('estado')) {
                if ($estado === 'activo') {
                    $query->where('estado', 'activo');
                } elseif ($estado === 'inactivo') {
                    $query->where('estado', 'inactivo');
                } elseif ($estado === 'agotado') {
                    $query->where('stock', '<=', 0);
                }
            }

            // Ordenamiento
            $sortBy = $request->input('sort_by', 'nombre');
            $sortDirection = $request->input('sort_direction', 'asc');

            $validSortFields = ['nombre', 'codigo', 'precio_venta', 'stock', 'created_at'];
            if (!in_array($sortBy, $validSortFields)) {
                $sortBy = 'nombre';
            }

            $query->orderBy($sortBy, $sortDirection);

            // Paginación
            $perPage = min((int) $request->input('per_page', 10), 50);
            $productos = $query->paginate($perPage);

            // Agregar permisos a cada producto
            foreach ($productos->items() as $producto) {
                $producto->can_delete = $this->canDeleteProducto($producto);
                $producto->can_toggle_in_index = false; // No mostrar botón de cambiar estado en el índice
                $producto->can_toggle_in_modal = true; // Sí mostrar en el modal
            }

            // Estadísticas basadas en estado del producto
            $stats = [
                'total' => Producto::count(),
                'activos' => Producto::where('estado', 'activo')->count(),
                'inactivos' => Producto::where('estado', 'inactivo')->count(),
                'agotado' => Producto::where('stock', '<=', 0)->count(),
            ];

            return Inertia::render('Productos/Index', [
                'productos' => $productos,
                'stats' => $stats,
                'filters' => $request->only(['search', 'estado']),
                'sorting' => ['sort_by' => $sortBy, 'sort_direction' => $sortDirection],
            ]);
        } catch (\Exception $e) {
            Log::error('Error en ProductoController@index: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Error al cargar los productos.');
        }
    }

    /**
     * Muestra el formulario para crear un nuevo producto.
     */
    public function create()
    {
        return Inertia::render('Productos/Create', [
            'categorias'  => Categoria::select('id', 'nombre')->get(),
            'marcas'      => Marca::select('id', 'nombre')->get(),
            'proveedores' => Proveedor::select('id', 'nombre_razon_social')->get(),
            'almacenes'   => Almacen::select('id', 'nombre')->get(),
        ]);
    }

    /**
     * Almacena un nuevo producto en la base de datos.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'nombre'            => 'required|string|max:255',
            'descripcion'       => 'nullable|string',
            'codigo'            => 'required|string|unique:productos,codigo',
            'codigo_barras'     => 'required|string|unique:productos,codigo_barras',
            'numero_serie'      => 'nullable|string',
            'categoria_id'      => 'required|exists:categorias,id',
            'marca_id'          => 'required|exists:marcas,id',
            'proveedor_id'      => 'nullable|exists:proveedores,id',
            'almacen_id'        => 'nullable|exists:almacenes,id',
            'stock'             => 'required|integer|min:0',
            'stock_minimo'      => 'required|integer|min:0',
            'precio_compra'     => 'required|numeric|min:0',
            'precio_venta'      => 'required|numeric|min:0',
            'impuesto'          => 'required|numeric|min:0',
            'unidad_medida'     => 'required|string',
            'fecha_vencimiento' => 'nullable|date',
            'tipo_producto'     => 'required|in:fisico,digital',
            'imagen'            => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
            'estado'            => 'required|in:activo,inactivo',
            'comision_vendedor' => 'nullable|numeric|min:0|max:100',
        ]);

        if ($request->hasFile('imagen')) {
            $validated['imagen'] = $request->file('imagen')->store('productos', 'public');
        }

        Producto::create($validated);

        return redirect()->route('productos.index')->with('success', 'Producto creado correctamente.');
    }

    /**
     * Muestra el formulario para editar un producto existente.
     */
    public function edit($id)
    {
        $producto    = Producto::findOrFail($id);
        $categorias  = Categoria::all(['id', 'nombre']);
        $marcas      = Marca::all(['id', 'nombre']);
        $proveedores = Proveedor::all(['id', 'nombre_razon_social']);
        $almacenes   = Almacen::all(['id', 'nombre']);

        return Inertia::render('Productos/Edit', [
            'producto'    => $producto,
            'categorias'  => $categorias,
            'marcas'      => $marcas,
            'proveedores' => $proveedores,
            'almacenes'   => $almacenes,
        ]);
    }

    /**
     * Actualiza un producto existente en la base de datos.
     */
    public function update(Request $request, Producto $producto)
    {
        $validated = $request->validate([
            // Datos requeridos
            'nombre'        => 'required|string|max:255',
            'descripcion'   => 'nullable|string|max:1000',
            'precio_venta'  => 'required|numeric|min:0',
            'stock'         => 'required|integer|min:0',
            'categoria_id'  => 'required|exists:categorias,id',
            'proveedor_id'  => 'nullable|exists:proveedores,id',

            // Validaciones para editar (permitir mismos códigos del propio producto)
            'codigo'        => 'nullable|string|unique:productos,codigo,' . $producto->id,
            'codigo_barras' => 'nullable|string|unique:productos,codigo_barras,' . $producto->id,

            'numero_serie'  => 'nullable|string',
            'marca_id'      => 'required|exists:marcas,id',
            'almacen_id'    => 'nullable|exists:almacenes,id',
            'stock_minimo'  => 'nullable|integer|min:0',
            'precio_compra' => 'nullable|numeric|min:0',
            'impuesto'      => 'nullable|numeric|min:0',
            'unidad_medida' => 'nullable|string',
            'fecha_vencimiento' => 'nullable|date',
            'tipo_producto' => 'nullable|in:fisico,digital',
            'estado'        => 'nullable|in:activo,inactivo',
            'comision_vendedor' => 'nullable|numeric|min:0|max:100',

            // Imagen
            'imagen'        => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
        ]);

        if ($request->hasFile('imagen')) {
            if ($producto->imagen) {
                Storage::disk('public')->delete($producto->imagen);
            }
            $validated['imagen'] = $request->file('imagen')->store('productos', 'public');
        }

        $producto->update($validated);

        return redirect()->route('productos.index')->with('success', 'Producto actualizado correctamente.');
    }

    /**
     * Elimina un producto de la base de datos.
     */
    public function destroy(Producto $producto)
    {
        // Verificar si puede ser eliminado usando la nueva lógica
        if (!$this->canDeleteProducto($producto)) {
            $razon = $producto->estado === 'activo'
                ? 'está activo'
                : 'está siendo utilizado en documentos de negocio';
            return redirect()->back()->with('error', "No se puede eliminar el producto porque {$razon}.");
        }

        if ($producto->imagen) {
            Storage::disk('public')->delete($producto->imagen);
        }

        $producto->delete();

        return redirect()->route('productos.index')->with('success', 'Producto eliminado correctamente.');
    }

    /**
     * Muestra un producto en JSON (incluye URL de la imagen si existe).
     */
    public function show($id)
    {
        $producto = Producto::with(['categoria', 'marca', 'proveedor', 'almacen'])->find($id);

        if (!$producto) {
            return response()->json(['error' => 'Producto no encontrado'], 404);
        }

        $producto->imagen_url = $producto->imagen ? asset('storage/' . $producto->imagen) : null;

        return response()->json($producto);
    }

    /**
     * Vista de inventario del producto.
     */
    public function showInventario($id)
    {
        $producto    = Producto::findOrFail($id);
        $inventarios = $producto->inventarios;

        return Inertia::render('Producto/Inventario', [
            'producto'    => $producto,
            'inventarios' => $inventarios,
        ]);
    }

    /**
     * Valida stock/precios de una lista de items (producto/servicio).
     */
    public function validateStock(Request $request): JsonResponse
    {
        $request->validate([
            'productos'         => 'required|array',
            'productos.*.id'    => 'required|integer',
            'productos.*.tipo'  => 'required|string|in:producto,servicio',
            'productos.*.cantidad' => 'required|numeric|min:1',
            'productos.*.precio'   => 'required|numeric|min:0',
        ]);

        $items = $request->input('productos');
        $errors = [];
        $pricesUpdated = [];
        $valid = true;

        foreach ($items as $item) {
            if ($item['tipo'] === 'producto') {
                // Validar stock de productos
                $producto = Producto::find($item['id']);

                if (!$producto) {
                    $errors[] = [
                        'producto' => "Producto ID {$item['id']}",
                        'mensaje'  => 'Producto no encontrado',
                    ];
                    $valid = false;
                    continue;
                }

                if ($producto->stock_disponible < $item['cantidad']) {
                    $errors[] = [
                        'producto' => $producto->nombre,
                        'mensaje'  => "Stock insuficiente. Disponible: {$producto->stock_disponible}, Solicitado: {$item['cantidad']}",
                    ];
                    $valid = false;
                }

                // Verificar si el precio ha cambiado
                if (isset($item['precio']) && (float) $producto->precio_venta !== (float) $item['precio']) {
                    $pricesUpdated[] = [
                        'id'          => $producto->id,
                        'tipo'        => 'producto',
                        'nombre'      => $producto->nombre,
                        'nuevoPrecio' => $producto->precio_venta,
                    ];
                }
            } else {
                // Servicios: solo verificar existencia
                $servicio = Servicio::find($item['id']);

                if (!$servicio) {
                    $errors[] = [
                        'producto' => "Servicio ID {$item['id']}",
                        'mensaje'  => 'Servicio no encontrado',
                    ];
                    $valid = false;
                }
            }
        }

        return response()->json([
            'valid'         => $valid,
            'errors'        => $errors,
            'pricesUpdated' => $pricesUpdated,
        ]);
    }

    /**
     * Exporta productos a CSV
     */
    public function export(Request $request)
    {
        try {
            $query = Producto::query()->with(['categoria', 'marca', 'proveedor', 'almacen']);

            // Aplicar los mismos filtros que en index
            if ($search = trim($request->input('search', ''))) {
                $query->where(function ($q) use ($search) {
                    $q->where('nombre', 'like', "%{$search}%")
                      ->orWhere('codigo', 'like', "%{$search}%")
                      ->orWhere('codigo_barras', 'like', "%{$search}%")
                      ->orWhere('descripcion', 'like', "%{$search}%");
                });
            }

            if ($estado = $request->input('estado')) {
                if ($estado === 'activo') {
                    $query->where('estado', 'activo');
                } elseif ($estado === 'inactivo') {
                    $query->where('estado', 'inactivo');
                } elseif ($estado === 'agotado') {
                    $query->where('stock', '<=', 0);
                }
            }

            $productos = $query->get();

            $filename = 'productos_' . date('Y-m-d_H-i-s') . '.csv';

            $headers = [
                'Content-Type' => 'text/csv; charset=UTF-8',
                'Content-Disposition' => 'attachment; filename="' . $filename . '"',
            ];

            $callback = function () use ($productos) {
                $file = fopen('php://output', 'w');
                fprintf($file, chr(0xEF) . chr(0xBB) . chr(0xBF)); // UTF-8 BOM

                fputcsv($file, [
                    'ID',
                    'Nombre',
                    'Código',
                    'Código de Barras',
                    'Descripción',
                    'Categoría',
                    'Marca',
                    'Proveedor',
                    'Precio Venta',
                    'Stock',
                    'Stock Mínimo',
                    'Estado',
                    'Fecha Creación'
                ]);

                foreach ($productos as $producto) {
                    fputcsv($file, [
                        $producto->id,
                        $producto->nombre,
                        $producto->codigo,
                        $producto->codigo_barras,
                        $producto->descripcion,
                        $producto->categoria?->nombre ?? '',
                        $producto->marca?->nombre ?? '',
                        $producto->proveedor?->nombre_razon_social ?? '',
                        $producto->precio_venta,
                        $producto->stock,
                        $producto->stock_minimo,
                        $producto->estado,
                        $producto->created_at?->format('d/m/Y H:i:s')
                    ]);
                }
                fclose($file);
            };

            Log::info('Exportación de productos', ['total' => $productos->count()]);

            return response()->stream($callback, 200, $headers);
        } catch (\Exception $e) {
            Log::error('Error en exportación de productos: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Error al exportar los productos.');
        }
    }

    /**
     * Alterna el estado de un producto (activo/inactivo).
     */
    public function toggle(Producto $producto)
    {
        try {
            $producto->update(['estado' => $producto->estado === 'activo' ? 'inactivo' : 'activo']);

            $mensaje = $producto->estado === 'activo' ? 'Producto activado correctamente' : 'Producto desactivado correctamente';

            return redirect()->back()->with('success', $mensaje);
        } catch (\Exception $e) {
            Log::error('Error al cambiar estado del producto: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Error al cambiar el estado del producto.');
        }
    }

    /**
     * Obtiene el detalle de stock por almacén para un producto.
     */
    public function getStockDetalle($id)
    {
        $producto = Producto::findOrFail($id);

        $stockPorAlmacen = \App\Models\Inventario::with('almacen')
            ->where('producto_id', $id)
            ->where('cantidad', '>', 0)
            ->get()
            ->map(function ($inventario) {
                return [
                    'almacen_id' => $inventario->almacen_id,
                    'almacen_nombre' => $inventario->almacen->nombre,
                    'cantidad' => $inventario->cantidad,
                    'stock_minimo' => $inventario->stock_minimo,
                ];
            });

        return response()->json([
            'producto' => [
                'id' => $producto->id,
                'nombre' => $producto->nombre,
                'codigo' => $producto->codigo,
                'stock_total' => $producto->stock,
            ],
            'stock_por_almacen' => $stockPorAlmacen,
        ]);
    }

    /**
     * Verifica si un producto puede ser eliminado.
     * Reglas:
     * - Solo productos inactivos pueden ser eliminados
     * - No debe estar siendo usado en ningún documento de negocio
     */
    private function canDeleteProducto(Producto $producto): bool
    {
        // Solo productos inactivos pueden ser eliminados
        if ($producto->estado === 'activo') {
            return false;
        }

        // Verificar si está siendo usado en documentos de negocio
        if ($producto->cotizacionItems()->count() > 0) {
            return false; // Tiene cotizaciones
        }

        if ($producto->pedidoItems()->count() > 0) {
            return false; // Tiene pedidos
        }

        if ($producto->ventaItems()->count() > 0) {
            return false; // Tiene ventas
        }

        if ($producto->compras()->count() > 0) {
            return false; // Tiene compras
        }

        if ($producto->ordenesCompra()->count() > 0) {
            return false; // Tiene órdenes de compra
        }

        return true; // Puede ser eliminado
    }
}
