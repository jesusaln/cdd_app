<?php

namespace App\Http\Controllers;

use Inertia\Inertia;
use App\Models\Cliente;
use Illuminate\Http\Request;
use App\Events\ClientCreated;
use Illuminate\Support\Facades\Log;

class ClienteController extends Controller
{
    public function index(Request $request)
    {
        $clientes = Cliente::paginate(10);
        $clientesCount = Cliente::count();

        return Inertia::render('Clientes/Index', [
            'clientes' => $clientes,
            'clientesCount' => $clientesCount,
        ]);
    }

    public function create()
    {
        return Inertia::render('Clientes/Create');
    }

    public function store(Request $request)
    {
        try {
            $request->validate([
                'nombre_razon_social' => 'required|string|max:255',
                'rfc' => 'nullable|string|max:20|unique:clientes,rfc',
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

            $cliente = Cliente::create($request->all());
            // event(new ClientCreated($cliente)); // Descomenta si necesitas el evento

            return redirect()->route('clientes.index')
                ->with('success', 'Cliente creado correctamente');
        } catch (\Illuminate\Validation\ValidationException $e) {
            return redirect()->back()
                ->with('error', 'Errores en el formulario')
                ->withErrors($e->errors())
                ->withInput();
        } catch (\Exception $e) {
            // En lugar de JSON, devolvemos una redirección con mensaje flash
            return redirect()->back()
                ->with('error', 'Hubo un problema al crear el cliente: ' . $e->getMessage())
                ->withInput();
        }
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
            'rfc' => 'nullable|string|max:20|unique:clientes,rfc,' . $cliente->id,
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

        return redirect()->route('clientes.index')
            ->with('success', 'Cliente actualizado correctamente');
    }

    public function destroy($id)
    {
        $cliente = Cliente::findOrFail($id);
        $cliente->delete();

        return redirect()->route('clientes.index')
            ->with('success', 'Cliente eliminado correctamente');
    }

    public function checkEmail(Request $request)
    {
        $exists = Cliente::where('email', $request->query('email'))->exists();
        return response()->json(['exists' => $exists]);
    }
}
