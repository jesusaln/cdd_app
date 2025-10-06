<?php

namespace App\Http\Controllers;

use App\Models\CuentasPorCobrar;
use App\Models\Venta;
use App\Enums\EstadoVenta;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;

class CuentasPorCobrarController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        try {
            $query = CuentasPorCobrar::with(['venta.cliente']);

            if ($request->filled('estado')) {
                $query->where('estado', $request->estado);
            }

            if ($request->filled('cliente_id')) {
                $query->whereHas('venta', function ($q) use ($request) {
                    $q->where('cliente_id', $request->cliente_id);
                });
            }

            $sortBy = $request->get('sort_by', 'fecha_vencimiento');
            $sortDirection = $request->get('sort_direction', 'asc');

            $query->orderBy($sortBy, $sortDirection);

            $cuentas = $query->paginate($request->integer('per_page', 15));

            $stats = [
                'total_pendiente' => CuentasPorCobrar::whereIn('estado', ['pendiente', 'parcial'])->sum('monto_pendiente'),
                'total_vencido' => CuentasPorCobrar::where('estado', 'vencido')->sum('monto_pendiente'),
                'cuentas_pendientes' => CuentasPorCobrar::whereIn('estado', ['pendiente', 'parcial'])->count(),
                'cuentas_vencidas' => CuentasPorCobrar::where('estado', 'vencido')->count(),
            ];

            return Inertia::render('CuentasPorCobrar/Index', [
                'cuentas' => $cuentas,
                'stats' => $stats,
                'filters' => $request->only(['estado', 'cliente_id']),
                'sorting' => [
                    'sort_by' => $sortBy,
                    'sort_direction' => $sortDirection,
                ],
            ]);
        } catch (\Throwable $e) {
            Log::error('Error in CuentasPorCobrarController@index: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'error' => 'Error al cargar cuentas por cobrar',
                'details' => app()->environment('local') ? $e->getMessage() : null,
            ], 500);
        }
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(Request $request)
    {
        $ventaId = $request->get('venta_id');
        $venta = null;

        if ($ventaId) {
            $venta = Venta::with('cliente')->findOrFail($ventaId);
        }

        $ventasDisponibles = Venta::with('cliente')
            ->whereDoesntHave('cuentaPorCobrar')
            ->where('estado', '!=', EstadoVenta::Cancelada->value)
            ->get();

        return Inertia::render('CuentasPorCobrar/Create', [
            'venta' => $venta,
            'ventas' => $ventasDisponibles,
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'venta_id' => 'required|exists:ventas,id',
            'monto_total' => 'required|numeric|min:0',
            'fecha_vencimiento' => 'nullable|date|after:today',
            'notas' => 'nullable|string|max:1000',
        ]);

        $venta = Venta::with('cuentaPorCobrar')->findOrFail($validated['venta_id']);

        if ($venta->cuentaPorCobrar) {
            return back()->with('error', 'Esta venta ya cuenta con una cuenta por cobrar.');
        }

        DB::transaction(function () use ($validated) {
            CuentasPorCobrar::create([
                'venta_id' => $validated['venta_id'],
                'monto_total' => $validated['monto_total'],
                'monto_pagado' => 0,
                'monto_pendiente' => $validated['monto_total'],
                'fecha_vencimiento' => $validated['fecha_vencimiento'],
                'estado' => 'pendiente',
                'notas' => $validated['notas'],
            ]);
        });

        return redirect()->route('cuentas-por-cobrar.index')->with('success', 'Cuenta por cobrar creada correctamente.');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $cuenta = CuentasPorCobrar::with(['venta.cliente', 'venta.items.ventable'])->findOrFail($id);

        return Inertia::render('CuentasPorCobrar/Show', [
            'cuenta' => $cuenta,
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $cuenta = CuentasPorCobrar::with('venta.cliente')->findOrFail($id);

        return Inertia::render('CuentasPorCobrar/Edit', [
            'cuenta' => $cuenta,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $cuenta = CuentasPorCobrar::with('venta')->findOrFail($id);

        $validated = $request->validate([
            'monto_pagado' => 'nullable|numeric|min:0|max:' . $cuenta->monto_total,
            'fecha_vencimiento' => 'nullable|date',
            'notas' => 'nullable|string|max:1000',
        ]);

        DB::transaction(function () use ($cuenta, $validated) {
            if (isset($validated['monto_pagado'])) {
                $diferencia = $validated['monto_pagado'] - $cuenta->monto_pagado;
                if ($diferencia > 0) {
                    $cuenta->registrarPago($diferencia, 'Pago registrado desde edición');
                }
            }

            $cuenta->update([
                'fecha_vencimiento' => $validated['fecha_vencimiento'] ?? $cuenta->fecha_vencimiento,
                'notas' => $validated['notas'] ?? $cuenta->notas,
            ]);

            if ($cuenta->monto_pendiente <= 0 && $cuenta->venta) {
                $cuenta->venta->update([
                    'pagado' => true,
                    'estado' => EstadoVenta::Aprobada,
                    'fecha_pago' => now(), // Siempre establecer fecha actual cuando se completa el pago
                ]);
            }
        });

        return redirect()->route('cuentas-por-cobrar.index')->with('success', 'Cuenta por cobrar actualizada correctamente.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $cuenta = CuentasPorCobrar::findOrFail($id);

        if ($cuenta->monto_pagado > 0) {
            return back()->with('error', 'No se puede eliminar una cuenta que ya tiene pagos registrados.');
        }

        $cuenta->delete();

        return redirect()->route('cuentas-por-cobrar.index')->with('success', 'Cuenta por cobrar eliminada correctamente.');
    }

    /**
      * Registrar un pago parcial.
      */
    public function registrarPago(Request $request, string $id)
    {
        $cuenta = CuentasPorCobrar::with('venta')->findOrFail($id);

        $validated = $request->validate([
            'monto' => 'required|numeric|min:0.01|max:' . $cuenta->monto_pendiente,
            'metodo_pago' => 'required|in:efectivo,transferencia,cheque,tarjeta,otros',
            'notas' => 'nullable|string|max:500',
        ]);

        DB::transaction(function () use ($cuenta, $validated, $request) {
            $cuenta->registrarPago($validated['monto'], $validated['notas']);
            $cuenta->refresh();

            if ($cuenta->venta) {
                if ($cuenta->monto_pendiente <= 0) {
                    // Venta completamente pagada - establecer fecha de pago actual
                    $cuenta->venta->update([
                        'pagado' => true,
                        'estado' => EstadoVenta::Aprobada,
                        'fecha_pago' => now(), // Siempre establecer fecha actual cuando se completa el pago
                        'metodo_pago' => $validated['metodo_pago'],
                        'notas_pago' => $validated['notas'],
                        'pagado_por' => $request->user()->id,
                    ]);
                } else {
                    // Venta parcialmente pagada - establecer fecha de pago actual para el pago parcial
                    $cuenta->venta->update([
                        'pagado' => false,
                        'fecha_pago' => now(), // Establecer fecha del último pago parcial
                        'metodo_pago' => $validated['metodo_pago'],
                        'notas_pago' => $validated['notas'],
                        'pagado_por' => $request->user()->id,
                    ]);
                }
            }
        });

        return redirect()->back()->with('success', 'Pago registrado correctamente.');
    }
}
