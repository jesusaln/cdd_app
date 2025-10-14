<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Models\Almacen;
use App\Http\Controllers\Controller;

class AlmacenController extends Controller
{
    /**
     * Muestra una lista de todos los almacenes en formato JSON.
     */
    public function index()
    {
        try {
            $almacenes = Almacen::all();
            return response()->json($almacenes);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Error al obtener los almacenes'], 500);
        }
    }

    /**
     * Almacena un nuevo almacén en la base de datos.
     */
    public function store(Request $request)
    {
        // Validar los datos recibidos
        $validated = $request->validate([
            'nombre' => 'required|string|max:100|unique:almacenes,nombre',
            'descripcion' => 'nullable|string|max:1000',
            'ubicacion' => 'required|string|max:255',
        ]);

        try {
            // Crear el almacén
            $almacen = Almacen::create($validated);

            // Devolver el almacén creado como respuesta JSON
            return response()->json($almacen, 201);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Error al crear el almacén'], 500);
        }
    }

    /**
     * Muestra un almacén específico en formato JSON.
     */
    public function show($id)
    {
        try {
            $almacen = Almacen::findOrFail($id);
            return response()->json($almacen);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Almacén no encontrado'], 404);
        }
    }

    /**
     * Actualiza un almacén existente en la base de datos.
     */
    public function update(Request $request, $id)
    {
        // Validar los datos recibidos
        $validated = $request->validate([
            'nombre' => 'required|string|max:100|unique:almacenes,nombre,' . $id,
            'descripcion' => 'nullable|string|max:1000',
            'ubicacion' => 'required|string|max:255',
        ]);

        try {
            $almacen = Almacen::findOrFail($id);
            $almacen->update($validated);

            // Devolver el almacén actualizado como respuesta JSON
            return response()->json($almacen);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Error al actualizar el almacén'], 500);
        }
    }

    /**
     * Elimina un almacén de la base de datos.
     */
    public function destroy($id)
    {
        try {
            $almacen = Almacen::findOrFail($id);

            // Verificar si tiene productos relacionados antes de eliminar
            if ($almacen->productos()->exists()) {
                return response()->json(['error' => 'No se puede eliminar el almacén porque tiene productos asociados'], 400);
            }

            $almacen->delete();

            return response()->json(['message' => 'Almacén eliminado correctamente']);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Error al eliminar el almacén'], 500);
        }
    }
}
