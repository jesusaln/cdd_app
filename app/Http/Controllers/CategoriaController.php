<?php

namespace App\Http\Controllers;

use Inertia\Inertia;
use App\Models\Categoria;
use Illuminate\Http\Request;

class CategoriaController extends Controller
{
    /**
     * Muestra una lista de todas las categorías.
     */
    public function index()
    {
        //return response()->json(Categoria::all());
        $categorias = Categoria::all();
        return Inertia::render('Categorias/Index', [
            'categorias' => $categorias,
        ]);
    }

    /**
     * Muestra el formulario para crear una nueva categoría.
     */
    public function create()
    {
        return Inertia::render('Categorias/Create');
    }

    /**
     * Almacena una nueva categoría en la base de datos.
     */
    public function store(Request $request)
    {
        $request->validate([
            'nombre' => 'required|string|max:100|unique:categorias,nombre',
            'descripcion' => 'nullable|string|max:1000',
        ]);

        Categoria::create($request->all());

        return redirect()->route('categorias.index')->with('success', 'Categoría creada correctamente.');
    }

    /**
     * Muestra el formulario para editar una categoría existente.
     */
    public function edit(Categoria $categoria)
    {
        return Inertia::render('Categorias/Edit', [
            'categoria' => $categoria,
        ]);
    }

    /**
     * Actualiza una categoría existente en la base de datos.
     */
    public function update(Request $request, Categoria $categoria)
    {
        $validated = $request->validate([
            'nombre' => 'required|string|max:100|unique:categorias,nombre,' . $categoria->id,
            'descripcion' => 'nullable|string|max:1000',
        ]);

        $categoria->update($validated);

        return redirect()->route('categorias.index')->with('success', 'Categoría actualizada correctamente.');
    }

    /**
     * Elimina una categoría de la base de datos.
     */
    public function destroy(Categoria $categoria)
    {
        $categoria->delete();
        return redirect()->route('categorias.index')->with('success', 'Categoría eliminada correctamente.');
    }
}
