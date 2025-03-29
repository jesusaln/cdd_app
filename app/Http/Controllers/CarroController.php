<?php

namespace App\Http\Controllers;

use App\Models\Carro;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Storage;

class CarroController extends Controller
{
    // Mostrar lista de carros
    public function index()
    {
        $carros = Carro::all()->map(function ($carro) {
            if ($carro->foto) {
                $carro->foto = Storage::url($carro->foto); // Genera la URL pública
            }
            return $carro;
        });

        return Inertia::render('Carros/Index', ['carros' => $carros]);
    }

    // Mostrar formulario para crear un nuevo carro
    public function create()
    {
        return Inertia::render('Carros/Create');
    }

    // Guardar un nuevo carro
    public function store(Request $request)
    {
        $validated = $request->validate([
            'marca' => 'required|string|max:255',
            'modelo' => 'required|string|max:255',
            'anio' => 'required|integer|min:1900|max:' . date('Y'),
            'color' => 'required|string|max:255',
            'precio' => 'required|numeric|min:0',
            'numero_serie' => 'required|string|max:255|unique:carros,numero_serie',
            'combustible' => 'required|in:Gasolina,Diésel,Eléctrico,Híbrido',
            'kilometraje' => 'required|integer|min:0',
            'placa' => 'nullable|string|max:20',
            'foto' => 'nullable|image|max:2048', // Máximo 2MB
        ]);

        if ($request->hasFile('foto')) {
            $validated['foto'] = $request->file('foto')->store('fotos_carros', 'public');
        }

        Carro::create($validated);

        return redirect()->route('carros.index')->with('success', 'Carro creado exitosamente.');
    }

    // Mostrar formulario para editar un carro
    public function edit(Carro $carro)
    {
        if ($carro->foto) {
            $carro->foto = Storage::url($carro->foto); // Genera la URL pública
        }

        return Inertia::render('Carros/Edit', ['carro' => $carro]);
    }

    // Actualizar un carro existente
    public function update(Request $request, Carro $carro)
    {
        $validated = $request->validate([
            'marca' => 'required|string|max:255',
            'modelo' => 'required|string|max:255',
            'anio' => 'required|integer|min:1900|max:' . date('Y'),
            'color' => 'required|string|max:255',
            'precio' => 'required|numeric|min:0',
            'numero_serie' => 'required|string|max:255|unique:carros,numero_serie,' . $carro->id,
            'combustible' => 'required|in:Gasolina,Diésel,Eléctrico,Híbrido',
            'kilometraje' => 'required|integer|min:0',
            'placa' => 'nullable|string|max:20',
            'foto' => 'nullable|image|max:2048', // Máximo 2MB
        ]);

        if ($request->hasFile('foto')) {
            $validated['foto'] = $request->file('foto')->store('fotos_carros', 'public');
        }

        $carro->update($validated);

        return redirect()->route('carros.index')->with('success', 'Carro actualizado exitosamente.');
    }

    // Eliminar un carro
    public function destroy(Carro $carro)
    {
        $carro->delete();

        return redirect()->route('carros.index')->with('success', 'Carro eliminado exitosamente.');
    }
}
