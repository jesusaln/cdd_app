<?php

namespace App\Http\Controllers;

use App\Models\Carro;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

class CarroController extends Controller
{
    // Mostrar lista de carros
    public function index(Request $request)
    {
        try {
            $query = Carro::query();

            // Filtros
            if ($search = trim($request->input('search', ''))) {
                $query->where(function ($q) use ($search) {
                    $q->where('marca', 'like', "%{$search}%")
                      ->orWhere('modelo', 'like', "%{$search}%")
                      ->orWhere('placa', 'like', "%{$search}%")
                      ->orWhere('numero_serie', 'like', "%{$search}%");
                });
            }

            if ($combustible = $request->input('combustible')) {
                $query->where('combustible', $combustible);
            }

            // Ordenamiento
            $sortBy = $request->input('sort_by', 'marca');
            $sortDirection = $request->input('sort_direction', 'asc');

            $validSortFields = ['marca', 'modelo', 'anio', 'precio', 'kilometraje', 'created_at'];
            if (!in_array($sortBy, $validSortFields)) {
                $sortBy = 'marca';
            }

            $query->orderBy($sortBy, $sortDirection);

            // Paginación
            $perPage = min((int) $request->input('per_page', 10), 50);
            $carros = $query->paginate($perPage)->appends($request->query());

            // Estadísticas
            $stats = [
                'total' => Carro::count(),
                'gasolina' => Carro::where('combustible', 'Gasolina')->count(),
                'diesel' => Carro::where('combustible', 'Diésel')->count(),
                'electrico' => Carro::where('combustible', 'Eléctrico')->count(),
                'hibrido' => Carro::where('combustible', 'Híbrido')->count(),
            ];

            // Mapear fotos
            $carros->getCollection()->transform(function ($carro) {
                if ($carro->foto) {
                    $carro->foto = Storage::url($carro->foto);
                }
                return $carro;
            });

            return Inertia::render('Carros/Index', [
                'carros' => $carros,
                'stats' => $stats,
                'filters' => $request->only(['search', 'combustible']),
                'sorting' => ['sort_by' => $sortBy, 'sort_direction' => $sortDirection],
            ]);
        } catch (\Exception $e) {
            \Log::error('Error en CarroController@index: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Error al cargar los carros.');
        }
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

    // Exportar carros a CSV
    public function export(Request $request)
    {
        try {
            $query = Carro::query();

            // Aplicar los mismos filtros que en index
            if ($search = trim($request->input('search', ''))) {
                $query->where(function ($q) use ($search) {
                    $q->where('marca', 'like', "%{$search}%")
                      ->orWhere('modelo', 'like', "%{$search}%")
                      ->orWhere('placa', 'like', "%{$search}%")
                      ->orWhere('numero_serie', 'like', "%{$search}%");
                });
            }

            if ($combustible = $request->input('combustible')) {
                $query->where('combustible', $combustible);
            }

            $carros = $query->get();

            $filename = 'carros_' . date('Y-m-d_H-i-s') . '.csv';

            $headers = [
                'Content-Type' => 'text/csv; charset=UTF-8',
                'Content-Disposition' => 'attachment; filename="' . $filename . '"',
            ];

            $callback = function () use ($carros) {
                $file = fopen('php://output', 'w');
                fprintf($file, chr(0xEF) . chr(0xBB) . chr(0xBF)); // UTF-8 BOM

                fputcsv($file, [
                    'ID',
                    'Marca',
                    'Modelo',
                    'Año',
                    'Color',
                    'Precio',
                    'Número de Serie',
                    'Combustible',
                    'Kilometraje',
                    'Placa',
                    'Fecha Creación'
                ]);

                foreach ($carros as $carro) {
                    fputcsv($file, [
                        $carro->id,
                        $carro->marca,
                        $carro->modelo,
                        $carro->anio,
                        $carro->color,
                        $carro->precio,
                        $carro->numero_serie,
                        $carro->combustible,
                        $carro->kilometraje,
                        $carro->placa ?? '',
                        $carro->created_at?->format('d/m/Y H:i:s')
                    ]);
                }
                fclose($file);
            };

            Log::info('Exportación de carros', ['total' => $carros->count()]);

            return response()->stream($callback, 200, $headers);
        } catch (\Exception $e) {
            Log::error('Error en exportación de carros: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Error al exportar los carros.');
        }
    }
}
