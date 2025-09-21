<?php

namespace App\Http\Controllers;

use Inertia\Inertia;
use App\Models\Servicio;
use App\Models\Categoria;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class ServicioController extends Controller
{
    /**
     * Muestra una lista de todos los servicios con paginación y filtros.
     */
    public function index(Request $request)
    {
        try {
            $query = Servicio::query()->with(['categoria']);

            // Filtros
            if ($search = trim($request->input('search', ''))) {
                $query->where(function ($q) use ($search) {
                    $q->where('nombre', 'like', "%{$search}%")
                      ->orWhere('codigo', 'like', "%{$search}%")
                      ->orWhere('descripcion', 'like', "%{$search}%");
                });
            }

            if ($estado = $request->input('estado')) {
                if ($estado === 'activo') {
                    $query->where('estado', 'activo');
                } elseif ($estado === 'inactivo') {
                    $query->where('estado', 'inactivo');
                }
            }

            // Ordenamiento
            $sortBy = $request->input('sort_by', 'nombre');
            $sortDirection = $request->input('sort_direction', 'asc');

            $validSortFields = ['nombre', 'codigo', 'precio', 'duracion', 'created_at'];
            if (!in_array($sortBy, $validSortFields)) {
                $sortBy = 'nombre';
            }

            $query->orderBy($sortBy, $sortDirection);

            // Paginación
            $perPage = min((int) $request->input('per_page', 10), 50);
            $servicios = $query->paginate($perPage)->appends($request->query());

            // Estadísticas basadas en estado del servicio
            $stats = [
                'total' => Servicio::count(),
                'activos' => Servicio::where('estado', 'activo')->count(),
                'inactivos' => Servicio::where('estado', 'inactivo')->count(),
            ];

            return Inertia::render('Servicios/Index', [
                'servicios' => $servicios,
                'stats' => $stats,
                'filters' => $request->only(['search', 'estado']),
                'sorting' => ['sort_by' => $sortBy, 'sort_direction' => $sortDirection],
            ]);
        } catch (\Exception $e) {
            Log::error('Error en ServicioController@index: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Error al cargar los servicios.');
        }
    }

    /**
     * Muestra el formulario para crear un nuevo servicio.
     */
    public function create()
    {
        return Inertia::render('Servicios/Create', [
            'categorias' => Categoria::select('id', 'nombre')->get(),
        ]);
    }

    /**
     * Almacena un nuevo servicio en la base de datos.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'nombre' => 'required|string|max:255',
            'descripcion' => 'nullable|string',
            'codigo' => 'required|string|unique:servicios,codigo',
            'categoria_id' => 'required|exists:categorias,id',
            'precio' => 'required|numeric|min:0',
            'duracion' => 'required|integer|min:0',
            'estado' => 'required|in:activo,inactivo',
            'es_instalacion' => 'nullable|boolean',
            'comision_vendedor' => 'nullable|numeric|min:0',
        ]);

        Servicio::create($validated);

        return redirect()->route('servicios.index')->with('success', 'Servicio creado correctamente.');
    }

    /**
     * Muestra el formulario para editar un servicio existente.
     */
    public function edit(Servicio $servicio)
    {
        return Inertia::render('Servicios/Edit', [
            'servicio' => $servicio,
            'categorias' => Categoria::all(),
        ]);
    }

    /**
     * Actualiza un servicio existente en la base de datos.
     */
    public function update(Request $request, Servicio $servicio)
    {
        $validated = $request->validate([
            'nombre' => 'required|string|max:255',
            'descripcion' => 'nullable|string',
            'codigo' => 'nullable|string|unique:servicios,codigo,' . $servicio->id,
            'categoria_id' => 'required|exists:categorias,id',
            'precio' => 'required|numeric|min:0',
            'duracion' => 'required|integer|min:0',
            'estado' => 'required|in:activo,inactivo',
            'es_instalacion' => 'nullable|boolean',
            'comision_vendedor' => 'nullable|numeric|min:0',
        ]);

        $servicio->update($validated);

        return redirect()->route('servicios.index')->with('success', 'Servicio actualizado correctamente.');
    }

    /**
     * Elimina un servicio de la base de datos.
     */
    public function destroy(Servicio $servicio)
    {
        $servicio->delete();

        return redirect()->route('servicios.index');
    }

    /**
     * Alterna el estado de un servicio (activo/inactivo).
     */
    public function toggle(Servicio $servicio)
    {
        try {
            $servicio->update(['estado' => $servicio->estado === 'activo' ? 'inactivo' : 'activo']);

            $mensaje = $servicio->estado === 'activo' ? 'Servicio activado correctamente' : 'Servicio desactivado correctamente';

            return redirect()->back()->with('success', $mensaje);
        } catch (\Exception $e) {
            Log::error('Error al cambiar estado del servicio: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Error al cambiar el estado del servicio.');
        }
    }

    /**
     * Muestra los detalles de un servicio específico.
     */
    public function show($id)
    {
        $servicio = Servicio::with('categoria')->find($id);

        if (!$servicio) {
            return response()->json(['error' => 'Servicio no encontrado'], 404);
        }

        return response()->json($servicio);
    }

    /**
     * Exporta servicios a CSV
     */
    public function export(Request $request)
    {
        try {
            $query = Servicio::query()->with(['categoria']);

            // Aplicar los mismos filtros que en index
            if ($search = trim($request->input('search', ''))) {
                $query->where(function ($q) use ($search) {
                    $q->where('nombre', 'like', "%{$search}%")
                      ->orWhere('codigo', 'like', "%{$search}%")
                      ->orWhere('descripcion', 'like', "%{$search}%");
                });
            }

            if ($estado = $request->input('estado')) {
                if ($estado === 'activo') {
                    $query->where('estado', 'activo');
                } elseif ($estado === 'inactivo') {
                    $query->where('estado', 'inactivo');
                }
            }

            $servicios = $query->get();

            $filename = 'servicios_' . date('Y-m-d_H-i-s') . '.csv';

            $headers = [
                'Content-Type' => 'text/csv; charset=UTF-8',
                'Content-Disposition' => 'attachment; filename="' . $filename . '"',
            ];

            $callback = function () use ($servicios) {
                $file = fopen('php://output', 'w');
                fprintf($file, chr(0xEF) . chr(0xBB) . chr(0xBF)); // UTF-8 BOM

                fputcsv($file, [
                    'ID',
                    'Nombre',
                    'Código',
                    'Descripción',
                    'Categoría',
                    'Precio',
                    'Duración (min)',
                    'Estado',
                    'Fecha Creación'
                ]);

                foreach ($servicios as $servicio) {
                    fputcsv($file, [
                        $servicio->id,
                        $servicio->nombre,
                        $servicio->codigo,
                        $servicio->descripcion,
                        $servicio->categoria?->nombre ?? '',
                        $servicio->precio,
                        $servicio->duracion,
                        $servicio->estado,
                        $servicio->created_at?->format('d/m/Y H:i:s')
                    ]);
                }
                fclose($file);
            };

            Log::info('Exportación de servicios', ['total' => $servicios->count()]);

            return response()->stream($callback, 200, $headers);
        } catch (\Exception $e) {
            Log::error('Error en exportación de servicios: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Error al exportar los servicios.');
        }
    }
}
