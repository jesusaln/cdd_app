<?php

namespace App\Http\Controllers;

use App\Models\CuentasPorPagar;
use App\Models\Compra;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;

class CuentasPorPagarController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        try {
            $query = CuentasPorPagar::with(['compra.proveedor']);

            // Filtros
            if ($request->filled('estado')) {
                $query->where('estado', $request->estado);
            }

            if ($request->filled('proveedor_id')) {
                $query->whereHas('compra', function ($q) use ($request) {
                    $q->where('proveedor_id', $request->proveedor_id);
                });
            }

            // Ordenamiento
            $sortBy = $request->get('sort_by', 'fecha_vencimiento');
            $sortDirection = $request->get('sort_direction', 'asc');
            $query->orderBy($sortBy, $sortDirection);

            $cuentas = $query->paginate(15);

            // Estadísticas
            $stats = [
                'total_pendiente' => CuentasPorPagar::whereIn('estado', ['pendiente', 'parcial'])->sum('monto_pendiente'),
                'total_vencido' => CuentasPorPagar::where('estado', 'vencido')->sum('monto_pendiente'),
                'cuentas_pendientes' => CuentasPorPagar::whereIn('estado', ['pendiente', 'parcial'])->count(),
                'cuentas_vencidas' => CuentasPorPagar::where('estado', 'vencido')->count(),
            ];

            return Inertia::render('CuentasPorPagar/Index', [
                'cuentas' => $cuentas,
                'stats' => $stats,
                'filters' => $request->only(['estado', 'proveedor_id']),
                'sorting' => ['sort_by' => $sortBy, 'sort_direction' => $sortDirection],
            ]);
        } catch (\Exception $e) {
            Log::error('Error in CuentasPorPagarController@index: ' . $e->getMessage());
            Log::error('Stack trace: ' . $e->getTraceAsString());
            return response()->json(['error' => $e->getMessage(), 'trace' => $e->getTraceAsString()], 500);
        }
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(Request $request)
    {
        $compraId = $request->get('compra_id');
        $compra = null;

        if ($compraId) {
            $compra = Compra::with('proveedor')->findOrFail($compraId);
        }

        return Inertia::render('CuentasPorPagar/Create', [
            'compra' => $compra,
            'compras' => Compra::with('proveedor')
                ->whereDoesntHave('cuentasPorPagar')
                ->where('estado', 'procesada')
                ->get(),
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'compra_id' => 'required|exists:compras,id',
            'monto_total' => 'required|numeric|min:0',
            'fecha_vencimiento' => 'nullable|date|after:today',
            'notas' => 'nullable|string|max:1000',
        ]);

        // Verificar que la compra no tenga ya una cuenta por pagar
        $compra = Compra::findOrFail($validated['compra_id']);
        if ($compra->cuentasPorPagar) {
            return redirect()->back()->with('error', 'Esta compra ya tiene una cuenta por pagar registrada.');
        }

        DB::transaction(function () use ($validated) {
            CuentasPorPagar::create([
                'compra_id' => $validated['compra_id'],
                'monto_total' => $validated['monto_total'],
                'monto_pagado' => 0,
                'monto_pendiente' => $validated['monto_total'],
                'fecha_vencimiento' => $validated['fecha_vencimiento'],
                'estado' => 'pendiente',
                'notas' => $validated['notas'],
            ]);
        });

        return redirect()->route('cuentas-por-pagar.index')->with('success', 'Cuenta por pagar creada correctamente.');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $cuenta = CuentasPorPagar::with(['compra.proveedor', 'compra.productos'])->findOrFail($id);

        return Inertia::render('CuentasPorPagar/Show', [
            'cuenta' => $cuenta,
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $cuenta = CuentasPorPagar::with('compra.proveedor')->findOrFail($id);

        return Inertia::render('CuentasPorPagar/Edit', [
            'cuenta' => $cuenta,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $cuenta = CuentasPorPagar::findOrFail($id);

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
        });

        return redirect()->route('cuentas-por-pagar.index')->with('success', 'Cuenta por pagar actualizada correctamente.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $cuenta = CuentasPorPagar::findOrFail($id);

        // Solo permitir eliminar si no hay pagos registrados
        if ($cuenta->monto_pagado > 0) {
            return redirect()->back()->with('error', 'No se puede eliminar una cuenta que ya tiene pagos registrados.');
        }

        $cuenta->delete();

        return redirect()->route('cuentas-por-pagar.index')->with('success', 'Cuenta por pagar eliminada correctamente.');
    }

    /**
     * Registrar un pago parcial
     */
    public function registrarPago(Request $request, string $id)
    {
        $cuenta = CuentasPorPagar::findOrFail($id);

        $validated = $request->validate([
            'monto' => 'required|numeric|min:0.01|max:' . $cuenta->monto_pendiente,
            'notas' => 'nullable|string|max:500',
        ]);

        $cuenta->registrarPago($validated['monto'], $validated['notas']);

        return redirect()->back()->with('success', 'Pago registrado correctamente.');
    }
}
