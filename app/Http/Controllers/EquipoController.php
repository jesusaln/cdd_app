<?php

namespace App\Http\Controllers;

use Inertia\Inertia;
use App\Models\Equipo;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class EquipoController extends Controller
{
    public function index(Request $request)
    {
        try {
            $query = Equipo::query();

            // Filtros
            if ($search = trim($request->input('search', ''))) {
                $query->where(function ($q) use ($search) {
                    $q->where('nombre', 'like', "%{$search}%")
                      ->orWhere('codigo', 'like', "%{$search}%")
                      ->orWhere('marca', 'like', "%{$search}%")
                      ->orWhere('modelo', 'like', "%{$search}%")
                      ->orWhere('numero_serie', 'like', "%{$search}%");
                });
            }

            if ($estado = $request->input('estado')) {
                $query->where('estado', $estado);
            }

            if ($condicion = $request->input('condicion')) {
                $query->where('condicion', $condicion);
            }

            // Ordenamiento
            $sortBy = $request->input('sort_by', 'created_at');
            $sortDirection = $request->input('sort_direction', 'desc');

            $validSortFields = ['nombre', 'codigo', 'marca', 'modelo', 'precio_renta_mensual', 'estado', 'condicion', 'created_at'];
            if (!in_array($sortBy, $validSortFields)) {
                $sortBy = 'created_at';
            }

            $query->orderBy($sortBy, $sortDirection);

            // Paginación
            $perPage = min((int) $request->input('per_page', 10), 50);
            $equipos = $query->paginate($perPage)->appends($request->query());

            // Estadísticas
            $total = Equipo::count();
            $disponibles = Equipo::where('estado', 'disponible')->count();
            $rentados = Equipo::where('estado', 'rentado')->count();
            $mantenimiento = Equipo::where('estado', 'mantenimiento')->count();

            $stats = [
                'total' => $total,
                'disponibles' => $disponibles,
                'rentados' => $rentados,
                'mantenimiento' => $mantenimiento,
                'disponibles_porcentaje' => $total > 0 ? round(($disponibles / $total) * 100, 1) : 0,
                'rentados_porcentaje' => $total > 0 ? round(($rentados / $total) * 100, 1) : 0,
                'mantenimiento_porcentaje' => $total > 0 ? round(($mantenimiento / $total) * 100, 1) : 0,
            ];

            return Inertia::render('Equipos/Index', [
                'equipos' => $equipos,
                'stats' => $stats,
                'filters' => $request->only(['search', 'estado', 'condicion']),
                'sorting' => ['sort_by' => $sortBy, 'sort_direction' => $sortDirection],
            ]);
        } catch (\Exception $e) {
            Log::error('Error en EquipoController@index: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Error al cargar los equipos.');
        }
    }

    public function create()
    {
        // 👈 AQUÍ ESTABA EL PROBLEMA: devolver Inertia, no view()
        return Inertia::render('Equipos/Create', [
            // Si necesitas catálogos iniciales, pásalos aquí
            'estados' => ['disponible', 'rentado', 'mantenimiento', 'reparacion', 'fuera_servicio'], // ver nota (punto 4)
            'condiciones' => ['nuevo', 'excelente', 'bueno', 'regular', 'malo'],
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'codigo' => ['required', 'string', 'max:255', 'unique:equipos,codigo'],
            'nombre' => ['required', 'string', 'max:255'],
            'marca' => ['nullable', 'string', 'max:255'],
            'modelo' => ['nullable', 'string', 'max:255'],
            'numero_serie' => ['nullable', 'string', 'max:255', 'unique:equipos,numero_serie'],
            'descripcion' => ['nullable', 'string'],
            'especificaciones' => ['nullable', 'array'],
            'imagen' => ['nullable', 'image', 'mimes:jpeg,png,jpg,gif', 'max:2048'],
            'precio_renta_mensual' => ['required', 'numeric', 'min:0'],
            'precio_compra' => ['nullable', 'numeric', 'min:0'],
            'fecha_adquisicion' => ['nullable', 'date'],
            'estado' => ['required', 'in:disponible,rentado,mantenimiento,reparacion,fuera_servicio'],
            'condicion' => ['required', 'in:nuevo,excelente,bueno,regular,malo'],
            'ubicacion_fisica' => ['nullable', 'string', 'max:255'],
            'notas' => ['nullable', 'string'],
            'accesorios' => ['nullable', 'array'],
            'fecha_garantia' => ['nullable', 'date'],
            'proveedor' => ['nullable', 'string', 'max:255'],
        ]);

        // Si llegan arrays/objetos para JSON:
        $data['especificaciones'] = $data['especificaciones'] ?? null;
        $data['accesorios'] = $data['accesorios'] ?? null;

        // Manejar imagen
        if ($request->hasFile('imagen')) {
            $imagenPath = $request->file('imagen')->store('equipos', 'public');
            $data['imagen'] = $imagenPath;
        }

        $equipo = Equipo::create($data);

        return redirect()->route('equipos.index')->with('success', 'Equipo creado correctamente.');
    }
    // 🔹 EDIT
    public function edit(Equipo $equipo)
    {
        return Inertia::render('Equipos/Edit', [
            'equipo' => [
                'id' => $equipo->id,
                'codigo' => $equipo->codigo,
                'nombre' => $equipo->nombre,
                'marca' => $equipo->marca,
                'modelo' => $equipo->modelo,
                'numero_serie' => $equipo->numero_serie,
                'descripcion' => $equipo->descripcion,
                'especificaciones' => $equipo->especificaciones, // cast array
                'precio_renta_mensual' => $equipo->precio_renta_mensual,
                'precio_compra' => $equipo->precio_compra,
                'fecha_adquisicion' => optional($equipo->fecha_adquisicion)->format('Y-m-d'),
                'estado' => $equipo->estado,
                'condicion' => $equipo->condicion,
                'ubicacion_fisica' => $equipo->ubicacion_fisica,
                'notas' => $equipo->notas,
                'accesorios' => $equipo->accesorios, // cast array
                'fecha_garantia' => optional($equipo->fecha_garantia)->format('Y-m-d'),
                'proveedor' => $equipo->proveedor,
                'created_at' => $equipo->created_at,
                'updated_at' => $equipo->updated_at,
            ],
            'estados' => ['disponible', 'rentado', 'mantenimiento', 'reparacion', 'fuera_servicio'],
            'condiciones' => ['nuevo', 'excelente', 'bueno', 'regular', 'malo'],
        ]);
    }

    // 🔹 UPDATE
    public function update(Request $request, Equipo $equipo)
    {
        $data = $request->validate([
            'codigo' => ['required', 'string', 'max:255', Rule::unique('equipos', 'codigo')->ignore($equipo->id)],
            'nombre' => ['required', 'string', 'max:255'],
            'marca' => ['nullable', 'string', 'max:255'],
            'modelo' => ['nullable', 'string', 'max:255'],
            'numero_serie' => ['nullable', 'string', 'max:255', Rule::unique('equipos', 'numero_serie')->ignore($equipo->id)],
            'descripcion' => ['nullable', 'string'],
            'especificaciones' => ['nullable', 'array'],
            'imagen' => ['nullable', 'image', 'mimes:jpeg,png,jpg,gif', 'max:2048'],
            'precio_renta_mensual' => ['required', 'numeric', 'min:0'],
            'precio_compra' => ['nullable', 'numeric', 'min:0'],
            'fecha_adquisicion' => ['nullable', 'date'],
            'estado' => ['required', Rule::in(['disponible', 'rentado', 'mantenimiento', 'reparacion', 'fuera_servicio'])],
            'condicion' => ['required', Rule::in(['nuevo', 'excelente', 'bueno', 'regular', 'malo'])],
            'ubicacion_fisica' => ['nullable', 'string', 'max:255'],
            'notas' => ['nullable', 'string'],
            'accesorios' => ['nullable', 'array'],
            'fecha_garantia' => ['nullable', 'date'],
            'proveedor' => ['nullable', 'string', 'max:255'],
        ]);

        $data['especificaciones'] = $data['especificaciones'] ?? null;
        $data['accesorios'] = $data['accesorios'] ?? null;

        // Manejar imagen
        if ($request->hasFile('imagen')) {
            // Eliminar imagen anterior si existe
            if ($equipo->imagen) {
                Storage::disk('public')->delete($equipo->imagen);
            }
            $imagenPath = $request->file('imagen')->store('equipos', 'public');
            $data['imagen'] = $imagenPath;
        }

        $equipo->update($data);

        return redirect()->route('equipos.index')->with('success', 'Equipo actualizado correctamente.');
    }

    public function destroy(Equipo $equipo)
    {
        $equipo->delete();
        return redirect()->route('equipos.index')->with('success', 'Equipo eliminado correctamente.');
    }

    public function toggle(Equipo $equipo)
    {
        try {
            // For equipos, toggle between disponible and fuera_servicio or something similar
            $nuevoEstado = $equipo->estado === 'disponible' ? 'fuera_servicio' : 'disponible';
            $equipo->update(['estado' => $nuevoEstado]);

            $mensaje = $nuevoEstado === 'disponible' ? 'Equipo activado correctamente' : 'Equipo desactivado correctamente';

            return redirect()->back()->with('success', $mensaje);
        } catch (\Exception $e) {
            Log::error('Error al cambiar estado del equipo: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Error al cambiar el estado del equipo.');
        }
    }

    public function export(Request $request)
    {
        try {
            $query = Equipo::query();

            // Aplicar los mismos filtros que en index
            if ($search = trim($request->input('search', ''))) {
                $query->where(function ($q) use ($search) {
                    $q->where('nombre', 'like', "%{$search}%")
                      ->orWhere('codigo', 'like', "%{$search}%")
                      ->orWhere('marca', 'like', "%{$search}%")
                      ->orWhere('modelo', 'like', "%{$search}%")
                      ->orWhere('numero_serie', 'like', "%{$search}%");
                });
            }

            if ($estado = $request->input('estado')) {
                $query->where('estado', $estado);
            }

            if ($condicion = $request->input('condicion')) {
                $query->where('condicion', $condicion);
            }

            $equipos = $query->get();

            $filename = 'equipos_' . date('Y-m-d_H-i-s') . '.csv';

            $headers = [
                'Content-Type' => 'text/csv; charset=UTF-8',
                'Content-Disposition' => 'attachment; filename="' . $filename . '"',
            ];

            $callback = function () use ($equipos) {
                $file = fopen('php://output', 'w');
                fprintf($file, chr(0xEF) . chr(0xBB) . chr(0xBF)); // UTF-8 BOM

                fputcsv($file, [
                    'ID', 'Código', 'Nombre', 'Marca', 'Modelo', 'Número Serie',
                    'Precio Renta Mensual', 'Estado', 'Condición', 'Fecha Creación'
                ]);

                foreach ($equipos as $equipo) {
                    fputcsv($file, [
                        $equipo->id,
                        $equipo->codigo,
                        $equipo->nombre,
                        $equipo->marca ?? '',
                        $equipo->modelo ?? '',
                        $equipo->numero_serie ?? '',
                        $equipo->precio_renta_mensual,
                        $equipo->estado,
                        $equipo->condicion,
                        $equipo->created_at?->format('d/m/Y H:i:s')
                    ]);
                }
                fclose($file);
            };

            Log::info('Exportación de equipos', ['total' => $equipos->count()]);

            return response()->stream($callback, 200, $headers);
        } catch (\Exception $e) {
            Log::error('Error en exportación de equipos: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Error al exportar los equipos.');
        }
    }
}
