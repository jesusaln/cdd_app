<?php

namespace App\Http\Controllers;

use App\Models\Cita;
use App\Models\Tecnico;
use App\Models\Cliente;
use Illuminate\Http\Request;
use Inertia\Inertia;

class CitaController extends Controller
{
    public function index()
    {
        $citas = Cita::with('tecnico', 'cliente')->get();
        return Inertia::render('Citas/Index', ['citas' => $citas]);
    }

    public function create()
    {
        $tecnicos = Tecnico::all();
        $clientes = Cliente::all();
        return Inertia::render('Citas/Create', ['tecnicos' => $tecnicos, 'clientes' => $clientes]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'tecnico_id' => 'required|exists:tecnicos,id',
            'cliente_id' => 'required|exists:clientes,id',
            'tipo_servicio' => 'required|string|max:255',
            'fecha_hora' => 'required|date',
            'descripcion' => 'nullable|string',
            'tipo_equipo' => 'required|string|max:255',
            'marca_equipo' => 'required|string|max:255',
            'modelo_equipo' => 'required|string|max:255',
            'problema_reportado' => 'nullable|string',
            'estado' => 'required|string|in:pendiente,en_proceso,completado,cancelado', // Validación del estado
        ]);

        Cita::create($validated);

        return redirect()->route('citas.index')->with('success', 'Cita creada exitosamente.');
    }

    public function edit(Cita $cita)
    {
        $tecnicos = Tecnico::all();
        $clientes = Cliente::all();
        return Inertia::render('Citas/Edit', ['cita' => $cita, 'tecnicos' => $tecnicos, 'clientes' => $clientes]);
    }

    public function update(Request $request, Cita $cita)
    {
        $validated = $request->validate([
            'tecnico_id' => 'required|exists:tecnicos,id',
            'cliente_id' => 'required|exists:clientes,id',
            'tipo_servicio' => 'required|string|max:255',
            'fecha_hora' => 'required|date',
            'descripcion' => 'nullable|string',
            'tipo_equipo' => 'required|string|max:255',
            'marca_equipo' => 'required|string|max:255',
            'modelo_equipo' => 'required|string|max:255',
            'problema_reportado' => 'nullable|string',
            'estado' => 'required|string|in:pendiente,en_proceso,completado,cancelado', // Validación del estado
        ]);

        $cita->update($validated);

        return redirect()->route('citas.index')->with('success', 'Cita actualizada exitosamente.');
    }

    public function destroy(Cita $cita)
    {
        $cita->delete();

        return redirect()->route('citas.index')->with('success', 'Cita eliminada exitosamente.');
    }

    public function show(Cita $cita)
    {
        // Cargar relaciones si es necesario
        $cita->load('cliente', 'tecnico');

        return Inertia::render('Citas/Show', [
            'cita' => $cita,
            'tecnicos' => Tecnico::all(), // Asegúrate de cargar los técnicos si es necesario
            'clientes' => Cliente::all(), // Asegúrate de cargar los clientes si es necesario
        ]);
    }
}
