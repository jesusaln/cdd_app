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
}
