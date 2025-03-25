<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Cliente;
use Illuminate\Http\Request;
use App\Events\ClientCreated;
use Illuminate\Validation\ValidationException;

class ClienteController extends Controller
{
    /**
     * Mostrar todos los clientes.
     */
    public function index()
    {
        $clientes = Cliente::all();
        return response()->json($clientes);
    }

    /**
     * Crear un nuevo cliente.
     */
    public function store(Request $request)
    {
        try {
            // Validar los datos del formulario
            $validated = $request->validate([
                'nombre_razon_social' => 'required|string|max:255',
                'tipo_persona' => 'required|in:fisica,moral', // Agregar tipo_persona
                'rfc' => 'required|string|max:20|unique:clientes,rfc', // Hacer rfc requerido y único
                'regimen_fiscal' => 'nullable|string|max:255',
                'uso_cfdi' => 'nullable|string|max:255',
                'email' => 'nullable|email|max:255|unique:clientes,email',
                'telefono' => 'nullable|string|max:20',
                'calle' => 'nullable|string|max:255',
                'numero_exterior' => 'nullable|string|max:20',
                'numero_interior' => 'nullable|string|max:20',
                'colonia' => 'nullable|string|max:255',
                'codigo_postal' => 'nullable|string|max:10',
                'municipio' => 'nullable|string|max:255',
                'estado' => 'nullable|string|max:255',
                'pais' => 'nullable|string|max:255',
            ]);

            // Crear el cliente
            $cliente = Cliente::create($validated);

            // Emitir evento si es necesario
            event(new ClientCreated($cliente));

            // Devolver respuesta JSON
            return response()->json($cliente, 201);
        } catch (ValidationException $e) {
            // Manejar errores de validación
            return response()->json([
                'message' => 'Errores de validación',
                'errors' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            // Manejar errores inesperados
            return response()->json([
                'message' => 'Error interno del servidor',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Mostrar un cliente específico.
     */
    public function show($id)
    {
        $cliente = Cliente::find($id);

        if (!$cliente) {
            return response()->json(['message' => 'Cliente no encontrado'], 404);
        }

        return response()->json($cliente);
    }

    /**
     * Actualizar un cliente existente.
     */
    public function update(Request $request, $id)
    {
        $cliente = Cliente::find($id);

        if (!$cliente) {
            return response()->json(['message' => 'Cliente no encontrado'], 404);
        }

        // Validar los datos del formulario
        $validated = $request->validate([
            'nombre_razon_social' => 'sometimes|required|string|max:255',
            'rfc' => 'nullable|string|max:20',
            'regimen_fiscal' => 'nullable|string|max:255',
            'uso_cfdi' => 'nullable|string|max:255',
            'email' => 'nullable|email|max:255|unique:clientes,email,' . $cliente->id,
            'telefono' => 'nullable|string|max:20',
            'calle' => 'nullable|string|max:255',
            'numero_exterior' => 'nullable|string|max:20',
            'numero_interior' => 'nullable|string|max:20',
            'colonia' => 'nullable|string|max:255',
            'codigo_postal' => 'nullable|string|max:10',
            'municipio' => 'nullable|string|max:255',
            'estado' => 'nullable|string|max:255',
            'pais' => 'nullable|string|max:255',
        ]);

        // Actualizar el cliente
        $cliente->update($validated);

        return response()->json($cliente);
    }

    /**
     * Eliminar un cliente.
     */
    public function destroy($id)
    {
        $cliente = Cliente::find($id);

        if (!$cliente) {
            return response()->json(['message' => 'Cliente no encontrado'], 404);
        }

        $cliente->delete();

        return response()->json(['message' => 'Cliente eliminado correctamente']);
    }

    /**
     * Verificar si un email ya existe.
     */
    public function checkEmail(Request $request)
    {
        $exists = Cliente::where('email', $request->query('email'))->exists();
        return response()->json(['exists' => $exists]);
    }
}
