<?php

namespace App\Http\Controllers;

use Inertia\Inertia;
use App\Models\Producto; // Modelo Producto
use Illuminate\Http\Request;

class ProductoController extends Controller
{
    /**
     * Muestra una lista de todos los productos.
     */
    public function index()
    {
        // Obtiene todos los productos y los pasa al frontend
        $productos = Producto::all();
        return Inertia::render('Productos/Index', [
            'productos' => $productos,
        ]);
    }

    /**
     * Muestra el formulario para crear un nuevo producto.
     */
    public function create()
    {
        return Inertia::render('Productos/Create');
    }

    /**
     * Almacena un nuevo producto en la base de datos.
     */
    public function store(Request $request)
    {
        // Valida los datos del formulario
        $request->validate([
            'nombre' => 'required|string|max:255',
            'descripcion' => 'nullable|string|max:1000',
            'precio' => 'required|numeric|min:0',
            'stock' => 'required|integer|min:0',
            'categoria' => 'nullable|string|max:255',
            'proveedor' => 'nullable|string|max:255',
        ]);

        // Crea un nuevo producto con los datos del formulario
        Producto::create($request->all());

        // Redirige a la lista de productos con un mensaje de éxito
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
        // Valida los datos del formulario
        $validated = $request->validate([
            'nombre' => 'required|string|max:255',
            'descripcion' => 'nullable|string|max:1000',
            'precio' => 'required|numeric|min:0',
            'stock' => 'required|integer|min:0',
            'categoria' => 'nullable|string|max:255',
            'proveedor' => 'nullable|string|max:255',
        ]);

        // Actualiza el producto con los datos validados
        $producto->update($validated);

        // Redirige a la lista de productos con un mensaje de éxito
        return redirect()->route('productos.index')->with('success', 'Producto actualizado correctamente.');
    }

    /**
     * Elimina un producto de la base de datos.
     */
    public function destroy($id)
    {
        // Busca el producto por su ID y lo elimina
        $producto = Producto::findOrFail($id);
        $producto->delete();

        // Redirige a la lista de productos con un mensaje de éxito
        return redirect()->route('productos.index')->with('success', 'Producto eliminado correctamente.');
    }
}
