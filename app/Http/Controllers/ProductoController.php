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
use Inertia\Inertia;

class ProductoController extends Controller
{
    /**
     * Muestra una lista de todos los productos.
     */
    public function index()
    {
        return Inertia::render('Productos/Index', [
            'productos' => Producto::all(),
        ]);
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
        if ($producto->imagen) {
            Storage::disk('public')->delete($producto->imagen);
        }

        $producto->delete();

        // Sin notificación adicional
        return redirect()->route('productos.index');
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
}
