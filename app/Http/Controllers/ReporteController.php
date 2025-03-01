<?php

// app/Http/Controllers/ReporteController.php

namespace App\Http\Controllers;

use App\Models\Reporte;
use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\Venta;
use App\Models\Compra;

class ReporteController extends Controller
{
    public function index()
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
