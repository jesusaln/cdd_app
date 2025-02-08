<?php

namespace App\Http\Controllers;

use Inertia\Inertia;
use App\Models\Producto;
use App\Models\Categoria;
use App\Models\Marca;
use App\Models\Proveedor;
use App\Models\Almacen;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

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
            'categorias' => Categoria::select('id', 'nombre')->get(),
            'marcas' => Marca::select('id', 'nombre')->get(),
            'proveedores' => Proveedor::select('id', 'nombre')->get(),
            'almacenes' => Almacen::select('id', 'nombre')->get(), // Corrección aquí
        ]);
    }

    /**
     * Almacena un nuevo producto en la base de datos.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'nombre' => 'required|string|max:255',
            'descripcion' => 'nullable|string',
            'codigo' => 'required|string|unique:productos,codigo',
            'codigo_barras' => 'required|string|unique:productos,codigo_barras',
            'numero_serie' => 'nullable|string',
            'categoria_id' => 'required|exists:categorias,id',
            'marca_id' => 'required|exists:marcas,id',
            'proveedor_id' => 'nullable|exists:proveedores,id',
            'almacen_id' => 'nullable|exists:almacenes,id', // Corrección aquí
            'stock' => 'required|integer|min:0',
            'stock_minimo' => 'required|integer|min:0',
            'precio_compra' => 'required|numeric|min:0',
            'precio_venta' => 'required|numeric|min:0',
            'impuesto' => 'required|numeric|min:0',
            'unidad_medida' => 'required|string',
            'fecha_vencimiento' => 'nullable|date',
            'tipo_producto' => 'required|in:fisico,digital',
            'imagen' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
            'estado' => 'required|in:activo,inactivo',
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
    public function edit(Producto $producto)
    {
        return Inertia::render('Productos/Edit', [
            'producto' => $producto,
        ]);
    }

    /**
     * Actualiza un producto existente en la base de datos.
     */
    public function update(Request $request, Producto $producto)
    {
        $validated = $request->validate([
            'nombre' => 'required|string|max:255',
            'descripcion' => 'nullable|string|max:1000',
            'precio_venta' => 'required|numeric|min:0',
            'stock' => 'required|integer|min:0',
            'categoria_id' => 'required|exists:categorias,id',
            'proveedor_id' => 'nullable|exists:proveedores,id',
            'imagen' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
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

        return redirect()->route('productos.index')->with('success', 'Producto eliminado correctamente.');
    }
}
