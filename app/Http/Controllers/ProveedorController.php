<?php

namespace App\Http\Controllers;

use Inertia\Inertia;
use App\Models\Proveedor;
use Illuminate\Http\Request;
// Importing the Log facade for logging
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;
use Exception;

class ProveedorController extends Controller
{
    public function index(Request $request)
    {
        try {
            $query = Proveedor::query();

            // Filtros de búsqueda
            if ($s = trim((string) $request->input('search', ''))) {
                $query->where(function ($w) use ($s) {
                    $w->where('nombre_razon_social', 'like', "%{$s}%")
                        ->orWhere('rfc', 'like', "%{$s}%")
                        ->orWhere('email', 'like', "%{$s}%");
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
            $validSort = ['nombre_razon_social', 'rfc', 'email', 'created_at', 'activo'];

            if (!in_array($sortBy, $validSort)) $sortBy = 'created_at';
            if (!in_array($sortDirection, ['asc', 'desc'])) $sortDirection = 'desc';

            $query->orderBy($sortBy, $sortDirection);

            // Paginación
            $proveedores = $query->paginate(10)->appends($request->query());

            // Estadísticas
            $proveedoresCount = Proveedor::count();
            $proveedoresActivos = Proveedor::where(function ($q) {
                $q->where('activo', true)->orWhereNull('activo');
            })->count();

            return Inertia::render('Proveedores/Index', [
                'proveedores' => $proveedores,
                'stats' => [
                    'total' => $proveedoresCount,
                    'activos' => $proveedoresActivos,
                    'inactivos' => $proveedoresCount - $proveedoresActivos,
                ],
                'filters' => $request->only(['search', 'activo']),
                'sorting' => ['sort_by' => $sortBy, 'sort_direction' => $sortDirection],
            ]);
        } catch (Exception $e) {
            Log::error('Error en ProveedorController@index: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Error al cargar la lista de proveedores.');
        }
    }

    // Método para mostrar el formulario de creación de proveedores
    public function create()
    {
        return Inertia::render('Proveedores/Create');
    }

    public function store(Request $request)
    {
        // Valida los datos del formulario
        $request->validate([
            'nombre_razon_social' => 'required|string|max:255',
            'rfc' => 'nullable|string|max:20',
            'contacto' => 'nullable|string|max:255',
            'telefono' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:255|unique:proveedores,email',
            'direccion' => 'nullable|string|max:255',
            'codigo_postal' => 'nullable|string|max:10',
            'municipio' => 'nullable|string|max:255',
            'estado' => 'nullable|string|max:255',
            'pais' => 'nullable|string|max:255',
        ]);

        // Crea un nuevo proveedor
        Proveedor::create($request->all());

        return redirect()->route('proveedores.index')->with('success', 'Proveedor creado correctamente.');
    }

    public function edit($id)
    {
        $proveedor = Proveedor::findOrFail($id);
        return Inertia::render('Proveedores/Edit', [
            'proveedor' => $proveedor
        ]);
    }
    public function update(Request $request, Proveedor $proveedor)
    {
        if (!$proveedor->exists) {
            Log::error('No se encontró el proveedor para actualizar');
            return response()->json(['error' => 'Proveedor no encontrado'], 404);
        }

        Log::info('ID del proveedor a actualizar: ' . $proveedor->id);
        Log::info('Datos recibidos para actualización:', $request->all());

        $validated = $request->validate([
            'nombre_razon_social' => 'required|string|max:255',
            'tipo_persona' => 'required|in:fisica,moral',
            'rfc' => [
                'nullable',
                'string',
                'max:20', // Corregido: Sin espacios adicionales
                Rule::unique('proveedores', 'rfc')->ignore($proveedor->id),
            ],
            'regimen_fiscal' => 'nullable|string|max:255',
            'uso_cfdi' => 'nullable|string|max:255',
            'email' => [
                'nullable',
                'email',
                'max:255',
                Rule::unique('proveedores', 'email')->ignore($proveedor->id),
            ],
            'telefono' => 'nullable|string|max:20',
            'calle' => 'nullable|string|max:255',
            'numero_exterior' => 'nullable|string|max:20',
            'numero_interior' => 'nullable|string|max:20',
            'colonia' => 'nullable|string|max:255',
            'codigo_postal' => 'nullable|string|max:10',
            'municipio' => 'nullable|string|max:255',
            'estado' => 'nullable|string|max:255',
            'pais' => 'nullable|string|max:255',
        ]);



        $proveedor->update($validated);
        Log::info('Proveedor actualizado:', $proveedor->toArray());

        return redirect()->route('proveedores.index')->with('success', 'Proveedor actualizado correctamente.');
    }

    public function destroy($id)
    {
        $proveedor = Proveedor::findOrFail($id);
        $proveedor->delete();

        return redirect()->route('proveedores.index')->with('success', 'Proveedor eliminado correctamente.');
    }

    public function toggle(Proveedor $proveedor)
    {
        try {
            $proveedor->update(['activo' => !$proveedor->activo]);
            return redirect()->back()->with('success', $proveedor->activo ? 'Proveedor activado correctamente' : 'Proveedor desactivado correctamente');
        } catch (Exception $e) {
            Log::error('Error cambiando estado del proveedor: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Hubo un problema al cambiar el estado del proveedor.');
        }
    }

    public function export(Request $request)
    {
        try {
            $query = Proveedor::query();

            // Aplicar los mismos filtros que en index
            if ($s = trim((string) $request->input('search', ''))) {
                $query->where(function ($w) use ($s) {
                    $w->where('nombre_razon_social', 'like', "%{$s}%")
                        ->orWhere('rfc', 'like', "%{$s}%")
                        ->orWhere('email', 'like', "%{$s}%");
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

            $proveedores = $query->get();

            $filename = 'proveedores_' . date('Y-m-d_H-i-s') . '.csv';

            $headers = [
                'Content-Type' => 'text/csv; charset=UTF-8',
                'Content-Disposition' => 'attachment; filename="' . $filename . '"',
            ];

            $callback = function () use ($proveedores) {
                $file = fopen('php://output', 'w');
                fprintf($file, chr(0xEF) . chr(0xBB) . chr(0xBF));

                fputcsv($file, [
                    'ID',
                    'Nombre/Razón Social',
                    'RFC',
                    'Email',
                    'Teléfono',
                    'Dirección',
                    'Estado',
                    'Activo',
                    'Fecha Creación'
                ]);

                foreach ($proveedores as $proveedor) {
                    $direccion = trim(implode(' ', [
                        $proveedor->calle,
                        $proveedor->numero_exterior,
                        $proveedor->numero_interior ? "Int. {$proveedor->numero_interior}" : '',
                        $proveedor->colonia,
                        $proveedor->municipio
                    ]));

                    fputcsv($file, [
                        $proveedor->id,
                        $proveedor->nombre_razon_social,
                        $proveedor->rfc,
                        $proveedor->email,
                        $proveedor->telefono,
                        $direccion,
                        $proveedor->estado,
                        $proveedor->activo ? 'Sí' : 'No',
                        $proveedor->created_at?->format('d/m/Y H:i:s')
                    ]);
                }
                fclose($file);
            };

            return response()->stream($callback, 200, $headers);
        } catch (Exception $e) {
            Log::error('Error en exportación de proveedores: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Error al exportar los proveedores.');
        }
    }

    public function checkEmail(Request $request)
    {
        $exists = Proveedor::where('email', $request->query('email'))->exists();
        return response()->json(['exists' => $exists]);
    }
}
