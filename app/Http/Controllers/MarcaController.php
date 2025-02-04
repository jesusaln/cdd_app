<?php

namespace App\Http\Controllers;

use Inertia\Inertia;
use App\Models\Marca;
use Illuminate\Http\Request;

class MarcaController extends Controller
{
    /**
     * Muestra una lista de todas las marcas.
     */
    public function index()
    {
        $marcas = Marca::all();
        return Inertia::render('Marcas/Index', [
            'marcas' => $marcas,
        ]);
    }

    /**
     * Muestra el formulario para crear una nueva marca.
     */
    public function create()
    {
        return Inertia::render('Marcas/Create');
    }

    /**
     * Almacena una nueva marca en la base de datos.
     */
    public function store(Request $request)
    {
        $request->validate([
            'nombre' => 'required|string|max:100|unique:marcas,nombre',
            'descripcion' => 'nullable|string|max:1000',
        ]);

        Marca::create($request->all());

        return redirect()->route('marcas.index')->with('success', 'Marca creada correctamente.');
    }

    /**
     * Muestra el formulario para editar una marca existente.
     */
    public function edit(Marca $marca)
    {
        return Inertia::render('Marcas/Edit', [
            'marca' => $marca,
        ]);
    }

    /**
     * Actualiza una marca existente en la base de datos.
     */
    public function update(Request $request, Marca $marca)
    {
        $validated = $request->validate([
            'nombre' => 'required|string|max:100|unique:marcas,nombre,' . $marca->id,
            'descripcion' => 'nullable|string|max:1000',
        ]);

        $marca->update($validated);

        return redirect()->route('marcas.index')->with('success', 'Marca actualizada correctamente.');
    }

    /**
     * Elimina una marca de la base de datos.
     */
    public function destroy(Marca $marca)
    {
        $marca->delete();

        return redirect()->route('marcas.index')->with('success', 'Marca eliminada correctamente.');
    }
}
