<?php

namespace App\Http\Controllers;

use Inertia\Inertia;
use App\Models\Prestamo;
use App\Models\Cliente;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use Illuminate\Validation\ValidationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class PrestamoController extends Controller
{
    private const ITEMS_PER_PAGE = 10;

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        try {
            $query = Prestamo::query()->with(['cliente']);

            // Filtros
            if ($search = $request->input('search')) {
                $query->whereHas('cliente', function ($q) use ($search) {
                    $q->where('nombre_razon_social', 'like', "%{$search}%")
                      ->orWhere('rfc', 'like', "%{$search}%");
                });
            }

            if ($estado = $request->input('estado')) {
                $query->where('estado', $estado);
            }

            if ($cliente_id = $request->input('cliente_id')) {
                $query->where('cliente_id', $cliente_id);
            }

            // Ordenamiento
            $sortBy = $request->get('sort_by', 'created_at');
            $sortDirection = $request->get('sort_direction', 'desc');

            $query->orderBy($sortBy, $sortDirection);

            $prestamos = $query->paginate(self::ITEMS_PER_PAGE)->appends($request->query());

            // Estadísticas
            $estadisticas = [
                'total' => Prestamo::count(),
                'activos' => Prestamo::where('estado', 'activo')->count(),
                'completados' => Prestamo::where('estado', 'completado')->count(),
                'cancelados' => Prestamo::where('estado', 'cancelado')->count(),
                'monto_total_prestado' => Prestamo::sum('monto_prestado'),
                'monto_total_pagado' => Prestamo::sum('monto_pagado'),
                'monto_total_pendiente' => Prestamo::sum('monto_pendiente'),
            ];

            return Inertia::render('Prestamos/Index', [
                'prestamos' => $prestamos,
                'estadisticas' => $estadisticas,
                'filters' => $request->only(['search', 'estado', 'cliente_id']),
                'sorting' => ['sort_by' => $sortBy, 'sort_direction' => $sortDirection],
            ]);
        } catch (\Exception $e) {
            Log::error('Error en PrestamoController@index: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Error al cargar la lista de préstamos.');
        }
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        try {
            // Obtener clientes activos para el componente de búsqueda
            $clientes = Cliente::where('activo', true)
                ->orderBy('nombre_razon_social')
                ->get([
                    'id',
                    'nombre_razon_social',
                    'rfc',
                    'email',
                    'telefono',
                    'estado',
                    'limite_credito',
                    'credito_disponible'
                ]);

            return Inertia::render('Prestamos/Create', [
                'clientes' => $clientes,
                'prestamo' => [
                    'cliente_id' => null,
                    'monto_prestado' => 0,
                    'tasa_interes' => 0,
                    'numero_pagos' => 12,
                    'frecuencia_pago' => 'mensual',
                    'fecha_inicio' => now()->format('Y-m-d'),
                    'descripcion' => null,
                    'notas' => null,
                ],
            ]);
        } catch (\Exception $e) {
            Log::error('Error en PrestamoController@create: ' . $e->getMessage());
            return redirect()->route('prestamos.index')->with('error', 'Error al cargar el formulario de creación.');
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        DB::beginTransaction();

        try {
            $validated = $request->validate([
                'cliente_id' => 'required|exists:clientes,id',
                'monto_prestado' => 'required|numeric|min:0.01|max:999999999.99',
                'tasa_interes_mensual' => 'required|numeric|min:0|max:100',
                'numero_pagos' => 'required|integer|min:1|max:1200',
                'frecuencia_pago' => 'required|in:semanal,quincenal,mensual',
                'fecha_inicio' => 'required|date|after_or_equal:today',
                'fecha_primer_pago' => 'nullable|date|after:fecha_inicio',
                'descripcion' => 'nullable|string|max:1000',
                'notas' => 'nullable|string|max:2000',
            ]);

            // Crear instancia del préstamo
            $prestamo = new Prestamo($validated);

            // Calcular fechas y pagos
            $this->calcularFechasYPagos($prestamo);

            // Calcular montos financieros con fórmula corregida
            $calculos = $prestamo->calcularPagos();
            $prestamo->pago_periodico = $calculos['pago_periodico'];
            $prestamo->monto_interes_total = $calculos['interes_total'];
            $prestamo->monto_total_pagar = $calculos['total_pagar'];
            $prestamo->monto_pendiente = $calculos['total_pagar'];
            $prestamo->pagos_pendientes = $prestamo->numero_pagos;

            $prestamo->save();

            DB::commit();

            Log::info('Préstamo creado', ['id' => $prestamo->id, 'cliente_id' => $prestamo->cliente_id]);

            return redirect()->route('prestamos.index')->with('success', 'Préstamo creado correctamente');
        } catch (ValidationException $e) {
            DB::rollBack();
            throw $e;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error al crear préstamo: ' . $e->getMessage(), ['data' => $request->all()]);
            throw $e;
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Prestamo $prestamo)
    {
        try {
            $prestamo->load(['cliente']);

            return Inertia::render('Prestamos/Show', [
                'prestamo' => $prestamo,
            ]);
        } catch (ModelNotFoundException $e) {
            return redirect()->route('prestamos.index')->with('error', 'Préstamo no encontrado.');
        } catch (\Exception $e) {
            Log::error('Error al mostrar préstamo: ' . $e->getMessage());
            return redirect()->route('prestamos.index')->with('error', 'Error al cargar el préstamo.');
        }
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Prestamo $prestamo)
    {
        try {
            $prestamo->load(['cliente']);

            // Obtener clientes activos para el componente de búsqueda
            $clientes = Cliente::where('activo', true)
                ->orderBy('nombre_razon_social')
                ->get([
                    'id',
                    'nombre_razon_social',
                    'rfc',
                    'email',
                    'telefono',
                    'estado',
                    'limite_credito',
                    'credito_disponible'
                ]);

            return Inertia::render('Prestamos/Edit', [
                'prestamo' => $prestamo,
                'clientes' => $clientes,
            ]);
        } catch (ModelNotFoundException $e) {
            return redirect()->route('prestamos.index')->with('error', 'Préstamo no encontrado.');
        } catch (\Exception $e) {
            Log::error('Error al cargar formulario de edición: ' . $e->getMessage());
            return redirect()->route('prestamos.index')->with('error', 'Error al cargar el formulario de edición.');
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Prestamo $prestamo)
    {
        DB::beginTransaction();

        try {
            $validated = $request->validate([
                'cliente_id' => 'required|exists:clientes,id',
                'monto_prestado' => 'required|numeric|min:0.01|max:999999999.99',
                'tasa_interes_mensual' => 'required|numeric|min:0|max:100',
                'numero_pagos' => 'required|integer|min:1|max:1200',
                'frecuencia_pago' => 'required|in:semanal,quincenal,mensual',
                'fecha_inicio' => 'required|date',
                'fecha_primer_pago' => 'nullable|date|after:fecha_inicio',
                'descripcion' => 'nullable|string|max:1000',
                'notas' => 'nullable|string|max:2000',
            ]);

            // Si el préstamo ya tiene pagos, no permitir cambios en montos o términos
            if ($prestamo->pagos_realizados > 0) {
                $camposProhibidos = ['monto_prestado', 'tasa_interes', 'numero_pagos', 'frecuencia_pago'];
                foreach ($camposProhibidos as $campo) {
                    if (isset($validated[$campo]) && $validated[$campo] != $prestamo->$campo) {
                        throw ValidationException::withMessages([
                            $campo => 'No se puede modificar este campo porque el préstamo ya tiene pagos registrados.'
                        ]);
                    }
                }
            }

            // Actualizar préstamo
            $prestamo->fill($validated);

            // Recalcular si es necesario
            if ($prestamo->pagos_realizados == 0) {
                $this->calcularFechasYPagos($prestamo);
                $calculos = $prestamo->calcularPagos();
                $prestamo->pago_periodico = $calculos['pago_periodico'];
                $prestamo->monto_interes_total = $calculos['interes_total'];
                $prestamo->monto_total_pagar = $calculos['total_pagar'];
                $prestamo->monto_pendiente = $calculos['total_pagar'];
                $prestamo->pagos_pendientes = $prestamo->numero_pagos;
            }

            $prestamo->save();

            DB::commit();

            Log::info('Préstamo actualizado', ['id' => $prestamo->id]);

            return redirect()->route('prestamos.index')->with('success', 'Préstamo actualizado correctamente');
        } catch (ValidationException $e) {
            DB::rollBack();
            throw $e;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error al actualizar préstamo: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Prestamo $prestamo)
    {
        try {
            if (!$prestamo->puedeSerEliminado()) {
                return redirect()->back()->with('error',
                    'No se puede eliminar el préstamo porque tiene pagos registrados o está activo.');
            }

            Log::info('Eliminando préstamo', [
                'id' => $prestamo->id,
                'cliente_id' => $prestamo->cliente_id
            ]);

            $prestamo->delete();

            return redirect()->route('prestamos.index')->with('success', 'Préstamo eliminado correctamente');
        } catch (ModelNotFoundException $e) {
            return redirect()->route('prestamos.index')->with('error', 'Préstamo no encontrado.');
        } catch (\Exception $e) {
            Log::error('Error al eliminar préstamo: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Error interno al eliminar el préstamo.');
        }
    }

    /**
     * Calcular fechas de pagos y actualizar modelo
     */
    private function calcularFechasYPagos(Prestamo $prestamo): void
    {
        $fechaInicio = $prestamo->fecha_inicio;

        // Si no hay fecha de primer pago, calcular según frecuencia
        if (!$prestamo->fecha_primer_pago) {
            $diasSumar = match($prestamo->frecuencia_pago) {
                'semanal' => 7,
                'quincenal' => 15,
                'mensual' => 30,
                default => 30,
            };

            $prestamo->fecha_primer_pago = $fechaInicio->copy()->addDays($diasSumar);
        }
    }

    /**
     * API para calcular pagos en tiempo real
     */
    public function calcularPagos(Request $request)
    {
        try {
            $validated = $request->validate([
                'monto_prestado' => 'required|numeric|min:0.01',
                'tasa_interes_mensual' => 'required|numeric|min:0|max:100',
                'numero_pagos' => 'required|integer|min:1|max:1200',
                'frecuencia_pago' => 'required|in:semanal,quincenal,mensual',
            ]);

            $prestamo = new Prestamo($validated);
            $calculos = $prestamo->calcularPagos();

            return response()->json([
                'success' => true,
                'calculos' => $calculos,
            ]);
        } catch (\Exception $e) {
            Log::error('Error calculando pagos: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error al calcular pagos'
            ], 500);
        }
    }

    /**
     * Cambiar estado del préstamo
     */
    public function cambiarEstado(Request $request, Prestamo $prestamo)
    {
        try {
            $validated = $request->validate([
                'estado' => 'required|in:activo,completado,cancelado',
            ]);

            $prestamo->estado = $validated['estado'];
            $prestamo->save();

            Log::info('Estado de préstamo cambiado', [
                'id' => $prestamo->id,
                'nuevo_estado' => $validated['estado']
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Estado actualizado correctamente',
                'prestamo' => $prestamo,
            ]);
        } catch (\Exception $e) {
            Log::error('Error cambiando estado: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error al cambiar estado'
            ], 500);
        }
    }
}
