<?php

namespace App\Http\Controllers;

use App\Models\EntregaDinero;
use App\Models\Cobranza;
use App\Models\Venta;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class EntregaDineroController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        // Solo administradores pueden acceder a esta funcionalidad
        if (!auth()->user()->hasRole('admin')) {
            abort(403, 'No tienes permisos para acceder a esta sección');
        }

        $userId = auth()->id();
        $isAdmin = auth()->user()->hasRole('admin');

        // Entregas manuales - admins ven todas, usuarios normales solo las suyas
        $query = EntregaDinero::with(['usuario', 'recibidoPor']);

        if (!$isAdmin) {
            $query->where('user_id', $userId);
        }

        // Filtros
        if (request('estado')) {
            $query->where('estado', request('estado'));
        }

        if (request('user_id')) {
            $query->where('user_id', request('user_id'));
        }

        // Ordenamiento
        $query->orderBy('fecha_entrega', 'desc')
            ->orderBy('created_at', 'desc');

        $entregas = $query->paginate(request('per_page', 15));

        // Obtener cobranzas pagadas con saldos pendientes
        $cobranzasQuery = Cobranza::with(['renta.cliente', 'responsableCobro'])
            ->where('estado', 'pagado')
            ->whereRaw('monto_pagado > COALESCE((SELECT SUM(total) FROM entregas_dinero WHERE tipo_origen = "cobranza" AND id_origen = cobranzas.id AND estado = "recibido"), 0)');

        // Si no es admin, filtrar solo por el usuario actual
        if (!$isAdmin) {
            $cobranzasQuery->where('responsable_cobro', $userId);
        }

        $cobranzasPagadas = $cobranzasQuery->orderBy('fecha_pago', 'desc')
            ->get()
            ->map(function ($cobranza) {
                $montoYaEntregado = EntregaDinero::where('tipo_origen', 'cobranza')
                    ->where('id_origen', $cobranza->id)
                    ->where('estado', 'recibido')
                    ->sum('total');
                $saldoPendiente = $cobranza->monto_pagado - $montoYaEntregado;

                return [
                    'id' => 'cobranza_' . $cobranza->id,
                    'tipo' => 'cobranza',
                    'tipo_origen' => 'cobranza',
                    'id_origen' => $cobranza->id,
                    'fecha_entrega' => $cobranza->fecha_pago->format('Y-m-d'),
                    'total' => $cobranza->monto_pagado,
                    'saldo_pendiente' => $saldoPendiente,
                    'ya_entregado' => $montoYaEntregado,
                    'concepto' => $cobranza->concepto,
                    'cliente' => $cobranza->renta->cliente->nombre_razon_social ?? 'Sin cliente',
                    'estado' => 'por_entregar',
                    'usuario' => $cobranza->responsableCobro,
                    'registro_original' => $cobranza,
                ];
            });

        // Obtener ventas pagadas con saldos pendientes
        $ventasQuery = Venta::with(['cliente', 'pagadoPor'])
            ->where('pagado', true)
            ->whereRaw('total > COALESCE((SELECT SUM(total) FROM entregas_dinero WHERE tipo_origen = "venta" AND id_origen = ventas.id AND estado = "recibido"), 0)');

        // Si no es admin, filtrar solo por el usuario actual
        if (!$isAdmin) {
            $ventasQuery->where('pagado_por', $userId);
        }

        $ventasPagadas = $ventasQuery->orderBy('fecha_pago', 'desc')
            ->get()
            ->map(function ($venta) {
                $montoYaEntregado = EntregaDinero::where('tipo_origen', 'venta')
                    ->where('id_origen', $venta->id)
                    ->where('estado', 'recibido')
                    ->sum('total');
                $saldoPendiente = $venta->total - $montoYaEntregado;

                return [
                    'id' => 'venta_' . $venta->id,
                    'tipo' => 'venta',
                    'tipo_origen' => 'venta',
                    'id_origen' => $venta->id,
                    'fecha_entrega' => $venta->fecha_pago->format('Y-m-d'),
                    'total' => $venta->total,
                    'saldo_pendiente' => $saldoPendiente,
                    'ya_entregado' => $montoYaEntregado,
                    'concepto' => 'Venta #' . $venta->numero_venta,
                    'cliente' => $venta->cliente->nombre_razon_social ?? 'Sin cliente',
                    'estado' => 'por_entregar',
                    'usuario' => $venta->pagadoPor,
                    'registro_original' => $venta,
                ];
            });

        // Combinar todos los registros
        $registrosAutomaticos = collect([...$cobranzasPagadas, ...$ventasPagadas]);

        // Estadísticas
        $stats = [
            'total_pendientes' => EntregaDinero::where('estado', 'pendiente')->sum('total'),
            'total_recibidas' => EntregaDinero::where('estado', 'recibido')->sum('total'),
            'entregas_pendientes' => EntregaDinero::where('estado', 'pendiente')->count(),
            'registros_automaticos' => $registrosAutomaticos->count(),
            'total_automatico' => $registrosAutomaticos->sum('total'),
        ];

        return Inertia::render('EntregasDinero/Index', [
            'entregas' => $entregas,
            'registrosAutomaticos' => $registrosAutomaticos,
            'stats' => $stats,
            'filters' => request()->only(['estado', 'user_id']),
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return Inertia::render('EntregasDinero/Create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'fecha_entrega' => 'required|date',
            'monto_efectivo' => 'required|numeric|min:0',
            'monto_cheques' => 'required|numeric|min:0',
            'monto_tarjetas' => 'required|numeric|min:0',
            'notas' => 'nullable|string|max:500',
        ]);

        $total = $request->monto_efectivo + $request->monto_cheques + $request->monto_tarjetas;

        if ($total <= 0) {
            return back()->withErrors(['total' => 'El total debe ser mayor a cero']);
        }

        EntregaDinero::create([
            'user_id' => auth()->id(),
            'fecha_entrega' => $request->fecha_entrega,
            'monto_efectivo' => $request->monto_efectivo,
            'monto_cheques' => $request->monto_cheques,
            'monto_tarjetas' => $request->monto_tarjetas,
            'total' => $total,
            'notas' => $request->notas,
        ]);

        return redirect()->route('entregas-dinero.index')->with('success', 'Entrega de dinero registrada correctamente');
    }

    /**
     * Display the specified resource.
     */
    public function show(EntregaDinero $entregaDinero)
    {
        $entregaDinero->load(['usuario', 'recibidoPor']);
        return Inertia::render('EntregasDinero/Show', ['entrega' => $entregaDinero]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(EntregaDinero $entregaDinero)
    {
        $entregaDinero->load(['usuario', 'recibidoPor']);
        return Inertia::render('EntregasDinero/Edit', ['entrega' => $entregaDinero]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, EntregaDinero $entregaDinero)
    {
        // Si es para marcar como recibido
        if ($request->has('marcar_recibido')) {
            $request->validate([
                'notas_recibido' => 'nullable|string|max:500',
            ]);

            $entregaDinero->update([
                'estado' => 'recibido',
                'recibido_por' => auth()->id(),
                'fecha_recibido' => now(),
                'notas_recibido' => $request->notas_recibido,
            ]);

            return redirect()->route('entregas-dinero.index')->with('success', 'Entrega marcada como recibida');
        }

        // Si es para actualizar la entrega (solo el usuario que la creó)
        if ($entregaDinero->user_id !== auth()->id()) {
            abort(403);
        }

        $request->validate([
            'fecha_entrega' => 'required|date',
            'monto_efectivo' => 'required|numeric|min:0',
            'monto_cheques' => 'required|numeric|min:0',
            'monto_tarjetas' => 'required|numeric|min:0',
            'notas' => 'nullable|string|max:500',
        ]);

        $total = $request->monto_efectivo + $request->monto_cheques + $request->monto_tarjetas;

        if ($total <= 0) {
            return back()->withErrors(['total' => 'El total debe ser mayor a cero']);
        }

        $entregaDinero->update([
            'fecha_entrega' => $request->fecha_entrega,
            'monto_efectivo' => $request->monto_efectivo,
            'monto_cheques' => $request->monto_cheques,
            'monto_tarjetas' => $request->monto_tarjetas,
            'total' => $total,
            'notas' => $request->notas,
        ]);

        return redirect()->route('entregas-dinero.index')->with('success', 'Entrega actualizada correctamente');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(EntregaDinero $entregaDinero)
    {
        // Solo el usuario que creó la entrega o admin puede eliminar
        if ($entregaDinero->user_id !== auth()->id() && !auth()->user()->hasRole('admin')) {
            abort(403);
        }

        // Solo se pueden eliminar entregas pendientes
        if ($entregaDinero->estado !== 'pendiente') {
            return back()->withErrors(['error' => 'Solo se pueden eliminar entregas pendientes']);
        }

        $entregaDinero->delete();

        return redirect()->route('entregas-dinero.index')->with('success', 'Entrega eliminada correctamente');
    }

    /**
     * API endpoint para obtener entregas pendientes por usuario (para dashboard)
     */
    public function pendientesPorUsuario()
    {
        $entregas = EntregaDinero::with('usuario')
            ->where('estado', 'pendiente')
            ->orderBy('total', 'desc')
            ->get()
            ->groupBy('user_id')
            ->map(function ($entregasUsuario, $userId) {
                $usuario = $entregasUsuario->first()->usuario;
                return [
                    'usuario' => $usuario->name,
                    'user_id' => $userId,
                    'total_pendiente' => $entregasUsuario->sum('total'),
                    'cantidad_entregas' => $entregasUsuario->count(),
                    'entregas' => $entregasUsuario->map(function ($entrega) {
                        return [
                            'id' => $entrega->id,
                            'fecha_entrega' => $entrega->fecha_entrega->format('Y-m-d'),
                            'total' => $entrega->total,
                            'notas' => $entrega->notas,
                        ];
                    }),
                ];
            })
            ->values();

        return response()->json($entregas);
    }

    /**
     * Marcar un registro automático (cobranza o venta) como recibido (puede ser parcial)
     */
    public function marcarAutomaticoRecibido(Request $request, $tipo_origen, $id_origen)
    {
        $request->validate([
            'monto_recibido' => 'required|numeric|min:0.01',
            'notas_recibido' => 'nullable|string|max:500',
        ]);

        $userId  = auth()->id();
        $isAdmin = auth()->user()->hasRole('admin');

        if ($tipo_origen === 'cobranza') {
            $q = Cobranza::query()
                ->where('id', $id_origen)
                ->where('estado', 'pagado');

            if (!$isAdmin) {
                $q->where('responsable_cobro', $userId);
            }

            $registro   = $q->firstOrFail();
            $montoTotal = $registro->monto_pagado;
            $concepto   = $registro->concepto;
            $fecha      = $registro->fecha_pago;
        } elseif ($tipo_origen === 'venta') {
            $q = Venta::query()
                ->where('id', $id_origen)
                ->where('pagado', true);

            if (!$isAdmin) {
                $q->where('pagado_por', $userId);
            }

            $registro   = $q->firstOrFail();
            $montoTotal = $registro->total;
            $concepto   = 'Venta #' . $registro->numero_venta;
            $fecha      = $registro->fecha_pago;
        } else {
            return response()->json(['error' => 'Tipo de registro no válido'], 422);
        }

        if ($request->monto_recibido > $montoTotal) {
            return response()->json(['error' => 'El monto recibido no puede ser mayor al total'], 422);
        }

        $montoYaEntregado = EntregaDinero::where('tipo_origen', $tipo_origen)
            ->where('id_origen', $id_origen)
            ->where('estado', 'recibido')
            ->sum('total');

        $montoPendiente = $montoTotal - $montoYaEntregado;

        if ($request->monto_recibido > $montoPendiente) {
            return response()->json(['error' => 'El monto recibido excede el saldo pendiente'], 422);
        }

        EntregaDinero::create([
            'user_id'         => $userId,
            'fecha_entrega'   => $fecha?->format('Y-m-d') ?? now()->toDateString(),
            'monto_efectivo'  => $request->monto_recibido,
            'monto_cheques'   => 0,
            'monto_tarjetas'  => 0,
            'total'           => $request->monto_recibido,
            'estado'          => 'recibido',
            'notas'           => "Entrega automática - {$concepto}",
            'tipo_origen'     => $tipo_origen,
            'id_origen'       => $id_origen,
            'recibido_por'    => $userId,
            'fecha_recibido'  => now(),
            'notas_recibido'  => $request->notas_recibido,
        ]);

        return redirect()->route('entregas-dinero.index')->with('success', 'Monto registrado correctamente');
    }
}
