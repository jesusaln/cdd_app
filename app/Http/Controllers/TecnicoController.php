<?php

namespace App\Http\Controllers;

use Inertia\Inertia;
use App\Models\Tecnico;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Exception;
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

            // Filtros de búsqueda
            if ($s = trim((string) $request->input('search', ''))) {
                $query->where(function ($w) use ($s) {
                    $w->where('nombre', 'like', "%{$s}%")
                        ->orWhere('apellido', 'like', "%{$s}%")
                        ->orWhere('email', 'like', "%{$s}%")
                        ->orWhere('telefono', 'like', "%{$s}%");
                });
            }

            // Filtrar por estado activo/inactivo
            if ($request->query->has('activo')) {
                $val = (string) $request->query('activo');
                if ($val === '1') {
                    $query->where(function ($query) {
                        $query->where('activo', true)->orWhereNull('activo');
                    });
                } elseif ($val === '0') {
                    $query->where('activo', false);
                }
            }

            // Ordenamiento
            $sortBy = $request->get('sort_by', 'created_at');
            $sortDirection = $request->get('sort_direction', 'desc');
            $validSort = ['nombre', 'apellido', 'email', 'telefono', 'created_at', 'activo'];

            if (!in_array($sortBy, $validSort)) $sortBy = 'created_at';
            if (!in_array($sortDirection, ['asc', 'desc'])) $sortDirection = 'desc';

            $query->orderBy($sortBy, $sortDirection);

            // Paginación
            $tecnicos = $query->paginate(10)->appends($request->query());

            // Estadísticas
            $tecnicosCount = Tecnico::count();
            $tecnicosActivos = Tecnico::where(function ($q) {
                $q->where('activo', true)->orWhereNull('activo');
            })->count();

            return Inertia::render('Tecnicos/Index', [
                'tecnicos' => $tecnicos,
                'stats' => [
                    'total' => $tecnicosCount,
                    'activos' => $tecnicosActivos,
                    'inactivos' => $tecnicosCount - $tecnicosActivos,
                ],
                'filters' => $request->only(['search', 'activo']),
                'sorting' => ['sort_by' => $sortBy, 'sort_direction' => $sortDirection],
            ]);
        } catch (Exception $e) {
            Log::error('Error en TecnicoController@index: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Error al cargar la lista de técnicos.');
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

    public function toggle(Tecnico $tecnico)
    {
        try {
            $tecnico->update(['activo' => !$tecnico->activo]);
            return redirect()->back()->with('success', $tecnico->activo ? 'Técnico activado correctamente' : 'Técnico desactivado correctamente');
        } catch (Exception $e) {
            Log::error('Error cambiando estado del técnico: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Hubo un problema al cambiar el estado del técnico.');
        }
    }

    public function export(Request $request)
    {
        try {
            $query = Tecnico::query();

            // Aplicar los mismos filtros que en index
            if ($s = trim((string) $request->input('search', ''))) {
                $query->where(function ($w) use ($s) {
                    $w->where('nombre', 'like', "%{$s}%")
                        ->orWhere('apellido', 'like', "%{$s}%")
                        ->orWhere('email', 'like', "%{$s}%")
                        ->orWhere('telefono', 'like', "%{$s}%");
                });
            }

            if ($request->query->has('activo')) {
                $val = (string) $request->query('activo');
                if ($val === '1') {
                    $query->where(function ($query) {
                        $query->where('activo', true)->orWhereNull('activo');
                    });
                } elseif ($val === '0') {
                    $query->where('activo', false);
                }
            }

            $tecnicos = $query->get();

            $filename = 'tecnicos_' . date('Y-m-d_H-i-s') . '.csv';

            $headers = [
                'Content-Type' => 'text/csv; charset=UTF-8',
                'Content-Disposition' => 'attachment; filename="' . $filename . '"',
            ];

            $callback = function () use ($tecnicos) {
                $file = fopen('php://output', 'w');
                fprintf($file, chr(0xEF) . chr(0xBB) . chr(0xBF));

                fputcsv($file, [
                    'ID',
                    'Nombre',
                    'Apellido',
                    'Email',
                    'Teléfono',
                    'Dirección',
                    'Activo',
                    'Fecha Creación'
                ]);

                foreach ($tecnicos as $tecnico) {
                    fputcsv($file, [
                        $tecnico->id,
                        $tecnico->nombre,
                        $tecnico->apellido,
                        $tecnico->email,
                        $tecnico->telefono,
                        $tecnico->direccion,
                        $tecnico->activo ? 'Sí' : 'No',
                        $tecnico->created_at?->format('d/m/Y H:i:s')
                    ]);
                }
                fclose($file);
            };

            return response()->stream($callback, 200, $headers);
        } catch (Exception $e) {
            Log::error('Error en exportación de técnicos: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Error al exportar los técnicos.');
        }
    }
}
