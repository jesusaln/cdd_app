<?php

namespace App\Http\Controllers\Api;

use App\Models\Servicio;
use App\Models\Categoria;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class ServicioController extends Controller
{
    /**
     * Muestra una lista de todos los servicios en formato JSON.
     */
    public function index()
    {
        try {
            $servicios = Servicio::with('categoria')->get();
            return response()->json($servicios, 200);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Error al obtener los servicios: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Muestra los detalles de un servicio específico.
     */
    public function show($id)
    {
        try {
            $servicio = Servicio::with('categoria')->findOrFail($id);
            return response()->json($servicio, 200);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Servicio no encontrado: ' . $e->getMessage()], 404);
        }
    }

    /**
     * Crea un nuevo servicio.
     */
    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'nombre' => 'required|string|max:255',
                'descripcion' => 'nullable|string',
                'codigo' => 'required|string|unique:servicios,codigo',
                'categoria_id' => 'required|exists:categorias,id',
                'precio' => 'required|numeric|min:0',
                'duracion' => 'required|integer|min:0',
                'estado' => 'required|in:activo,inactivo',
            ]);

            $servicio = Servicio::create($validated);

            return response()->json($servicio->load('categoria'), 201);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Error al crear el servicio: ' . $e->getMessage()], 400);
        }
    }

    /**
     * Actualiza un servicio existente.
     */
    public function update(Request $request, $id)
    {
        try {
            $servicio = Servicio::findOrFail($id);

            $validated = $request->validate([
                'nombre' => 'sometimes|required|string|max:255',
                'descripcion' => 'nullable|string',
                'codigo' => 'sometimes|required|string|unique:servicios,codigo,' . $servicio->id,
                'categoria_id' => 'sometimes|required|exists:categorias,id',
                'precio' => 'sometimes|required|numeric|min:0',
                'duracion' => 'sometimes|required|integer|min:0',
                'estado' => 'sometimes|required|in:activo,inactivo',
            ]);

            $servicio->update($validated);

            return response()->json($servicio->load('categoria'), 200);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Error al actualizar el servicio: ' . $e->getMessage()], 400);
        }
    }

    /**
     * Elimina un servicio.
     */
    public function destroy($id)
    {
        try {
            $servicio = Servicio::findOrFail($id);
            $servicio->delete();

            return response()->json(['message' => 'Servicio eliminado correctamente'], 200);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Error al eliminar el servicio: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Obtiene las categorías disponibles (opcional, para formularios API).
     */
    public function getCategorias()
    {
        try {
            $categorias = Categoria::select('id', 'nombre')->get();
            return response()->json($categorias, 200);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Error al obtener las categorías: ' . $e->getMessage()], 500);
        }
    }
}
