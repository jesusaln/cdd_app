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
            'estado' => 'required|string|in:pendiente,en_proceso,completado,cancelado',
            'evidencias' => 'nullable|string',
            'foto_equipo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'foto_hoja_servicio' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'foto_identificacion' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        // Guardar archivos y obtener sus rutas
        $filePaths = $this->saveFiles($request, ['foto_equipo', 'foto_hoja_servicio', 'foto_identificacion']);

        // Crear la cita con los datos validados y las rutas de los archivos
        $cita = Cita::create(array_merge($validated, $filePaths));

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
            'estado' => 'required|string|in:pendiente,en_proceso,completado,cancelado',
            'evidencias' => 'nullable|string',
            'foto_equipo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'foto_hoja_servicio' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'foto_identificacion' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        // Guardar archivos y obtener sus rutas
        $filePaths = $this->saveFiles($request, ['foto_equipo', 'foto_hoja_servicio', 'foto_identificacion']);

        // Actualizar la cita con los datos validados y las rutas de los archivos
        $cita->update(array_merge($validated, $filePaths));

        return redirect()->route('citas.index')->with('success', 'Cita actualizada exitosamente.');
    }

    public function destroy(Cita $cita)
    {
        $cita->delete();

        return redirect()->route('citas.index')->with('success', 'Cita eliminada exitosamente.');
    }

    public function show(Cita $cita)
    {
        $cita->load('cliente', 'tecnico');
        return Inertia::render('Citas/Show', [
            'cita' => $cita,
            'tecnicos' => Tecnico::all(),
            'clientes' => Cliente::all(),
        ]);
    }

    private function saveFiles(Request $request, array $fileFields)
    {
        $filePaths = [];
        foreach ($fileFields as $field) {
            if ($request->hasFile($field)) {
                $file = $request->file($field);
                $path = $file->store('citas', 'public');
                $filePaths[$field] = $path;
            }
        }
        return $filePaths;
    }
}
