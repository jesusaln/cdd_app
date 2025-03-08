<?php

namespace App\Http\Controllers;

use App\Models\Mantenimiento;
use App\Models\Carro;
use Illuminate\Http\Request;
use Inertia\Inertia;

class MantenimientoController extends Controller
{
    // Mostrar lista de mantenimientos
    public function index()
    {
        $mantenimientos = Mantenimiento::with('carro')->get();
        return Inertia::render('Mantenimientos/Index', ['mantenimientos' => $mantenimientos]);
    }

    // Mostrar formulario para crear un nuevo mantenimiento
    public function create()
    {
        $carros = Carro::all();
        return Inertia::render('Mantenimientos/Create', ['carros' => $carros]);
    }

    // Guardar un nuevo mantenimiento
    public function store(Request $request)
    {
        $validated = $request->validate([
            'carro_id' => 'required|exists:carros,id',
            'tipo' => 'required|string|max:255',
            'fecha' => 'required|date',
            'proximo_mantenimiento' => 'required|date',
            'notas' => 'nullable|string',
        ]);

        Mantenimiento::create($validated);

        return redirect()->route('mantenimientos.index')->with('success', 'Mantenimiento creado exitosamente.');
    }

    // Mostrar formulario para editar un mantenimiento
    public function edit(Mantenimiento $mantenimiento)
    {
        $carros = Carro::all();
        return Inertia::render('Mantenimientos/Edit', [
            'mantenimiento' => $mantenimiento,
            'carros' => $carros
        ]);
    }

    // Actualizar un mantenimiento existente
    public function update(Request $request, Mantenimiento $mantenimiento)
    {
        $validated = $request->validate([
            'carro_id' => 'required|exists:carros,id',
            'tipo' => 'required|string|max:255',
            'fecha' => 'required|date',
            'proximo_mantenimiento' => 'required|date',
            'notas' => 'nullable|string',
        ]);

        $mantenimiento->update($validated);

        return redirect()->route('mantenimientos.index')->with('success', 'Mantenimiento actualizado exitosamente.');
    }

    // Eliminar un mantenimiento
    public function destroy(Mantenimiento $mantenimiento)
    {
        $mantenimiento->delete();

        return redirect()->route('mantenimientos.index')->with('success', 'Mantenimiento eliminado exitosamente.');
    }
}
