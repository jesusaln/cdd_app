<?php

// app/Http/Controllers/ReporteController.php

namespace App\Http\Controllers;

use App\Models\Reporte;
use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\Venta;
use App\Models\Compra;
use App\Models\Producto;

class ReporteController extends Controller
{
    public function index()
    {
        // Reporte de Ventas
        $ventas = Venta::with('productos')->get();
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
        // Reporte de Compras
        $compras = Compra::with('productos')->get();
        $totalCompras = $compras->sum('total');

        // Reporte de Inventarios
        $productos = Producto::all();

        return Inertia::render('Reportes/Index', [
            'reportesVentas' => $ventas,
            'corteVentas' => $corteVentas,
            'utilidadVentas' => $utilidadVentas,
            'reportesCompras' => $compras,
            'totalCompras' => $totalCompras,
            'inventario' => $productos,
        ]);
    }

    public function ventas()
    {
        // Obtener todas las ventas con sus productos
        $ventas = Venta::with('productos')->get();

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

        // Calcular totales por método de pago
        $totalesPorMetodo = [
            'efectivo' => 0,
            'transferencia' => 0,
            'cheque' => 0,
            'tarjeta' => 0,
            'otros' => 0,
        ];

        $totalGeneral = 0;

        foreach ($ventasPagadas as $venta) {
            $metodo = $venta->metodo_pago ?? 'otros';
            if (isset($totalesPorMetodo[$metodo])) {
                $totalesPorMetodo[$metodo] += $venta->total;
            } else {
                $totalesPorMetodo['otros'] += $venta->total;
            }
            $totalGeneral += $venta->total;
        }

        // Formatear datos para la vista
        $ventasFormateadas = $ventasPagadas->map(function ($venta) {
            return [
                'id' => $venta->id,
                'numero_venta' => $venta->numero_venta,
                'cliente' => $venta->cliente->nombre_razon_social ?? 'Sin cliente',
                'total' => $venta->total,
                'metodo_pago' => $venta->metodo_pago,
                'fecha_pago' => $venta->fecha_pago ? $venta->fecha_pago->format('Y-m-d H:i') : null,
                'notas_pago' => $venta->notas_pago,
                'pagado_por' => $venta->pagadoPor?->name ?? 'Sistema',
            ];
        });

        return Inertia::render('Reportes/CorteDiario', [
            'ventasPagadas' => $ventasFormateadas,
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

        // Calcular totales por método de pago
        $totalesPorMetodo = [
            'efectivo' => 0,
            'transferencia' => 0,
            'cheque' => 0,
            'tarjeta' => 0,
            'otros' => 0,
        ];

        $totalGeneral = 0;

        foreach ($ventasPagadas as $venta) {
            $metodo = $venta->metodo_pago ?? 'otros';
            if (isset($totalesPorMetodo[$metodo])) {
                $totalesPorMetodo[$metodo] += $venta->total;
            } else {
                $totalesPorMetodo['otros'] += $venta->total;
            }
            $totalGeneral += $venta->total;
        }

        // Para este ejemplo, devolveremos JSON que puede ser usado por JavaScript para generar Excel
        // En un entorno real, usaríamos librerías como Laravel Excel o Maatwebsite Excel

        $data = [
            'periodo' => $periodo,
            'fecha' => $fecha,
            'fecha_inicio' => $fecha_inicio,
            'fecha_fin' => $fecha_fin,
            'total_general' => $totalGeneral,
            'totales_por_metodo' => $totalesPorMetodo,
            'ventas' => $ventasPagadas->map(function ($venta) {
                return [
                    'numero_venta' => $venta->numero_venta,
                    'cliente' => $venta->cliente->nombre_razon_social ?? 'Sin cliente',
                    'metodo_pago' => $venta->metodo_pago,
                    'total' => $venta->total,
                    'fecha_pago' => $venta->fecha_pago ? $venta->fecha_pago->format('Y-m-d H:i:s') : null,
                    'notas_pago' => $venta->notas_pago,
                    'pagado_por' => $venta->pagadoPor?->name ?? 'Sistema',
                ];
            })->toArray()
        ];

        return response()->json($data);
    }
}
