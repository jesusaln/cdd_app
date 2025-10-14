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
    private const ITEMS_PER_PAGE = 10;
    private const CACHE_TTL = 60;
    private const VALID_SORT_FIELDS = ['nombre_razon_social', 'rfc', 'email', 'created_at', 'activo'];
    private const VALID_SORT_DIRECTIONS = ['asc', 'desc'];
    private const DEFAULT_SORT_BY = 'created_at';
    private const DEFAULT_SORT_DIRECTION = 'desc';

    public function index(Request $request)
    {
        try {
            $perPage = (int) ($request->integer('per_page') ?: self::ITEMS_PER_PAGE);

            // Validar elementos por página
            $validPerPages = [10, 15, 25, 50, 100];
            if (!in_array($perPage, $validPerPages)) {
                $perPage = self::ITEMS_PER_PAGE;
            }

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

            // Filtrar por tipo de persona
            if ($request->filled('tipo_persona')) {
                $query->where('tipo_persona', $request->tipo_persona);
            }

            // Filtrar por estado (entidad federativa)
            if ($request->filled('estado')) {
                $query->where('estado', $request->estado);
            }

            // Ordenamiento
            $sortBy = $request->get('sort_by', self::DEFAULT_SORT_BY);
            $sortDirection = $request->get('sort_direction', self::DEFAULT_SORT_DIRECTION);

            if (!in_array($sortBy, self::VALID_SORT_FIELDS)) $sortBy = self::DEFAULT_SORT_BY;
            if (!in_array($sortDirection, self::VALID_SORT_DIRECTIONS)) $sortDirection = self::DEFAULT_SORT_DIRECTION;

            $query->orderBy($sortBy, $sortDirection)->orderBy('id', 'desc');

            // Paginación con appends para mantener filtros
            $proveedores = $query->paginate($perPage)->appends($request->query());

            // Estadísticas
            $stats = [
                'total' => Proveedor::count(),
                'activos' => Proveedor::where(function ($q) {
                    $q->where('activo', true)->orWhereNull('activo');
                })->count(),
                'inactivos' => Proveedor::where('activo', false)->count(),
                'personas_fisicas' => Proveedor::where('tipo_persona', 'fisica')->count(),
                'personas_morales' => Proveedor::where('tipo_persona', 'moral')->count(),
                'con_email' => Proveedor::whereNotNull('email')->where('email', '!=', '')->count(),
                'sin_email' => Proveedor::where(function ($q) {
                    $q->whereNull('email')->orWhere('email', '');
                })->count(),
            ];

            // Opciones para filtros
            $filterOptions = [
                'tipos_persona' => [
                    ['value' => 'fisica', 'label' => 'Persona Física'],
                    ['value' => 'moral', 'label' => 'Persona Moral'],
                ],
                'estados_activo' => [
                    ['value' => '', 'label' => 'Todos los Estados'],
                    ['value' => '1', 'label' => 'Activos'],
                    ['value' => '0', 'label' => 'Inactivos'],
                ],
                'per_page_options' => [10, 15, 25, 50, 100],
            ];

            return Inertia::render('Proveedores/Index', [
                'proveedores' => $proveedores,
                'stats' => $stats,
                'filterOptions' => $filterOptions,
                'filters' => $request->only(['search', 'activo', 'tipo_persona', 'estado']),
                'sorting' => [
                    'sort_by' => $sortBy,
                    'sort_direction' => $sortDirection,
                    'allowed_sorts' => self::VALID_SORT_FIELDS,
                ],
                'pagination' => [
                    'current_page' => $proveedores->currentPage(),
                    'last_page' => $proveedores->lastPage(),
                    'per_page' => $perPage,
                    'total' => $proveedores->total(),
                    'from' => $proveedores->firstItem(),
                    'to' => $proveedores->lastItem(),
                ],
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
        // Determinar si es una petición AJAX/modal (campos limitados)
        $isAjax = $request->expectsJson() || $request->ajax();

        if ($isAjax) {
            // Validación simplificada para modal
            $validated = $request->validate([
                'nombre_razon_social' => 'required|string|max:255',
                'rfc' => 'nullable|string|max:13',
                'telefono' => 'nullable|string|max:20',
                'email' => 'required|email|max:255|unique:proveedores,email',
            ]);

            // Agregar valores por defecto para campos requeridos en BD
            $validated = array_merge($validated, [
                'tipo_persona' => 'fisica',
                'regimen_fiscal' => '601', // Régimen general
                'uso_cfdi' => 'G01', // Adquisición de mercancías
                'calle' => 'Por definir',
                'numero_exterior' => 'S/N',
                'colonia' => 'Por definir',
                'codigo_postal' => '00000',
                'municipio' => 'Por definir',
                'estado' => 'Por definir',
                'pais' => 'México',
            ]);
        } else {
            // Validación completa para formulario normal
            $validated = $request->validate([
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
        }

        // Crear el proveedor
        $proveedor = Proveedor::create($validated);

        // Si es AJAX, devolver JSON
        if ($isAjax) {
            return response()->json([
                'success' => true,
                'proveedor' => $proveedor,
                'message' => 'Proveedor creado correctamente.'
            ]);
        }

        // Si no es AJAX, redirigir normalmente
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
