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
        //return response()->json(Marca::all());
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
    public function destroy($id)
    {
        try {
            // Buscar la marca por ID
            $marca = Marca::findOrFail($id);

            // Verificar si tiene productos relacionados antes de eliminarla
            if ($marca->productos()->exists()) {
                return back()->withErrors(['error' => 'No se puede eliminar la marca porque tiene productos asociados.']);
            }

            // Eliminar la marca
            $marca->delete();

            // Retornar mensaje de éxito
            return back()->with('success', 'La marca ha sido eliminada exitosamente.');
        } catch (\Exception $e) {
            // Capturar y manejar cualquier error inesperado
            return back()->withErrors(['error' => 'Hubo un error al intentar eliminar la marca.']);
        }
    }
}
