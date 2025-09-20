<?php

namespace App\Http\Controllers;

use Inertia\Inertia;
use App\Models\Tecnico;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
//use App\Events\TecnicoCreated; // Evento opcional si deseas notificar creación de técnicos

class TecnicoController extends Controller
{
    /**
     * Mostrar la lista de técnicos.
     */
    public function index(Request $request)
    {
        try {
            $query = Tecnico::query();

            // Filtros
            if ($search = trim($request->input('search', ''))) {
                $query->where(function ($q) use ($search) {
                    $q->where('nombre', 'like', "%{$search}%")
                      ->orWhere('apellido', 'like', "%{$search}%")
                      ->orWhere('email', 'like', "%{$search}%")
                      ->orWhere('telefono', 'like', "%{$search}%");
                });
            }

            // Ordenamiento
            $sortBy = $request->input('sort_by', 'nombre');
            $sortDirection = $request->input('sort_direction', 'asc');

            $validSortFields = ['nombre', 'apellido', 'email', 'telefono', 'created_at'];
            if (!in_array($sortBy, $validSortFields)) {
                $sortBy = 'nombre';
            }

            $query->orderBy($sortBy, $sortDirection);

            // Paginación
            $perPage = min((int) $request->input('per_page', 10), 50);
            $tecnicos = $query->paginate($perPage)->appends($request->query());

            // Estadísticas
            $total = Tecnico::count();

            $stats = [
                'total' => $total,
            ];

            return Inertia::render('Tecnicos/Index', [
                'tecnicos' => $tecnicos,
                'stats' => $stats,
                'filters' => $request->only(['search']),
                'sorting' => ['sort_by' => $sortBy, 'sort_direction' => $sortDirection],
            ]);
        } catch (\Exception $e) {
            Log::error('Error en TecnicoController@index: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Error al cargar los técnicos.');
        }
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
