<?php

namespace App\Http\Controllers;

use App\Models\Almacen;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;
use Exception;
use Illuminate\Support\Facades\Log;

class AlmacenController extends Controller
{
    /**
     * Display a listing of warehouses.
     *
     * @return Response
     */
    public function index(): Response
    {
        try {
            $almacenes = Almacen::select(['id', 'nombre', 'descripcion', 'ubicacion', 'created_at'])
                ->orderBy('nombre')
                ->get();

            return Inertia::render('Almacenes/Index', [
                'almacenes' => $almacenes,
                'success' => session('success'),
                'error' => session('error'),
            ]);
        } catch (Exception $e) {
            return Inertia::render('Almacenes/Index', [
                'almacenes' => collect([]),
                'error' => 'Error al cargar los almacenes. Por favor, intente nuevamente.',
            ]);
        }
    }

    /**
     * Show the form for creating a new warehouse.
     *
     * @return Response
     */
    public function create(): Response
    {
        return Inertia::render('Almacenes/Create');
    }

    /**
     * Store a newly created warehouse in storage.
     *
     * @param Request $request
     * @return RedirectResponse
     * @throws ValidationException
     */
    public function store(Request $request): RedirectResponse
    {
        try {
            $validated = $this->validateWarehouse($request);

            $almacen = Almacen::create([
                'nombre' => trim($validated['nombre']),
                'descripcion' => isset($validated['descripcion']) ? trim($validated['descripcion']) : null,
                'ubicacion' => trim($validated['ubicacion']),
            ]);

            return redirect()
                ->route('almacenes.index')
                ->with('success', "El almacén '{$almacen->nombre}' ha sido creado exitosamente.");
        } catch (ValidationException $e) {
            throw $e;
        } catch (Exception $e) {
            return redirect()
                ->back()
                ->withInput()
                ->withErrors(['error' => 'Error al crear el almacén. Por favor, intente nuevamente.']);
        }
    }

    /**
     * Display the specified warehouse.
     *
     * @param Almacen $almacen
     * @return Response
     */
    public function show(Almacen $almacen): Response
    {
        $almacen->load(['productos' => function ($query) {
            $query->select(['id', 'nombre', 'almacen_id', 'stock']);
        }]);

        return Inertia::render('Almacenes/Show', [
            'almacen' => $almacen,
        ]);
    }

    /**
     * Muestra el formulario para editar un almacén existente.
     *
     * @param int $id
     * @return Response
     */
    public function edit(int $id): Response
    {
        try {
            $almacen = Almacen::select(['id', 'nombre', 'descripcion', 'ubicacion'])
                ->findOrFail($id);

            Log::info('Cargando almacén para edición', [
                'id' => $almacen->id,
                'nombre' => $almacen->nombre
            ]);

            return Inertia::render('Almacenes/Edit', [
                'almacen' => [
                    'id' => $almacen->id,
                    'nombre' => $almacen->nombre,
                    'descripcion' => $almacen->descripcion,
                    'ubicacion' => $almacen->ubicacion,
                ],
                'status' => [
                    'success' => session('success'),
                    'error' => session('error'),
                ],
            ]);
        } catch (ModelNotFoundException $e) {
            Log::warning('Almacén no encontrado para edición', ['id' => $id]);

            return Inertia::render('Almacenes/Index', [
                'almacenes' => Almacen::select(['id', 'nombre', 'descripcion', 'ubicacion', 'created_at'])
                    ->orderBy('nombre')
                    ->get(),
                'status' => [
                    'error' => 'El almacén solicitado no existe.'
                ],
            ]);
        } catch (Exception $e) {
            Log::error('Error al cargar almacén para edición', [
                'id' => $id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return Inertia::render('Almacenes/Index', [
                'almacenes' => Almacen::select(['id', 'nombre', 'descripcion', 'ubicacion', 'created_at'])
                    ->orderBy('nombre')
                    ->get(),
                'status' => [
                    'error' => 'Error al cargar el almacén para edición.'
                ],
            ]);
        }
    }
    /**
     * MÉTODO ALTERNATIVO usando Route Model Binding (si el anterior no funciona)
     * Reemplaza el método edit de arriba con este si tienes problemas:
     */
    /*
    public function edit(Almacen $almacen): Response
    {
        try {
            // Debug log
            \Log::info('Almacén recibido via model binding:', $almacen->toArray());

            // Verificar que el almacén existe
            if (!$almacen->exists) {
                throw new ModelNotFoundException();
            }

            return Inertia::render('Almacenes/Edit', [
                'almacen' => $almacen->toArray(), // Usar toArray() en lugar de only()
            ]);
        } catch (Exception $e) {
            \Log::error('Error en edit method:', ['error' => $e->getMessage()]);

            return redirect()
                ->route('almacenes.index')
                ->withErrors(['error' => 'Error al cargar el almacén para edición.']);
        }
    }
    */

    /**
     * Update the specified warehouse in storage.
     *
     * @param Request $request
     * @param int $id
     * @return RedirectResponse
     * @throws ValidationException
     */
    public function update(Request $request, $id): RedirectResponse
    {
        try {
            // Buscar el almacén explícitamente
            $almacen = Almacen::findOrFail($id);

            $validated = $this->validateWarehouse($request, $almacen->id);

            $almacen->update([
                'nombre' => trim($validated['nombre']),
                'descripcion' => isset($validated['descripcion']) ? trim($validated['descripcion']) : null,
                'ubicacion' => trim($validated['ubicacion']),
            ]);

            return redirect()
                ->route('almacenes.index')
                ->with('success', "El almacén '{$almacen->nombre}' ha sido actualizado exitosamente.");
        } catch (ModelNotFoundException $e) {
            return redirect()
                ->route('almacenes.index')
                ->withErrors(['error' => 'El almacén solicitado no existe.']);
        } catch (ValidationException $e) {
            throw $e;
        } catch (Exception $e) {
            Log::error('Error al actualizar almacén:', [
                'id' => $id,
                'error' => $e->getMessage()
            ]);

            return redirect()
                ->back()
                ->withInput()
                ->withErrors(['error' => 'Error al actualizar el almacén. Por favor, intente nuevamente.']);
        }
    }

    /**
     * Remove the specified warehouse from storage.
     *
     * @param int $id
     * @return RedirectResponse
     */
    public function destroy(int $id): RedirectResponse
    {
        try {
            $almacen = Almacen::findOrFail($id);

            // Check if warehouse has associated products
            if ($this->hasAssociatedProducts($almacen)) {
                return redirect()
                    ->back()
                    ->withErrors([
                        'error' => "No se puede eliminar el almacén '{$almacen->nombre}' porque tiene productos asociados. Primero elimine o reasigne los productos."
                    ]);
            }

            $nombreAlmacen = $almacen->nombre;
            $almacen->delete();

            return redirect()
                ->route('almacenes.index')
                ->with('success', "El almacén '{$nombreAlmacen}' ha sido eliminado exitosamente.");
        } catch (ModelNotFoundException $e) {
            return redirect()
                ->back()
                ->withErrors(['error' => 'El almacén solicitado no existe.']);
        } catch (Exception $e) {
            return redirect()
                ->back()
                ->withErrors(['error' => 'Error al eliminar el almacén. Por favor, intente nuevamente.']);
        }
    }

    /**
     * Validate warehouse data.
     *
     * @param Request $request
     * @param int|null $almacenId
     * @return array
     * @throws ValidationException
     */
    private function validateWarehouse(Request $request, ?int $almacenId = null): array
    {
        $uniqueRule = $almacenId
            ? "unique:almacenes,nombre,{$almacenId}"
            : 'unique:almacenes,nombre';

        return $request->validate([
            'nombre' => [
                'required',
                'string',
                'min:2',
                'max:100',
                $uniqueRule,
                'regex:/^[a-zA-ZáéíóúÁÉÍÓÚñÑ0-9\s\-_]+$/', // Solo letras, números, espacios, guiones y guión bajo
            ],
            'descripcion' => [
                'nullable',
                'string',
                'max:1000',
            ],
            'ubicacion' => [
                'required',
                'string',
                'min:5',
                'max:255',
            ],
        ], [
            'nombre.required' => 'El nombre del almacén es obligatorio.',
            'nombre.min' => 'El nombre debe tener al menos 2 caracteres.',
            'nombre.max' => 'El nombre no puede exceder los 100 caracteres.',
            'nombre.unique' => 'Ya existe un almacén con este nombre.',
            'nombre.regex' => 'El nombre solo puede contener letras, números, espacios, guiones y guión bajo.',
            'descripcion.max' => 'La descripción no puede exceder los 1000 caracteres.',
            'ubicacion.required' => 'La ubicación es obligatoria.',
            'ubicacion.min' => 'La ubicación debe tener al menos 5 caracteres.',
            'ubicacion.max' => 'La ubicación no puede exceder los 255 caracteres.',
        ]);
    }

    /**
     * Check if warehouse has associated products.
     *
     * @param Almacen $almacen
     * @return bool
     */
    private function hasAssociatedProducts(Almacen $almacen): bool
    {
        return $almacen->productos()->exists();
    }

    /**
     * Get warehouses for select options.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getSelectOptions()
    {
        try {
            $almacenes = Almacen::select(['id', 'nombre', 'ubicacion'])
                ->orderBy('nombre')
                ->get()
                ->map(function ($almacen) {
                    return [
                        'value' => $almacen->id,
                        'label' => $almacen->nombre,
                        'ubicacion' => $almacen->ubicacion,
                    ];
                });

            return response()->json([
                'success' => true,
                'data' => $almacenes,
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al cargar los almacenes.',
            ], 500);
        }
    }
}
