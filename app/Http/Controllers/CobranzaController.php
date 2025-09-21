<?php

namespace App\Http\Controllers;

use App\Models\Cobranza;
use App\Models\Renta;
use App\Models\Reporte;
use App\Models\Venta;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Inertia\Inertia;

class CobranzaController extends Controller
{
    /**
     * Muestra una lista de cobranzas.
     */
    public function index()
    {
        // Obtener ventas pendientes de pago
        $ventasQuery = Venta::with(['cliente:id,nombre_razon_social,email'])
            ->where('pagado', false)
            ->where('estado', '!=', 'cancelada');

        // Aplicar filtros
        if (request('search')) {
            $search = request('search');
            $ventasQuery->where(function($q) use ($search) {
                $q->whereHas('cliente', function($clienteQuery) use ($search) {
                    $clienteQuery->where('nombre_razon_social', 'like', '%' . $search . '%');
                })
                ->orWhere('numero_venta', 'like', '%' . $search . '%');
            });
        }

        if (request('estado')) {
            // Para ventas, mapear 'pendiente' a false en pagado
            if (request('estado') === 'pendiente') {
                $ventasQuery->where('pagado', false);
            } elseif (request('estado') === 'pagado') {
                $ventasQuery->where('pagado', true);
            }
        }

        // Aplicar ordenamiento
        $sortBy = request('sort_by', 'fecha');
        $sortDirection = request('sort_direction', 'desc');
        $ventasQuery->orderBy($sortBy, $sortDirection);

        $ventasCollection = $ventasQuery->get();

        // Transformar ventas para que tengan estructura similar a cobranzas
        $ventasTransformadas = $ventasCollection->map(function ($venta) {
            return [
                'id' => $venta->id,
                'tipo' => 'venta',
                'numero_venta' => $venta->numero_venta,
                'cliente' => $venta->cliente,
                'fecha_cobro' => $venta->fecha ? $venta->fecha->format('Y-m-d') : $venta->created_at->format('Y-m-d'),
                'monto_cobrado' => $venta->total,
                'concepto' => 'Venta pendiente de pago',
                'estado' => $venta->pagado ? 'pagado' : 'pendiente',
                'notas' => $venta->notas,
                'created_at' => $venta->created_at,
                'updated_at' => $venta->updated_at,
            ];
        });

        // Crear paginación manual
        $perPage = request('per_page', 10);
        $currentPage = \Illuminate\Pagination\Paginator::resolveCurrentPage('page');
        $paginatedItems = $ventasTransformadas->forPage($currentPage, $perPage);
        $ventas = new \Illuminate\Pagination\LengthAwarePaginator(
            $paginatedItems,
            $ventasTransformadas->count(),
            $perPage,
            $currentPage,
            ['path' => request()->url(), 'pageName' => 'page']
        );

        // Calcular estadísticas de ventas pendientes
        $stats = [
            'total' => Venta::where('pagado', false)->where('estado', '!=', 'cancelada')->count(),
            'pendientes' => Venta::where('pagado', false)->where('estado', '!=', 'cancelada')->count(),
            'pagadas' => Venta::where('pagado', true)->count(),
            'vencidas' => 0, // No aplicable para ventas
            'total_pendiente' => Venta::where('pagado', false)->where('estado', '!=', 'cancelada')->sum('total'),
            'total_pagado' => Venta::where('pagado', true)->sum('total'),
        ];

        return inertia('Cobranza/Index', [
            'cobranzas' => $ventas,
            'stats' => $stats,
            'filters' => request()->only(['search', 'estado', 'mes', 'anio']),
            'sorting' => [
                'sort_by' => $sortBy,
                'sort_direction' => $sortDirection
            ]
        ]);
    }

    /**
     * Muestra el formulario para crear una nueva cobranza.
     */
    public function create()
    {
        $rentas = Renta::with('cliente:id,nombre_razon_social,email')
            ->where('estado', 'activo')
            ->select('id', 'numero_contrato', 'cliente_id', 'monto_mensual', 'estado')
            ->get();

        return Inertia::render('Cobranza/Create', [
            'rentas' => $rentas,
        ]);
    }

    /**
     * Almacena una nueva cobranza.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'renta_id' => 'required|exists:rentas,id',
            'fecha_cobro' => 'required|date',
            'monto_cobrado' => 'required|numeric|min:0',
            'concepto' => 'required|string|max:255',
            'notas' => 'nullable|string'
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        Cobranza::create([
            'renta_id' => $request->renta_id,
            'fecha_cobro' => $request->fecha_cobro,
            'monto_cobrado' => $request->monto_cobrado,
            'concepto' => $request->concepto,
            'estado' => 'pendiente',
            'notas' => $request->notas,
            'responsable_cobro' => auth()->id(),
        ]);

        return redirect()->route('cobranza.index')->with('success', 'Cobranza creada exitosamente.');
    }

    /**
     * Muestra los detalles de una cobranza.
     */
    public function show(Cobranza $cobranza)
    {
        $cobranza->load('renta.cliente');
        return inertia('Cobranza/Show', compact('cobranza'));
    }

    /**
     * Muestra el formulario para editar una cobranza.
     */
    public function edit(Cobranza $cobranza)
    {
        $cobranza->load('renta.cliente');
        $rentas = Renta::with('cliente:id,nombre_razon_social,email')
            ->where('estado', 'activo')
            ->select('id', 'numero_contrato', 'cliente_id', 'monto_mensual', 'estado')
            ->get();

        return Inertia::render('Cobranza/Edit', [
            'cobranza' => $cobranza,
            'rentas' => $rentas,
        ]);
    }

    /**
     * Actualiza una cobranza existente.
     */
    public function update(Request $request, Cobranza $cobranza)
    {
        $validator = Validator::make($request->all(), [
            'renta_id' => 'required|exists:rentas,id',
            'fecha_cobro' => 'required|date',
            'monto_cobrado' => 'required|numeric|min:0',
            'concepto' => 'required|string|max:255',
            'estado' => 'required|in:pendiente,pagado,parcial,vencido,cancelado',
            'fecha_pago' => 'nullable|date',
            'monto_pagado' => 'nullable|numeric|min:0',
            'metodo_pago' => 'nullable|string|max:255',
            'referencia_pago' => 'nullable|string|max:255',
            'notas' => 'nullable|string'
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        $cobranza->update($request->only([
            'renta_id',
            'fecha_cobro',
            'monto_cobrado',
            'concepto',
            'estado',
            'fecha_pago',
            'monto_pagado',
            'metodo_pago',
            'referencia_pago',
            'notas',
        ]));

        return redirect()->route('cobranza.index')->with('success', 'Cobranza actualizada correctamente.');
    }

    /**
     * Elimina una cobranza.
     */
    public function destroy(Cobranza $cobranza)
    {
        $cobranza->delete();
        return redirect()->route('cobranza.index')->with('success', 'Cobranza eliminada correctamente.');
    }

    /**
     * Marca una venta como pagada desde cobranza.
     */
    public function marcarVentaPagada(Request $request, $id)
    {
        $venta = Venta::findOrFail($id);

        // Verificar que la venta no esté ya pagada
        if ($venta->pagado) {
            return response()->json([
                'success' => false,
                'error' => 'Esta venta ya está marcada como pagada'
            ], 400);
        }

        // Verificar que la venta no esté cancelada
        if ($venta->estado === 'cancelada') {
            return response()->json([
                'success' => false,
                'error' => 'No se puede marcar como pagada una venta cancelada'
            ], 400);
        }

        $request->validate([
            'fecha_pago' => 'required|date',
            'monto_pagado' => 'required|numeric|min:0',
            'metodo_pago' => 'required|in:efectivo,transferencia,cheque,tarjeta,otros',
            'referencia_pago' => 'nullable|string|max:255',
            'notas_pago' => 'nullable|string|max:500'
        ]);

        DB::beginTransaction();
        try {
            // Actualizar la venta con la información de pago
            $venta->update([
                'pagado' => true,
                'metodo_pago' => $request->metodo_pago,
                'fecha_pago' => $request->fecha_pago,
                'notas_pago' => $request->notas_pago,
                'pagado_por' => $request->user()->id,
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Venta marcada como pagada exitosamente',
                'venta' => [
                    'id' => $venta->id,
                    'pagado' => true,
                    'metodo_pago' => $venta->metodo_pago,
                    'fecha_pago' => $venta->fecha_pago->format('Y-m-d'),
                    'notas_pago' => $venta->notas_pago,
                ]
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error al marcar venta como pagada desde cobranza: ' . $e->getMessage(), [
                'venta_id' => $id,
                'user_id' => $request->user()->id,
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'error' => 'Error interno al procesar el pago',
                'details' => app()->environment('local') ? $e->getMessage() : null
            ], 500);
        }
    }

    /**
     * Marca una cobranza como pagada.
     */
    public function marcarPagada(Request $request, $id)
    {
        $request->validate([
            'fecha_pago' => 'required|date',
            'monto_pagado' => 'required|numeric|min:0',
            'metodo_pago' => 'required|in:efectivo,transferencia,cheque,tarjeta,otros',
            'referencia_pago' => 'nullable|string|max:255',
            'recibido_por' => 'nullable|string|max:255',
            'notas_pago' => 'nullable|string|max:500'
        ]);

        $cobranza = Cobranza::findOrFail($id);

        // Verificar que la cobranza no esté ya pagada
        if ($cobranza->estado === 'pagado') {
            return response()->json([
                'success' => false,
                'error' => 'Esta cobranza ya está marcada como pagada'
            ], 400);
        }

        // Verificar que la cobranza no esté cancelada
        if ($cobranza->estado === 'cancelado') {
            return response()->json([
                'success' => false,
                'error' => 'No se puede marcar como pagada una cobranza cancelada'
            ], 400);
        }

        DB::beginTransaction();
        try {
            // Determinar el estado basado en el monto pagado
            $estado = $request->monto_pagado >= $cobranza->monto_cobrado ? 'pagado' : 'parcial';

            // Actualizar la cobranza con la información de pago
            $cobranza->update([
                'estado' => $estado,
                'fecha_pago' => $request->fecha_pago,
                'monto_pagado' => $request->monto_pagado,
                'metodo_pago' => $request->metodo_pago,
                'referencia_pago' => $request->referencia_pago,
                'recibido_por' => $request->recibido_por,
                'notas_pago' => $request->notas_pago,
                'responsable_cobro' => $request->user()->id,
            ]);

            // Agregar al reporte del corte diario
            $fechaCorte = now()->format('Y-m-d');
            $nombreCorte = 'Corte de Caja ' . now()->format('d/m/Y');

            $reporteCorte = Reporte::where('nombre', $nombreCorte)
                ->where('fecha', $fechaCorte)
                ->first();

            $nuevoIngreso = "Pago de {$cobranza->concepto} - Renta {$cobranza->renta->numero_contrato}: {$cobranza->monto_cobrado} vía {$request->metodo_pago}";

            if ($reporteCorte) {
                // Agregar a la descripción existente
                $descripcionActual = $reporteCorte->descripcion ? $reporteCorte->descripcion . "\n" : "";
                $reporteCorte->update([
                    'descripcion' => $descripcionActual . $nuevoIngreso
                ]);
            } else {
                // Crear nuevo reporte del corte
                Reporte::create([
                    'nombre' => $nombreCorte,
                    'descripcion' => $nuevoIngreso,
                    'fecha' => $fechaCorte,
                ]);
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Cobranza marcada como pagada exitosamente',
                'cobranza' => [
                    'id' => $cobranza->id,
                    'estado' => $estado,
                    'fecha_pago' => $cobranza->fecha_pago->format('Y-m-d'),
                    'monto_pagado' => $cobranza->monto_pagado,
                    'metodo_pago' => $cobranza->metodo_pago,
                    'referencia_pago' => $cobranza->referencia_pago,
                    'recibido_por' => $cobranza->recibido_por,
                    'notas_pago' => $cobranza->notas_pago,
                ]
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error al marcar cobranza como pagada: ' . $e->getMessage(), [
                'cobranza_id' => $cobranza->id,
                'user_id' => $request->user()->id,
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'error' => 'Error interno al procesar el pago',
                'details' => app()->environment('local') ? $e->getMessage() : null
            ], 500);
        }
    }

    /**
     * Genera cobranzas automáticas para rentas activas.
     */
    public function generarCobranzas(Request $request)
    {
        $request->validate([
            'mes' => 'required|integer|min:1|max:12',
            'anio' => 'required|integer|min:2020|max:' . (date('Y') + 1),
        ]);

        $mes = $request->mes;
        $anio = $request->anio;

        // Obtener rentas activas
        $rentas = Renta::where('estado', 'activo')->get();

        $creadas = 0;
        foreach ($rentas as $renta) {
            // Verificar si ya existe cobranza para este mes
            $existe = Cobranza::where('renta_id', $renta->id)
                ->whereYear('fecha_cobro', $anio)
                ->whereMonth('fecha_cobro', $mes)
                ->where('concepto', 'mensualidad')
                ->exists();

            if (!$existe) {
                Cobranza::create([
                    'renta_id' => $renta->id,
                    'fecha_cobro' => \Carbon\Carbon::create($anio, $mes, $renta->dia_pago ?? 1),
                    'monto_cobrado' => $renta->monto_mensual,
                    'concepto' => 'mensualidad',
                    'estado' => 'pendiente',
                    'notas' => "Cobranza automática generada para {$mes}/{$anio}",
                    'responsable_cobro' => auth()->id(),
                ]);
                $creadas++;
            }
        }

        return redirect()->back()->with('success', "Se generaron {$creadas} cobranzas automáticas.");
    }
}
