<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Producto;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Storage;

class ProductoController extends Controller
{
    /**
     * Obtener el siguiente código disponible para un nuevo producto.
     */
    public function nextCodigo()
    {
        try {
            $siguienteCodigo = Producto::generateNextCodigo();
            return response()->json([
                'siguiente_codigo' => $siguienteCodigo,
                'mensaje' => 'Código siguiente disponible obtenido correctamente'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Error al obtener el siguiente código',
                'detalle' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Mostrar todos los productos.
     */
    public function index()
    {
        $productos = Producto::with(['categoria', 'marca', 'proveedor', 'almacen'])->get();
        return response()->json($productos->toArray());
    }

    /**
     * Crear un nuevo producto.
     */
    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'nombre' => 'required|string|max:255',
                'descripcion' => 'nullable|string',
                'codigo' => 'required|string|unique:productos,codigo',
                'codigo_barras' => 'required|string|unique:productos,codigo_barras',
                                'categoria_id' => 'required|exists:categorias,id',
                'marca_id' => 'required|exists:marcas,id',
                'proveedor_id' => 'nullable|exists:proveedores,id',
                'almacen_id' => 'nullable|exists:almacenes,id',
                'stock' => 'required|integer|min:0',
                'stock_minimo' => 'required|integer|min:0',
                'precio_compra' => 'required|numeric|min:0',
                'precio_venta' => 'required|numeric|min:0',
                'impuesto' => 'required|numeric|min:0',
                'unidad_medida' => 'required|string',
                'fecha_vencimiento' => 'nullable|date',
                'tipo_producto' => 'required|in:fisico,digital',
                'requiere_serie' => 'boolean',
                'imagen' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
                'estado' => 'required|in:activo,inactivo',
            ]);

            if ($request->hasFile('imagen')) {
                $validated['imagen'] = $request->file('imagen')->store('productos', 'public');
            }

            $producto = Producto::create($validated);

            return response()->json($producto, 201);
        } catch (ValidationException $e) {
            return response()->json([
                'message' => 'Errores de validación',
                'errors' => $e->errors(),
            ], 422);
        }
    }

    /**
     * Mostrar un producto específico.
     */
    public function show($id)
    {
        $producto = Producto::with(['categoria', 'marca', 'proveedor', 'almacen'])->find($id);

        if (!$producto) {
            return response()->json(['message' => 'Producto no encontrado'], 404);
        }

        if ($producto->imagen) {
            $producto->imagen_url = $this->generateCorrectStorageUrl($producto->imagen);
        } else {
            $producto->imagen_url = null;
        }

        return response()->json($producto);
    }

    /**
     * Actualizar un producto existente.
     */
    public function update(Request $request, $id)
    {
        $producto = Producto::find($id);

        if (!$producto) {
            return response()->json(['message' => 'Producto no encontrado'], 404);
        }

        try {
            $validated = $request->validate([
                'nombre' => 'required|string|max:255',
                'descripcion' => 'nullable|string',
                'codigo' => 'required|string|unique:productos,codigo,' . $producto->id,
                'codigo_barras' => 'required|string|unique:productos,codigo_barras,' . $producto->id,
                                'categoria_id' => 'required|exists:categorias,id',
                'marca_id' => 'required|exists:marcas,id',
                'proveedor_id' => 'nullable|exists:proveedores,id',
                'almacen_id' => 'nullable|exists:almacenes,id',
                'stock' => 'required|integer|min:0',
                'stock_minimo' => 'required|integer|min:0',
                'precio_compra' => 'required|numeric|min:0',
                'precio_venta' => 'required|numeric|min:0',
                'impuesto' => 'required|numeric|min:0',
                'unidad_medida' => 'required|string',
                'fecha_vencimiento' => 'nullable|date',
                'tipo_producto' => 'required|in:fisico,digital',
                'requiere_serie' => 'boolean',
                'imagen' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
                'estado' => 'required|in:activo,inactivo',
            ]);

            if ($request->hasFile('imagen')) {
                if ($producto->imagen) {
                    Storage::disk('public')->delete($producto->imagen);
                }
                $validated['imagen'] = $request->file('imagen')->store('productos', 'public');
            }

            $producto->update($validated);

            return response()->json($producto);
        } catch (ValidationException $e) {
            return response()->json([
                'message' => 'Errores de validación',
                'errors' => $e->errors(),
            ], 422);
        }
    }

    /**
     * Eliminar un producto.
     */
    public function destroy($id)
    {
        $producto = Producto::find($id);

        if (!$producto) {
            return response()->json(['message' => 'Producto no encontrado'], 404);
        }

        if ($producto->imagen) {
            Storage::disk('public')->delete($producto->imagen);
        }

        $producto->delete();

        return response()->json(['message' => 'Producto eliminado correctamente']);
    }

    /**
     * Generar URL de storage correcta independientemente de APP_URL
     */
    private function generateCorrectStorageUrl($path)
    {
        $scheme = request()->isSecure() ? 'https' : 'http';
        $host = request()->getHost();
        $port = request()->getPort();

        // No agregar puerto si es el puerto estándar
        $portString = ( ($scheme === 'http' && $port !== 80) || ($scheme === 'https' && $port !== 443) ) ? ':' . $port : '';

        return "{$scheme}://{$host}{$portString}/storage/{$path}";
    }
}

