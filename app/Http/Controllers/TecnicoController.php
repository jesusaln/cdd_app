<?php

namespace App\Http\Controllers;

use Inertia\Inertia;
use App\Models\Tecnico;
use Illuminate\Http\Request;
//use App\Events\TecnicoCreated; // Evento opcional si deseas notificar creación de técnicos

class TecnicoController extends Controller
{
    /**
     * Mostrar la lista de técnicos.
     */
    public function index()
    {
        // Obtiene todos los técnicos y los pasa al frontend
        $tecnicos = Tecnico::all();

        return Inertia::render('Tecnicos/Index', [
            'tecnicos' => $tecnicos,
        ]);
    }

    /**
     * Mostrar el formulario para crear un nuevo técnico.
     */
    public function create()
    {
        return Inertia::render('Tecnicos/Create');
    }

    /**
     * Almacenar un nuevo técnico en la base de datos.
     */
    public function store(Request $request)
    {
        // Validar los datos del formulario
        $request->validate([
            'nombre' => 'required|string|max:255',
            'apellido' => 'required|string|max:255',
            'email' => 'nullable|email|max:255|unique:tecnicos,email',
            'telefono' => 'nullable|string|max:20',
            'direccion' => 'nullable|string|max:255',
        ]);

        // Crear un nuevo técnico con los datos del formulario
        $tecnico = Tecnico::create($request->all());

        // Opcional: Emitir un evento para notificar la creación del técnico
        //event(new TecnicoCreated($tecnico));

        // Redirigir al índice con un mensaje de éxito
        return redirect()->route('tecnicos.index')->with('success', 'Técnico creado correctamente.');
    }

    /**
     * Mostrar el formulario para editar un técnico existente.
     */
    public function edit(Tecnico $tecnico)
    {
        return Inertia::render('Tecnicos/Edit', [
            'tecnico' => $tecnico,
        ]);
    }

    /**
     * Actualizar un técnico existente en la base de datos.
     */
    public function update(Request $request, Tecnico $tecnico)
    {
        // Validar los datos del formulario
        $validated = $request->validate([
            'nombre' => 'required|string|max:255',
            'apellido' => 'required|string|max:255',
            'email' => 'nullable|email|max:255|unique:tecnicos,email,' . $tecnico->id,
            'telefono' => 'nullable|string|max:20',
            'direccion' => 'nullable|string|max:255',
        ]);

        // Actualizar el técnico con los datos validados
        $tecnico->update($validated);

        // Redirigir al índice con un mensaje de éxito
        return redirect()->route('tecnicos.index')->with('success', 'Técnico actualizado correctamente.');
    }

    /**
     * Eliminar un técnico de la base de datos.
     */
    public function destroy(Tecnico $tecnico)
    {
        $tecnico->delete();

        // Redirigir al índice con un mensaje de éxito
        return redirect()->route('tecnicos.index')->with('success', 'Técnico eliminado correctamente.');
    }

    /**
     * Verificar si un email ya existe (opcional para validaciones en tiempo real).
     */
    public function checkEmail(Request $request)
    {
        $exists = Tecnico::where('email', $request->query('email'))->exists();
        return response()->json(['exists' => $exists]);
    }
}
