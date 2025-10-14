<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Models\Proveedor;
use App\Http\Controllers\Controller;

class ProveedorController extends Controller
{
    /**
     * Muestra una lista de todos los proveedores en formato JSON.
     */
    public function index()
    {
        try {
            $proveedores = Proveedor::all();
            return response()->json($proveedores);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Error al obtener los proveedores'], 500);
        }
    }

    /**
     * Almacena un nuevo proveedor en la base de datos.
     */
    public function store(Request $request)
    {
        // Validar los datos recibidos
        $validated = $request->validate([
            'nombre' => 'required|string|max:255',
            'rfc' => 'nullable|string|max:20',
            'contacto' => 'nullable|string|max:255',
            'telefono' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:255|unique:proveedores,email',
            'direccion' => 'nullable|string|max:255',
            'codigo_postal' => 'nullable|string|max:10',
            'municipio' => 'nullable|string|max:255',
            'estado' => 'nullable|string|max:255',
            'pais' => 'nullable|string|max:255',
        ]);

        try {
            // Crear el proveedor
            $proveedor = Proveedor::create($validated);

            // Devolver el proveedor creado como respuesta JSON
            return response()->json($proveedor, 201);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Error al crear el proveedor'], 500);
        }
    }

    /**
     * Muestra un proveedor específico en formato JSON.
     */
    public function show($id)
    {
        try {
            $proveedor = Proveedor::findOrFail($id);
            return response()->json($proveedor);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Proveedor no encontrado'], 404);
        }
    }

    /**
     * Actualiza un proveedor existente en la base de datos.
     */
    public function update(Request $request, $id)
    {
        // Validar los datos recibidos
        $validated = $request->validate([
            'nombre' => 'required|string|max:255',
            'rfc' => 'nullable|string|max:20',
            'contacto' => 'nullable|string|max:255',
            'telefono' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:255|unique:proveedores,email,' . $id,
            'direccion' => 'nullable|string|max:255',
            'codigo_postal' => 'nullable|string|max:10',
            'municipio' => 'nullable|string|max:255',
            'estado' => 'nullable|string|max:255',
            'pais' => 'nullable|string|max:255',
        ]);

        try {
            $proveedor = Proveedor::findOrFail($id);
            $proveedor->update($validated);

            // Devolver el proveedor actualizado como respuesta JSON
            return response()->json($proveedor);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Error al actualizar el proveedor'], 500);
        }
    }

    /**
     * Elimina un proveedor de la base de datos.
     */
    public function destroy($id)
    {
        try {
            $proveedor = Proveedor::findOrFail($id);
            $proveedor->delete();

            return response()->json(['message' => 'Proveedor eliminado correctamente']);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Error al eliminar el proveedor'], 500);
        }
    }

    /**
     * Verifica si un correo electrónico ya existe.
     */
    public function checkEmail(Request $request)
    {
        $exists = Proveedor::where('email', $request->query('email'))->exists();
        return response()->json(['exists' => $exists]);
    }
}
