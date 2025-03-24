<?php

namespace App\Http\Controllers;

use App\Models\Cita;
use App\Models\Tecnico;
use App\Models\Cliente;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class CitaController extends Controller
{
    /**
     * Mostrar todas las citas.
     */
    public function index()
    {
        $citas = Cita::with('tecnico', 'cliente')->get();
        return Inertia::render('Citas/Index', ['citas' => $citas]);
    }


    /**
     * Mostrar formulario para crear una nueva cita.
     */
    public function create()
    {
        $tecnicos = Tecnico::all();
        $clientes = Cliente::all();
        return Inertia::render('Citas/Create', ['tecnicos' => $tecnicos, 'clientes' => $clientes]);
    }

    /**
     * Almacenar una nueva cita en la base de datos.
     */
    public function store(Request $request)
    {
        // Validar los datos recibidos
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

    /**
     * Mostrar formulario para editar una cita existente.
     */
    public function edit(Cita $cita)
    {
        $tecnicos = Tecnico::all();
        $clientes = Cliente::all();
        return Inertia::render('Citas/Edit', ['cita' => $cita, 'tecnicos' => $tecnicos, 'clientes' => $clientes]);
    }

    /**
     * Actualizar una cita existente en la base de datos.
     */
    public function update(Request $request, Cita $cita)
    {
        // Validar los datos recibidos
        $validated = $request->validate([
            'tecnico_id' => 'sometimes|required|exists:tecnicos,id',
            'cliente_id' => 'sometimes|required|exists:clientes,id',
            'tipo_servicio' => 'sometimes|required|string|max:255',
            'fecha_hora' => 'sometimes|required|date',
            'descripcion' => 'nullable|string',
            'tipo_equipo' => 'sometimes|required|string|max:255',
            'marca_equipo' => 'sometimes|required|string|max:255',
            'modelo_equipo' => 'sometimes|required|string|max:255',
            'problema_reportado' => 'nullable|string',
            'estado' => 'sometimes|required|string|in:pendiente,en_proceso,completado,cancelado',
            'evidencias' => 'nullable|string',
            'foto_equipo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'foto_hoja_servicio' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'foto_identificacion' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        // Guardar archivos y obtener sus rutas (conservando los archivos existentes si no se suben nuevos)
        $filePaths = $this->saveFiles($request, ['foto_equipo', 'foto_hoja_servicio', 'foto_identificacion'], [
            'foto_equipo' => $cita->foto_equipo,
            'foto_hoja_servicio' => $cita->foto_hoja_servicio,
            'foto_identificacion' => $cita->foto_identificacion,
        ]);

        // Actualizar la cita con los datos validados y las rutas de los archivos
        $cita->update(array_merge($validated, $filePaths));

        return redirect()->route('citas.index')->with('success', 'Cita actualizada exitosamente.');
    }

    private function saveFiles(Request $request, array $fileFields, $existingFiles = [])
    {
        $filePaths = [];
        foreach ($fileFields as $field) {
            if ($request->hasFile($field)) {
                try {
                    $file = $request->file($field);
                    $path = $file->store('citas', 'public'); // Guarda el archivo en el disco "public"
                    $filePaths[$field] = $path;

                    // Eliminar el archivo anterior si existe
                    if (!empty($existingFiles[$field])) {
                        Storage::disk('public')->delete($existingFiles[$field]);
                    }
                } catch (\Exception $e) {
                    Log::error("Error al guardar el archivo {$field}: " . $e->getMessage());
                    $filePaths[$field] = null; // Manejar el error asignando `null`
                }
            } else {
                $filePaths[$field] = $existingFiles[$field] ?? null; // Conservar el archivo existente
            }
        }
        return $filePaths;
    }

    /**
     * Eliminar una cita existente.
     */
    public function destroy(Cita $cita)
    {
        $cita->delete();

        return redirect()->route('citas.index')->with('success', 'Cita eliminada exitosamente.');
    }

    /**
     * Mostrar detalles de una cita.
     */
    public function show(Cita $cita)
    {
        $cita->load('cliente', 'tecnico');
        return Inertia::render('Citas/Show', [
            'cita' => $cita,
            'tecnicos' => Tecnico::all(),
            'clientes' => Cliente::all(),
        ]);
    }

    public function updateIndex(Request $request, $id)
    {
        $cita = Cita::findOrFail($id);

        $validated = $request->validate([
            'estado' => 'required|in:pendiente,en_proceso,completado,cancelado',
        ]);

        $cita->update([
            'estado' => $validated['estado'],
        ]);

        // Devolver una respuesta compatible con Inertia
        return redirect()->back()->with('success', 'Estado actualizado correctamente');
        return redirect()->route('citas.index')->with('success', 'Cita creada exitosamente.');
    }
}
