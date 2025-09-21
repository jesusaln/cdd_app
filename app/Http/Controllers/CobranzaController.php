<?php

namespace App\Http\Controllers;

use App\Models\Cobranza;
use App\Models\Renta;
use App\Models\Reporte;
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
        $query = Cobranza::with(['renta.cliente:id,nombre_razon_social,email']);

        // Aplicar filtros
        if (request('search')) {
            $search = request('search');
            $query->where(function($q) use ($search) {
                $q->whereHas('renta.cliente', function($clienteQuery) use ($search) {
                    $clienteQuery->where('nombre_razon_social', 'like', '%' . $search . '%');
                })
                ->orWhereHas('renta', function($rentaQuery) use ($search) {
                    $rentaQuery->where('numero_contrato', 'like', '%' . $search . '%');
                })
                ->orWhere('concepto', 'like', '%' . $search . '%');
            });
        }

        if (request('estado')) {
            $query->where('estado', request('estado'));
        }

        if (request('mes') && request('anio')) {
            $query->delMes(request('mes'), request('anio'));
        }

        // Aplicar ordenamiento
        $sortBy = request('sort_by', 'fecha_cobro');
        $sortDirection = request('sort_direction', 'desc');
        $query->orderBy($sortBy, $sortDirection);

        $cobranzas = $query->paginate(request('per_page', 10));

        // Calcular estadísticas
        $stats = [
            'total' => Cobranza::count(),
            'pendientes' => Cobranza::where('estado', 'pendiente')->count(),
            'pagadas' => Cobranza::where('estado', 'pagado')->count(),
            'vencidas' => Cobranza::where('estado', 'vencido')->count(),
            'total_pendiente' => Cobranza::where('estado', 'pendiente')->sum('monto_cobrado'),
            'total_pagado' => Cobranza::where('estado', 'pagado')->sum('monto_pagado'),
        ];

        return inertia('Cobranza/Index', [
            'cobranzas' => $cobranzas,
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
            'responsable_cobro' => auth()->user()->name ?? 'Sistema',
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
     * Marca una cobranza como pagada.
     */
    public function marcarPagada(Request $request, $id)
    {
        $request->validate([
            'metodo_pago' => 'required|in:efectivo,transferencia,cheque,tarjeta,otros',
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
            // Actualizar la cobranza con la información de pago
            $cobranza->update([
                'estado' => 'pagado',
                'fecha_pago' => now(),
                'monto_pagado' => $cobranza->monto_cobrado,
                'metodo_pago' => $request->metodo_pago,
                'referencia_pago' => $request->notas_pago,
                'responsable_cobro' => $request->user()->id, // Usar ID del usuario como en ventas
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
                    'estado' => 'pagado',
                    'metodo_pago' => $cobranza->metodo_pago,
                    'fecha_pago' => $cobranza->fecha_pago->format('Y-m-d'),
                    'referencia_pago' => $cobranza->referencia_pago,
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
                    'responsable_cobro' => auth()->user()->name ?? 'Sistema',
                ]);
                $creadas++;
            }
        }

        return redirect()->back()->with('success', "Se generaron {$creadas} cobranzas automáticas.");
    }
}
