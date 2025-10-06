<?php

// app/Http/Controllers/ReporteController.php

namespace App\Http\Controllers;

use App\Models\Reporte;
use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\Venta;
use App\Models\Compra;
use App\Models\Producto;
use App\Models\Cobranza;
use App\Models\Cliente;
use App\Models\Servicio;
use App\Models\Cita;
use App\Models\Mantenimiento;
use App\Models\Renta;
use App\Models\Equipo;
use App\Models\User;
use App\Models\BitacoraActividad;

class ReporteController extends Controller
{
    public function index(Request $request)
    {
        // Filtros para ventas
        $fechaInicio = $request->get('fecha_inicio', now()->startOfMonth()->format('Y-m-d'));
        $fechaFin = $request->get('fecha_fin', now()->endOfMonth()->format('Y-m-d'));
        $clienteId = $request->get('cliente_id');
        $pagado = $request->get('pagado'); // null, true, false

        // Reporte de Ventas con filtros
        $ventasQuery = Venta::with(['productos', 'cliente', 'vendedor', 'items.ventable'])
            ->whereBetween('fecha', [$fechaInicio, $fechaFin]);

        if ($clienteId) {
            $ventasQuery->where('cliente_id', $clienteId);
        }

        if ($pagado !== null) {
            $ventasQuery->where('pagado', $pagado === 'true');
        }

        $ventas = $ventasQuery->get();
        $corteVentas = $ventas->sum('total');

        // Calcular la utilidad total (suma de utilidades por venta)
        $utilidadVentas = $ventas->sum(function ($venta) {
            return $venta->total - $venta->calcularCostoTotal();
        });

        // Agregar el costo_total a cada venta
        $ventasConCosto = $ventas->map(function ($venta) {
            $venta->costo_total = $venta->calcularCostoTotal();
            return $venta;
        });

        // Reporte de Compras con filtros similares
        $comprasQuery = Compra::with(['productos', 'proveedor'])
            ->whereBetween('fecha', [$fechaInicio, $fechaFin]);

        $compras = $comprasQuery->get();
        $totalCompras = $compras->sum('total');

        // Reporte de Inventarios
        $productos = Producto::with(['categoria', 'marca'])->get();

        // Estadísticas adicionales
        $clientes = \App\Models\Cliente::select('id', 'nombre_razon_social')->get();

        return Inertia::render('Reportes/Index', [
            'reportesVentas' => $ventasConCosto,
            'corteVentas' => $corteVentas,
            'utilidadVentas' => $utilidadVentas,
            'reportesCompras' => $compras,
            'totalCompras' => $totalCompras,
            'inventario' => $productos,
            'clientes' => $clientes,
            'filtros' => [
                'fecha_inicio' => $fechaInicio,
                'fecha_fin' => $fechaFin,
                'cliente_id' => $clienteId,
                'pagado' => $pagado,
            ],
        ]);
    }

    public function ventas()
    {
        // Obtener todas las ventas con sus productos e items
        $ventas = Venta::with(['productos', 'items.ventable'])->get();

        // Calcular el corte total (suma de todos los totales de ventas)
        $corte = $ventas->sum('total');

        // Calcular la utilidad total (suma de utilidades por venta)
        $utilidad = $ventas->sum(function ($venta) {
            return $venta->total - $venta->calcularCostoTotal();
        });

        // Agregar el costo_total a cada venta
        $ventasConCosto = $ventas->map(function ($venta) {
            $venta->costo_total = $venta->calcularCostoTotal();
            return $venta;
        });

        return Inertia::render('Reportes/Index', [
            'reportes' => $ventasConCosto,
            'corte' => $corte,
            'utilidad' => $utilidad,
        ]);
    }
    public function create()
    {
        return Inertia::render('Reportes/Create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'nombre' => 'required|string|max:255',
            'descripcion' => 'nullable|string',
            'fecha' => 'required|date',
        ]);

        Reporte::create($request->all());

        return redirect()->route('reportes.index')->with('success', 'Reporte creado exitosamente.');
    }

    public function show(Reporte $reporte)
    {
        return Inertia::render('Reportes/Show', ['reporte' => $reporte]);
    }

    public function edit(Reporte $reporte)
    {
        return Inertia::render('Reportes/Edit', ['reporte' => $reporte]);
    }

    public function update(Request $request, Reporte $reporte)
    {
        $request->validate([
            'nombre' => 'required|string|max:255',
            'descripcion' => 'nullable|string',
            'fecha' => 'required|date',
        ]);

        $reporte->update($request->all());

        return redirect()->route('reportes.index')->with('success', 'Reporte actualizado exitosamente.');
    }

    public function destroy(Reporte $reporte)
    {
        $reporte->delete();
        return redirect()->route('reportes.index')->with('success', 'Reporte eliminado exitosamente.');
    }

    /**
     * Mostrar corte de pagos por período
     */
    public function corteDiario(Request $request)
    {
        $periodo = $request->get('periodo', 'diario');
        $fecha = $request->get('fecha', now()->format('Y-m-d'));

        // Calcular fechas de inicio y fin según el período
        $fecha_inicio = $fecha_fin = $fecha;
        $periodoLabel = 'Diario';

        if ($periodo === 'diario') {
            $fecha_inicio = $fecha_fin = $fecha;
            $periodoLabel = 'Diario';
        } elseif ($periodo === 'semanal') {
            $carbon = \Carbon\Carbon::parse($fecha);
            $fecha_inicio = $carbon->startOfWeek()->format('Y-m-d');
            $fecha_fin = $carbon->endOfWeek()->format('Y-m-d');
            $periodoLabel = 'Semanal';
        } elseif ($periodo === 'mensual') {
            $carbon = \Carbon\Carbon::parse($fecha);
            $fecha_inicio = $carbon->startOfMonth()->format('Y-m-d');
            $fecha_fin = $carbon->endOfMonth()->format('Y-m-d');
            $periodoLabel = 'Mensual';
        } elseif ($periodo === 'anual') {
            $carbon = \Carbon\Carbon::parse($fecha);
            $fecha_inicio = $carbon->startOfYear()->format('Y-m-d');
            $fecha_fin = $carbon->endOfYear()->format('Y-m-d');
            $periodoLabel = 'Anual';
        } elseif ($periodo === 'personalizado') {
            $fecha_inicio = $request->get('fecha_inicio', $fecha);
            $fecha_fin = $request->get('fecha_fin', $fecha);
            $periodoLabel = 'Personalizado';
        }

        // Obtener ventas pagadas en el período especificado
        $ventasPagadas = Venta::with(['cliente', 'items.ventable'])
            ->where('pagado', true)
            ->where('fecha_pago', '>=', $fecha_inicio . ' 00:00:00')
            ->where('fecha_pago', '<=', $fecha_fin . ' 23:59:59')
            ->orderBy('fecha_pago', 'desc')
            ->get();

        // Obtener cobranzas pagadas en el período especificado
        $cobranzasPagadas = Cobranza::with(['renta.cliente', 'responsableCobro'])
            ->whereIn('estado', ['pagado', 'parcial'])
            ->where('fecha_pago', '>=', $fecha_inicio . ' 00:00:00')
            ->where('fecha_pago', '<=', $fecha_fin . ' 23:59:59')
            ->orderBy('fecha_pago', 'desc')
            ->get();

        // Calcular totales por método de pago
        $totalesPorMetodo = [
            'efectivo' => 0,
            'transferencia' => 0,
            'cheque' => 0,
            'tarjeta' => 0,
            'otros' => 0,
        ];

        $totalGeneral = 0;

        // Procesar ventas
        foreach ($ventasPagadas as $venta) {
            $metodo = $venta->metodo_pago ?? 'otros';
            if (isset($totalesPorMetodo[$metodo])) {
                $totalesPorMetodo[$metodo] += $venta->total;
            } else {
                $totalesPorMetodo['otros'] += $venta->total;
            }
            $totalGeneral += $venta->total;
        }

        // Procesar cobranzas
        foreach ($cobranzasPagadas as $cobranza) {
            $metodo = $cobranza->metodo_pago ?? 'otros';
            if (isset($totalesPorMetodo[$metodo])) {
                $totalesPorMetodo[$metodo] += $cobranza->monto_pagado;
            } else {
                $totalesPorMetodo['otros'] += $cobranza->monto_pagado;
            }
            $totalGeneral += $cobranza->monto_pagado;
        }

        // Formatear datos para la vista
        $ventasFormateadas = $ventasPagadas->map(function ($venta) {
            return [
                'id' => $venta->id,
                'tipo' => 'venta',
                'numero' => $venta->numero_venta,
                'cliente' => $venta->cliente->nombre_razon_social ?? 'Sin cliente',
                'concepto' => 'Venta',
                'total' => $venta->total,
                'metodo_pago' => $venta->metodo_pago,
                'fecha_pago' => $venta->fecha_pago ? $venta->fecha_pago->format('Y-m-d H:i') : null,
                'notas_pago' => $venta->notas_pago,
                'pagado_por' => $venta->pagadoPor?->name ?? 'Sistema',
            ];
        });

        $cobranzasFormateadas = $cobranzasPagadas->map(function ($cobranza) {
            return [
                'id' => $cobranza->id,
                'tipo' => 'cobranza',
                'numero' => $cobranza->renta->numero_contrato ?? 'N/A',
                'cliente' => $cobranza->renta->cliente->nombre_razon_social ?? 'Sin cliente',
                'concepto' => $cobranza->concepto ?? 'Cobranza',
                'total' => $cobranza->monto_pagado,
                'metodo_pago' => $cobranza->metodo_pago,
                'fecha_pago' => $cobranza->fecha_pago ? $cobranza->fecha_pago->format('Y-m-d H:i') : null,
                'notas_pago' => $cobranza->notas_pago,
                'pagado_por' => $cobranza->responsableCobro?->name ?? 'Sistema',
            ];
        });

        // Combinar y ordenar por fecha de pago
        $pagosFormateados = collect([...$ventasFormateadas, ...$cobranzasFormateadas])
            ->sortByDesc('fecha_pago')
            ->values();

        return Inertia::render('Reportes/CorteDiario', [
            'pagos' => $pagosFormateados,
            'totalesPorMetodo' => $totalesPorMetodo,
            'totalGeneral' => $totalGeneral,
            'periodo' => $periodo,
            'periodoLabel' => $periodoLabel,
            'fecha' => $fecha,
            'fecha_inicio' => $fecha_inicio,
            'fecha_fin' => $fecha_fin,
            'fechaFormateada' => $periodo === 'personalizado'
                ? "Del " . \Carbon\Carbon::parse($fecha_inicio)->locale('es')->isoFormat('D [de] MMMM [de] YYYY') . " al " . \Carbon\Carbon::parse($fecha_fin)->locale('es')->isoFormat('D [de] MMMM [de] YYYY')
                : \Carbon\Carbon::parse($fecha)->locale('es')->isoFormat($periodo === 'diario' ? 'dddd, D [de] MMMM [de] YYYY' : ($periodo === 'semanal' ? '[Semana del] D [de] MMMM [de] YYYY' : ($periodo === 'mensual' ? 'MMMM [de] YYYY' : 'YYYY'))),
        ]);
    }

    /**
     * Exportar corte de pagos a Excel/CSV
     */
    public function exportarCorteDiario(Request $request)
    {
        $periodo = $request->get('periodo', 'diario');
        $fecha = $request->get('fecha', now()->format('Y-m-d'));
        $tipo = $request->get('tipo', 'excel'); // excel, csv, pdf

        // Calcular fechas de inicio y fin según el período
        $fecha_inicio = $fecha_fin = $fecha;

        if ($periodo === 'diario') {
            $fecha_inicio = $fecha_fin = $fecha;
        } elseif ($periodo === 'semanal') {
            $carbon = \Carbon\Carbon::parse($fecha);
            $fecha_inicio = $carbon->startOfWeek()->format('Y-m-d');
            $fecha_fin = $carbon->endOfWeek()->format('Y-m-d');
        } elseif ($periodo === 'mensual') {
            $carbon = \Carbon\Carbon::parse($fecha);
            $fecha_inicio = $carbon->startOfMonth()->format('Y-m-d');
            $fecha_fin = $carbon->endOfMonth()->format('Y-m-d');
        } elseif ($periodo === 'anual') {
            $carbon = \Carbon\Carbon::parse($fecha);
            $fecha_inicio = $carbon->startOfYear()->format('Y-m-d');
            $fecha_fin = $carbon->endOfYear()->format('Y-m-d');
        } elseif ($periodo === 'personalizado') {
            $fecha_inicio = $request->get('fecha_inicio', $fecha);
            $fecha_fin = $request->get('fecha_fin', $fecha);
        }

        // Obtener ventas pagadas en el período
        $ventasPagadas = Venta::with(['cliente', 'items.ventable'])
            ->where('pagado', true)
            ->where('fecha_pago', '>=', $fecha_inicio . ' 00:00:00')
            ->where('fecha_pago', '<=', $fecha_fin . ' 23:59:59')
            ->orderBy('fecha_pago', 'desc')
            ->get();

        // Obtener cobranzas pagadas en el período
        $cobranzasPagadas = Cobranza::with(['renta.cliente', 'responsableCobro'])
            ->whereIn('estado', ['pagado', 'parcial'])
            ->where('fecha_pago', '>=', $fecha_inicio . ' 00:00:00')
            ->where('fecha_pago', '<=', $fecha_fin . ' 23:59:59')
            ->orderBy('fecha_pago', 'desc')
            ->get();

        // Calcular totales por método de pago
        $totalesPorMetodo = [
            'efectivo' => 0,
            'transferencia' => 0,
            'cheque' => 0,
            'tarjeta' => 0,
            'otros' => 0,
        ];

        $totalGeneral = 0;

        // Procesar ventas
        foreach ($ventasPagadas as $venta) {
            $metodo = $venta->metodo_pago ?? 'otros';
            if (isset($totalesPorMetodo[$metodo])) {
                $totalesPorMetodo[$metodo] += $venta->total;
            } else {
                $totalesPorMetodo['otros'] += $venta->total;
            }
            $totalGeneral += $venta->total;
        }

        // Procesar cobranzas
        foreach ($cobranzasPagadas as $cobranza) {
            $metodo = $cobranza->metodo_pago ?? 'otros';
            if (isset($totalesPorMetodo[$metodo])) {
                $totalesPorMetodo[$metodo] += $cobranza->monto_pagado;
            } else {
                $totalesPorMetodo['otros'] += $cobranza->monto_pagado;
            }
            $totalGeneral += $cobranza->monto_pagado;
        }

        // Para este ejemplo, devolveremos JSON que puede ser usado por JavaScript para generar Excel
        // En un entorno real, usaríamos librerías como Laravel Excel o Maatwebsite Excel

        // Combinar ventas y cobranzas para exportación
        $ventasFormateadas = $ventasPagadas->map(function ($venta) {
            return [
                'tipo' => 'venta',
                'numero' => $venta->numero_venta,
                'cliente' => $venta->cliente->nombre_razon_social ?? 'Sin cliente',
                'concepto' => 'Venta',
                'metodo_pago' => $venta->metodo_pago,
                'total' => $venta->total,
                'fecha_pago' => $venta->fecha_pago ? $venta->fecha_pago->format('Y-m-d H:i:s') : null,
                'notas_pago' => $venta->notas_pago,
                'pagado_por' => $venta->pagadoPor?->name ?? 'Sistema',
            ];
        });

        $cobranzasFormateadas = $cobranzasPagadas->map(function ($cobranza) {
            return [
                'tipo' => 'cobranza',
                'numero' => $cobranza->renta->numero_contrato ?? 'N/A',
                'cliente' => $cobranza->renta->cliente->nombre_razon_social ?? 'Sin cliente',
                'concepto' => $cobranza->concepto ?? 'Cobranza',
                'metodo_pago' => $cobranza->metodo_pago,
                'total' => $cobranza->monto_pagado,
                'fecha_pago' => $cobranza->fecha_pago ? $cobranza->fecha_pago->format('Y-m-d H:i:s') : null,
                'notas_pago' => $cobranza->notas_pago,
                'pagado_por' => $cobranza->responsableCobro?->name ?? 'Sistema',
            ];
        });

        $pagosCombinados = collect([...$ventasFormateadas, ...$cobranzasFormateadas])
            ->sortByDesc('fecha_pago')
            ->values();

        $data = [
            'periodo' => $periodo,
            'fecha' => $fecha,
            'fecha_inicio' => $fecha_inicio,
            'fecha_fin' => $fecha_fin,
            'total_general' => $totalGeneral,
            'totales_por_metodo' => $totalesPorMetodo,
            'pagos' => $pagosCombinados->toArray()
        ];

        return response()->json($data);
    }

    /**
     * Reporte de clientes
     */
    public function clientes(Request $request)
    {
        $fechaInicio = $request->get('fecha_inicio', now()->startOfMonth()->format('Y-m-d'));
        $fechaFin = $request->get('fecha_fin', now()->endOfMonth()->format('Y-m-d'));
        $tipo = $request->get('tipo', 'todos'); // todos, activos, deudores, nuevos

        $clientesQuery = Cliente::with(['ventas' => function($q) use ($fechaInicio, $fechaFin) {
            $q->whereBetween('fecha', [$fechaInicio, $fechaFin]);
        }, 'rentas.cobranzas' => function($q) use ($fechaInicio, $fechaFin) {
            $q->whereBetween('fecha_pago', [$fechaInicio, $fechaFin]);
        }]);

        if ($tipo === 'nuevos') {
            $clientesQuery->whereBetween('created_at', [$fechaInicio, $fechaFin]);
        }

        $clientes = $clientesQuery->get()->map(function ($cliente) {
            $totalVentas = $cliente->ventas->sum('total');
            $totalCobranzas = $cliente->rentas->flatMap->cobranzas->sum('monto_pagado');
            $deudaPendiente = $cliente->rentas->where('estado', 'activa')->sum('monto_total') - $totalCobranzas;

            return [
                'id' => $cliente->id,
                'nombre_razon_social' => $cliente->nombre_razon_social,
                'email' => $cliente->email,
                'telefono' => $cliente->telefono,
                'fecha_registro' => $cliente->created_at->format('Y-m-d'),
                'total_ventas' => $totalVentas,
                'total_cobranzas' => $totalCobranzas,
                'deuda_pendiente' => $deudaPendiente,
                'numero_ventas' => $cliente->ventas->count(),
                'numero_rentas' => $cliente->rentas->count(),
            ];
        });

        // Filtrar según tipo
        if ($tipo === 'activos') {
            $clientes = $clientes->filter(function ($cliente) {
                return $cliente['numero_ventas'] > 0 || $cliente['numero_rentas'] > 0;
            });
        } elseif ($tipo === 'deudores') {
            $clientes = $clientes->filter(function ($cliente) {
                return $cliente['deuda_pendiente'] > 0;
            });
        }

        $estadisticas = [
            'total_clientes' => $clientes->count(),
            'clientes_activos' => $clientes->where('numero_ventas', '>', 0)->count(),
            'clientes_deudores' => $clientes->where('deuda_pendiente', '>', 0)->count(),
            'total_ventas' => $clientes->sum('total_ventas'),
            'total_deuda' => $clientes->sum('deuda_pendiente'),
        ];

        return Inertia::render('Reportes/Clientes', [
            'clientes' => $clientes->values(),
            'estadisticas' => $estadisticas,
            'filtros' => [
                'fecha_inicio' => $fechaInicio,
                'fecha_fin' => $fechaFin,
                'tipo' => $tipo,
            ],
        ]);
    }

    /**
     * Reporte de inventario
     */
    public function inventario(Request $request)
    {
        $categoriaId = $request->get('categoria_id');
        $marcaId = $request->get('marca_id');
        $tipo = $request->get('tipo', 'todos'); // todos, bajos, sin_stock

        $productosQuery = Producto::with(['categoria', 'marca', 'proveedor']);

        if ($categoriaId) {
            $productosQuery->where('categoria_id', $categoriaId);
        }

        if ($marcaId) {
            $productosQuery->where('marca_id', $marcaId);
        }

        // Obtener marcas para el filtro
        $marcas = \App\Models\Marca::select('id', 'nombre')->get();

        $productos = $productosQuery->get()->map(function ($producto) {
            return [
                'id' => $producto->id,
                'nombre' => $producto->nombre,
                'codigo' => $producto->codigo,
                'categoria' => $producto->categoria?->nombre,
                'marca' => $producto->marca?->nombre,
                'proveedor' => $producto->proveedor?->nombre_razon_social,
                'stock' => $producto->stock,
                'stock_minimo' => $producto->stock_minimo,
                'precio_compra' => $producto->precio_compra,
                'precio_venta' => $producto->precio_venta,
                'estado' => $producto->stock <= 0 ? 'sin_stock' : ($producto->stock <= $producto->stock_minimo ? 'bajo' : 'normal'),
            ];
        });

        // Filtrar según tipo
        if ($tipo === 'bajos') {
            $productos = $productos->filter(function ($p) {
                return $p['stock'] <= $p['stock_minimo'] && $p['stock'] > 0;
            });
        } elseif ($tipo === 'sin_stock') {
            $productos = $productos->filter(function ($p) {
                return $p['stock'] <= 0;
            });
        }

        $estadisticas = [
            'total_productos' => $productos->count(),
            'productos_bajos' => $productos->where('estado', 'bajo')->count(),
            'productos_sin_stock' => $productos->where('estado', 'sin_stock')->count(),
            'valor_inventario' => $productos->sum(function ($p) {
                return $p['stock'] * $p['precio_compra'];
            }),
        ];

        $categorias = \App\Models\Categoria::select('id', 'nombre')->get();
        $marcas = \App\Models\Marca::select('id', 'nombre')->get();

        return Inertia::render('Reportes/Inventario', [
            'productos' => $productos->values(),
            'estadisticas' => $estadisticas,
            'categorias' => $categorias,
            'marcas' => $marcas,
            'filtros' => [
                'categoria_id' => $categoriaId,
                'marca_id' => $marcaId,
                'tipo' => $tipo,
            ],
        ]);
    }

    /**
     * Reporte de servicios
     */
    public function servicios(Request $request)
    {
        $fechaInicio = $request->get('fecha_inicio', now()->startOfMonth()->format('Y-m-d'));
        $fechaFin = $request->get('fecha_fin', now()->endOfMonth()->format('Y-m-d'));

        $servicios = Servicio::with(['ventas' => function($q) use ($fechaInicio, $fechaFin) {
            $q->whereBetween('fecha', [$fechaInicio, $fechaFin]);
        }])->get()->map(function ($servicio) {
            $totalVendido = $servicio->ventas->sum(function ($venta) {
                $pivot = $venta->pivot;
                return ($pivot->precio - ($pivot->descuento ?? 0)) * $pivot->cantidad;
            });
            $cantidadVendida = $servicio->ventas->sum('pivot.cantidad');
            $ganancia = $totalVendido * ($servicio->margen_ganancia / 100);

            return [
                'id' => $servicio->id,
                'nombre' => $servicio->nombre,
                'descripcion' => $servicio->descripcion,
                'precio' => $servicio->precio,
                'margen_ganancia' => $servicio->margen_ganancia,
                'comision_vendedor' => $servicio->comision_vendedor,
                'es_instalacion' => $servicio->es_instalacion,
                'cantidad_vendida' => $cantidadVendida,
                'total_vendido' => $totalVendido,
                'ganancia' => $ganancia,
                'numero_ventas' => $servicio->ventas->count(),
            ];
        })->sortByDesc('total_vendido')->values();

        $estadisticas = [
            'total_servicios' => $servicios->count(),
            'servicios_vendidos' => $servicios->where('numero_ventas', '>', 0)->count(),
            'total_ingresos' => $servicios->sum('total_vendido'),
            'total_ganancias' => $servicios->sum('ganancia'),
        ];

        return Inertia::render('Reportes/Servicios', [
            'servicios' => $servicios,
            'estadisticas' => $estadisticas,
            'filtros' => [
                'fecha_inicio' => $fechaInicio,
                'fecha_fin' => $fechaFin,
            ],
        ]);
    }

    /**
     * Reporte de citas
     */
    public function citas(Request $request)
    {
        $fechaInicio = $request->get('fecha_inicio', now()->startOfMonth()->format('Y-m-d'));
        $fechaFin = $request->get('fecha_fin', now()->endOfMonth()->format('Y-m-d'));
        $tecnicoId = $request->get('tecnico_id');
        $estado = $request->get('estado', 'todos'); // todos, pendiente, completada, cancelada

        $citasQuery = Cita::with(['cliente', 'tecnico', 'servicio'])
            ->whereBetween('fecha', [$fechaInicio, $fechaFin]);

        if ($tecnicoId) {
            $citasQuery->where('tecnico_id', $tecnicoId);
        }

        if ($estado !== 'todos') {
            $citasQuery->where('estado', $estado);
        }

        $citas = $citasQuery->get()->map(function ($cita) {
            return [
                'id' => $cita->id,
                'fecha' => $cita->fecha->format('Y-m-d'),
                'hora' => $cita->hora,
                'cliente' => $cita->cliente?->nombre_razon_social,
                'tecnico' => $cita->tecnico?->nombre . ' ' . $cita->tecnico?->apellido,
                'servicio' => $cita->servicio?->nombre,
                'estado' => $cita->estado,
                'notas' => $cita->notas,
                'precio' => $cita->precio,
            ];
        });

        $estadisticas = [
            'total_citas' => $citas->count(),
            'citas_pendientes' => $citas->where('estado', 'pendiente')->count(),
            'citas_completadas' => $citas->where('estado', 'completada')->count(),
            'citas_canceladas' => $citas->where('estado', 'cancelada')->count(),
            'total_ingresos' => $citas->where('estado', 'completada')->sum('precio'),
        ];

        $tecnicos = \App\Models\Tecnico::select('id', 'nombre', 'apellido')->get();

        return Inertia::render('Reportes/Citas', [
            'citas' => $citas,
            'estadisticas' => $estadisticas,
            'tecnicos' => $tecnicos,
            'filtros' => [
                'fecha_inicio' => $fechaInicio,
                'fecha_fin' => $fechaFin,
                'tecnico_id' => $tecnicoId,
                'estado' => $estado,
            ],
        ]);
    }

    /**
     * Reporte de ganancias generales
     */
    public function ganancias(Request $request)
    {
        $fechaInicio = $request->get('fecha_inicio', now()->startOfMonth()->format('Y-m-d'));
        $fechaFin = $request->get('fecha_fin', now()->endOfMonth()->format('Y-m-d'));

        // Ventas
        $ventas = Venta::with('productos')->whereBetween('fecha', [$fechaInicio, $fechaFin])->get();
        $ingresosVentas = $ventas->sum('total');
        $costosVentas = $ventas->sum(function ($venta) {
            return $venta->calcularCostoTotal();
        });
        $gananciasVentas = $ingresosVentas - $costosVentas;

        // Compras
        $compras = Compra::whereBetween('fecha', [$fechaInicio, $fechaFin])->get();
        $gastosCompras = $compras->sum('total');

        // Servicios (citas completadas)
        $citasCompletadas = Cita::whereBetween('fecha', [$fechaInicio, $fechaFin])
            ->where('estado', 'completada')->get();
        $ingresosServicios = $citasCompletadas->sum('precio');

        // Cobranzas
        $cobranzas = Cobranza::whereBetween('fecha_pago', [$fechaInicio, $fechaFin])
            ->whereIn('estado', ['pagado', 'parcial'])->get();
        $ingresosCobranzas = $cobranzas->sum('monto_pagado');

        // Rentas activas (ingresos proyectados)
        $rentasActivas = Renta::where('estado', 'activa')->with('cobranzas')->get();
        $ingresosRentasProyectados = $rentasActivas->sum(function ($renta) {
            $pagado = $renta->cobranzas->whereIn('estado', ['pagado', 'parcial'])->sum('monto_pagado');
            return $renta->monto_total - $pagado;
        });

        $totalIngresos = $ingresosVentas + $ingresosServicios + $ingresosCobranzas;
        $totalGastos = $costosVentas + $gastosCompras;
        $gananciaNeta = $totalIngresos - $totalGastos;

        return Inertia::render('Reportes/Ganancias', [
            'periodo' => [
                'inicio' => $fechaInicio,
                'fin' => $fechaFin,
            ],
            'ingresos' => [
                'ventas' => $ingresosVentas,
                'servicios' => $ingresosServicios,
                'cobranzas' => $ingresosCobranzas,
                'rentas_proyectadas' => $ingresosRentasProyectados,
                'total' => $totalIngresos,
            ],
            'gastos' => [
                'compras' => $gastosCompras,
                'costos_ventas' => $costosVentas,
                'total' => $totalGastos,
            ],
            'ganancias' => [
                'ventas' => $gananciasVentas,
                'neta' => $gananciaNeta,
            ],
        ]);
    }

    /**
     * Reporte de mantenimientos
     */
    public function mantenimientos(Request $request)
    {
        $fechaInicio = $request->get('fecha_inicio', now()->startOfMonth()->format('Y-m-d'));
        $fechaFin = $request->get('fecha_fin', now()->endOfMonth()->format('Y-m-d'));
        $tecnicoId = $request->get('tecnico_id');
        $estado = $request->get('estado', 'todos'); // todos, pendiente, completado, cancelado

        $mantenimientosQuery = Mantenimiento::with(['carro', 'tecnico', 'herramientas'])
            ->whereBetween('fecha', [$fechaInicio, $fechaFin]);

        if ($tecnicoId) {
            $mantenimientosQuery->where('tecnico_id', $tecnicoId);
        }

        if ($estado !== 'todos') {
            $mantenimientosQuery->where('estado', $estado);
        }

        $mantenimientos = $mantenimientosQuery->get()->map(function ($mantenimiento) {
            return [
                'id' => $mantenimiento->id,
                'fecha' => $mantenimiento->fecha->format('Y-m-d'),
                'carro' => $mantenimiento->carro?->modelo . ' ' . $mantenimiento->carro?->placas,
                'tecnico' => $mantenimiento->tecnico?->nombre . ' ' . $mantenimiento->tecnico?->apellido,
                'tipo' => $mantenimiento->tipo,
                'descripcion' => $mantenimiento->descripcion,
                'estado' => $mantenimiento->estado,
                'costo' => $mantenimiento->costo,
                'notas' => $mantenimiento->notas,
                'herramientas_count' => $mantenimiento->herramientas->count(),
            ];
        });

        $estadisticas = [
            'total_mantenimientos' => $mantenimientos->count(),
            'mantenimientos_pendientes' => $mantenimientos->where('estado', 'pendiente')->count(),
            'mantenimientos_completados' => $mantenimientos->where('estado', 'completado')->count(),
            'mantenimientos_cancelados' => $mantenimientos->where('estado', 'cancelado')->count(),
            'total_costos' => $mantenimientos->sum('costo'),
        ];

        $tecnicos = \App\Models\Tecnico::select('id', 'nombre', 'apellido')->get();

        return Inertia::render('Reportes/Mantenimientos', [
            'mantenimientos' => $mantenimientos,
            'estadisticas' => $estadisticas,
            'tecnicos' => $tecnicos,
            'filtros' => [
                'fecha_inicio' => $fechaInicio,
                'fecha_fin' => $fechaFin,
                'tecnico_id' => $tecnicoId,
                'estado' => $estado,
            ],
        ]);
    }

    /**
     * Reporte de rentas y equipos
     */
    public function rentas(Request $request)
    {
        $fechaInicio = $request->get('fecha_inicio', now()->startOfMonth()->format('Y-m-d'));
        $fechaFin = $request->get('fecha_fin', now()->endOfMonth()->format('Y-m-d'));
        $estado = $request->get('estado', 'todos'); // todos, activa, finalizada, cancelada

        $rentasQuery = Renta::with(['cliente', 'equipos', 'cobranzas'])
            ->whereBetween('fecha_inicio', [$fechaInicio, $fechaFin]);

        if ($estado !== 'todos') {
            $rentasQuery->where('estado', $estado);
        }

        $rentas = $rentasQuery->get()->map(function ($renta) {
            $totalCobrado = $renta->cobranzas->whereIn('estado', ['pagado', 'parcial'])->sum('monto_pagado');
            $pendiente = $renta->monto_total - $totalCobrado;

            return [
                'id' => $renta->id,
                'numero_contrato' => $renta->numero_contrato,
                'cliente' => $renta->cliente?->nombre_razon_social,
                'fecha_inicio' => $renta->fecha_inicio->format('Y-m-d'),
                'fecha_fin' => $renta->fecha_fin?->format('Y-m-d'),
                'estado' => $renta->estado,
                'monto_total' => $renta->monto_total,
                'total_cobrado' => $totalCobrado,
                'pendiente' => $pendiente,
                'equipos_count' => $renta->equipos->count(),
                'cobranzas_count' => $renta->cobranzas->count(),
            ];
        });

        $estadisticas = [
            'total_rentas' => $rentas->count(),
            'rentas_activas' => $rentas->where('estado', 'activa')->count(),
            'rentas_finalizadas' => $rentas->where('estado', 'finalizada')->count(),
            'rentas_canceladas' => $rentas->where('estado', 'cancelada')->count(),
            'total_ingresos' => $rentas->sum('total_cobrado'),
            'total_pendiente' => $rentas->sum('pendiente'),
        ];

        return Inertia::render('Reportes/Rentas', [
            'rentas' => $rentas,
            'estadisticas' => $estadisticas,
            'filtros' => [
                'fecha_inicio' => $fechaInicio,
                'fecha_fin' => $fechaFin,
                'estado' => $estado,
            ],
        ]);
    }

    /**
     * Reporte de cobranzas detallado
     */
    public function cobranzas(Request $request)
    {
        $fechaInicio = $request->get('fecha_inicio', now()->startOfMonth()->format('Y-m-d'));
        $fechaFin = $request->get('fecha_fin', now()->endOfMonth()->format('Y-m-d'));
        $estado = $request->get('estado', 'todos'); // todos, pagado, parcial, pendiente
        $metodoPago = $request->get('metodo_pago');

        $cobranzasQuery = Cobranza::with(['renta.cliente', 'responsableCobro'])
            ->whereBetween('fecha_pago', [$fechaInicio, $fechaFin]);

        if ($estado !== 'todos') {
            $cobranzasQuery->where('estado', $estado);
        }

        if ($metodoPago) {
            $cobranzasQuery->where('metodo_pago', $metodoPago);
        }

        $cobranzas = $cobranzasQuery->get()->map(function ($cobranza) {
            return [
                'id' => $cobranza->id,
                'numero_contrato' => $cobranza->renta?->numero_contrato,
                'cliente' => $cobranza->renta?->cliente?->nombre_razon_social,
                'concepto' => $cobranza->concepto,
                'monto_total' => $cobranza->monto_total,
                'monto_pagado' => $cobranza->monto_pagado,
                'estado' => $cobranza->estado,
                'metodo_pago' => $cobranza->metodo_pago,
                'fecha_pago' => $cobranza->fecha_pago?->format('Y-m-d H:i'),
                'notas_pago' => $cobranza->notas_pago,
                'responsable' => $cobranza->responsableCobro?->name,
            ];
        });

        $estadisticas = [
            'total_cobranzas' => $cobranzas->count(),
            'cobranzas_pagadas' => $cobranzas->where('estado', 'pagado')->count(),
            'cobranzas_parciales' => $cobranzas->where('estado', 'parcial')->count(),
            'cobranzas_pendientes' => $cobranzas->where('estado', 'pendiente')->count(),
            'total_cobrado' => $cobranzas->whereIn('estado', ['pagado', 'parcial'])->sum('monto_pagado'),
            'total_pendiente' => $cobranzas->where('estado', 'pendiente')->sum('monto_total'),
        ];

        return Inertia::render('Reportes/Cobranzas', [
            'cobranzas' => $cobranzas,
            'estadisticas' => $estadisticas,
            'filtros' => [
                'fecha_inicio' => $fechaInicio,
                'fecha_fin' => $fechaFin,
                'estado' => $estado,
                'metodo_pago' => $metodoPago,
            ],
        ]);
    }

    /**
     * Reporte de productos (más vendidos, ganancias)
     */
    public function productos(Request $request)
    {
        // Si no hay fechas especificadas, usar un rango amplio (último año)
        $fechaInicio = $request->get('fecha_inicio', now()->subYear()->format('Y-m-d'));
        $fechaFin = $request->get('fecha_fin', now()->format('Y-m-d'));
        $categoriaId = $request->get('categoria_id');
        $marcaId = $request->get('marca_id');

        $productosQuery = Producto::with(['categoria', 'marca']);

        if ($categoriaId) {
            $productosQuery->where('categoria_id', $categoriaId);
        }

        if ($marcaId) {
            $productosQuery->where('marca_id', $marcaId);
        }

        $productos = $productosQuery->get()->map(function ($producto) use ($fechaInicio, $fechaFin) {
            // Obtener estadísticas de ventas para este producto en el período
            // Incluir tanto ventas pagadas como no pagadas
            $ventaItems = \App\Models\VentaItem::with('venta')
                ->where('ventable_type', \App\Models\Producto::class)
                ->where('ventable_id', $producto->id)
                ->whereHas('venta', function($q) use ($fechaInicio, $fechaFin) {
                    $q->whereBetween('fecha', [$fechaInicio, $fechaFin]);
                })
                ->get();

            $cantidadVendida = $ventaItems->sum('cantidad');
            $totalVendido = $ventaItems->sum(function ($item) {
                return ($item->precio - ($item->descuento ?? 0)) * $item->cantidad;
            });
            $costoTotal = $ventaItems->sum(function ($item) use ($producto) {
                return ($item->costo_unitario ?? $producto->precio_compra) * $item->cantidad;
            });
            $ganancia = $totalVendido - $costoTotal;
            $numeroVentas = $ventaItems->groupBy('venta_id')->count();

            return [
                'id' => $producto->id,
                'nombre' => $producto->nombre,
                'codigo' => $producto->codigo,
                'categoria' => $producto->categoria?->nombre,
                'marca' => $producto->marca?->nombre,
                'precio_compra' => $producto->precio_compra,
                'precio_venta' => $producto->precio_venta,
                'stock' => $producto->stock,
                'cantidad_vendida' => $cantidadVendida,
                'total_vendido' => $totalVendido,
                'costo_total' => $costoTotal,
                'ganancia' => $ganancia,
                'numero_ventas' => $numeroVentas,
            ];
        })->sortByDesc('total_vendido')->values();

        $estadisticas = [
            'total_productos' => $productos->count(),
            'productos_vendidos' => $productos->where('numero_ventas', '>', 0)->count(),
            'total_ingresos' => $productos->sum('total_vendido'),
            'total_ganancias' => $productos->sum('ganancia'),
            'total_vendido_unidades' => $productos->sum('cantidad_vendida'),
        ];

        $categorias = \App\Models\Categoria::select('id', 'nombre')->get();
        $marcas = \App\Models\Marca::select('id', 'nombre')->get();

        return Inertia::render('Reportes/Productos', [
            'productos' => $productos,
            'estadisticas' => $estadisticas,
            'categorias' => $categorias,
            'marcas' => $marcas,
            'filtros' => [
                'fecha_inicio' => $fechaInicio,
                'fecha_fin' => $fechaFin,
                'categoria_id' => $categoriaId,
                'marca_id' => $marcaId,
            ],
        ]);
    }

    /**
     * Reporte de proveedores
     */
    public function proveedores(Request $request)
    {
        $fechaInicio = $request->get('fecha_inicio', now()->startOfMonth()->format('Y-m-d'));
        $fechaFin = $request->get('fecha_fin', now()->endOfMonth()->format('Y-m-d'));

        $proveedores = \App\Models\Proveedor::with(['productos.compras' => function($q) use ($fechaInicio, $fechaFin) {
            $q->whereBetween('fecha', [$fechaInicio, $fechaFin]);
        }])->get()->map(function ($proveedor) {
            $productos = $proveedor->productos;
            $totalComprado = $productos->flatMap->compras->sum('pivot.total');
            $cantidadComprada = $productos->flatMap->compras->sum('pivot.cantidad');

            return [
                'id' => $proveedor->id,
                'nombre_razon_social' => $proveedor->nombre_razon_social,
                'email' => $proveedor->email,
                'telefono' => $proveedor->telefono,
                'productos_count' => $productos->count(),
                'cantidad_comprada' => $cantidadComprada,
                'total_comprado' => $totalComprado,
                'compras_count' => $productos->flatMap->compras->count(),
            ];
        })->sortByDesc('total_comprado')->values();

        $estadisticas = [
            'total_proveedores' => $proveedores->count(),
            'proveedores_activos' => $proveedores->where('compras_count', '>', 0)->count(),
            'total_compras' => $proveedores->sum('total_comprado'),
            'total_productos_comprados' => $proveedores->sum('cantidad_comprada'),
        ];

        return Inertia::render('Reportes/Proveedores', [
            'proveedores' => $proveedores,
            'estadisticas' => $estadisticas,
            'filtros' => [
                'fecha_inicio' => $fechaInicio,
                'fecha_fin' => $fechaFin,
            ],
        ]);
    }

    /**
     * Reporte de empleados/usuarios
     */
    public function empleados(Request $request)
    {
        $fechaInicio = $request->get('fecha_inicio', now()->startOfMonth()->format('Y-m-d'));
        $fechaFin = $request->get('fecha_fin', now()->endOfMonth()->format('Y-m-d'));
        $tipo = $request->get('tipo', 'todos'); // todos, tecnicos, usuarios

        $query = User::with(['tecnico']);

        if ($tipo === 'tecnicos') {
            $query->whereHas('tecnico');
        }

        $empleados = $query->get()->map(function ($user) {
            $tecnico = $user->tecnico;
            $ventasCount = 0;
            $citasCount = 0;
            $mantenimientosCount = 0;

            if ($tecnico) {
                $ventasCount = Venta::where('vendedor_type', \App\Models\Tecnico::class)
                    ->where('vendedor_id', $tecnico->id)->count();
                $citasCount = Cita::where('tecnico_id', $tecnico->id)->count();
                $mantenimientosCount = Mantenimiento::where('tecnico_id', $tecnico->id)->count();
            }

            return [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'activo' => $user->activo,
                'fecha_registro' => $user->created_at->format('Y-m-d'),
                'es_tecnico' => $tecnico ? true : false,
                'tecnico_nombre' => $tecnico ? $tecnico->nombre . ' ' . $tecnico->apellido : null,
                'ventas_count' => $ventasCount,
                'citas_count' => $citasCount,
                'mantenimientos_count' => $mantenimientosCount,
            ];
        });

        $estadisticas = [
            'total_empleados' => $empleados->count(),
            'empleados_activos' => $empleados->where('activo', true)->count(),
            'tecnicos' => $empleados->where('es_tecnico', true)->count(),
            'total_ventas' => $empleados->sum('ventas_count'),
            'total_citas' => $empleados->sum('citas_count'),
            'total_mantenimientos' => $empleados->sum('mantenimientos_count'),
        ];

        return Inertia::render('Reportes/Empleados', [
            'empleados' => $empleados,
            'estadisticas' => $estadisticas,
            'filtros' => [
                'fecha_inicio' => $fechaInicio,
                'fecha_fin' => $fechaFin,
                'tipo' => $tipo,
            ],
        ]);
    }

    /**
     * Reporte de auditoría/bitácora
     */
    public function auditoria(Request $request)
    {
        $fechaInicio = $request->get('fecha_inicio', now()->startOfMonth()->format('Y-m-d'));
        $fechaFin = $request->get('fecha_fin', now()->endOfMonth()->format('Y-m-d'));
        $usuarioId = $request->get('usuario_id');
        $tipo = $request->get('tipo'); // login, logout, create, update, delete

        $bitacorasQuery = BitacoraActividad::with('usuario')
            ->whereBetween('created_at', [$fechaInicio . ' 00:00:00', $fechaFin . ' 23:59:59']);

        if ($usuarioId) {
            $bitacorasQuery->where('user_id', $usuarioId);
        }

        if ($tipo) {
            $bitacorasQuery->where('tipo', $tipo);
        }

        $bitacoras = $bitacorasQuery->orderBy('created_at', 'desc')->get()->map(function ($bitacora) {
            return [
                'id' => $bitacora->id,
                'usuario' => $bitacora->usuario?->name,
                'tipo' => $bitacora->tipo,
                'descripcion' => $bitacora->descripcion,
                'modelo' => $bitacora->modelo,
                'modelo_id' => $bitacora->modelo_id,
                'datos_anteriores' => $bitacora->datos_anteriores,
                'datos_nuevos' => $bitacora->datos_nuevos,
                'ip' => $bitacora->ip,
                'fecha' => $bitacora->created_at->format('Y-m-d H:i:s'),
            ];
        });

        $estadisticas = [
            'total_actividades' => $bitacoras->count(),
            'actividades_login' => $bitacoras->where('tipo', 'login')->count(),
            'actividades_logout' => $bitacoras->where('tipo', 'logout')->count(),
            'actividades_create' => $bitacoras->where('tipo', 'create')->count(),
            'actividades_update' => $bitacoras->where('tipo', 'update')->count(),
            'actividades_delete' => $bitacoras->where('tipo', 'delete')->count(),
        ];

        $usuarios = User::select('id', 'name')->get();

        return Inertia::render('Reportes/Auditoria', [
            'bitacoras' => $bitacoras,
            'estadisticas' => $estadisticas,
            'usuarios' => $usuarios,
            'filtros' => [
                'fecha_inicio' => $fechaInicio,
                'fecha_fin' => $fechaFin,
                'usuario_id' => $usuarioId,
                'tipo' => $tipo,
            ],
        ]);
    }

    /**
     * Exportar reporte de clientes a JSON para Excel
     */
    public function exportarClientes(Request $request)
    {
        $fechaInicio = $request->get('fecha_inicio', now()->startOfMonth()->format('Y-m-d'));
        $fechaFin = $request->get('fecha_fin', now()->endOfMonth()->format('Y-m-d'));
        $tipo = $request->get('tipo', 'todos');

        $clientesQuery = Cliente::with(['ventas' => function($q) use ($fechaInicio, $fechaFin) {
            $q->whereBetween('fecha', [$fechaInicio, $fechaFin]);
        }, 'rentas.cobranzas' => function($q) use ($fechaInicio, $fechaFin) {
            $q->whereBetween('fecha_pago', [$fechaInicio, $fechaFin]);
        }]);

        if ($tipo === 'nuevos') {
            $clientesQuery->whereBetween('created_at', [$fechaInicio, $fechaFin]);
        }

        $clientes = $clientesQuery->get()->map(function ($cliente) {
            $totalVentas = $cliente->ventas->sum('total');
            $totalCobranzas = $cliente->rentas->flatMap->cobranzas->sum('monto_pagado');
            $deudaPendiente = $cliente->rentas->where('estado', 'activa')->sum('monto_total') - $totalCobranzas;

            return [
                'Nombre/Razón Social' => $cliente->nombre_razon_social,
                'Email' => $cliente->email,
                'Teléfono' => $cliente->telefono,
                'Fecha Registro' => $cliente->created_at->format('Y-m-d'),
                'Total Ventas' => $totalVentas,
                'Total Cobranzas' => $totalCobranzas,
                'Deuda Pendiente' => $deudaPendiente,
                'Número Ventas' => $cliente->ventas->count(),
                'Número Rentas' => $cliente->rentas->count(),
            ];
        });

        // Filtrar según tipo
        if ($tipo === 'activos') {
            $clientes = $clientes->filter(function ($cliente) {
                return $cliente['Número Ventas'] > 0 || $cliente['Número Rentas'] > 0;
            });
        } elseif ($tipo === 'deudores') {
            $clientes = $clientes->filter(function ($cliente) {
                return $cliente['Deuda Pendiente'] > 0;
            });
        }

        return response()->json([
            'data' => $clientes->values()->toArray(),
            'filename' => 'reporte_clientes_' . now()->format('Y-m-d_H-i-s') . '.json'
        ]);
    }

    /**
     * Exportar reporte de inventario a JSON para Excel
     */
    public function exportarInventario(Request $request)
    {
        $categoriaId = $request->get('categoria_id');
        $marcaId = $request->get('marca_id');
        $tipo = $request->get('tipo', 'todos');

        $productosQuery = Producto::with(['categoria', 'marca', 'proveedor']);

        if ($categoriaId) {
            $productosQuery->where('categoria_id', $categoriaId);
        }

        if ($marcaId) {
            $productosQuery->where('marca_id', $marcaId);
        }

        $productos = $productosQuery->get()->map(function ($producto) {
            return [
                'Nombre' => $producto->nombre,
                'Código' => $producto->codigo,
                'Categoría' => $producto->categoria?->nombre,
                'Marca' => $producto->marca?->nombre,
                'Proveedor' => $producto->proveedor?->nombre_razon_social,
                'Stock' => $producto->stock,
                'Stock Mínimo' => $producto->stock_minimo,
                'Precio Compra' => $producto->precio_compra,
                'Precio Venta' => $producto->precio_venta,
                'Estado' => $producto->stock <= 0 ? 'Sin Stock' : ($producto->stock <= $producto->stock_minimo ? 'Bajo' : 'Normal'),
            ];
        });

        // Filtrar según tipo
        if ($tipo === 'bajos') {
            $productos = $productos->filter(function ($p) {
                return $p['Stock'] <= $p['Stock Mínimo'] && $p['Stock'] > 0;
            });
        } elseif ($tipo === 'sin_stock') {
            $productos = $productos->filter(function ($p) {
                return $p['Stock'] <= 0;
            });
        }

        return response()->json([
            'data' => $productos->values()->toArray(),
            'filename' => 'reporte_inventario_' . now()->format('Y-m-d_H-i-s') . '.json'
        ]);
    }

    /**
     * Reporte de ventas pendientes de pago
     */
    public function ventasPendientes(Request $request)
    {
        $search = $request->get('search');
        $estado = $request->get('estado');
        $perPage = $request->get('per_page', 10);

        $ventasQuery = Venta::with(['cliente:id,nombre_razon_social,email'])
            ->where('estado', '!=', 'cancelada')
            ->where('pagado', false)
            ->orderBy('created_at', 'desc');

        // Aplicar filtros
        if ($search) {
            $ventasQuery->where(function($q) use ($search) {
                $q->whereHas('cliente', function($clienteQuery) use ($search) {
                    $clienteQuery->where('nombre_razon_social', 'like', '%' . $search . '%');
                })
                ->orWhere('numero_venta', 'like', '%' . $search . '%');
            });
        }

        if ($estado) {
            if ($estado === 'borrador') {
                $ventasQuery->where('estado', 'borrador');
            } elseif ($estado === 'pendiente') {
                $ventasQuery->where('estado', 'pendiente');
            } elseif ($estado === 'aprobada') {
                $ventasQuery->where('estado', 'aprobada');
            }
        }

        $ventas = $ventasQuery->paginate($perPage);

        // Calcular estadísticas de ventas pendientes de pago
        $ventasPendientes = Venta::where('pagado', false)->where('estado', '!=', 'cancelada');
        $estadisticas = [
            'total' => $ventasPendientes->count(),
            'total_monto' => $ventasPendientes->sum('total'),
            'aprobadas' => $ventasPendientes->where('estado', 'aprobada')->count(),
            'borrador' => $ventasPendientes->where('estado', 'borrador')->count(),
        ];

        return Inertia::render('Reportes/VentasPendientes', [
            'ventas' => $ventas,
            'estadisticas' => $estadisticas,
            'filters' => [
                'search' => $search,
                'estado' => $estado,
            ]
        ]);
    }

    /**
     * Exportar reporte de productos a JSON para Excel
     */
    public function exportarProductos(Request $request)
    {
        $fechaInicio = $request->get('fecha_inicio', now()->subYear()->format('Y-m-d'));
        $fechaFin = $request->get('fecha_fin', now()->format('Y-m-d'));
        $categoriaId = $request->get('categoria_id');
        $marcaId = $request->get('marca_id');

        $productosQuery = Producto::with(['categoria', 'marca']);

        if ($categoriaId) {
            $productosQuery->where('categoria_id', $categoriaId);
        }

        if ($marcaId) {
            $productosQuery->where('marca_id', $marcaId);
        }

        $productos = $productosQuery->get()->map(function ($producto) use ($fechaInicio, $fechaFin) {
            // Obtener estadísticas de ventas para este producto en el período
            $ventaItemsQuery = \App\Models\VentaItem::with('venta')
                ->where('ventable_type', \App\Models\Producto::class)
                ->where('ventable_id', $producto->id);

            // Solo filtrar por fechas si están especificadas
            if ($fechaInicio && $fechaFin) {
                $ventaItemsQuery->whereHas('venta', function($q) use ($fechaInicio, $fechaFin) {
                    $q->whereBetween('fecha', [$fechaInicio, $fechaFin]);
                });
            }

            $ventaItems = $ventaItemsQuery->get();

            $cantidadVendida = $ventaItems->sum('cantidad');
            $totalVendido = $ventaItems->sum(function ($item) {
                return ($item->precio - ($item->descuento ?? 0)) * $item->cantidad;
            });
            $costoTotal = $ventaItems->sum(function ($item) use ($producto) {
                return ($item->costo_unitario ?? $producto->precio_compra) * $item->cantidad;
            });
            $ganancia = $totalVendido - $costoTotal;
            $numeroVentas = $ventaItems->groupBy('venta_id')->count();

            return [
                'Nombre' => $producto->nombre,
                'Código' => $producto->codigo,
                'Categoría' => $producto->categoria?->nombre,
                'Marca' => $producto->marca?->nombre,
                'Precio Compra' => $producto->precio_compra,
                'Precio Venta' => $producto->precio_venta,
                'Stock' => $producto->stock,
                'Cantidad Vendida' => $cantidadVendida,
                'Total Vendido' => $totalVendido,
                'Costo Total' => $costoTotal,
                'Ganancia' => $ganancia,
                'Número Ventas' => $numeroVentas,
            ];
        })->sortByDesc('Total Vendido')->values();

        return response()->json([
            'data' => $productos->toArray(),
            'filename' => 'reporte_productos_' . now()->format('Y-m-d_H-i-s') . '.json'
        ]);
    }
}
