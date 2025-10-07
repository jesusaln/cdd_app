<?php

namespace App\Http\Controllers;

use App\Models\Cobranza;
use App\Models\Renta;
use App\Models\Reporte;
use App\Models\EntregaDinero;
use App\Services\EntregaDineroService;
use App\Models\Venta;
use App\Models\BitacoraActividad;
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
        // Obtener cobranzas con relaciones
        $cobranzasQuery = Cobranza::with(['renta.cliente:id,nombre_razon_social,email'])
            ->whereHas('renta', function($q) {
                $q->where('estado', 'activo');
            });

        // Aplicar filtros
        if (request('search')) {
            $search = request('search');
            $cobranzasQuery->where(function($q) use ($search) {
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
            $cobranzasQuery->where('estado', request('estado'));
        }

        if (request('mes')) {
            $cobranzasQuery->whereMonth('fecha_cobro', request('mes'));
        }

        if (request('anio')) {
            $cobranzasQuery->whereYear('fecha_cobro', request('anio'));
        }

        // Aplicar ordenamiento
        $sortBy = request('sort_by', 'fecha_cobro');
        $sortDirection = request('sort_direction', 'desc');
        $cobranzasQuery->orderBy($sortBy, $sortDirection);

        $cobranzas = $cobranzasQuery->paginate(request('per_page', 10));

        // Calcular estadísticas
        $stats = [
            'total' => Cobranza::whereHas('renta', fn($q) => $q->where('estado', 'activo'))->count(),
            'pendientes' => Cobranza::where('estado', 'pendiente')->whereHas('renta', fn($q) => $q->where('estado', 'activo'))->count(),
            'pagadas' => Cobranza::where('estado', 'pagado')->whereHas('renta', fn($q) => $q->where('estado', 'activo'))->count(),
            'parciales' => Cobranza::where('estado', 'parcial')->whereHas('renta', fn($q) => $q->where('estado', 'activo'))->count(),
            'vencidas' => Cobranza::where('estado', 'vencido')->whereHas('renta', fn($q) => $q->where('estado', 'activo'))->count(),
            'total_pendiente' => Cobranza::whereIn('estado', ['pendiente', 'parcial', 'vencido'])->whereHas('renta', fn($q) => $q->where('estado', 'activo'))->sum('monto_cobrado'),
            'total_pagado' => Cobranza::where('estado', 'pagado')->whereHas('renta', fn($q) => $q->where('estado', 'activo'))->sum('monto_pagado'),
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
            'responsable_cobro' => auth()->user()->id,
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
            return redirect()->back()->withErrors(['error' => 'Esta venta ya está marcada como pagada']);
        }

        // Verificar que la venta no esté cancelada
        if ($venta->estado === 'cancelada') {
            return redirect()->back()->withErrors(['error' => 'No se puede marcar como pagada una venta cancelada']);
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

            return redirect()->back()->with('success', 'Venta marcada como pagada exitosamente');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error al marcar venta como pagada desde cobranza: ' . $e->getMessage(), [
                'venta_id' => $id,
                'user_id' => $request->user()->id,
                'trace' => $e->getTraceAsString()
            ]);

            return redirect()->back()->withErrors(['error' => 'Error interno al procesar el pago']);
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
            return redirect()->back()->withErrors(['error' => 'Esta cobranza ya está marcada como pagada']);
        }

        // Verificar que la cobranza no esté cancelada
        if ($cobranza->estado === 'cancelado') {
            return redirect()->back()->withErrors(['error' => 'No se puede marcar como pagada una cobranza cancelada']);
        }

        // Bloqueo condicional si ya existen entregas recibidas
        $montoEntregado = EntregaDinero::where('tipo_origen', 'cobranza')
            ->where('id_origen', $cobranza->id)
            ->where('estado', 'recibido')
            ->sum('total');
        $esAdmin = auth()->user() && method_exists(auth()->user(), 'hasRole') ? auth()->user()->hasRole('admin') : false;

        if ($montoEntregado > 0) {
            if ((float)$request->monto_pagado < (float)$montoEntregado) {
                return redirect()->back()->withErrors(['error' => 'El monto pagado no puede ser menor al total ya entregado (' . number_format($montoEntregado, 2) . ')']);
            }

            $cambiaMetodo = $cobranza->metodo_pago && $request->metodo_pago !== $cobranza->metodo_pago;
            $cambiaFecha = $cobranza->fecha_pago ? ($request->fecha_pago !== $cobranza->fecha_pago->format('Y-m-d')) : false;
            $disminuyeMonto = $cobranza->monto_pagado && ((float)$request->monto_pagado < (float)$cobranza->monto_pagado);

            if (($cambiaMetodo || $cambiaFecha || $disminuyeMonto) && !$esAdmin) {
                return redirect()->back()->withErrors(['error' => 'No se pueden modificar método/fecha o disminuir el monto cuando existe una entrega recibida.']);
            }

            if (($cambiaMetodo || $cambiaFecha || $disminuyeMonto) && $esAdmin) {
                $request->validate([
                    'motivo_override' => 'required|string|min:5'
                ]);
            }
        }

        DB::beginTransaction();
        try {
            $montoAnterior = (float) ($cobranza->monto_pagado ?? 0);
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

            // Crear Entrega de Dinero pendiente por el delta cobrado
            $montoNuevo = (float) $request->monto_pagado;
            $delta = max(0, $montoNuevo - $montoAnterior);

            // Verificar si ya existe una entrega automática pendiente para esta cobranza
            $entregaExistente = EntregaDinero::where('tipo_origen', 'cobranza')
                ->where('id_origen', $cobranza->id)
                ->where('estado', 'pendiente')
                ->first();

            if ($delta > 0 && !$entregaExistente) {
                $montoEfectivo = 0; $montoCheques = 0; $montoTarjetas = 0;
                switch ($request->metodo_pago) {
                    case 'efectivo':
                    case 'transferencia':
                    case 'otros':
                        $montoEfectivo = $delta; break;
                    case 'cheque':
                        $montoCheques = $delta; break;
                    case 'tarjeta':
                        $montoTarjetas = $delta; break;
                }

                EntregaDinero::create([
                    'user_id'        => $request->user()->id,
                    'fecha_entrega'  => \Carbon\Carbon::parse($request->fecha_pago)->format('Y-m-d'),
                    'monto_efectivo' => $montoEfectivo,
                    'monto_cheques'  => $montoCheques,
                    'monto_tarjetas' => $montoTarjetas,
                    'total'          => $delta,
                    'estado'         => 'pendiente',
                    'notas'          => 'Entrega automática pendiente - Cobranza #' . $cobranza->id . ' - Renta ' . ($cobranza->renta->numero_contrato ?? 'N/A') . ' - Método: ' . $request->metodo_pago,
                    'tipo_origen'    => 'cobranza',
                    'id_origen'      => $cobranza->id,
                ]);
            }

            // Bitácora
            try {
                BitacoraActividad::create([
                    'user_id'     => $request->user()->id,
                    'cliente_id'  => $cobranza->renta?->cliente_id,
                    'titulo'      => 'Actualización de Cobranza #' . $cobranza->id,
                    'descripcion' => 'Pago actualizado. Estado: ' . $estado . ', Monto: ' . number_format((float)$cobranza->monto_pagado, 2) . ', Método: ' . $cobranza->metodo_pago . ($request->filled('motivo_override') ? (' | Override admin: ' . $request->motivo_override) : ''),
                    'fecha'       => now()->toDateString(),
                    'tipo'        => 'update',
                    'estado'      => 'completado',
                ]);
            } catch (\Throwable $e) {
                // no-op
            }

            DB::commit();

            return redirect()->back()->with('success', 'Cobranza marcada como pagada exitosamente');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error al marcar cobranza como pagada: ' . $e->getMessage(), [
                'cobranza_id' => $cobranza->id,
                'user_id' => $request->user()->id,
                'trace' => $e->getTraceAsString()
            ]);

            return redirect()->back()->withErrors(['error' => 'Error interno al procesar el pago']);
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
                    'responsable_cobro' => auth()->user()->id,
                ]);
                $creadas++;
            }
        }

        return redirect()->back()->with('success', "Se generaron {$creadas} cobranzas automáticas.");
    }
}
