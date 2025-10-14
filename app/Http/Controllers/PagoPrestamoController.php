<?php

namespace App\Http\Controllers;

use Inertia\Inertia;
use App\Models\PagoPrestamo;
use App\Models\Prestamo;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class PagoPrestamoController extends Controller
{
    private const ITEMS_PER_PAGE = 15;

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        try {
            $query = PagoPrestamo::query()->with(['prestamo.cliente', 'historialPagos']);

            // Filtros
            if ($prestamo_id = $request->input('prestamo_id')) {
                $query->where('prestamo_id', $prestamo_id);
            }

            if ($estado = $request->input('estado')) {
                $query->where('estado', $estado);
            }

            if ($fecha_desde = $request->input('fecha_desde')) {
                $query->where('fecha_programada', '>=', $fecha_desde);
            }

            if ($fecha_hasta = $request->input('fecha_hasta')) {
                $query->where('fecha_programada', '<=', $fecha_hasta);
            }

            // Solo mostrar pagos de préstamos activos
            $query->whereHas('prestamo', function ($q) {
                $q->where('estado', 'activo');
            });

            // Ordenamiento
            $sortBy = $request->get('sort_by', 'fecha_programada');
            $sortDirection = $request->get('sort_direction', 'asc');

            $query->orderBy($sortBy, $sortDirection);

            $pagos = $query->paginate(self::ITEMS_PER_PAGE)->appends($request->query());

            // Estadísticas
            $estadisticas = [
                'total_pagos' => PagoPrestamo::count(),
                'pagos_pendientes' => PagoPrestamo::where('estado', 'pendiente')->count(),
                'pagos_pagados' => PagoPrestamo::where('estado', 'pagado')->count(),
                'pagos_atrasados' => PagoPrestamo::where('estado', 'atrasado')->count(),
                'monto_pendiente' => PagoPrestamo::where('estado', 'pendiente')->sum('monto_programado'),
                'monto_pagado_hoy' => PagoPrestamo::where('fecha_pago', now()->toDateString())->sum('monto_pagado'),
            ];

            // Obtener préstamos activos para el filtro
            $prestamos = Prestamo::where('estado', 'activo')
                ->with('cliente')
                ->orderBy('created_at', 'desc')
                ->get(['id', 'cliente_id', 'monto_prestado', 'pago_periodico'])
                ->map(function ($prestamo) {
                    return [
                        'id' => $prestamo->id,
                        'cliente_nombre' => $prestamo->cliente->nombre_razon_social,
                        'monto_prestado' => $prestamo->monto_prestado,
                        'pago_periodico' => $prestamo->pago_periodico,
                    ];
                });

            return Inertia::render('Pagos/Index', [
                'pagos' => $pagos,
                'estadisticas' => $estadisticas,
                'prestamos' => $prestamos,
                'filters' => $request->only(['prestamo_id', 'estado', 'fecha_desde', 'fecha_hasta']),
                'sorting' => ['sort_by' => $sortBy, 'sort_direction' => $sortDirection],
            ]);
        } catch (\Exception $e) {
            Log::error('Error en PagoPrestamoController@index: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Error al cargar la lista de pagos.');
        }
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(Request $request)
    {
        try {
            $prestamoId = $request->get('prestamo_id');

            if (!$prestamoId) {
                return redirect()->route('pagos.index')->with('error', 'Debe seleccionar un préstamo.');
            }

            $prestamo = Prestamo::with('cliente')->findOrFail($prestamoId);

            // Crear pagos programados si no existen
            if (!$prestamo->pagos()->exists()) {
                $prestamo->crearPagosProgramados();
            }

            // Obtener pagos pendientes del préstamo con historial
            $pagosPendientes = $prestamo->pagos()
                ->with('historialPagos')
                ->whereIn('estado', ['pendiente', 'parcial'])
                ->orderBy('numero_pago')
                ->get();

            if ($pagosPendientes->isEmpty()) {
                return redirect()->route('pagos.index')->with('warning', 'Este préstamo no tiene pagos pendientes.');
            }

            return Inertia::render('Pagos/Create', [
                'prestamo' => $prestamo,
                'pagos_pendientes' => $pagosPendientes,
            ]);
        } catch (ModelNotFoundException $e) {
            return redirect()->route('pagos.index')->with('error', 'Préstamo no encontrado.');
        } catch (\Exception $e) {
            Log::error('Error en PagoPrestamoController@create: ' . $e->getMessage());
            return redirect()->route('pagos.index')->with('error', 'Error al cargar el formulario de pago.');
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
                'prestamo_id' => 'required|exists:prestamos,id',
                'pago_id' => 'required|exists:pagos_prestamos,id',
                'monto_pagado' => 'required|numeric|min:0.01',
                'fecha_pago' => 'required|date',
                'metodo_pago' => 'nullable|string|max:100',
                'referencia' => 'nullable|string|max:255',
                'notas' => 'nullable|string|max:1000',
            ]);

            $pago = PagoPrestamo::findOrFail($validated['pago_id']);
            $prestamo = $pago->prestamo;

            // Verificar que el pago pertenece al préstamo
            if ($pago->prestamo_id != $validated['prestamo_id']) {
                throw ValidationException::withMessages([
                    'pago_id' => 'El pago seleccionado no pertenece al préstamo especificado.'
                ]);
            }

            // Agregar el pago (acumula si es parcial)
            $exito = $pago->agregarPago(
                $validated['monto_pagado'],
                $validated['fecha_pago'],
                $validated['metodo_pago'],
                $validated['referencia']
            );

            if (!$exito) {
                throw ValidationException::withMessages([
                    'monto_pagado' => 'El monto debe ser mayor a cero.'
                ]);
            }

            // Actualizar información adicional
            $pago->update([
                'metodo_pago' => $validated['metodo_pago'],
                'referencia' => $validated['referencia'],
                'notas' => $validated['notas'],
                'confirmado' => true,
            ]);

            DB::commit();

            Log::info('Pago registrado', [
                'pago_id' => $pago->id,
                'prestamo_id' => $prestamo->id,
                'monto' => $validated['monto_pagado']
            ]);

            return redirect()->route('pagos.index')->with('success', 'Pago registrado correctamente');
        } catch (ValidationException $e) {
            DB::rollBack();
            throw $e;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error al registrar pago: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(PagoPrestamo $pago)
    {
        try {
            $pago->load(['prestamo.cliente', 'historialPagos']);

            return Inertia::render('Pagos/Show', [
                'pago' => $pago,
            ]);
        } catch (ModelNotFoundException $e) {
            return redirect()->route('pagos.index')->with('error', 'Pago no encontrado.');
        } catch (\Exception $e) {
            Log::error('Error al mostrar pago: ' . $e->getMessage());
            return redirect()->route('pagos.index')->with('error', 'Error al cargar el pago.');
        }
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(PagoPrestamo $pago)
    {
        try {
            $pago->load(['prestamo.cliente', 'historialPagos']);

            return Inertia::render('Pagos/Edit', [
                'pago' => $pago,
            ]);
        } catch (ModelNotFoundException $e) {
            return redirect()->route('pagos.index')->with('error', 'Pago no encontrado.');
        } catch (\Exception $e) {
            Log::error('Error al cargar formulario de edición: ' . $e->getMessage());
            return redirect()->route('pagos.index')->with('error', 'Error al cargar el formulario de edición.');
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, PagoPrestamo $pago)
    {
        DB::beginTransaction();

        try {
            $validated = $request->validate([
                'monto_pagado' => 'required|numeric|min:0',
                'fecha_pago' => 'required|date',
                'metodo_pago' => 'nullable|string|max:100',
                'referencia' => 'nullable|string|max:255',
                'notas' => 'nullable|string|max:1000',
            ]);

            // Agregar el pago (acumula si es parcial)
            $exito = $pago->agregarPago(
                $validated['monto_pagado'],
                $validated['fecha_pago'],
                $validated['metodo_pago'],
                $validated['referencia']
            );

            if (!$exito) {
                throw ValidationException::withMessages([
                    'monto_pagado' => 'El monto debe ser mayor a cero.'
                ]);
            }

            // Actualizar información adicional
            $pago->update([
                'metodo_pago' => $validated['metodo_pago'],
                'referencia' => $validated['referencia'],
                'notas' => $validated['notas'],
            ]);

            DB::commit();

            Log::info('Pago actualizado', [
                'pago_id' => $pago->id,
                'monto' => $validated['monto_pagado']
            ]);

            return redirect()->route('pagos.index')->with('success', 'Pago actualizado correctamente');
        } catch (ValidationException $e) {
            DB::rollBack();
            throw $e;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error al actualizar pago: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(PagoPrestamo $pago)
    {
        try {
            if ($pago->estado === 'pagado') {
                return redirect()->back()->with('error',
                    'No se puede eliminar un pago ya registrado. Solo se pueden editar.');
            }

            Log::info('Eliminando pago', [
                'pago_id' => $pago->id,
                'prestamo_id' => $pago->prestamo_id
            ]);

            $pago->delete();

            return redirect()->route('pagos.index')->with('success', 'Pago eliminado correctamente');
        } catch (ModelNotFoundException $e) {
            return redirect()->route('pagos.index')->with('error', 'Pago no encontrado.');
        } catch (\Exception $e) {
            Log::error('Error al eliminar pago: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Error interno al eliminar el pago.');
        }
    }

    /**
     * Obtener pagos de un préstamo específico
     */
    public function pagosPorPrestamo(Prestamo $prestamo)
    {
        try {
            // Crear pagos programados si no existen
            if (!$prestamo->pagos()->exists()) {
                $prestamo->crearPagosProgramados();
            }

            $pagos = $prestamo->pagos()
                ->with('historialPagos')
                ->orderBy('numero_pago')
                ->get();

            return response()->json([
                'success' => true,
                'pagos' => $pagos,
            ]);
        } catch (\Exception $e) {
            Log::error('Error obteniendo pagos del préstamo: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener pagos del préstamo'
            ], 500);
        }
    }
}
