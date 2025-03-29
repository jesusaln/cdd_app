<?php

namespace App\Http\Controllers;

use Inertia\Inertia;
use App\Models\Proveedor;
use Illuminate\Http\Request;
// Importing the Log facade for logging
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;

class ProveedorController extends Controller
{
    public function index()
    {
        // Obtiene todos los proveedores y los pasa al frontend
        $proveedores = Proveedor::all();
        //return response()->json(Proveedor::all());

        return Inertia::render('Proveedores/Index', [
            'proveedores' => $proveedores,
        ]);
    }

    // Método para mostrar el formulario de creación de proveedores
    public function create()
    {
        return Inertia::render('Proveedores/Create');
    }

    public function store(Request $request)
    {
        // Valida los datos del formulario
        $request->validate([
            'nombre_razon_social' => 'required|string|max:255',
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

        // Crea un nuevo proveedor
        Proveedor::create($request->all());

        return redirect()->route('proveedores.index')->with('success', 'Proveedor creado correctamente.');
    }

    public function edit($id)
    {
        $proveedor = Proveedor::findOrFail($id);
        return Inertia::render('Proveedores/Edit', [
            'proveedor' => $proveedor
        ]);
    }
    public function update(Request $request, Proveedor $proveedor)
    {
        if (!$proveedor->exists) {
            Log::error('No se encontró el proveedor para actualizar');
            return response()->json(['error' => 'Proveedor no encontrado'], 404);
        }

        Log::info('ID del proveedor a actualizar: ' . $proveedor->id);
        Log::info('Datos recibidos para actualización:', $request->all());

        $validated = $request->validate([
            'nombre_razon_social' => 'required|string|max:255',
            'tipo_persona' => 'required|in:fisica,moral',
            'rfc' => [
                'nullable',
                'string',
                'max:20', // Corregido: Sin espacios adicionales
                Rule::unique('proveedores', 'rfc')->ignore($proveedor->id),
            ],
            'regimen_fiscal' => 'nullable|string|max:255',
            'uso_cfdi' => 'nullable|string|max:255',
            'email' => [
                'nullable',
                'email',
                'max:255',
                Rule::unique('proveedores', 'email')->ignore($proveedor->id),
            ],
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



        $proveedor->update($validated);
        Log::info('Proveedor actualizado:', $proveedor->toArray());

        return redirect()->route('proveedores.index')->with('success', 'Proveedor actualizado correctamente.');
    }

    public function destroy($id)
    {
        $proveedor = Proveedor::findOrFail($id);
        $proveedor->delete();

        return redirect()->route('proveedores.index')->with('success', 'Proveedor eliminado correctamente.');
    }

    public function checkEmail(Request $request)
    {
        $exists = Proveedor::where('email', $request->query('email'))->exists();
        return response()->json(['exists' => $exists]);
    }
}
