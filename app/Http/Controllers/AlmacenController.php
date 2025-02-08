<?php

namespace App\Http\Controllers;

use App\Models\Almacen;
use Illuminate\Http\Request;
use Inertia\Inertia;

class AlmacenController extends Controller
{
    /**
     * Muestra una lista de todos los almacenes.
     */
    public function index()
    {

        $almacenes = Almacen::all();
        return Inertia::render('Almacenes/Index', [
            'almacenes' => $almacenes,
        ]);
    }

    /**
     * Muestra el formulario para crear un nuevo almacén.
     */
    public function create()
    {
        return Inertia::render('Almacenes/Create');
    }

    /**
     * Almacena un nuevo almacén en la base de datos.
     */
    public function store(Request $request)
    {
        $request->validate([
            'nombre' => 'required|string|max:100|unique:almacenes,nombre',
            'descripcion' => 'nullable|string|max:1000',
            'ubicacion' => 'required|string|max:255',
        ]);

        Almacen::create($request->all());

        return redirect()->route('almacenes.index')->with('success', 'Almacén creado correctamente.');
    }

    /**
     * Muestra el formulario para editar un almacén existente.
     */
    public function edit(Almacen $almacen)
    {
        return Inertia::render('Almacenes/Edit', [
            'almacen' => $almacen,
        ]);
    }

    /**
     * Actualiza un almacén existente en la base de datos.
     */
    public function update(Request $request, Almacen $almacen)
    {
        $validated = $request->validate([
            'nombre' => 'required|string|max:100|unique:almacenes,nombre,' . $almacen->id,
            'descripcion' => 'nullable|string|max:1000',
            'ubicacion' => 'required|string|max:255',
        ]);

        $almacen->update($validated);

        return redirect()->route('almacenes.index')->with('success', 'Almacén actualizado correctamente.');
    }

    /**
     * Elimina un almacén de la base de datos.
     */
    public function destroy(Almacen $almacen)
    {
        $almacen->delete();

        return redirect()->route('almacenes.index')->with('success', 'Almacén eliminado correctamente.');
    }
}
