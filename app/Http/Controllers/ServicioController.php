<?php

namespace App\Http\Controllers;

use Inertia\Inertia;
use App\Models\Servicio;
use App\Models\Categoria;
use Illuminate\Http\Request;

class ServicioController extends Controller
{
    /**
     * Muestra una lista de todos los servicios.
     */
    public function index()
    {
        return Inertia::render('Servicios/Index', [
            'servicios' => Servicio::all(),
        ]);
    }

    /**
     * Muestra el formulario para crear un nuevo servicio.
     */
    public function create()
    {
        return Inertia::render('Servicios/Create', [
            'categorias' => Categoria::select('id', 'nombre')->get(),
        ]);
    }

    /**
     * Almacena un nuevo servicio en la base de datos.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'nombre' => 'required|string|max:255',
            'descripcion' => 'nullable|string',
            'codigo' => 'required|string|unique:servicios,codigo',
            'categoria_id' => 'required|exists:categorias,id',
            'precio' => 'required|numeric|min:0',
            'duracion' => 'required|integer|min:0',
            'estado' => 'required|in:activo,inactivo',
        ]);

        Servicio::create($validated);

        return redirect()->route('servicios.index')->with('success', 'Servicio creado correctamente.');
    }

    /**
     * Muestra el formulario para editar un servicio existente.
     */
    public function edit(Servicio $servicio)
    {
        return Inertia::render('Servicios/Edit', [
            'servicio' => $servicio,
            'categorias' => Categoria::all(),
        ]);
    }

    /**
     * Actualiza un servicio existente en la base de datos.
     */
    public function update(Request $request, Servicio $servicio)
    {
        $validated = $request->validate([
            'nombre' => 'required|string|max:255',
            'descripcion' => 'nullable|string',
            'codigo' => 'nullable|string|unique:servicios,codigo,' . $servicio->id,
            'categoria_id' => 'required|exists:categorias,id',
            'precio' => 'required|numeric|min:0',
            'duracion' => 'required|integer|min:0',
            'estado' => 'required|in:activo,inactivo',
        ]);

        $servicio->update($validated);

        return redirect()->route('servicios.index')->with('success', 'Servicio actualizado correctamente.');
    }

    /**
     * Elimina un servicio de la base de datos.
     */
    public function destroy(Servicio $servicio)
    {
        $servicio->delete();

        return redirect()->route('servicios.index');
    }

    /**
     * Muestra los detalles de un servicio específico.
     */
    public function show($id)
    {
        $servicio = Servicio::with('categoria')->find($id);

        if (!$servicio) {
            return response()->json(['error' => 'Servicio no encontrado'], 404);
        }

        return response()->json($servicio);
    }
}
