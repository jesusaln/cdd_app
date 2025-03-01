<?php

namespace App\Http\Controllers;

use Inertia\Inertia;
use App\Models\Cliente;
use Illuminate\Http\Request;
use App\Events\ClientCreated;

class ClienteController extends Controller
{
    public function index()
    {
        // Obtiene todos los clientes y los pasa al frontend
        $clientes = Cliente::all();

        return Inertia::render('Clientes/Index', [
            'clientes' => $clientes,
        ]);

        $clientesCount = Cliente::count(); // Contar clientes
        return Inertia::render('Clientes/Index', [
            'clientesCount' => $clientesCount
        ]);
    }

    // Método para mostrar el formulario de creación de clientes
    public function create()
    {
        return Inertia::render('Clientes/Create');
    }

    public function store(Request $request)
    {
        // Valida los datos del formulario
        $request->validate([
            'nombre_razon_social' => 'required|string|max:255',
            'rfc' => 'nullable|string|max:20',
            'regimen_fiscal' => 'nullable|string|max:255',
            'uso_cfdi' => 'nullable|string|max:255',
            'email' => 'nullable|email|max:255',
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

        // Crea un nuevo cliente con los datos del formulario
        $cliente = Cliente::create($request->all());

        // Crea un evento para notificar a las aplicaciones subscritas al evento
        event(new ClientCreated($cliente));

        // Devuelve una respuesta JSON para Inertia.js
        // return response()->json($cliente, 201);
        return redirect()->route('clientes.index')->with('success', 'Cliente creado correctamente.');
    }

    public function edit(Cliente $cliente)
    {
        return Inertia::render('Clientes/Edit', [
            'cliente' => $cliente,
        ]);
    }

    public function update(Request $request, Cliente $cliente)
    {
        $validated = $request->validate([
            'nombre_razon_social' => 'required|string|max:255',
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

        $cliente->update($validated);

        return redirect()->route('clientes.index')->with('success', 'Cliente actualizado correctamente.');
    }

    public function destroy($id)
    {
        $cliente = Cliente::findOrFail($id);
        $cliente->delete();

        return redirect()->route('clientes.index')->with('success', 'Cliente eliminado correctamente.');
    }

    public function checkEmail(Request $request)
    {
        $exists = Cliente::where('email', $request->query('email'))->exists();
        return response()->json(['exists' => $exists]);
    }
}
